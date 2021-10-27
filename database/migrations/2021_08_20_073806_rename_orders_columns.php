<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameOrdersColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('order_data', 'ecwid_order_data');
            $table->renameColumn('payment_data_code', 'maxpay_payment_data_code');
            $table->renameColumn('payment_data', 'maxpay_payment_data');
            $table->renameColumn('update_status_http_code', 'ecwid_update_status_http_code');
            $table->renameColumn('update_status_data', 'ecwid_update_status_data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('ecwid_order_data', 'order_data');
            $table->renameColumn('maxpay_payment_data_code', 'payment_data_code');
            $table->renameColumn('maxpay_payment_data', 'payment_data');
            $table->renameColumn('ecwid_update_status_http_code', 'update_status_http_code');
            $table->renameColumn('ecwid_update_status_data', 'update_status_data');
        });
    }
}
