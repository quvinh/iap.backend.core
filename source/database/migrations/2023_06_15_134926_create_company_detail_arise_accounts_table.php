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
        Schema::create('company_detail_arise_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_detail_id');
            $table->unsignedBigInteger('arise_account_id');
            $table->float('value_from')->default(0);
            $table->float('value_to')->default(0);
            $table->float('value_avg')->default(0);
            // $table->boolean('visible_value')->default(1);

            $table->foreign('company_detail_id')->references('id')->on('company_details');
            $table->foreign('arise_account_id')->references('id')->on('first_arise_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_detail_arise_accounts');
    }
};
