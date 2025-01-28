<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned()->primary(); // ID به صورت کلید اصلی و unsigned
            $table->timestamp('dateInsert')->nullable(); // تاریخ ورود
            $table->bigInteger('total')->unsigned(); // مبلغ کل
            $table->bigInteger('PaidAmount')->unsigned(); // مبلغ پرداختی
            $table->bigInteger('Remain')->unsigned(); // مبلغ باقی‌مانده
                    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bills');
    }
}
