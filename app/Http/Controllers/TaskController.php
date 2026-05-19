<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Employee;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Task::with(['employee.department', 'assigner']);

        // Employees see only their own tasks
        if ($user->isEmployee()) {
            $query->where('employee_id', $user->employee->id);
        }

        if ($request->status)      $query->where('status', $request->status);
        if ($request->priority)    $query->where('priority', $request->priority);
        if ($request->employee_id) $query->where('employee_id', $request->employee_id);

        $tasks     = $query->latest()->paginate(15)->withQueryString();
        $employees = Employee::where('status', 'active')->get();

        // Stats
        $statsQuery = Task::query();
        if ($user->isEmployee()) {
            $statsQuery->where('employee_id', $user->employee->id);
        }
        $stats = [
            'total'       => (clone $statsQuery)->count(),
            'pending'     => (clone $statsQuery)->where('status', 'pending')->count(),
            'in_progress' => (clone $statsQuery)->where('status', 'in_progress')->count(),
            'completed'   => (clone $statsQuery)->where('status', 'completed')->count(),
            'overdue'     => (clone $statsQuery)->whereNotIn('status', ['completed', 'cancelled'])
                                ->whereNotNull('due_date')->where('due_date', '<', now())->count(),
        ];

        return view('tasks.index', compact('tasks', 'employees', 'stats'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->get();
        return view('tasks.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'priority'    => 'required|in:low,medium,high,urgent',
            'due_date'    => 'nullable|date',
        ]);

        $validated['assigned_by'] = auth()->id();
        $validated['status']      = 'pending';

        Task::create($validated);
        return redirect()->route('tasks.index')->with('success', 'Task assigned successfully!');
    }

    public function show(Task $task)
    {
        $user = auth()->user();
        if ($user->isEmployee() && $task->employee_id !== $user->employee->id) {
            abort(403);
        }
        $task->load(['employee.department', 'assigner']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $employees = Employee::where('status', 'active')->get();
        return view('tasks.edit', compact('task', 'employees'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'priority'    => 'required|in:low,medium,high,urgent',
            'status'      => 'required|in:pending,in_progress,completed,cancelled',
            'due_date'    => 'nullable|date',
        ]);

        if ($validated['status'] === 'completed' && $task->status !== 'completed') {
            $validated['completed_at'] = now();
        }

        $task->update($validated);
        return redirect()->route('tasks.index')->with('success', 'Task updated successfully!');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully!');
    }

    /**
     * Employee updates the status of their own task (e.g. start working, mark complete).
     */
    public function updateStatus(Request $request, Task $task)
    {
        $user = auth()->user();

        // Employees can only update their own tasks
        if ($user->isEmployee() && $task->employee_id !== $user->employee->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status'  => 'required|in:in_progress,completed',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $task->update($validated);

        $msg = $validated['status'] === 'completed'
            ? 'Task marked as completed!'
            : 'Task status updated!';

        return back()->with('success', $msg);
    }
}
