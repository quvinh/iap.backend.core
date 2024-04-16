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
        Schema::table('companies', function (Blueprint $table) {
            $table->dateTime('registered_date')->after('status')->nullable();
            $table->string('registration_file')->after('status')->nullable();
            $table->string('place_of_registration')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('registered_date');
            $table->dropColumn('registration_file');
            $table->dropColumn('place_of_registration');
        });
    }
};
