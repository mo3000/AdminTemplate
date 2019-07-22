<?php

namespace App\Http\Controllers\Auth;

use App\Models\Auth\Roles;
use App\Utils\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    public function list(Request $request)
    {
    	return Roles::paginate();
    }

    public function edit(Request $request)
    {
    	$this->validate($request, [
    		[
    			'name' => 'required',
    			'display_name' => 'required',
		    ]
	    ]);

    	if (!$request->filled('id')) {
    		$role = new Roles(
    			[
    				'name' => $request->input('name'),
    				'display_name' => $request->input('display_name'),
			    ]
		    );
    		$role->save();
	    } else {
    		Roles::where('id', $request->input('id'))
			    ->update(
			    	[
			    		'name' => $request->input('name'),
			    		'display_name' => $request->input('display_name'),
				    ]
			    );
	    }

    	return new JsonResponse(0);
    }

    //角色关联权限
    public function syncRolePermission(Request $request)
    {
    	Roles::findOrFail($request->input('id'))
		    ->permissions()
		    ->sync($request->input('permissions'));
    	return new JsonResponse(0);
    }
}
