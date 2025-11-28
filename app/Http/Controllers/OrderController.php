<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Hold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'hold_id' => 'required|exists:holds,id'
        ]);

        return DB::transaction(function () use ($request) {
            $hold = Hold::lockForUpdate()->find($request->hold_id);

            // Validate hold is not expired and not already used
            if ($hold->expires_at->isPast()) {
                return response()->json(['error' => 'Hold has expired'], 400);
            }

            if ($hold->used) {
                return response()->json(['error' => 'Hold has already been used'], 400);
            }

            $order = Order::create([
                'hold_id' => $hold->id,
                'status' => 'pending',
            ]);

            $hold->update(['used' => true]);

            // Clear stock cache
            $hold->product->clearStockCache();

            return response()->json([
                'order_id' => $order->id,
                'status' => $order->status
            ]);
        });
    }
}
