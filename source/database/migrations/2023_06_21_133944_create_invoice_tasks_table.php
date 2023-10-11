<?php

use App\Helpers\Enums\TaskStatus;
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
        Schema::create('invoice_tasks', function (Blueprint $table) {
            $table->id()->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('month_of_year', 7)->index();
            $table->string('task_import', 20)->default(TaskStatus::IN_PROGRESS);
            $table->string('task_progress', 20)->default(TaskStatus::NOT_YET_STARTED);
            $table->string('note')->nullable();
            $table->decimal('opening_balance_value', 12, 2)->default(0);
            $table->decimal('total_money_sold', 12, 2)->default(0);
            $table->decimal('total_money_purchase', 12, 2)->default(0);
            // $table->decimal('ending_balance_value', 12, 2)->default(0);
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
        Schema::dropIfExists('invoice_tasks');
    }
};
