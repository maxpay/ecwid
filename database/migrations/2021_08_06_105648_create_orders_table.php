<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration CreateOrdersTable class.
 */
class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('store_id');
            $table->bigInteger('order_number');
            $table->string('reference_transaction_id');
            $table->string('token');
            $table->json('order_data');
            $table->json('payment_data')->nullable();
            $table->smallInteger('update_status_http_code')->nullable();
            $table->json('update_status_data')->nullable();
            $table->timestamps();

            $table->index(['reference_transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}
