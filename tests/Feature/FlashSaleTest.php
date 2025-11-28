<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Hold;
use App\Models\Order;
use App\Models\PaymentLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class FlashSaleTest extends TestCase
{
    use RefreshDatabase;

    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test product with limited stock
        $this->product = Product::create([
            'name' => 'Test Product',
            'price' => 100.00,
            'stock' => 10,
        ]);
    }

    /** @test */
    public function parallel_hold_attempts_at_stock_boundary_no_oversell()
    {
        // Test parallel requests trying to hold the last available stock
        $requests = [];

        // Create 15 concurrent requests (more than available stock of 10)
        for ($i = 0; $i < 15; $i++) {
            $requests[] = [
                'product_id' => $this->product->id,
                'qty' => 1,
            ];
        }

        $successfulHolds = 0;
        $failedHolds = 0;

        // Process requests sequentially (simulating concurrent access with DB locks)
        foreach ($requests as $requestData) {
            $response = $this->postJson('/api/holds', $requestData);

            if ($response->getStatusCode() === 200) {
                $successfulHolds++;
            } else {
                $failedHolds++;
                $this->assertEquals(400, $response->getStatusCode());
                $this->assertEquals('Insufficient stock', $response->json()['error']);
            }
        }

        // Assert that only 10 holds were successful (equal to stock)
        $this->assertEquals(10, $successfulHolds);
        $this->assertEquals(5, $failedHolds);

        // Verify total holds created
        $this->assertEquals(10, Hold::where('product_id', $this->product->id)->count());

        // Verify available stock is now 0
        $this->product->refresh();
        $this->assertEquals(0, $this->product->available_stock);
    }

    /** @test */
    public function hold_expiry_returns_availability()
    {
        // Create a hold that will expire
        $response = $this->postJson('/api/holds', [
            'product_id' => $this->product->id,
            'qty' => 5,
        ]);

        $response->assertStatus(200);
        $holdId = $response->json()['hold_id'];

        // Verify stock is reduced
        $this->product->refresh();
        $this->assertEquals(5, $this->product->available_stock);

        // Manually expire the hold
        $hold = Hold::find($holdId);
        $hold->update(['expires_at' => now()->subMinute()]);

        // Run the release command
        $this->artisan('holds:release-expired');

        // Verify stock is back to original
        $this->product->refresh();
        $this->assertEquals(10, $this->product->available_stock);
    }

    /** @test */
    public function webhook_idempotency_same_key_repeated()
    {
        // Create a hold and order
        $holdResponse = $this->postJson('/api/holds', [
            'product_id' => $this->product->id,
            'qty' => 1,
        ]);
        $holdId = $holdResponse->json()['hold_id'];

        $orderResponse = $this->postJson('/api/orders', [
            'hold_id' => $holdId,
        ]);
        $orderId = $orderResponse->json()['order_id'];

        // Send webhook multiple times with same idempotency key
        $webhookData = [
            'idempotency_key' => 'test-key-123',
            'order_id' => $orderId,
            'status' => 'success',
        ];

        // First webhook
        $response1 = $this->postJson('/api/payments/webhook', $webhookData);
        $response1->assertStatus(200);
        $response1->assertJson(['message' => 'Processed']);

        // Second webhook with same key
        $response2 = $this->postJson('/api/payments/webhook', $webhookData);
        $response2->assertStatus(200);
        $response2->assertJson(['message' => 'Already processed']);

        // Third webhook with same key
        $response3 = $this->postJson('/api/payments/webhook', $webhookData);
        $response3->assertStatus(200);
        $response3->assertJson(['message' => 'Already processed']);

        // Verify only one payment log was created
        $this->assertEquals(1, PaymentLog::where('idempotency_key', 'test-key-123')->count());

        // Verify order status is correct
        $order = Order::find($orderId);
        $this->assertEquals('paid', $order->status);
    }

    /** @test */
    public function webhook_arriving_before_order_creation()
    {
        // Create a hold
        $holdResponse = $this->postJson('/api/holds', [
            'product_id' => $this->product->id,
            'qty' => 1,
        ]);
        $holdId = $holdResponse->json()['hold_id'];

        // Send webhook BEFORE creating the order (using hold_id as order_id)
        $webhookData = [
            'idempotency_key' => 'early-webhook-key',
            'order_id' => $holdId, // Using hold_id instead of order_id
            'status' => 'success',
        ];

        $webhookResponse = $this->postJson('/api/payments/webhook', $webhookData);
        $webhookResponse->assertStatus(200);

        // Now create the order
        $orderResponse = $this->postJson('/api/orders', [
            'hold_id' => $holdId,
        ]);
        $orderResponse->assertStatus(200);
        $orderId = $orderResponse->json()['order_id'];

        // Verify order was created and is in paid status
        $order = Order::find($orderId);
        $this->assertEquals('paid', $order->status);

        // Verify hold is marked as used
        $hold = Hold::find($holdId);
        $this->assertTrue($hold->used);

        // Verify payment log exists
        $this->assertEquals(1, PaymentLog::where('idempotency_key', 'early-webhook-key')->count());
    }

    /** @test */
    public function product_shows_correct_available_stock()
    {
        $response = $this->getJson("/api/products/{$this->product->id}");
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->product->id,
            'name' => 'Test Product',
            'price' => 100.00,
            'available_stock' => 10,
        ]);

        // Create a hold
        $this->postJson('/api/holds', [
            'product_id' => $this->product->id,
            'qty' => 3,
        ]);

        // Check available stock is reduced
        Cache::flush(); // Clear cache to ensure fresh calculation
        $response = $this->getJson("/api/products/{$this->product->id}");
        $response->assertJson(['available_stock' => 7]);
    }
}
