<?php


namespace App\Service;



use Illuminate\Support\Facades\Route;

class RegistAuthRoutes
{
	public static function all()
	{
		Route::any('/login', 'Auth\LoginController@login');
		Route::any('/logout', 'Auth\LoginController@logout');
		Route::any('/userinfo', 'Auth\LoginController@userinfo');
//		Route::any('/userinfo', 'Auth\LoginController@userinfo');
	}
}