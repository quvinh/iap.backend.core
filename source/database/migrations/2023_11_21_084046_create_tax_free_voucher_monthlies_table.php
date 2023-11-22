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
        Schema::create('tax_free_voucher_monthlies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_detail_tax_id');
            $table->string('number_account', 10);
            $table->decimal('original_price', 12, 2)->default(0);
            $table->integer('amount_month')->default(0);
            $table->json('meta')->nullable(); # Monthly 1 -> 12
            $table->json('year_end_balance')->nullable();

            $table->foreign('company_detail_tax_id')->references('id')->on('company_detail_tax_free_vouchers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_free_voucher_monthlies');
    }
};
