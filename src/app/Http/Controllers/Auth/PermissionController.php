<?php

namespace App\Http\Controllers\Auth;

use App\Models\Auth\Permissions;
use App\Utils\JsonResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
	//树状结构列表
    public function treeview(Request $request)
    {
		$permissions = Permissions::where('groupid', 1)
			->all();
		$tree = [];
		foreach ($permissions as $permission) {
			$this->set($tree, $permission);
		}
		return new JsonResponse(0, '', $tree);
    }

    private function set(&$arr, $permission)
    {
    	$keys = explode('.', $permission->pathname);
    	for ($i = 0; $i < count($keys) - 1; $i++) {
    		if (!isset($arr['children'])) {
    			$arr['name'] = $keys[$i];
    			$arr['children'] = [];
		    }
    		$arr = $arr['children'];
	    }
	    if (!isset($arr['display_name'])) {
		    $arr['display_name'] = $permission->display_name;
		    $arr['children'] = [];
		    $arr['name'] = $permission->name;
		    $arr['hidden_on_reject'] = $permission->hidden_on_reject;
		    $arr['extra'] = $permission->extra;
		    $arr['in_menu_tree'] = $permission->in_menu_tree;
	    }
    }

    public function edit(Request $request)
    {
    	$this->validate($request, [
    		'parentid' => 'nullable',
		    'alias' => 'nullable',
		    'display_name' => $request->input('display_name'),
		    'hidden_on_reject' => 'required|in:0,1',
		    'in_menu_tree' => 'required|in:0,1',
	    ]);

    	DB::beginTransaction();
    	try {
		    if ($request->filled("id")) {
			    Permissions::where('id', $request->input('id'))
			               ->update(
				               [
					               'name' => $request->input('name'),
					               'alias' => $request->input('alias'),
					               'updated_at' => Carbon::now()->toDateTimeString(),
				               ]
			               );
		    } else {
		    	if ($request->filled('parentid')) {
		    		$parent = Permissions::findOrFail($request->input('parentid'));
				    $pathname = $parent->pathname.'.'.$request->input('name');
			    } else {
		    		$pathname = $request->input('name');
			    }
			    if (Permissions::where('pathname', $pathname)->exists()) {
				    throw new \RuntimeException('路径已存在');
			    }
			    $permission = new Permissions(
				    array_merge($request->only(
					    ['name', 'display_name', 'alias',
					     'hidden_on_reject', 'in_menu_tree']),
				                ['pathname' => $pathname]));
			    $permission->save();
		    }
		    DB::commit();
	    } catch (\RuntimeException $e) {
    		DB::rollBack();
    		return new JsonResponse(-1, $e->getMessage());
	    }

    	return new JsonResponse(0);
    }
}
