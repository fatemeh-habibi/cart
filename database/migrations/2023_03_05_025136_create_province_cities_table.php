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
        Schema::create('province_cities', function (Blueprint $table) {
            $table->unsignedSmallInteger('id',true)->comment('City ID');
            $table->string('name',128)->comment('City Name');
            $table->unsignedSmallInteger('parent_id')->default(0)->comment('Parent ID');
            $table->index('parent_id');
            $table->unsignedTinyInteger('sort')->default(1);
            $table->unsignedTinyInteger('activated')->default(1)->comment('0 hide, 1 show');
            $table->unsignedSmallInteger('created_user_id')->comment('ID of the user created a record');
            $table->unsignedSmallInteger('updated_user_id')->nullable()->comment('ID of the user has performed last update');
            $table->index('created_user_id');
            $table->index('updated_user_id');
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
        Schema::dropIfExists('province_cities');
    }
};
