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
        Schema::create('users_roles', function (Blueprint $table) {
            $table->unsignedSmallInteger('user_id');
            $table->unsignedInteger('role_id');

            //FOREIGN KEY CONSTRAINTS
           $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
           $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade')->onUpdate('cascade');

            //SETTING THE PRIMARY KEYS
           $table->primary(['user_id','role_id']);
           $table->unsignedSmallInteger('priority')->default(1);
           $table->unsignedSmallInteger('created_user_id')->nullable()->comment('User id for created');
           $table->unsignedSmallInteger('updated_user_id')->nullable()->comment('User id for updated');
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
        Schema::dropIfExists('users_roles');
    }
};
