<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();

        $totalUsers = User::count();
        $todayUsers = User::whereDate('created_at', $today)->count();
        $totalFarmers = User::where('role_id', 2)->count();
        $totalVendors = User::where('role_id', 3)->count();

        $todayFarmers = User::where('role_id', 2)->whereDate('created_at', $today)->count();
        $todayVendors = User::where('role_id', 3)->whereDate('created_at', $today)->count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'todayUsers',
            'totalFarmers',
            'totalVendors',
            'todayFarmers',
            'todayVendors'
        ));
    }

    public function user_index()
    {
        $users = User::orderBy('id', 'ASC')->get();
        return view('admin.user.user_index')->with('users', $users);
    }
}
