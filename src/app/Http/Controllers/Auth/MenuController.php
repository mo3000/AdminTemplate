<?php

namespace App\Http\Controllers\Auth;


use App\Utils\DB\QueryHelper;
use App\Utils\Format\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController {
	public function list(Request $request)
	{
		$list = (new QueryHelper(
			DB::table('menus')
			->select('id', '')
		))
			->equal('parent')
			->endHelper()
			->when(empty($request->input('parent')), function ($query) {
				return $query->where('parent', null);
			})
			->get()
			->toArray();
		return new JsonResponse(0, '', $list);
	}

	public function edit(Request $request)
	{
		$request->validate(
			[
				'id' => 'required|integer|min:1',
				'permissionid' => 'required|integer|unique:menus',
				'display_name' => 'required',
				'html_component' => 'string|max:32',
				'parent' => 'nullable|integer|min:1'
			]
		);
		DB::table('munes')
		  ->where('id', $request->input('id'))
		  ->update(
			  [
				  'permissionid' => $request->input('permissionid'),
				  'display_name' => $request->input('display_name'),
				  'html_component' => $request->input('html_component'),
				  'parent' => $request->input('parent')
			  ]
		  );
		return new JsonResponse(0);
	}
}