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
        Schema::create('products_lang', function (Blueprint $table) {
            $table->unsignedMediumInteger('id',true)->comment('Product lang ID');
            $table->unsignedMediumInteger('product_id')->comment('Product ID');
            $table->unsignedTinyInteger('lang_id')->comment('Language ID');
            $table->string('url',250)->nullable()->comment('Product URL');
            $table->string('name',128)->comment('Product Name');
            $table->string('home_title' , 255)->nullable()->comment('Home Title');
            $table->string('summary',250)->nullable()->comment('Product Summary');
            $table->mediumText('description')->nullable()->comment('Product  Description');
            $table->string('meta_title',70)->nullable()->comment('To have a different title from the product name');
            $table->string('meta_description',255)->nullable()->comment('Product Meta Description Show In Google Result Page');
            $table->string('meta_keywords',255)->nullable()->comment('Product Keywords');
            $table->string('tags',500)->nullable()->comment('Comma separated tags');
            $table->index('product_id');
            $table->unique(['product_id','lang_id'],'language_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('lang_id')->references('id')->on('languages')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_lang');
    }
};
