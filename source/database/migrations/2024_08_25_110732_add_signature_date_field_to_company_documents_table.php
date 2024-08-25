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
            $table->dateTime('signature_date')->nullable()->after('is_contract');
            $table->dateTime('expiry_date')->nullable()->after('signature_date');
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
            $table->dropColumn('signature_date');
            $table->dropColumn('expiry_date');
        });
    }
};
