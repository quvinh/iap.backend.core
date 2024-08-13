<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->tinyInteger('is_imported_goods')->default(0)->after('locked');
            $table->string('icp_currency')->nullable()->after('is_imported_goods')->comment('international commodity prices');
            $table->decimal('icp_currency_price', 12, 2)->default(0)->after('icp_currency')->comment('international commodity prices');
            $table->string('isf_currency')->nullable()->after('icp_currency')->comment('international shipping fee');
            $table->decimal('isf_currency_price', 12, 2)->default(0)->after('isf_currency')->comment('international shipping fee');
            $table->decimal('isf_sum_fee', 12, 2)->default(0)->after('isf_currency_price')->comment('international shipping fee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('is_imported_goods');
            $table->dropColumn('icp_currency');
            $table->dropColumn('icp_currency_price');
            $table->dropColumn('isf_currency');
            $table->dropColumn('isf_currency_price');
            $table->dropColumn('isf_sum_fee');
        });
    }
};
