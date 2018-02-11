<?php

namespace App\Http\Controllers\Auth;


use App\Admin;
use App\Http\Requests\admin\EditSelf;
use App\Http\Requests\admin\ModifyPassword;
use app\Utils\Auth\AuthHelper;
use App\Utils\Format\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Psy\Util\Json;

class UserController {
	public function login(Request $request)
	{
		$request->validate(
			[
				'username' => 'required|string|min:4|max:32',
				'password' => 'required|string|min:6|max:32'
			]
		);
		$admin = DB::table('admin')
			->where('name', $request->input('username'))
			->first();
		if (!Hash::check($request->input('password'), $admin->password)) {
			return new JsonResponse(-1, '账号密码不匹配');
		}
		if ($admin->status == 0) {
			return new JsonResponse(-1, '账号被停用');
		}
		$token = (new AuthHelper())->sign($admin);
		return new JsonResponse(0, '', strval($token));
	}

	public function logout(Request $request)
	{
		Admin::where('id', Admin::currentUser()->getId())
			->update(['authcode' => '']);
		return new JsonResponse(0);
	}

	public function edit(EditSelf $request)
	{
		$admin = Admin::currentUser()->getAdmin();
		$admin->realname = $request->input('realname');
		if (!empty($request->input('phone'))) {
			$admin->phone = $request->input('phone');
		}
		$admin->save();
		return new JsonResponse(0);
	}

	public function modifyPassword(ModifyPassword $request)
	{
		$admin = Admin::currentUser()->getAdmin();
		if (!Hash::check($request->input('password'), $admin->password)) {
			return new JsonResponse(-1, '原密码不对');
		}
		$admin->password = Hash::make($request->input('password'));
		$admin->save();
		return new JsonResponse(0);
	}
}