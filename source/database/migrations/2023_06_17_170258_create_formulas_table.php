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
        Schema::create('formulas', function (Blueprint $table) {
            $table->id()->index();
            $table->string('name');
            $table->unsignedBigInteger('company_detail_id');
            $table->unsignedBigInteger('company_type_id');
            $table->float('sum_from')->default(0);
            $table->float('sum_to')->default(0);
            $table->float('sum_avg')->default(0);
            $table->boolean('status')->default(1);
            $table->string('note')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('company_detail_id')->references('id')->on('company_details');
            $table->foreign('company_type_id')->references('id')->on('company_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formulas');
    }
};
