<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'stock',
    ];

    public function holds()
    {
        return $this->hasMany(Hold::class);
    }

    public function getAvailableStockAttribute()
    {
        return Cache::remember(
            "product_{$this->id}_available_stock",
            60, // cache for 1 minute
            function () {
                $activeHoldsQty = $this->holds()
                    ->where('expires_at', '>', now())
                    ->where('used', false)
                    ->sum('qty');

                return max(0, $this->stock - $activeHoldsQty);
            }
        );
    }

    public function clearStockCache()
    {
        Cache::forget("product_{$this->id}_available_stock");
    }
}
