<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaxpayRefundCallbackDataToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('maxpay_refund_callback_data')->after('maxpay_refund_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('orders', 'maxpay_refund_callback_data')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('maxpay_refund_callback_data');
            });
        }
    }
}
