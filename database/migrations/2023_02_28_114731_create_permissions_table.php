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
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('module');
            $table->string('module_fa');
            $table->string('name');
            $table->string('name_fa');
            $table->string('slug');
            $table->unique(['module', 'name']);
            $table->unique(['module', 'name_fa']);
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
        Schema::dropIfExists('permissions');
    }
};
