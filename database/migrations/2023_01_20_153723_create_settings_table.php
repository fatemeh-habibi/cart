<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('id',true)->comment('Setting ID');
            $table->string('name',255)->comment('Setting Name');
            $table->string('value',255)->comment('Setting value');
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
        Schema::dropIfExists('settings');
    }
}
