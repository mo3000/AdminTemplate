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
			$table->text('authcode')->nullable();
			$table->string('nickname', 64);
			$table->string('realname', 64)->nullable();
			$table->string('qq', 32)->nullable();
			$table->string('phone', 11)->nullable();
			$table->text('headimg')->nullable();
			$table->smallInteger('status')->default(1);
			$table->integer('gymid')->nullable();
			$table->text('serialid')->nullable()->comment('工号');
			$table->smallInteger('sex')->nullable();
			$table->date('birthday')->nullable();
			$table->text('position')->nullable()->comment('职位');
			$table->timestamps();
			$table->unique('name');
		});

		Schema::create('roles', function (Blueprint $table) {
			$table->increments('id');
			$table->text('name');
			$table->text('display_name')->nullable();
			$table->text('description')->nullable();
			$table->timestamps();
			$table->unique('name');
		});

		Schema::create('permissions', function (Blueprint $table) {
			$table->increments('id');
			$table->text('name');
			$table->text('display_name')->nullable();
			$table->integer('gymid')->nullable();
			$table->text('project_name')->nullable();
			$table->text('path_name')->nullable();
			$table->smallInteger('needed')->default(1);
			$table->unique('name');
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

		\Illuminate\Support\Facades\DB::table('roles')
		                              ->insert(
			                              [
				                              'name' => 'superadmin',
				                              'display_name' => '超级管理员',
			                              ]
		                              );

		\Illuminate\Support\Facades\DB::table('admin')
		                              ->insert(
			                              [
				                              'name' => 'admin',
				                              'password' => \Illuminate\Support\Facades\Hash::make(env("DEFAULT_ADMIN_PASSWORD")),
				                              'nickname' => '超级管理员',
			                              ]
		                              );

		\Illuminate\Support\Facades\DB::table('admin_role')
		                              ->insert(
			                              [
				                              'roleid' => 1,
				                              'adminid' => 1
			                              ]
		                              );
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
		Schema::drop('permissions');
		Schema::drop('roles');
		Schema::drop('admin');
	}
}
