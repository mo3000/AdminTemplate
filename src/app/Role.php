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

	public function menus(bool $idOnly=false)
	{
		return DB::table('role_menu as rm')
		         ->leftJoin(
			         'permissions as p',
			         'rm.permissionid',
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

	public function syncMenus(array $menus)
	{
		$currentMenus = $this->menus(true);
		$new = array_diff($menus, $currentMenus);
		$old = array_diff($currentMenus, $menus);
		if (count($old) > 0) {
			DB::table('role_menu')
			  ->where('roleid', $this->id)
			  ->whereIn('menuid', $old)
			  ->delete();
		}
		if (count($new) > 0) {
			$roleid = $this->id;
			DB::table('role_permission')
			  ->insert(
				  collect($menus)
					  ->map(function ($item) use ($roleid) {
						  return ['roleid' => $roleid, 'menuid' => $item];
					  })
					  ->reject(function ($item) {
						  return empty($item);
					  })
					  ->toArray()
			  );
		}
	}
}