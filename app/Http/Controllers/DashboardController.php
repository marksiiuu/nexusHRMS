<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_employees'  => Employee::whereNull('archived_at')->where('status','active')->count(),
            'total_departments'=> Department::whereNull('archived_at')->where('is_active',true)->count(),
            'pending_leaves'   => Leave::where('status','pending')->count(),
            'today_present'    => Attendance::where('date', Carbon::today('Asia/Manila')->format('Y-m-d'))->where('status','present')->count(),
            'today_absent'     => Attendance::where('date', Carbon::today('Asia/Manila')->format('Y-m-d'))->where('status','absent')->count(),
            'total_users'      => User::whereNull('archived_at')->count(),
        ];

        $recentLeaves = Leave::with(['employee','leaveType'])->latest()->limit(5)->get();
        $recentEmployees = Employee::whereNull('archived_at')->with('department')->latest()->limit(5)->get();

        $monthlyAttendance = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today('Asia/Manila')->subDays($i);
            $monthlyAttendance[] = [
                'date'    => $date->format('M d'),
                'present' => Attendance::where('date',$date->format('Y-m-d'))->where('status','present')->count(),
                'absent'  => Attendance::where('date',$date->format('Y-m-d'))->where('status','absent')->count(),
            ];
        }

        $departmentStats = Department::whereNull('archived_at')
            ->withCount(['employees' => fn($q)=>$q->whereNull('archived_at')->where('status','active')])
            ->get();

        return view('dashboard.index', compact('stats','recentLeaves','recentEmployees','monthlyAttendance','departmentStats'));
    }
}
