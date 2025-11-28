<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Flash Sale Product',
            'price' => 199.99,
            'stock' => 50, 
        ]);
    }
}
