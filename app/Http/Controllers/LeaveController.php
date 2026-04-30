<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Leave::with(['employee.department','leaveType','approver']);

        if ($user->isEmployee()) {
            $query->where('employee_id', $user->employee->id);
        }

        if ($request->status)      $query->where('status',$request->status);
        if ($request->employee_id) $query->where('employee_id',$request->employee_id);
        if ($request->leave_type_id) $query->where('leave_type_id',$request->leave_type_id);

        $leaves     = $query->latest()->paginate(15)->withQueryString();
        $leaveTypes = LeaveType::all();
        $employees  = Employee::where('status','active')->get();

        return view('leaves.index', compact('leaves','leaveTypes','employees'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::all();
        $employees  = Employee::where('status','active')->get();
        return view('leaves.create', compact('leaveTypes','employees'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|max:1000',
        ]);

        if ($user->isEmployee()) {
            $validated['employee_id'] = $user->employee->id;
        }

        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);
        $days  = $start->diffInWeekdays($end) + 1;

        // Check for overlapping leaves
        $overlap = Leave::where('employee_id',$validated['employee_id'])
            ->whereIn('status',['pending','approved'])
            ->where(function($q) use($validated){
                $q->whereBetween('start_date',[$validated['start_date'],$validated['end_date']])
                  ->orWhereBetween('end_date',[$validated['start_date'],$validated['end_date']]);
            })->exists();

        if ($overlap) {
            return back()->with('error','You already have a leave request during this period!')->withInput();
        }

        Leave::create(array_merge($validated,['total_days'=>$days,'status'=>'pending']));
        return redirect()->route('leaves.index')->with('success','Leave request submitted successfully!');
    }

    public function show(Leave $leave)
    {
        $leave->load(['employee','leaveType','approver']);
        return view('leaves.show', compact('leave'));
    }

    public function edit(Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->with('error','Only pending leaves can be edited!');
        }
        $leaveTypes = LeaveType::all();
        $employees  = Employee::where('status','active')->get();
        return view('leaves.edit', compact('leave','leaveTypes','employees'));
    }

    public function update(Request $request, Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->with('error','Only pending leaves can be edited!');
        }
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|max:1000',
        ]);
        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);
        $validated['total_days'] = $start->diffInWeekdays($end) + 1;
        $leave->update($validated);
        return redirect()->route('leaves.index')->with('success','Leave request updated!');
    }

    public function destroy(Leave $leave)
    {
        if (!in_array($leave->status,['pending','cancelled'])) {
            return back()->with('error','Only pending or cancelled leaves can be deleted!');
        }
        $leave->delete();
        return redirect()->route('leaves.index')->with('success','Leave request deleted!');
    }

    public function approve(Leave $leave)
    {
        $leave->update(['status'=>'approved','approved_by'=>auth()->id(),'approved_at'=>now()]);
        return back()->with('success','Leave approved successfully!');
    }

    public function reject(Request $request, Leave $leave)
    {
        $request->validate(['rejection_reason'=>'required|string|max:500']);
        $leave->update(['status'=>'rejected','rejection_reason'=>$request->rejection_reason,'approved_by'=>auth()->id()]);
        return back()->with('success','Leave rejected!');
    }

    public function cancel(Leave $leave)
    {
        if (!in_array($leave->status,['pending'])) {
            return back()->with('error','Only pending leaves can be cancelled!');
        }
        $leave->update(['status'=>'cancelled']);
        return back()->with('success','Leave cancelled!');
    }
}
