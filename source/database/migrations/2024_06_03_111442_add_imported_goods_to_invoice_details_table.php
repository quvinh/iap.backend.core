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
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->decimal('icp_price', 12, 2)->default(1)->after('note')->comment('international commodity prices');
            $table->decimal('isf_price', 12, 2)->default(1)->after('icp_price')->comment('international shipping fee');
            $table->float('import_tax')->default(0)->after('isf_price'); # Thuế nhập khẩu
            $table->float('special_consumption_tax')->default(0)->after('import_tax'); # Thuế TTĐB
            $table->string('customs_code')->nullable()->after('item_code_id'); # Mã hải quan
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->dropColumn('icp_price');
            $table->dropColumn('isf_price');
            $table->dropColumn('import_tax');
            $table->dropColumn('special_consumption_tax');
            $table->dropColumn('customs_code');
        });
    }
};
