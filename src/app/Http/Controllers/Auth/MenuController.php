<?php

namespace App\Http\Controllers\Auth;


use App\Role;
use App\Utils\DB\QueryHelper;
use App\Utils\Format\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController {
	public function list(Request $request)
	{
		$list = DB::table('menus')
			->select('id', 'display_name', 'html_component', 'parentid')
			->get()
			->toArray();
		return new JsonResponse(0, '', $list);
	}

	public function add(Request $request)
	{
		$request->validate(
			[
				'display_name' => 'required',
				'html_component' => 'string|max:32',
				'parentid' => 'nullable|integer|min:1'
			]
		);

		DB::table('menus')
			->insert(
				[
					'display_name' => $request->input('display_name'),
					'html_component' => $request->input('html_component'),
					'parentid' => $request->input('parentid')
				]
			);
		return new JsonResponse(0);
	}

	public function delete(Request $request)
	{
		$request->validate(
			[
				'id' => 'required|integer',
			]
		);
		$menu = DB::table("menus")
			->where('id', $request->input("id"))
			->first();
		if ($menu) {
			DB::table("menus")
				->update(['parentid' => $menu->parentid]);
		}
		DB::table("menus")
			->where('id', $menu->id)
			->delete();
		return new JsonResponse(0);
	}

	public function deleteCascade(Request $request)
	{
		$request->validate(
			[
				'id' => 'required|integer',
			]
		);

		$this->deleteChildren($request->input('id'));
		DB::table("menus")
			->whereIn('id', $this->children)
			->delete();
		return new JsonResponse(0);
	}

	private $children = [];
	private function deleteChildren($id) {
		$deletes = DB::table('menus')
		             ->select('id')
		             ->where('parentid', $id)
		             ->get()
		             ->pluck('id')
		             ->toArray();
		$this->children[] = $id;
		foreach ($deletes as $childid) {
			$this->deleteChildren($childid);
		}
	}

 	public function edit(Request $request)
	{
		$request->validate(
			[
				'id' => 'required|integer|min:1',
				'display_name' => 'required',
				'html_component' => 'string|max:32',
				'parent' => 'nullable|integer|min:1'
			]
		);
		DB::table('menus')
		  ->where('id', $request->input('id'))
		  ->update(
			  [
				  'display_name' => $request->input('display_name'),
				  'html_component' => $request->input('html_component'),
				  'parentid' => $request->input('parentid')
			  ]
		  );
		return new JsonResponse(0);
	}

	public function roleMenuList(Request $request)
	{
		$request->validate(
			[
				'id' => 'required|int|min:1'
			]
		);
		$role = Role::where('id', $request->input('id'))
			->first();
		if (empty($role)) {
			return new JsonResponse(-1, 'role不存在: '.$request->input('id'));
		}
		$list = $role->menus();
		return new JsonResponse(0, '', $list);
	}

	public function roleMenuEdit(Request $request)
	{
		$role = Role::where('id', $request->input('id'))
			->first();
		if (empty($role)) {
			return new JsonResponse(-1, 'role不存在: '.$request->input('id'));
		}
		try {
			DB::beginTransaction();
			$role->syncMenus($request->input('menu'));
			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();
			return new JsonResponse(-1, $e->getMessage());
		}

		return new JsonResponse(0);
	}
}