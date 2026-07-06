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
        Schema::table('subscription_orders', function (Blueprint $table) {
            $table->string('pakasir_order_id')->nullable()->after('payment_method_id');
            $table->string('pakasir_payment_number')->nullable()->after('pakasir_order_id');
            $table->decimal('total_payment', 12, 2)->nullable()->after('pakasir_payment_number');
            $table->unsignedBigInteger('payment_method_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_orders', function (Blueprint $table) {
            $table->dropColumn(['pakasir_order_id', 'pakasir_payment_number', 'total_payment']);
        });
    }
};
