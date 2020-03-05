<?php


namespace App\Service;



use Illuminate\Support\Facades\Route;

class RegistAuthRoutes
{
	public static function all()
	{
		Route::any('/login', 'Auth\LoginController@login');
		Route::any('/logout', 'Auth\LoginController@logout');

        Route::any('/userinfo', 'Auth\UserController@userinfo');
        Route::any('/user/list', 'Auth\UserController@list');
		//重置密码
		Route::any('/user/reset-password', 'Auth\UserController@resetPassword');

		//通知
		Route::any('/notification/get', 'Auth\NotificationController@get');
		Route::any('/notification/mark-all-read', 'Auth\NotificationController@markAllRead');
		Route::any('/notification/mark-one-read', 'Auth\NotificationController@markOneRead');

	}
}
