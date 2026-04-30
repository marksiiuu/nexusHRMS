<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{


    public function index(Request $request)
    {
        $showArchived = $request->boolean('archived');
        $query = User::with('employee');

        if ($showArchived) {
            $query->archived();
        } else {
            $query->active();
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name','like',"%{$request->search}%")
                  ->orWhere('email','like',"%{$request->search}%");
            });
        }
        if ($request->role) $query->where('role',$request->role);

        $users        = $query->latest()->paginate(15)->withQueryString();
        $archivedCount= User::archived()->count();

        return view('users.index', compact('users','archivedCount','showArchived'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'role'  => 'required|in:admin,hr_manager,payroll_officer,job_recruiter,employee',
        ]);

        // Auto-generate default password: first name + last 4 digits of timestamp
        $defaultPassword = strtolower(explode(' ', $validated['name'])[0]) . rand(1000, 9999);

        User::create([
            'name'             => $validated['name'],
            'email'            => $validated['email'],
            'password'         => Hash::make($defaultPassword),
            'role'             => $validated['role'],
            'is_active'        => true,
            'default_password' => $defaultPassword,
        ]);

        return redirect()->route('users.index')
            ->with('success', "User created! Default password: <strong>{$defaultPassword}</strong>");
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => ['required','email',Rule::unique('users','email')->ignore($user->id)],
            'role'     => 'required|in:admin,hr_manager,payroll_officer,job_recruiter,employee',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
            $validated['default_password'] = null; // clear default password once changed
        }

        $user->update($validated);
        return redirect()->route('users.index')->with('success','User updated successfully!');
    }

    // ARCHIVE instead of delete
    public function archive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error','You cannot archive your own account!');
        }
        $user->update(['archived_at' => now(), 'is_active' => false]);
        return redirect()->route('users.index')->with('success','User archived successfully!');
    }

    // Restore archived user
    public function restore(User $user)
    {
        $user->update(['archived_at' => null, 'is_active' => true]);
        return redirect()->route('users.index')->with('success','User restored successfully!');
    }

    // Reset password to new default
    public function resetPassword(User $user)
    {
        $defaultPassword = strtolower(explode(' ', $user->name)[0]) . rand(1000, 9999);
        $user->update([
            'password'         => Hash::make($defaultPassword),
            'default_password' => $defaultPassword,
        ]);
        return back()->with('success', "Password reset! New default: <strong>{$defaultPassword}</strong>");
    }
}
