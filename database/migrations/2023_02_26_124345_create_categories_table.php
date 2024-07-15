<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('parent_id')->nullable()->comment('Parent ID');
            $table->string('name' , 50)->comment('Categorie Name');
            $table->string('url' , 255)->unique()->comment('Categorie Url');
            $table->string('homepage_position' , 128)->nullable()->comment('Values [MegaMenu1,MegaMenu2]');
            $table->boolean('show_product')->default(true)->comment('Show Product');
            $table->boolean('activated')->default(true)->comment('Activation (0 hidden, 1 show)');
            $table->boolean('home_show')->default(false)->comment('Home Show (0 hidden, 1 show)');
            $table->unsignedSmallInteger('sort_order')->nullable()->comment('Menu sort position');
            $table->string('icon' , 128)->nullable()->comment('Category Image Name');     
            $table->unsignedTinyInteger('level')->nullable()->comment('Category level');       
            $table->unsignedSmallInteger('created_user_id')->nullable()->comment('User id for created');
            $table->unsignedSmallInteger('updated_user_id')->nullable()->comment('User id for updated');
            $table->index('parent_id');
            $table->index(DB::raw('sort_order'),'sort_order')->algorithm('btree');
            $table->index(DB::raw('activated'),'activated')->algorithm('btree');
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
        Schema::dropIfExists('categories');
    }
};
