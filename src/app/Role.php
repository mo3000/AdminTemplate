<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Role extends Model {
	protected $table = 'roles';

	public function permissions(bool $idOnly=false)
	{
		return DB::table('role_permission as rp')
			->leftJoin(
				'permissions as p',
				'rp.permissionid',
				'=',
				'p.id'
			)
			->select('id', 'name')
			->where('rp.roleid', $this->id)
			->get()
			->when($idOnly, function ($c) {
				return $c->pluck('id');
			})
			->toArray();
	}

	public function syncPermissions(array $permissions)
	{
		$currentPermissions = $this->permissions(true);
		$new = array_diff($permissions, $currentPermissions);
		$old = array_diff($currentPermissions, $permissions);
		if (count($old) > 0) {
			DB::table('role_permission')
			  ->where('roleid', $this->id)
			  ->whereIn('permissionid', $permissions)
			  ->delete();
		}
		if (count($new) > 0) {
			$adminid = $this->id;
			DB::table('role_permission')
			  ->insert(
				  collect($permissions)
					  ->map(function ($item) use ($adminid) {
						  return ['roleid' => $adminid, 'permissionid' => $item];
					  })
					  ->reject(function ($item) {
						  return empty($item);
					  })
					  ->toArray()
			  );
		}
	}
}