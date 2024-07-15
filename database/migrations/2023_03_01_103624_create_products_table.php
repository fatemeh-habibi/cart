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
        Schema::create('products', function (Blueprint $table) {
            $table->unsignedMediumInteger('id',true)->comment('Product ID');
            $table->unsignedBigInteger('category_id')->nullable()->comment('Category ID');
            $table->unsignedTinyInteger('type')->nullable()->comment('(1 simple, 2 detail, 3 bundle)');
            $table->unsignedTinyInteger('activated')->default('0')->comment('Activation (0 hidden, 1 show)');
            $table->unsignedSmallInteger('viewed')->nullable()->comment('Count OF View by Visitor');
            $table->string('sku',25)->nullable()->comment('Product Stock Keeping Unit')->index();
            $table->string('sku_alias',25)->nullable()->comment('Product Stock Keeping Unit For Supplier');
            $table->unsignedBigInteger('upc')->nullable()->comment('Universal Product Code');
            $table->string('mpn',40)->nullable()->comment('Manufacturer Part Number');
            $table->decimal('cost', $precision = 12, 0)->default('0')->comment('Total Cost');
            $table->boolean('home_show')->default(false)->comment('Home Show (0 hidden, 1 show)');
            $table->unsignedSmallInteger('quantity')->default('0')->comment('Product Quantity');
            $table->unsignedSmallInteger('stock')->default('0')->comment('Product Stock Quantity');
            $table->string('note',500)->nullable()->comment('Extra Note For owner or admin');
            $table->unsignedTinyInteger('status_id')->comment('Product Status ID')->index();
            $table->unsignedMediumInteger('sort_order')->nullable()->comment('Product display priority');
            $table->unsignedTinyInteger('low_stock_alert')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedSmallInteger('created_user_id')->comment('ID of the user created a record')->index();
            $table->unsignedSmallInteger('updated_user_id')->nullable()->comment('ID of the user has performed last update')->index();
            $table->timestamps();
            $table->softDeletes()->comment('Date of soft delete setting');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
