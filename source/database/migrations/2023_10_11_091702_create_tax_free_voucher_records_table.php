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
        Schema::create('tax_free_voucher_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tax_free_voucher_id');
            $table->unsignedBigInteger('company_detail_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('count_month')->default(0);
            $table->integer('start_month');
            $table->integer('end_month');
            $table->json('json')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('tax_free_voucher_id')->references('id')->on('tax_free_vouchers')->onDelete('cascade');
            $table->foreign('company_detail_id')->references('id')->on('company_details')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_free_voucher_records');
    }
};
