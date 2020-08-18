<?php

namespace App\Http\Controllers\Auth;

use App\Admin;
use App\Models\Auth\Roles;
use App\Notifications\ChangePassword;
use App\Utils\JsonResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function list(Request $request)
    {
        $list = Admin::with('roles')
            ->when($request->filled('realname'), function ($query) use ($request) {
                $query->where('admins.realname', 'like', '%'.$request->input('realname').'%');
            })
            ->when($request->filled('username'), function ($query) use ($request) {
                $query->where('admins.username', 'like', '%'.$request->input('username').'%');
            })
            ->when($request->filled('rolename'), function ($query) use ($request) {
                $query->where('roles.display_name', 'like', '%'.$request->input('rolename').'%');
            })
            ->paginate();
        return new JsonResponse(0, '', $list);
    }

    public function edit(Request $request)
    {
        if ($request->filled('id')) {
            DB::beginTransaction();
            try {
                $admin = Admin::where('id', $request->input('id'))->first();
                if (empty($admin)) {
                    throw new \RuntimeException('用户不存在');
                }
                $admin->realname = $request->input('realname');
                $admin->save();
                $roles = Roles::select('id')
                    ->whereIn('name', $request->input('roles'))
                    ->get()
                    ->pluck('id')
                    ->toArray();
                $admin->roles()->sync($roles);
                DB::commit();
            } catch (\RuntimeException $e) {
                DB::rollBack();
                return new JsonResponse(-1, $e->getMessage());
            }
        } else {
            $request->validate(['username' => 'required', 'realname' => 'required', 'roles' => 'required']);
            $password = empty($request->input('password')) ? $request->input('password') : '123456';
            if (Admin::where('username', $request->input('username'))
                ->exists()) {
                return new JsonResponse(-1, '用户名已存在');
            }
            $admin = new Admin(
                [
                    'realname' => $request->input('realname'),
                    'username' => $request->input('username'),
                    'password' => Hash::make($password),
                ]
            );
            DB::beginTransaction();
            try {
                $rolenames = $request->input('roles');
                $roles = Roles::select('id')
                    ->whereIn('name', $rolenames)
                    ->get()
                    ->pluck('id')
                    ->toArray();
                if (empty($roles)) {
                    throw new \RuntimeException("角色不存在: ".join(', ', $rolenames));
                }
                $admin->save();
                $admin->roles()->sync($roles);
                DB::commit();
            } catch (\RuntimeException $e) {
                DB::rollBack();
                return new JsonResponse(-1, $e->getMessage());
            }
        }
        return new JsonResponse(0);
    }

    public function resetPassword(Request $request)
    {
        Admin::find($request->input('id'))->notify(new ChangePassword());
        return new JsonResponse(0);
        $request->validate(['id' => 'required']);
        $user = Admin::findOrFail($request->input('id'));
        $user->password = Hash::make($request->input('123456'));
        $user->save();
        $user->notify();
        return new JsonResponse(0);
    }

    public function toggleStatus(Request $request)
    {
        $request->validate(['id' => 'required']);
        if ($request->input('id') == $request->user()->id) {
            return new JsonResponse(-1, '不能禁用自己');
        }
        DB::table('admins')
            ->where('id', $request->input('id'))
            ->update(['status' => DB::raw('status ^ 1')]);
        return new JsonResponse(0);
    }

    public function info($id)
    {
        return Admin::find($id)->with('roles');
    }

    public function roleList(Request $request)
    {
        return Roles::get();
    }

    //关联角色
    public function syncUserRoles(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'roles' => 'required|array'
        ]);

        Admin::findOrFail($request->input('id'))
            ->roles()
            ->sync($request->input('roles'));

        return new JsonResponse(0);
    }

    public function userinfo(Request $request)
    {
        $user = $request->user();
        $info = $user->with('roles')->find($user->id);
        $info->notifications = DB::table('admin_notification')
            ->select('id', 'data', 'read_at', 'created_at')
            ->where('notifiable_id', $user->id)
            ->limit(15)
            ->get()
            ->map(function ($v) {
                $v->data = json_decode($v->data);
                return $v;
            });
        return new JsonResponse(0, '', $info);
    }
}
