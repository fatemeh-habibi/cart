<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->unsignedMediumInteger('id',true)->comment('Customer ID');
            $table->string('first_name',128)->comment('Customer First Name');
            $table->string('last_name',128)->comment('Customer Last Name');
            $table->string('nickname',32)->nullable()->comment('Customer Nickname');
            $table->string('email',128)->unique()->nullable()->comment('Customer Email');
            $table->string('password',150)->nullable()->comment('Customer Password');
            $table->string('telephone',32)->nullable()->comment('Customer Telephone')->index();
            $table->string('mobile',32)->unique()->comment('Customer Mobile');
            $table->string('fax',32)->nullable()->comment('Customer Fax');
            $table->string('image',128)->nullable()->comment('Customer image name');
            $table->decimal('credit_limit', $precision = 12, 0)->default('0')->comment('Credit limit');
            $table->decimal('credit_limit_mounth', $precision = 12, 0)->default('0')->comment('how many months is the customer"s credit limit');
            $table->unsignedTinyInteger('gender')->nullable()->comment('Customer Gender [0:female, 1:male, 2:both]');
            $table->unsignedDecimal('credit',14)->default(0.00)->comment('Customer Credit Amount');
            $table->unsignedTinyInteger('newsletter')->default(0)->comment('Send newsletter?');
            $table->unsignedMediumInteger('address_id')->nullable()->comment('Default Address ID');
            $table->string('activated_at',25)->nullable()->comment('Date of Activation');
            $table->string('activation_code',40)->nullable()->comment('Activation Code');
            $table->string('forgotten_password_code',40)->nullable();
            $table->unsignedInteger('otp_time')->nullable();
            $table->string('remember_code',40)->nullable();
            $table->unsignedInteger('last_login')->nullable()->comment('Last Login Time');
            $table->string('note',255)->nullable()->comment('Extra Note For Customer');
            $table->string('register_type',25)->nullable()->comment('Website,Inventory,Facebook,Google');
            $table->string('ip_address',15)->nullable();
            $table->tinyInteger('activated')->default('0')->comment('Activation Status');
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
        Schema::dropIfExists('customers');
    }
}
