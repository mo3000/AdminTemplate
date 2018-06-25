<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Role extends Model {
	protected $table = 'roles';

	protected $fillable = ['display_name', 'name', 'gymid'];

	public function permissions($gymid, bool $idOnly=false)
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
		         ->where('p.gymid', $gymid)
		         ->get()
		         ->when($idOnly, function ($c) {
			         return $c->pluck('id');
		         })
		         ->toArray();
	}

	public function menus(bool $idOnly=false)
	{
		return DB::table('role_menu as rm')
		         ->leftJoin(
			         'menus as m',
			         'rm.menuid',
			         '=',
			         'm.id'
		         )
		         ->select('menuid', 'm.display_name')
		         ->where('rm.roleid', $this->id)
		         ->get()
		         ->when($idOnly, function ($c) {
			         return $c->pluck('menuid');
		         })
		         ->toArray();
	}

	public function syncPermissions($gymid, array $permissions)
	{
		$currentPermissions = $this->permissions($gymid, true);
		$new = array_diff($permissions, $currentPermissions);
		$old = array_diff($currentPermissions, $permissions);
		if (count($old) > 0) {
			DB::table('role_permission')
			  ->where('roleid', $this->id)
			  ->whereIn('permissionid', $old)
			  ->delete();
		}
		if (count($new) > 0) {
			$roleid = $this->id;
			DB::table('role_permission')
			  ->insert(
				  collect($new)
					  ->map(function ($item) use ($roleid) {
						  return ['roleid' => $roleid, 'permissionid' => $item];
					  })
					  ->reject(function ($item) {
						  return empty($item);
					  })
					  ->toArray()
			  );
		}
	}

}