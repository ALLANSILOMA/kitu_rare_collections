<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('mpesa_transaction_id')->nullable()->unique()->after('payment_status');
            $table->string('payment_phone_number')->nullable()->after('mpesa_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['mpesa_transaction_id', 'payment_phone_number']);
        });
    }
};
