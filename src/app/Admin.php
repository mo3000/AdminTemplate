<?php

namespace App;


use App\Service\User\CurrentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

	public function hasRole(string $rolename) : bool {
		$role = Role::where('name', $rolename)
			->first();
		if (empty($role)) {
			return false;
		}
		return DB::table('admin_role')
			->where('adminid', $this->id)
			->where('roleid', $role->id)
			->exists();
	}

	public function roles(bool $idOnly=false) {
		return DB::table('admin_role as ar')
			->leftJoin(
				'roles as r',
				'ar.roleid',
				'=',
				'r.id'
			)
			->select('id', 'name')
			->where('ar.adminid', $this->id)
			->get()
			->when($idOnly, function ($c) {
				return $c->pluck('id');
			})
			->toArray();
	}

	public function hasPermission(string $permission) : bool
	{
		return DB::table('role_permission as rp')
			->leftJoin(
				'permissions as p',
				'rp.permissionsid',
				'=',
				'p.id'
			)
			->whereRaw("rp.roleid in (select roleid from role_permission 
			where adminid=".$this->id.")")
			->where('p.name', $permission)
			->exists();
	}

	public function syncRoles(array $roles)
	{
		$currentRoles = $this->roles(true);
		$new = array_diff($roles, $currentRoles);
		$old = array_diff($currentRoles, $roles);
		if (count($old) > 0) {
			DB::table('admin_role')
				->where('adminid', $this->id)
				->whereIn('roleid', $roles)
				->delete();
		}
		if (count($new) > 0) {
			$adminid = $this->id;
			DB::table('admin_role')
				->insert(
					collect($roles)
					->map(function ($item) use ($adminid) {
						return ['adminid' => $adminid, 'roleid' => $item];
					})
					->reject(function ($item) {
						return empty($item);
					})
					->toArray()
				);
		}
	}
}