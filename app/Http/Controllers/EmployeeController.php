<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $showArchived = $request->boolean('archived');
        $query = Employee::with(['department','user']);

        if ($showArchived) {
            $query->archived();
        } else {
            $query->active();
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name','like',"%{$request->search}%")
                  ->orWhere('last_name','like',"%{$request->search}%")
                  ->orWhere('email','like',"%{$request->search}%")
                  ->orWhere('employee_id','like',"%{$request->search}%");
            });
        }
        if ($request->department_id) $query->where('department_id',$request->department_id);
        if ($request->status)        $query->where('status',$request->status);

        $employees     = $query->latest()->paginate(15)->withQueryString();
        $departments   = Department::whereNull('archived_at')->get();
        $archivedCount = Employee::archived()->count();

        return view('employees.index', compact('employees','departments','archivedCount','showArchived'));
    }

    public function create()
    {
        $departments = Department::whereNull('archived_at')->get();
        // Auto-generate next biometric ID
        $lastBio = Employee::withoutGlobalScopes()->whereNotNull('biometric_id')
            ->orderByRaw("CAST(REPLACE(biometric_id,'BIO-','') AS UNSIGNED) DESC")
            ->value('biometric_id');
        $nextBioNum = $lastBio ? (int)str_replace('BIO-','',$lastBio)+1 : 1;
        $nextBioId = 'BIO-'.str_pad($nextBioNum,3,'0',STR_PAD_LEFT);

        return view('employees.create', compact('departments','nextBioId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => 'required|email|unique:employees,email',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:500',
            'department_id' => 'nullable|exists:departments,id',
            'position'      => 'required|string|max:100',
            'employment_type'=>'required|in:full_time,part_time,contract,intern',
            'hire_date'     => 'required|date',
            'birth_date'    => 'nullable|date',
            'gender'        => 'nullable|in:male,female,other',
            'salary'        => 'required|numeric|min:0',
            'biometric_id'  => 'nullable|string|max:50',
            'avatar'        => 'nullable|image|max:2048',
            'emergency_contact_name'  => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        // Auto-generate default password
        $defaultPassword = strtolower($validated['first_name']) . rand(1000, 9999);

        $user = User::create([
            'name'             => $validated['first_name'].' '.$validated['last_name'],
            'email'            => $validated['email'],
            'password'         => Hash::make($defaultPassword),
            'role'             => 'employee',
            'default_password' => $defaultPassword,
        ]);

        // Generate employee ID
        $lastEmp = Employee::withoutGlobalScopes()->orderBy('id','desc')->first();
        $empId   = 'EMP-'.str_pad(($lastEmp ? $lastEmp->id + 1 : 1), 5, '0', STR_PAD_LEFT);

        // Avatar upload
        $avatarName = null;
        if ($request->hasFile('avatar')) {
            $avatarName = time().'.'.$request->file('avatar')->getClientOriginalExtension();
            $request->file('avatar')->storeAs('avatars', $avatarName, 'public');
        }

        Employee::create(array_merge($validated, [
            'user_id'     => $user->id,
            'employee_id' => $empId,
            'avatar'      => $avatarName,
            'status'      => 'active',
        ]));

        return redirect()->route('employees.index')
            ->with('success', "Employee created! Default password: <strong>{$defaultPassword}</strong>");
    }

    public function show(Employee $employee)
    {
        // Allow access if admin, HR, or if viewing own profile
        if (!auth()->user()->hasHrAccess() && auth()->user()->id !== $employee->user_id) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to view this profile.');
        }

        $employee->load(['department','user',
            'attendances' => fn($q) => $q->active()->latest()->limit(10),
            'leaves'      => fn($q) => $q->latest()->limit(5),
            'payrolls'    => fn($q) => $q->latest()->limit(6),
        ]);
        return view('employees.show', compact('employee'));
    }

    public function updatePassword(Request $request, Employee $employee)
    {
        // Only allow user to update their own password OR admin/HR
        if (!auth()->user()->hasHrAccess() && auth()->user()->id !== $employee->user_id) {
            return back()->with('error', 'You are not authorized to change this password.');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', 'min:8'],
        ]);

        $employee->user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    public function edit(Employee $employee)
    {
        $departments = Department::whereNull('archived_at')->get();
        return view('employees.edit', compact('employee','departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => ['required','email',Rule::unique('employees','email')->ignore($employee->id)],
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:500',
            'department_id' => 'nullable|exists:departments,id',
            'position'      => 'required|string|max:100',
            'employment_type'=>'required|in:full_time,part_time,contract,intern',
            'status'        => 'required|in:active,inactive,terminated,on_leave',
            'hire_date'     => 'required|date',
            'birth_date'    => 'nullable|date',
            'gender'        => 'nullable|in:male,female,other',
            'salary'        => 'required|numeric|min:0',
            'biometric_id'  => 'nullable|string|max:50',
            'avatar'        => 'nullable|image|max:2048',
            'emergency_contact_name'  => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('avatar')) {
            if ($employee->avatar) Storage::disk('public')->delete('avatars/'.$employee->avatar);
            $avatarName = time().'.'.$request->file('avatar')->getClientOriginalExtension();
            $request->file('avatar')->storeAs('avatars', $avatarName, 'public');
            $validated['avatar'] = $avatarName;
        }

        $employee->update($validated);
        $employee->user->update([
            'name'  => $validated['first_name'].' '.$validated['last_name'],
            'email' => $validated['email'],
        ]);

        return redirect()->route('employees.show',$employee)->with('success','Employee updated successfully!');
    }

    // ARCHIVE instead of delete
    public function archive(Employee $employee)
    {
        $employee->update(['archived_at' => now(), 'status' => 'inactive']);
        $employee->user->update(['archived_at' => now(), 'is_active' => false]);
        return redirect()->route('employees.index')->with('success','Employee archived successfully!');
    }

    // Restore
    public function restore(Employee $employee)
    {
        $employee->update(['archived_at' => null, 'status' => 'active']);
        $employee->user->update(['archived_at' => null, 'is_active' => true]);
        return redirect()->route('employees.index')->with('success','Employee restored!');
    }

    // Kept for compatibility but now just calls archive
    public function destroy(Employee $employee)
    {
        return $this->archive($employee);
    }
}
