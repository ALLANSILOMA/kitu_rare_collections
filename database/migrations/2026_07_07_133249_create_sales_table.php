<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->nullOnDelete();
            $table->string('order_reference');
            $table->string('customer_name');
            $table->string('variation_label')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('buying_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('total_revenue', 10, 2); // selling_price * qty + shipping
            $table->decimal('total_cost', 10, 2); //buying_price * qty
            $table->decimal('profit', 10, 2);
            $table->string('payment_method')->default('M-Pesa');
            $table->string('shipping_method')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
