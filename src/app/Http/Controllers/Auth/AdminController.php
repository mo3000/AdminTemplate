<?php

namespace App\Http\Controllers\Auth;


use App\Admin;
use App\Company;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\NewAdmin;
use App\Service\Rbac;
use App\Utils\CloudStorage\WithImageUpload;
use App\Utils\DB\QueryHelper;
use App\Utils\Format\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class AdminController extends Controller {
	use WithImageUpload;
	public function list(Request $request)
	{
		Rbac::requireExplicitly(null, 'auth', 'admin.select');
		$users = (new QueryHelper(
			Admin::select('id', 'name', 'realname', 'nickname',
			              'created_at', 'updated_at', 'status', 'headimg',
			              'phone', 'sex', 'serialid', 'position')))
			->equal('id')
			->like('name')
			->like('position')
			->like('phone')
			->equal('serialid')
			->like('realname')
			->endHelper()
			->orderBy('created_at', 'desc')
			->paginate();
		return new JsonResponse(0, '', $users);
	}

	public function add(NewAdmin $request)
	{
		Rbac::requireExplicitly(null, 'auth', 'admin.update');

		$admin = new Admin();
		try {
			$admin->realname = $request->input('realname');
			$admin->name = $request->input('name');
			$admin->sex = $request->input('sex');
			$admin->gymid = $request->input('gymid');
			$admin->birthday = $request->input('birthday');
			$admin->position = $request->input('position');
			$admin->serialid = $request->input('serialid');
			$admin->phone = $request->input('phone');
			if ($request->filled('headimg')) {
				$admin->headimg = $this->processImage($request->input('headimg'));
			}
			$admin->password = Hash::make($request->input(
				'password', env('ADMIN_DEFAULT_PASSWORD')
			));
			$admin->save();
		} catch (\RuntimeException $e) {
			return new JsonResponse(-1, $e->getMessage());
		}
		return new JsonResponse(0);
	}

	public function update(NewAdmin $request)
	{
		$admin = Admin::find($request->input('id'));
		if (is_null($admin)) {
			return new JsonResponse(0, 'id不能为空');
		}
		Rbac::requireExplicitly(null, 'auth', 'admin.update');
		try {
			$admin->realname = $request->input('realname');
			$admin->sex = $request->input('sex');
			$admin->gymid = $request->input('gymid');
			$admin->birthday = $request->input('birthday');
			$admin->position = $request->input('position');
			$admin->serialid = $request->input('serialid');
			$admin->phone = $request->input('phone');
			if ($request->filled('headimg')) {
				$admin->headimg = $this->processImage($request->input('headimg'));
			}
			$admin->save();
		} catch (\RuntimeException $e) {
			return new JsonResponse(-1, $e->getMessage());
		}

		return new JsonResponse(0);
	}

	public function companyList()
	{
		$company = Company::get()->toArray();
		return new JsonResponse(0, '', $company);
	}


	public function resetPassword(Request $request)
	{
		$request->validate(['id' => 'required|integer|min:1']);
		Rbac::requireExplicitly(null, 'auth','admin.update');
		Admin::where('id', $request->input('id'))
		     ->update(['password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD'))]);
		return new JsonResponse(0);
	}

	public function setStatus(Request $request)
	{

		$request->validate(['id' => 'required|integer|min:1',
		                    'status' => 'required|integer|in:0,1']);
		if ($request->input('id') == Admin::currentAdmin()->getId()) {
			return new JsonResponse(-1,'不能禁止自己登录');
		}
		Rbac::requireExplicitly(null, 'auth','admin.update');
		Admin::where('id', $request->input('id'))
		     ->update(['status' => $request->input('status')]);
		return new JsonResponse(0);
	}

	public function detail(Request $request) {
		$request->validate(
			[
				'id' => 'required'
			]
		);
		$admin = Admin::find($request->input('id'));
		if (empty($admin)) {
			return new JsonResponse(-1, '记录不存在: '.$request->input('id'));
		}
		Rbac::requireExplicitly(null, 'auth','admin.select');

		return new JsonResponse(0, '', $admin);
	}
}