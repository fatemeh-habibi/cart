<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_products', function (Blueprint $table) {
            $table->unsignedMediumInteger('id',true)->comment('Cart Product ID');
            $table->unsignedMediumInteger('cart_id')->comment('Shopping Cart ID');
            $table->unsignedMediumInteger('product_id')->comment('Product Id');
            $table->unsignedTinyInteger('quantity')->default(1)->comment('Quantity of product in cart');
            $table->decimal('discount', $precision = 12, $scale = 0)->default(0)->comment('Discount value set by admin for product');
            $table->tinyInteger('discount_type')->default(0)->comment('0 value, 1 percent');
            $table->unsignedSmallInteger('created_user_id')->comment('ID of the user created a record');
            $table->unsignedSmallInteger('updated_user_id')->nullable()->comment('ID of the user has performed last update');
            $table->index('created_user_id');
            $table->index('updated_user_id');
            $table->timestamps();
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
        });
    }
 
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_products');
    }
}
