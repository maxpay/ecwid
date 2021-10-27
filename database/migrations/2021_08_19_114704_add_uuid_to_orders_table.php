<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUuidToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('orders', 'reference_transaction_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('reference_transaction_id');
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->char('uuid', 32)->after('id');
            $table->unique(['uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        exit('This migration can not be reverted');
    }
}
