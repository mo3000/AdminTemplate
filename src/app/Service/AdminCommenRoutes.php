<?php

namespace App\Service;


use Illuminate\Support\Facades\Route;

class AdminCommenRoutes {
	public static function bind()
	{
		Route::any('/auth/admin/list', 'Auth\AdminController@list');
		Route::any('/auth/admin/edit', 'Auth\AdminController@edit');
		Route::any('/auth/admin/add', 'Auth\AdminController@add');
		Route::any('/auth/admin/list', 'Auth\AdminController@list');
		
		Route::any('/auth/menu/list', 'Auth\MenuController@list');
		Route::any('/auth/menu/edit', 'Auth\MenuController@edit');
		
		Route::any('/auth/role/list', 'Auth\RbacController@roleList');
		Route::any('/auth/role/add', 'Auth\RbacController@roleAdd');
		Route::any('/auth/role/edit', 'Auth\RbacController@roleEdit');
		Route::any('/auth/role/delete', 'Auth\RbacController@roleDelete');

		Route::any('/auth/permission/list', 'Auth\RbacController@permissionList');
		Route::any('/auth/permission/add', 'Auth\RbacController@permissionAdd');
		Route::any('/auth/permission/edit', 'Auth\RbacController@permissionEdit');
		Route::any('/auth/permission/delete', 'Auth\RbacController@permissionDelete');

		Route::any('/auth/admin-role/list', 'Auth\RbacController@adminRoleList');
		Route::any('/auth/admin-role/edit', 'Auth\RbacController@adminRoleEdit');

		Route::any('/auth/user/login', 'Auth\UserController@login');
		Route::any('/auth/user/logout', 'Auth\UserController@logout');
		Route::any('/auth/user/edit', 'Auth\UserController@edit');
		Route::any('/auth/user/modify-password', 'Auth\UserController@modifyPassword');
	}
}