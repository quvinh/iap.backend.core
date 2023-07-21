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
        Schema::create('formula_category_solds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('formula_id');
            $table->unsignedBigInteger('category_sold_id');
            $table->float('value_from')->default(0);
            $table->float('value_to')->default(0);
            $table->float('value_avg')->default(0);
            $table->string('active_path', 100)->nullable();
            // $table->boolean('visible_value')->default(1);

            $table->foreign('formula_id')->references('id')->on('formulas');
            $table->foreign('category_sold_id')->references('id')->on('category_solds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formula_category_solds');
    }
};
