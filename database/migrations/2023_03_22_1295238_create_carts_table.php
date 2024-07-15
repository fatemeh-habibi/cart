<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->unsignedMediumInteger('id',true)->comment('Cart ID');
            $table->unsignedTinyInteger('store_id')->nullable()->comment('Store ID')->index();
            $table->unsignedMediumInteger('customer_id')->nullable()->comment('Customer ID')->unique();
            $table->smallInteger('delivery_id')->nullable()->comment('Delivery ID')->index();
            $table->decimal('total_discount', $precision = 12, $scale = 0)->default(0)->comment('Cart Total Discount');
            $table->decimal('total_shipment_weight', $precision = 12, $scale = 0)->default(0)->comment('Shipment Weight In Tonne');
            $table->enum('invoice_type', ['A', 'B'])->index();
            $table->tinyInteger('discount_type')->default(0)->comment('0 value, 1 percent');
            $table->tinyInteger('payment_method')->default(0)->comment('0 cash, 1 validation');
            $table->text('comment')->nullable()->comment('Cart Comment');
            $table->string('ip',40)->nullable()->comment('Remote IP Address');
            $table->string('forwarded_ip',40)->nullable()->comment('Forwarded Remote IP Address');
            $table->string('token',255)->nullable()->comment('Cart Token for auto payment');
            $table->unsignedSmallInteger('created_user_id')->comment('ID of the user created a record');
            $table->unsignedSmallInteger('updated_user_id')->nullable()->comment('ID of the user has performed last update');
            $table->index('created_user_id');
            $table->index('updated_user_id');
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carts');
    }
}