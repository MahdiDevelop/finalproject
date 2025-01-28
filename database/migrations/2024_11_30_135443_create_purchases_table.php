<?php use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stocks_id')->constrained('stocks')->onDelete('cascade');
            $table->integer('qty');
            $table->decimal('weight', 8, 2);
            $table->timestamp('dateInsert')->nullable();
            $table->decimal('rate', 8, 2);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('isdelete')->default(false);
            $table->decimal('purchase_price', 8, 2);
            $table->decimal('sell_price', 8, 2);
            $table->timestamp('expiry_date')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('accounts_id')->constrained('accounts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}