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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->dateTime('date')->nullable();
            $table->dateTime('date_started')->nullable();
            $table->dateTime('date_ended')->nullable();
            $table->integer('number_of_months')->default(0);
            $table->tinyInteger('status');
            $table->mediumText('template')->nullable();
            $table->json('meta')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('template_id')->references('id')->on('templates');
            $table->foreign('parent_id')->references('id')->on('contracts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracts');
    }
};
