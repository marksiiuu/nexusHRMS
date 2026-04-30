<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $showArchived = $request->has('archived');
        $query        = Attendance::query()->with('employee.department');

        if ($showArchived) {
            $query->whereNotNull('archived_at');
        } else {
            $query->active();
        }

        if ($request->date)        $query->whereDate('date',$request->date);
        if ($request->employee_id) $query->where('employee_id',$request->employee_id);
        if ($request->status)      $query->where('status',$request->status);
        if ($request->month)       $query->whereRaw("DATE_FORMAT(date,'%Y-%m') = ?"  ,[$request->month]);

        $attendances   = $query->latest('date')->paginate(20)->withQueryString();
        $employees     = Employee::whereNull('archived_at')->where('status','active')->get();
        $archivedCount = Attendance::whereNotNull('archived_at')->count();

        return view('attendance.index', compact('attendances','employees', 'showArchived', 'archivedCount'));
    }

    public function create()
    {
        $employees = Employee::whereNull('archived_at')->where('status','active')->with('department')->get();
        return view('attendance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'time_in'     => 'nullable|date_format:H:i',
            'time_out'    => 'nullable|date_format:H:i|after:time_in',
            'status'      => 'required|in:present,absent,late,half_day,on_leave',
            'notes'       => 'nullable|string|max:500',
        ]);

        $existing = Attendance::where('employee_id',$validated['employee_id'])->where('date',$validated['date'])->first();
        if ($existing) return back()->with('error','Attendance already recorded for this employee on this date!')->withInput();

        if (!empty($validated['time_in']) && !empty($validated['time_out'])) {
            $validated['hours_worked'] = round((strtotime($validated['time_out'])-strtotime($validated['time_in']))/3600,2);
        }

        Attendance::create($validated);
        return redirect()->route('attendance.index')->with('success','Attendance recorded!');
    }

    public function edit(Attendance $attendance)
    {
        $employees = Employee::whereNull('archived_at')->where('status','active')->get();
        return view('attendance.edit', compact('attendance','employees'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'time_in'  => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i|after:time_in',
            'status'   => 'required|in:present,absent,late,half_day,on_leave',
            'notes'    => 'nullable|string|max:500',
        ]);
        if (!empty($validated['time_in']) && !empty($validated['time_out'])) {
            $validated['hours_worked'] = round((strtotime($validated['time_out'])-strtotime($validated['time_in']))/3600,2);
        }
        $attendance->update($validated);
        return redirect()->route('attendance.index')->with('success','Attendance updated!');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->update(['archived_at' => now()]);
        return redirect()->route('attendance.index')->with('success','Attendance record archived!');
    }

    public function myAttendance(Request $request)
    {
        $employee    = auth()->user()->employee;
        if (!$employee) return redirect()->route('dashboard')->with('error','No employee profile linked to your account.');

        $attendances = Attendance::active()->where('employee_id',$employee->id)
            ->when($request->month, fn($q)=>$q->whereRaw("DATE_FORMAT(date,'%Y-%m') = ?",[$request->month]))
            ->latest('date')->paginate(20)->withQueryString();

        return view('attendance.my', compact('attendances'));
    }

    // Clock In/Out using Philippine Time
    public function clockIn(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) return back()->with('error','No employee profile linked.');

        $phNow   = Carbon::now('Asia/Manila');
        $today   = $phNow->format('Y-m-d');
        $timeNow = $phNow->format('H:i');

        $existing = Attendance::where('employee_id',$employee->id)->where('date',$today)->first();

        if ($existing) {
            if ($existing->time_out) return back()->with('error','You have already clocked out today!');
            
            // Fix: Use parse because time_in from DB often includes seconds (HH:mm:ss)
            $inTime = Carbon::parse($existing->time_in, 'Asia/Manila');
            $hours  = round($phNow->diffInMinutes($inTime) / 60, 2);
            
            $existing->update(['time_out'=>$timeNow,'hours_worked'=>$hours]);
            return back()->with('success','Clock-out recorded at '.$phNow->format('h:i A').' PHT');
        }

        // 8:15 AM grace period
        $status = ($phNow->hour > 8 || ($phNow->hour === 8 && $phNow->minute > 15)) ? 'late' : 'present';

        Attendance::create([
            'employee_id' => $employee->id,
            'date'        => $today,
            'time_in'     => $timeNow,
            'status'      => $status,
        ]);
        return back()->with('success','Clock-in recorded at '.$phNow->format('h:i A').' PHT'.($status==='late'?' — Late':''));
    }
}
