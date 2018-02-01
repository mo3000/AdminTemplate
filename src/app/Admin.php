<?php

namespace App;


use App\Service\User\CurrentUser;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model {
	protected $table = 'admin';
	private static $currentUser;

	public static function currentUser() : CurrentUser {
		return self::$currentUser;
	}

	public static function setCurrentUser(CurrentUser $user) {
		self::$currentUser = $user;
	}

	protected $hidden = [
		'password',
	];
}