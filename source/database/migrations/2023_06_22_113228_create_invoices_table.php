<?php

use App\Helpers\Enums\InvoiceCurrencies;
use App\Helpers\Enums\InvoiceNumberForms;
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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id()->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('invoice_task_id');
            $table->string('type', 10)->index();
            $table->date('date');
            $table->string('invoice_symbol', 20)->index();
            $table->tinyInteger('invoice_number_form')->default(InvoiceNumberForms::VALUE_ADDED);
            $table->bigInteger('invoice_number')->index();
            $table->tinyInteger('property')->default(0); # tchat
            $table->text('note')->nullable();
            $table->string('partner_name')->nullable();
            $table->string('partner_tax_code', 60)->index();
            $table->string('partner_address')->nullable();
            $table->string('currency', 3)->default(InvoiceCurrencies::VND);
            $table->float('currency_price')->default(1);
            $table->decimal('sum_money_no_vat', 12, 2)->default(0);
            $table->decimal('sum_money_vat', 12, 2)->default(0);
            $table->decimal('sum_money_discount', 12, 2)->default(0);
            $table->decimal('sum_money', 12, 2)->default(0);
            $table->boolean('rounding')->default(1);
            $table->string('payment_method', 30)->default('TM/CK');
            $table->string('verification_code')->nullable();
            $table->boolean('verification_code_status')->default(1); # default -> co ma co quan thue
            $table->json('json')->nullable();
            $table->tinyInteger('status')->default(0); # 0 - chua xu ly; 1 - da xu ly
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('invoice_task_id')->references('id')->on('invoice_tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
