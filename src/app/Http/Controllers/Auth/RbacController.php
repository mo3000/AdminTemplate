<?php

namespace App\Http\Controllers\Auth;


use App\Admin;
use App\Permissions;
use App\Role;
use App\Utils\DB\QueryHelper;
use App\Utils\Format\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RbacController {
	public function roleList(Request $request) {
		$list = (new QueryHelper(DB::table('douniwan')))
			->like('name')
			->like('display_name', $request->input('display_name'))
			->endHelper()
			->orderBy('created_at', 'desc')
			->paginate();
		return new JsonResponse(0, '', $list);
	}

	public function roleNew(Request $request) {
		$request->validate(
			[
				'name' => 'required|unique:roles',
				'display_name' => 'required',
			]
		);
		DB::table('roles')
		  ->insert(
			  [
				  'name' => $request->input('name'),
				  'display_name' => $request->input('display_name')
			  ]
		  );
		return new JsonResponse(0);
	}

	public function roleEdit(Request $request) {
		$request->validate(
			[
				'display_name' => 'required',
				'id' => 'required|integer|min:1'
			]
		);
		Role::where('id', $request->input('id'))
		    ->update(['display_name' => $request->input('display_name')]);
		return new JsonResponse(0);
	}

	public function roleDelete(Request $request) {
		$request->validate(
			[
				'id' => 'required|integer|min:1'
			]
		);
		Role::where('id', $request->input('id'))
		    ->delete();
		return new JsonResponse(0);
	}

	public function permissionList(Request $request) {
		$list = (new QueryHelper(DB::table('permissions')))
			->like('name', $request->input('name'))
			->like('display_name', $request->input('display_name'))
			->endHelper()
			->orderBy('created_at', 'desc')
			->paginate();
		return new JsonResponse(0, '', $list);
	}

	public function permissionDelete(Request $request) {
		$request->validate(
			[
				'id' => 'required|integer|min:1'
			]
		);
		Permissions::where('id', $request->input('id'))
		           ->delete();
		return new JsonResponse(0);
	}

	public function permissionAdd(Request $request)
	{
		$request->validate(
			[
				'name' => 'required|unique:permissions',
				'display_name' => 'required',
			]
		);
		DB::table('permissions')
		  ->insert(
			  [
				  'name' => $request->input('name'),
				  'display_name' => $request->input('display_name')
			  ]
		  );
		return new JsonResponse(0);
	}

	public function permissionEdit(Request $request)
	{
		$request->validate(
			[
				'display_name' => 'required',
				'id' => 'required|integer|min:1'
			]
		);
		Permissions::where('id', $request->input('id'))
		    ->update(['display_name' => $request->input('display_name')]);
		return new JsonResponse(0);
	}

	public function rolePermissionList(Request $request)
	{
		$list = (new QueryHelper(
			DB::table('role_permission as rp')
				->select('p.id', 'p.name', 'p.display_name')
				->leftJoin(
					'permissions as p',
					'rp.permissionid',
					'=',
					'p.id'
				)
		))
			->like('name', $request->input('name'))
			->like('display_name', $request->input('display_name'))
			->endHelper()
			->paginate();
		return new JsonResponse(0, '', $list);
	}

	public function rolePermissionEdit(Request $request)
	{
		$request->validate(
			[
				'roleid' => 'required|integer|min:1',
				'permissionids' => 'required|array'
			]
		);
		$role = Role::where('id', $request->input('roleid'))
			->first();
		$role->syncPermissions($request->input('permissionids'));
		return new JsonResponse(0);
	}

	public function adminRoleList(Request $request)
	{
		$list = (new QueryHelper(
			DB::table('admin_role as ar')
			  ->select('r.id', 'r.name', 'r.display_name')
			  ->leftJoin(
				  'roles as r',
				  'ar.roleid',
				  '=',
				  'p.id'
			  )
		))
			->like('name', $request->input('name'))
			->like('display_name', $request->input('display_name'))
			->endHelper()
			->paginate();
		return new JsonResponse(0, '', $list);
	}

	public function adminRoleEdit(Request $request)
	{
		$request->validate(
			[
				'adminid' => 'required|integer|min:1',
				'roles' => 'required|array'
			]
		);

		$admin = Admin::where('id', $request->input('adminid'))
		            ->first();
		$admin->syncRoles($request->input('roles'));
		return new JsonResponse(0);
	}
}