<?php

namespace App\Http\Controllers\Auth;


use App\Admin;
use App\Http\Requests\admin\EditSelf;
use App\Http\Requests\admin\ModifyPassword;
use App\Utils\Auth\AuthHelper;
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
		$admin = Admin::where('name', $request->input('username'))
			->first();
		if (empty($admin)) {
			return new JsonResponse(-1, '账号不存在');
		}
		if (!Hash::check($request->input('password'), $admin->password)) {
			return new JsonResponse(-1, '账号密码不匹配');
		}
		if ($admin->status == 0) {
			return new JsonResponse(-1, '账号被停用');
		}
		$token = (new AuthHelper())->signAdmin($admin);
		return new JsonResponse(0, '', strval($token));
	}

	public function logout(Request $request)
	{
		Admin::where('id', Admin::currentAdmin()->getId())
			->update(['authcode' => '']);
		return new JsonResponse(0);
	}

	public function edit(EditSelf $request)
	{
		$admin = Admin::currentAdmin()->getAdmin();
		$admin->realname = $request->input('realname');
		if (!empty($request->input('phone'))) {
			$admin->phone = $request->input('phone');
		}
		$admin->save();
		return new JsonResponse(0);
	}

	public function modifyPassword(ModifyPassword $request)
	{
		$admin = Admin::currentAdmin()->getAdmin();
		if (!Hash::check($request->input('password'), $admin->password)) {
			return new JsonResponse(-1, '原密码不对');
		}
		$admin->password = Hash::make($request->input('newpassword'));
		$admin->save();
		return new JsonResponse(0);
	}

	public function userinfo()
	{
		$admin = Admin::currentAdmin();
		return new JsonResponse(0, '', [
			'userid' => $admin->getId(),
			'username' => $admin->getName(),
			'realname' => $admin->getRealname(),
		]);
	}
}