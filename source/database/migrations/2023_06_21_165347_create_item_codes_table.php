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
        Schema::create('item_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('product_code');
            $table->string('product_exchange')->default('product_exchange');
            $table->string('product')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->float('quantity')->default(0);
            $table->decimal('begining_total_value', 12, 2)->default(0);
            $table->string('unit', 100)->default('unit');
            $table->string('year', 4);
            $table->boolean('status')->default(1);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_codes');
    }
};
