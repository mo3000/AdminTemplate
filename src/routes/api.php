<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/* <===============后台权限路由begin ===========>*/
Route::any('/auth/admin/list', 'Auth\AdminController@list');
Route::any('/auth/admin/edit', 'Auth\AdminController@edit');
Route::any('/auth/admin/add', 'Auth\AdminController@add');
Route::any('/auth/admin/reset-password', 'Auth\AdminController@resetPassword');
Route::any('/auth/admin/set-status', 'Auth\AdminController@setStatus');

Route::any('/auth/menu/list', 'Auth\MenuController@list');
Route::any('/auth/menu/edit', 'Auth\MenuController@edit');
Route::any('/auth/menu/add', 'Auth\MenuController@add');
Route::any('/auth/menu/delete', 'Auth\MenuController@delete');
Route::any('/auth/menu/delete-cascade', 'Auth\MenuController@deleteCascade');

Route::any('/auth/role/list', 'Auth\RbacController@roleList');
Route::any('/auth/role/add', 'Auth\RbacController@roleAdd');
Route::any('/auth/role/edit', 'Auth\RbacController@roleEdit');
Route::any('/auth/role/delete', 'Auth\RbacController@roleDelete');

Route::any('/auth/role-menu/list', 'Auth\RbacController@roleMenuList');
Route::any('/auth/role-menu/edit', 'Auth\RbacController@roleMenuEdit');

Route::any('/auth/permission/list', 'Auth\RbacController@permissionList');
Route::any('/auth/permission/add', 'Auth\RbacController@permissionAdd');
Route::any('/auth/permission/edit', 'Auth\RbacController@permissionEdit');

Route::any('/auth/admin-role/list', 'Auth\RbacController@adminRoleList');
Route::any('/auth/admin-role/edit', 'Auth\RbacController@adminRoleEdit');

Route::any('/auth/role-permission/list', 'Auth\RbacController@rolePermissionList');
Route::any('/auth/role-permission/edit', 'Auth\RbacController@rolePermissionEist');

Route::any('/auth/user/login', 'Auth\UserController@login');
Route::any('/auth/user/logout', 'Auth\UserController@logout');
Route::any('/auth/user/edit', 'Auth\UserController@edit');
Route::any('/auth/user/userinfo', 'Auth\UserController@userinfo');
Route::any('/auth/user/modify-password', 'Auth\UserController@modifyPassword');
/* <===============后台权限路由end ===========>*/
