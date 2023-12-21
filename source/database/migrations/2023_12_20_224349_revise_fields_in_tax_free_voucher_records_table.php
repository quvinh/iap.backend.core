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
        Schema::table('tax_free_voucher_records', function (Blueprint $table) {
            # Delete foreign keys
            $table->dropForeign(['tax_free_voucher_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::table('tax_free_voucher_records', function (Blueprint $table) {
            # Delete field: tax_free_voucher_id
            $table->dropColumn('tax_free_voucher_id');
            # Delete field: user_id
            $table->dropColumn('user_id');
            # Rename json to meta column
            $table->renameColumn('json', 'meta');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tax_free_voucher_records', function (Blueprint $table) {
            # Rollback field: tax_free_voucher_id
            $table->unsignedBigInteger('tax_free_voucher_id');
            # Rollback field: user_id
            $table->unsignedBigInteger('user_id');
            # Rollback rename
            $table->renameColumn('meta', 'json');
        });
        Schema::table('tax_free_voucher_records', function (Blueprint $table) {
            # Rollback delete foreign keys
            $table->foreign('tax_free_voucher_id')->references('id')->on('tax_free_vouchers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
