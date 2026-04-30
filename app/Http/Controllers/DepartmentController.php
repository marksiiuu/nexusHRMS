<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $showArchived = $request->boolean('archived');
        $query = Department::withCount('employees');
        if ($showArchived) {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }
        if ($request->search) {
            $query->where('name','like',"%{$request->search}%")->orWhere('code','like',"%{$request->search}%");
        }
        $departments   = $query->latest()->paginate(15)->withQueryString();
        $archivedCount = Department::whereNotNull('archived_at')->count();
        return view('departments.index', compact('departments','archivedCount','showArchived'));
    }

    public function create()
    {
        $employees = Employee::whereNull('archived_at')->where('status','active')->get();
        return view('departments.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100|unique:departments,name',
            'code'       => 'required|string|max:20|unique:departments,code',
            'description'=> 'nullable|string|max:500',
            'manager_id' => 'nullable|exists:employees,id',
            'positions'  => 'nullable|string',
        ]);
        if ($request->positions) {
            $validated['positions'] = array_filter(array_map('trim', explode(',', $request->positions)));
        }
        $validated['is_active'] = true;
        Department::create($validated);
        return redirect()->route('departments.index')->with('success','Department created!');
    }

    public function show(Department $department)
    {
        $department->load(['employees.user','manager']);
        return view('departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        $employees = Employee::whereNull('archived_at')->where('status','active')->get();
        return view('departments.edit', compact('department','employees'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name'       => ['required','string','max:100',Rule::unique('departments','name')->ignore($department->id)],
            'code'       => ['required','string','max:20',Rule::unique('departments','code')->ignore($department->id)],
            'description'=> 'nullable|string|max:500',
            'manager_id' => 'nullable|exists:employees,id',
            'positions'  => 'nullable|string',
        ]);
        if ($request->positions) {
            $validated['positions'] = array_filter(array_map('trim', explode(',', $request->positions)));
        } else {
            $validated['positions'] = null;
        }
        $validated['is_active'] = $request->has('is_active');
        $department->update($validated);
        return redirect()->route('departments.index')->with('success','Department updated!');
    }

    // Archive instead of delete
    public function archive(Department $department)
    {
        $department->update(['archived_at'=>now(),'is_active'=>false]);
        return redirect()->route('departments.index')->with('success','Department archived!');
    }

    public function restore(Department $department)
    {
        $department->update(['archived_at'=>null,'is_active'=>true]);
        return redirect()->route('departments.index')->with('success','Department restored!');
    }

    // Keep for compatibility
    public function destroy(Department $department)
    {
        return $this->archive($department);
    }
}
