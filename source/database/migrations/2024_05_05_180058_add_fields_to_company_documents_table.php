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
        Schema::table('company_documents', function (Blueprint $table) {
            $table->tinyInteger('is_contract')->default(1)->after('file');
            $table->json('meta')->nullable()->after('is_contract');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_documents', function (Blueprint $table) {
            $table->dropColumn('is_contract');
            $table->dropColumn('meta');
        });
    }
};
