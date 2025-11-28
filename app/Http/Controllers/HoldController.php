<?php

namespace App\Http\Controllers;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HoldController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1'
        ]);

        return DB::transaction(function () use ($request) {
            $product = Product::lockForUpdate()->find($request->product_id);

            // Check available stock (calculate directly, not from cache)
            $activeHoldsQty = $product->holds()
                ->where('expires_at', '>', now())
                ->where('used', false)
                ->sum('qty');
            $availableStock = max(0, $product->stock - $activeHoldsQty);

            if ($availableStock < $request->qty) {
                return response()->json(['error' => 'Insufficient stock'], 400);
            }

            $hold = Hold::create([
                'product_id' => $product->id,
                'qty' => $request->qty,
                'expires_at' => Carbon::now()->addMinutes(2),
            ]);

            // Clear stock cache
            $product->clearStockCache();

            return response()->json([
                'hold_id' => $hold->id,
                'expires_at' => $hold->expires_at
            ]);
        });
    }
}
