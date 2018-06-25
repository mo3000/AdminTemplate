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
/* ===============后台权限路由begin ===========>*/
Route::any('/auth/admin/list', 'Auth\AdminController@list');
Route::any('/auth/admin/detail', 'Auth\AdminController@detail');
Route::any('/auth/admin/add', 'Auth\AdminController@add');
Route::any('/auth/admin/update', 'Auth\AdminController@update');
Route::any('/auth/admin/reset-password', 'Auth\AdminController@resetPassword');
Route::any('/auth/admin/set-status', 'Auth\AdminController@setStatus');

Route::any('/auth/role/list', 'Auth\RbacController@roleList');
Route::any('/auth/role/update', 'Auth\RbacController@roleUpdate');
Route::any('/auth/role/delete', 'Auth\RbacController@roleDelete');
Route::any('/auth/role/detail', 'Auth\RbacController@roleDetail');
//角色编辑需要的场馆列表
Route::any('/auth/gym/list', 'Auth\RbacController@gymList');

Route::any('/auth/permission/list', 'Auth\RbacController@permissionList');

Route::any('/auth/admin-role/list', 'Auth\RbacController@adminRoleList');
Route::any('/auth/admin-role/edit', 'Auth\RbacController@adminRoleEdit');

Route::any('/auth/user/login', 'Auth\UserController@login');
Route::any('/auth/user/logout', 'Auth\UserController@logout');
Route::any('/auth/user/edit', 'Auth\UserController@edit');
Route::any('/auth/user/userinfo', 'Auth\UserController@userinfo');
Route::any('/auth/user/modify-password', 'Auth\UserController@modifyPassword');
Route::any('/auth/user/menus', 'Auth\UserController@menuList');

//订单管理
Route::any('/order/list', 'Order\OrderController@list');
Route::any('/order/detail', 'Order\OrderController@detail');
//会员列表
Route::any('/admin/user/list', 'Admin\UserController@list');

Route::any('/test/index', 'TestController@index');

/* <===============后台权限路由end ===========*/
