<?php

use App\Helpers\Enums\AriseAccountTypes;
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
        Schema::table('first_arise_accounts', function (Blueprint $table) {
            $table->tinyInteger('is_tracking')->default(AriseAccountTypes::NONE); # Theo doi/phan bo
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('first_arise_accounts', function (Blueprint $table) {
            $table->dropColumn('is_tracking');
        });
    }
};
