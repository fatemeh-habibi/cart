<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->unsignedTinyInteger('id',true)->comment('Language ID');
            $table->string('name',64)->unique()->nullable()->comment('Language Name');
            $table->string('iso_code',12)->unique()->nullable()->comment('ISO 639-1 Code');
            $table->string('language_code',3)->nullable()->comment('Standard Language Code');
            $table->unsignedTinyInteger('rtl')->default(0)->comment('0: ltr, 1:rtl');
            $table->string('flag',128)->nullable()->comment('Flag Image url');
            $table->unsignedTinyInteger('activated')->default(0)->comment('0: Deactive, 1: Active');
            $table->unsignedSmallInteger('created_user_id')->comment('ID of the user created a record');
            $table->unsignedSmallInteger('updated_user_id')->nullable()->comment('ID of the user has performed last update');
            $table->index('created_user_id');
            $table->index('updated_user_id');
            $table->timestamps();
            $table->softDeletes()->comment('Date of soft delete language');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
    }
}
