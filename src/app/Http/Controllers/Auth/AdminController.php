<?php

namespace App\Http\Controllers\Auth;


use App\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\NewAdmin;
use App\Service\User\CurrentUser;
use App\Utils\DB\QueryHelper;
use App\Utils\Format\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller {
	public function list(Request $request)
	{
		$request->validate(['id' => 'required|integer']);
		$users = (new QueryHelper(
			Admin::select('id', 'name', 'realname', 'nickname',
			              'created_at', 'updated_at')))
			->equal('id')
			->like('name')
			->like('realname')
			->endHelper()
			->orderBy('id', 'desc')
			->paginate();
		return new JsonResponse(0, '', $users);
	}

	public function add(NewAdmin $request)
	{
		$admin = new Admin();
		$admin->realname = $request->input('realname');
		$admin->name = $request->input('name');
		$admin->save();
		return new JsonResponse(0);
	}

	public function resetPassword(Request $request)
	{
		$request->validate(['id' => 'required|integer|min:1']);
		Admin::where('id', $request->input('id'))
			->update(['password' => Hash::make(env('DEFAULT_PASSWORD'))]);
	}

	public function setStatus(Request $request)
	{
		$request->validate(['id' => 'required|integer|min:1',
			                   'status' => 'required|integer|in:0,1']);
		if ($request->input('id') == Admin::currentUser()->getId()) {
			return new JsonResponse('不能禁止自己登录');
		}
		Admin::where('id', $request->input('id'))
			->update(['status' => $request->input('status')]);
		return new JsonResponse(0);
	}
}