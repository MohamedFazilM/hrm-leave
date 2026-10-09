<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the application dashboard based on user role.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        $stats = [
            'total_departments' => Department::count(),
            'total_employees' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_managers' => User::where('role', 'manager')->count(),
            'total_staff' => User::where('role', 'employee')->count(),
        ];

        $recentEmployees = User::with('department')->latest()->take(5)->get();

        return view('dashboard', compact('user', 'stats', 'recentEmployees'));
    }
}
