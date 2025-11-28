<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\Hold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $request->validate([
            'idempotency_key' => 'required|string',
            'order_id' => 'required|integer',
            'status' => 'required|string',
        ]);

        return DB::transaction(function () use ($request) {
            // Check if already processed
            $existing = PaymentLog::where('idempotency_key', $request->idempotency_key)->first();
            if ($existing) {
                return response()->json(['message' => 'Already processed']);
            }

            // Find or create order if webhook arrives before order creation
            $order = Order::lockForUpdate()->find($request->order_id);
            if (!$order) {
                // Webhook arrived before order creation - create order in appropriate state
                $hold = Hold::where('id', $request->order_id)->first();
                if (!$hold) {
                    return response()->json(['error' => 'Invalid order/hold ID'], 400);
                }

                $order = Order::create([
                    'hold_id' => $hold->id,
                    'status' => $request->status === 'success' ? 'paid' : 'canceled',
                ]);

                $hold->update(['used' => true]);
            }

            // Store log
            PaymentLog::create([
                'idempotency_key' => $request->idempotency_key,
                'order_id' => $order->id,
                'status' => $request->status,
                'raw_payload' => $request->all(),
            ]);

            // Update order status if not already in final state
            if ($order->status === 'pending') {
                if ($request->status === 'success') {
                    $order->update(['status' => 'paid']);
                } else {
                    $order->update(['status' => 'canceled']);
                }
            }

            return response()->json(['message' => 'Processed']);
        });
    }
}
