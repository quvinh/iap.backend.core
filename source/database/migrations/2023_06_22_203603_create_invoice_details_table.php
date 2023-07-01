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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('formula_commodity_id')->nullable();
            $table->unsignedBigInteger('formula_material_id')->nullable();
            $table->unsignedBigInteger('item_code_id')->nullable();
            $table->string('formula_path_id')->nullable(); // category | formula
            $table->string('product');
            $table->string('unit', 100);
            $table->float('quantity')->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('vat')->default(0);
            $table->decimal('vat_money', 12, 2)->default(0);
            $table->decimal('total_money', 12, 2)->default(0); // price * quantity (novat)
            $table->boolean('warehouse')->default(0);
            $table->boolean('main_entity')->default(1);
            $table->boolean('visible')->default(1);
            $table->string('note')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_details');
    }
};
