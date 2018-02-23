<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('admin', function (Blueprint $table) {
		    $table->increments('id');
		    $table->string('name', 64);
		    $table->text('password');
		    $table->string('authcode', 8);
		    $table->string('nickname', 64);
		    $table->string('realname', 64);
		    $table->string('qq', 32);
		    $table->string('phone', 11);
		    $table->text('headimg')->nullable();
		    $table->tinyInteger('status')->default(1);
		    $table->timestamps();
		    $table->unique('name');
	    });

	    Schema::create('roles', function (Blueprint $table) {
		    $table->increments('id');
		    $table->text('name');
		    $table->text('display_name');
		    $table->text('description')->nullable();
		    $table->string('headimg', 256);
		    $table->timestamps();
		    $table->unique('name');
	    });

	    Schema::create('permissions', function (Blueprint $table) {
		    $table->increments('id');
		    $table->text('name');
		    $table->text('display_name');
		    $table->text('description')->nullable();
		    $table->timestamps();
		    $table->unique('name');
	    });

	    Schema::create('menus', function (Blueprint $table) {
		    $table->increments('id');
		    $table->integer('permissionid');
		    $table->text('display_name');
		    $table->text('description')->nullable();
		    $table->string('headimg', 256);
		    $table->timestamps();
		    $table->foreign('permissionid')
		        ->references('id')->on('permissions')->onDelete('cascade');
	    });

	    Schema::create('admin_role', function (Blueprint $table) {
	    	$table->integer('adminid');
	    	$table->index('adminid');
	    	$table->integer('roleid');
	    	$table->index('roleid');
	    	$table->foreign('roleid')
		          ->references('id')->on('roles')->onDelete('cascade');
	    	$table->foreign('adminid')
		        ->references('id')->on('admin')->onDelete('cascade');
	    });

	    Schema::create('role_permission', function (Blueprint $table) {
		    $table->integer('roleid');
		    $table->index('roleid');
		    $table->integer('permissionid');
		    $table->index('permissionid');
		    $table->foreign('roleid')
		          ->references('id')->on('roles')->onDelete('cascade');
		    $table->foreign('permissionid')
		          ->references('id')->on('permissions')->onDelete('cascade');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::drop('admin_role');
	    Schema::drop('role_permission');
	    Schema::drop('menus');
	    Schema::drop('permissions');
	    Schema::drop('roles');
        Schema::drop('admin');
    }
}
