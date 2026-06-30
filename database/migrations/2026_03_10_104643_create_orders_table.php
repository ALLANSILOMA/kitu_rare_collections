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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('product_id')->nullable()->constrained('products');

            // Contact & Shipping Info
            $table->string('email');
            $table->string('first_name');
            $table->string('last_name');
            $table->text('shipping_address');
            $table->string('city');
            $table->string('phone');

            // The "Essence" of your UI: Capturing the Zone
            $table->string('shipping_method_name'); // e.g., "ZONE A: Within CBD"
            $table->decimal('shipping_cost', 10, 2);
            $table->string('pickup_agent_details')->nullable(); // For "Pick Up Mtaani location & agent"
            $table->enum('status',['pending', 'new', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');// pending, processing, shipped, delivered


            // Financials
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method')->default('paypal');
            $table->string('payment_status')->default('pending');

            $table->timestamps();
        });    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
