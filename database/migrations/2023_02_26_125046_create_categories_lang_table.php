<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesLangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories_lang', function (Blueprint $table) {
            $table->unsignedSmallInteger('category_id')->comment('Category ID');
            $table->unsignedTinyInteger('lang_id')->comment('Language ID');
            $table->string('title' , 255)->nullable()->comment('Title');
            $table->string('url' , 255)->unique()->comment('Url');
            $table->string('keywords' , 255)->nullable()->comment('Keywords');
            $table->string('description' , 500)->nullable()->comment('Description');
            $table->string('home_title' , 255)->nullable()->comment('Home Title');
            $table->string('slogan' , 255)->nullable()->comment('Slogan');
            $table->string('heading' , 255)->nullable()->comment('Heading');
            $table->text('header')->nullable()->comment('Header');
            $table->text('body')->nullable()->comment('Body');
            $table->text('footer')->nullable()->comment('Footer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories_lang');
    }
}
