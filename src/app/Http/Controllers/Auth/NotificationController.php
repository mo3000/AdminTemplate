<?php


namespace App\Http\Controllers\Auth;


use App\Utils\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController
{
    public function unread(Request $request)
    {
        return new JsonResponse(0, '', $request->user()->unreadNotifycations);
    }

    public function get(Request $request)
    {
       $notifycations = DB::table('admin_notification')
            ->select('id', 'data', 'read_at', 'created_at')
            ->where('created_at', '>', now()->subDays(7)->toDateTimeString())
            ->where('notifiable_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate();
       foreach ($notifycations as $k => $v) {
           $notifycations[$k]->data = json_decode($v->data);
       }
       return new JsonResponse(0, '', $notifycations);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return new JsonResponse(0);
    }

    public function markOneRead(Request $request)
    {
        $request->validate(['id' => 'required']);
        DB::table('admin_notifycation')
            ->where('id', $request->input('id'))
            ->update(['read_at' => now()]);
        return new JsonResponse(0);
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => 'required|array']);
        DB::table('admin_notifycation')
            ->whereIn('id', $request->input('id'))
            ->delete();
        return new JsonResponse(0);
    }
}
