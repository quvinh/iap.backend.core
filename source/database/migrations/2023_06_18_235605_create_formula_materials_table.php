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
        Schema::create('formula_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('formula_id');
            $table->string('name');
            $table->float('value_from')->default(0);
            $table->float('value_to')->default(0);
            $table->float('value_avg')->default(0);
            $table->boolean('status')->default(1);
            $table->string('note')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('formula_id')->references('id')->on('formulas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formula_materials');
    }
};
