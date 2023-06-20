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
        Schema::create('company_detail_tax_free_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_detail_id');
            $table->unsignedBigInteger('tax_free_voucher_id');

            $table->foreign('company_detail_id')->references('id')->on('company_details');
            $table->foreign('tax_free_voucher_id')->references('id')->on('tax_free_vouchers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_detail_tax_free_vouchers');
    }
};
