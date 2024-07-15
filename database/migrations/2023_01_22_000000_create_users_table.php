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
        Schema::create('users', function (Blueprint $table) {
            $table->smallIncrements('id',true)->comment('User ID');
            $table->string('email',128)->nullable()->unique()->comment('User email');
            $table->string('mobile',32)->nullable()->comment('User Mobile');
            $table->string('password',255)->comment('Password');
            $table->string('username',255)->unique()->comment('User Name');
            $table->string('first_name',255)->nullable()->comment('First Name');
            $table->string('last_name',255)->nullable()->comment('Last Name');
            $table->timestamp('email_verified_at')->nullable()->comment('Date of email verified');
            $table->timestamp('otp_verified_at')->nullable()->comment('Date of otp verified');
            $table->timestamp('otp_start_verify_time')->nullable()->comment('Date of start otp verify');
            $table->integer('otp_try_in_day')->nullable()->comment('Number of try otp in day');
            $table->rememberToken()->comment('Remember code');
            $table->timestamp('last_login')->nullable()->comment('Last login');
            $table->string('default_page',255)->nullable()->comment('Default Page');
            $table->string('activation_code',255)->unique()->nullable()->comment('Activation Code');
            $table->string('forgotten_password_code',40)->nullable();
            $table->unsignedInteger('forgotten_password_time')->nullable();
            $table->string('image',128)->nullable()->comment('user image name');
            $table->unsignedSmallInteger('expert_limit_sell_in_mounth')->nullable();
            $table->unsignedTinyInteger('activated')->default(1)->comment('Activation status');
            $table->unsignedSmallInteger('created_user_id')->comment('ID of the user created a record');
            $table->unsignedSmallInteger('updated_user_id')->nullable()->comment('ID of the user has performed last update');
            $table->unsignedTinyInteger('default_lang_id')->default(1)->comment('Default language for the user');
            $table->index('created_user_id');
            $table->index('updated_user_id');
            $table->index('default_lang_id');
            $table->timestamps();
            $table->softDeletes()->comment('Date of soft delete user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
