<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();

        $totalUsers   = User::count();
        $todayUsers   = User::whereDate('created_at', $today)->count();
        $totalFarmers = User::where('role_id', 2)->count();
        $totalVendors = User::where('role_id', 3)->count();
        $todayFarmers = User::where('role_id', 2)->whereDate('created_at', $today)->count();
        $todayVendors = User::where('role_id', 3)->whereDate('created_at', $today)->count();

        return view('admin.dashboard', compact(
            'totalUsers', 'todayUsers',
            'totalFarmers', 'totalVendors',
            'todayFarmers', 'todayVendors'
        ));
    }

    public function user_index()
    {
        $users = User::orderBy('id', 'ASC')->get();
        return view('admin.user.user_index')->with('users', $users);
    }

    public function notifications()
    {
        /** @var User $admin */
        $admin         = auth()->user();
        $notifications = $admin->notifications()->latest()->paginate(20);
        $admin->unreadNotifications->markAsRead();
        return view('admin.notifications', compact('notifications'));
    }

    public function notificationsDropdown()
    {
        /** @var User $admin */
        $admin         = auth()->user();
        $notifications = $admin->notifications()->latest()->take(8)->get()
            ->map(function ($n) {
                $data = $n->data;
                return [
                    'id'      => $n->id,
                    'message' => $data['message'] ?? 'Notification',
                    'type'    => $data['type']    ?? 'INFO',
                    'tx_id'   => $data['transaction_id']   ?? null,
                    'tx_code' => $data['transaction_code'] ?? null,
                    'is_read' => ! is_null($n->read_at),
                    'time'    => $n->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $admin->unreadNotifications()->count(),
        ]);
    }

    public function markNotificationRead(string $id)
    {
        /** @var User $admin */
        $admin = auth()->user();
        $admin->notifications()->where('id', $id)->update(['read_at' => now()]);
        return response()->json(['status' => 'ok']);
    }

    public function markAllNotificationsRead(\Illuminate\Http\Request $request)
    {
        /** @var User $admin */
        $admin = auth()->user();
        $admin->unreadNotifications->markAsRead();

        if ($request->ajax()) {
            return response()->json(['status' => 'ok']);
        }
        return back()->with('success', 'All notifications marked as read.');
    }
}
