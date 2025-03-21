<?php

use App\Helpers\Enums\CategoryActions;
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
        Schema::create('first_arise_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('number_account', 20)->nullable();
            $table->boolean('number_percent')->default(1);
            $table->char('method', 10)->default(CategoryActions::PLUS);
            $table->string('note')->nullable();
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('first_arise_accounts');
    }
};
