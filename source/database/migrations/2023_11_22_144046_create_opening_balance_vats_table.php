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
        Schema::create('opening_balance_vats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_detail_id');
            $table->integer('count_month')->default(1);
            $table->integer('start_month');
            $table->integer('end_month');
            $table->decimal('money', 12, 2)->default(0);
            $table->json('meta');

            $table->foreign('company_detail_id')->references('id')->on('company_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opening_balance_vats');
    }
};
