<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('qty');
            $table->dateTime('expires_at');
            $table->boolean('used')->default(false); // becomes true when converted to order
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holds');
    }
};
