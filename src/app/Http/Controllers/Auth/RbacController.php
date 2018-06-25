<?php

namespace App\Http\Controllers\Auth;


use App\Admin;
use App\Permissions;
use App\Role;
use App\Service\MenuService;
use App\Service\Rbac;
use App\Utils\DB\QueryHelper;
use App\Utils\Format\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Overtrue\Socialite\AuthorizeFailedException;
use Psy\Util\Json;

class RbacController {
	public function roleList(Request $request) {
		if ($request->input("nopage")) {
			$list = DB::table('roles')
			          ->select('id', 'display_name')
			          ->when($request->filled('gymid'), function ($query) use ($request) {
				          return $query->where('gymid', $request->input('gymid'));
			          })
			          ->get();
		} else {
			$list = (new QueryHelper(
				DB::table('roles as r')
				  ->select('r.id', 'r.display_name', 'r.gymid', 'gym.name as gym_name')
				  ->selectRaw("json_agg(project_name) as project_name")
				  ->leftJoin(
					  'gym',
					  'r.gymid',
					  '=',
					  'gym.id'
				  )
				  ->leftJoin(
					  'role_permission as rp',
					  'r.id',
					  '=',
					  'rp.roleid'
				  )
				  ->leftJoin(
					  'permissions as p',
					  'rp.permissionid',
					  '=',
					  'p.id'
				  )
				  ->groupBy(['r.id', 'r.display_name', 'r.gymid', 'gym.name'])
			))
				->like('r.display_name', 'display_name')
				->equal('r.gymid', 'gymid')
				->endHelper()
				->orderBy('r.created_at', 'desc')
				->paginate();

			$dict = array_merge(config('rbac.dict'), config('rbac.project'));
		}

		return new JsonResponse(0, '', ['list' => $list, 'dict' => isset($dict) ? $dict : []]);
	}

	public function gymList()
	{
		$list = DB::table("gym")
		          ->select('id', 'name')
		          ->get()
		          ->toArray();
		return new JsonResponse(0, '', $list);
	}


	public function roleUpdate(Request $request) {
		$request->validate(
			[
				'display_name' => 'required',
				'permissions' => 'required|array',
				//				'gymid' => 'required|int'
			]
		);
		$permissions = array_filter(
			$request->input('permissions'),
			function ($item) {
				return !preg_match('/^uselessKey\d+/', $item);
			});
		DB::beginTransaction();
		try {
			if (!$request->filled('id')) {
				$role = new Role(
					[
						'display_name' => $request->input('display_name'),
						'name' => bin2hex(random_bytes(16)),
						'gymid' => $request->input('gymid')
					]
				);
			} else {
				$role = Role::find($request->input('id'));
				if (empty($role)) {
					throw new \RuntimeException("角色不存在: " . $request->input('id'));
				}
				$role->display_name = $request->input('display_name');
				$role->gymid        = $request->input('gymid');
			}

			$role->save();
			$role->syncPermissions($request->input('gymid'), $permissions);
			Rbac::requireExplicitly(null, 'auth', 'role.update');
			DB::commit();
		} catch (AuthorizationException $e) {
			DB::rollback();
			throw $e;
		} catch (\RuntimeException $e) {
			DB::rollback();
			return new JsonResponse(-1, $e->getMessage());
		}

		return new JsonResponse(0);
	}

	public function roleDetail(Request $request)
	{
		if ($request->filled('id')) {
			$role = Role::find($request->input('id'));
			if (empty($role)) {
				return new JsonResponse(-1, "角色不存在: ".$request->input('id'));
			}
			$role = ['gymid' => $role->gymid, 'display_name' => $role->display_name];
		} else {
			$role = ['gymid' => null, 'display_name' => '',];
		}

		$menuService = new MenuService();
		$permissions = $menuService->getRolePermissionTree(
			$request->input('id'),
			$request->input('gymid')
		);

		return new JsonResponse(0, '', [
			'role' => $role,
			'permissions' => $permissions,
		]);
	}

	public function roleDelete(Request $request) {
		$request->validate(
			[
				'id' => 'required|integer|min:1'
			]
		);
		Rbac::requireExplicitly(null, 'auth', 'role.update');
		$role = Role::find($request->input('id'));
		if (empty($role)) {
			return new JsonResponse(-1, '角色不存在');
		}
		if (preg_match('/\w+_\d+/', $role->name)) {
			return new JsonResponse(-1, '特殊权限不能删除');
		}
		if ($role->name == 'superadmin' || $role->name == 'coachlabadmin'
		    || $role->name == 'periodslabadmin') {
			return new JsonResponse(-1, '特殊权限不能删除');
		}

		Role::where('id', $request->input('id'))
		    ->delete();
		return new JsonResponse(0);
	}

	public function permissionList(Request $request) {
		$list = (new QueryHelper(DB::table('permissions')))
			->like('name', $request->input('name'))
			->like('display_name', $request->input('display_name'))
			->endHelper()
			->orderBy('created_at', 'desc');
		if ($request->input('no_pagination') == 1) {
			$list = $list->paginate();
		} else {
			$list = $list->get();
		}
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


	public function adminRoleList(Request $request)
	{
		$request->validate(
			[
				'adminid' => 'required|integer'
			]
		);
		$list = DB::table("admin_role")
		          ->select('roleid')
		          ->where('adminid', $request->input('adminid'))
		          ->pluck('roleid')
		          ->toArray();
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
		if (empty($admin)) {
			return new JsonResponse(-1, 'admin不存在: '.$request->input('adminid'));
		}
		DB::beginTransaction();
		try {
			$admin->syncRoles($request->input('roles'));
			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();
			return new JsonResponse(-1, $e->getMessage());
		}

		return new JsonResponse(0);
	}
}