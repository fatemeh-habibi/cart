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
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('name_fa')->unique();
            $table->string('slug')->unique();
            $table->unsignedTinyInteger('activated')->default('1')->comment('Activation (0 hidden, 1 show)');
            $table->unsignedSmallInteger('created_user_id')->nullable()->comment('User id for created');
            $table->unsignedSmallInteger('updated_user_id')->nullable()->comment('User id for updated');
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
        Schema::dropIfExists('roles');
    }
};
