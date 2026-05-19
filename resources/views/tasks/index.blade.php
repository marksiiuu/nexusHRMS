@extends('layouts.app')
@section('title','Tasks')
@section('page-title','Task Management')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Tasks</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Tasks</li>
        </ol></nav>
    </div>
    @if(auth()->user()->hasHrAccess())
    <a href="{{ route('tasks.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Assign Task</a>
    @endif
</div>

<!-- Stats -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Tasks</div>
                </div>
                <div class="stat-icon" style="background:#E3EDF9;color:#253D90;"><i class="bi bi-list-task"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value" style="color:#0d6efd;">{{ $stats['in_progress'] }}</div>
                    <div class="stat-label">In Progress</div>
                </div>
                <div class="stat-icon" style="background:#cfe2ff;color:#0d6efd;"><i class="bi bi-play-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value" style="color:#198754;">{{ $stats['completed'] }}</div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-icon" style="background:#d1e7dd;color:#198754;"><i class="bi bi-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value" style="color:#dc3545;">{{ $stats['overdue'] }}</div>
                    <div class="stat-label">Overdue</div>
                </div>
                <div class="stat-icon" style="background:#f8d7da;color:#dc3545;"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            @if(auth()->user()->hasHrAccess())
            <div class="col-md-3">
                <select name="employee_id" class="form-select form-select-sm">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id?'selected':'' }}>{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['pending','in_progress','completed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ str_replace('_',' ',ucfirst($s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select form-select-sm">
                    <option value="">All Priority</option>
                    @foreach(['low','medium','high','urgent'] as $p)
                    <option value="{{ $p }}" {{ request('priority')==$p?'selected':'' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6><i class="bi bi-list-task me-2"></i>Task List</h6>
        <span class="badge bg-secondary">{{ $tasks->total() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Task</th>
                    <th>Priority</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Assigned By</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                <tr class="{{ $task->is_overdue ? 'table-danger' : '' }}">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="emp-avatar" style="width:30px;height:30px;font-size:.7rem;">{{ strtoupper(substr($task->employee->first_name,0,1).substr($task->employee->last_name,0,1)) }}</div>
                            <div>
                                <div class="small fw-500">{{ $task->employee->full_name }}</div>
                                <div class="text-muted" style="font-size:.7rem;">{{ $task->employee->department?->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small fw-600">{{ Str::limit($task->title, 40) }}</div>
                        @if($task->description)
                        <div class="text-muted" style="font-size:.7rem;">{{ Str::limit($task->description, 50) }}</div>
                        @endif
                    </td>
                    <td>{!! $task->priority_badge !!}</td>
                    <td class="small">
                        @if($task->due_date)
                            <span class="{{ $task->is_overdue ? 'text-danger fw-600' : '' }}">
                                {{ $task->due_date->format('M d, Y') }}
                            </span>
                            @if($task->is_overdue)
                            <div class="text-danger" style="font-size:.65rem;"><i class="bi bi-exclamation-circle"></i> Overdue</div>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{!! $task->status_badge !!}</td>
                    <td class="small text-muted">{{ $task->assigner->name ?? '—' }}</td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end flex-wrap align-items-center">
                            <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-secondary" title="View Details"><i class="bi bi-eye"></i></a>
                            @if(auth()->user()->hasHrAccess())
                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="m-0" onsubmit="return confirm('Delete this task?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                            @if($task->status === 'pending' && (auth()->user()->hasHrAccess() || (auth()->user()->employee && auth()->user()->employee->id === $task->employee_id)))
                            <form method="POST" action="{{ route('tasks.update-status', $task) }}" class="m-0">
                                @csrf
                                <input type="hidden" name="status" value="in_progress">
                                <button class="btn btn-sm btn-info text-white" title="Start Task"><i class="bi bi-play-fill"></i></button>
                            </form>
                            @endif
                            @if($task->status === 'in_progress' && (auth()->user()->hasHrAccess() || (auth()->user()->employee && auth()->user()->employee->id === $task->employee_id)))
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#completeModal{{ $task->id }}" title="Complete"><i class="bi bi-check-lg"></i></button>
                            @endif
                        </div>

                        <!-- Complete Modal -->
                        @if($task->status === 'in_progress')
                        <div class="modal fade" id="completeModal{{ $task->id }}" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content">
                                <div class="modal-header"><h6 class="modal-title">Complete Task</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                                <form method="POST" action="{{ route('tasks.update-status', $task) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="completed">
                                    <div class="modal-body">
                                        <p class="small text-muted">Mark <strong>"{{ $task->title }}"</strong> as completed.</p>
                                        <label class="form-label">Completion Remarks <span class="text-muted">(optional)</span></label>
                                        <textarea name="remarks" class="form-control" rows="3" placeholder="Any notes about the completed task…"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-circle me-1"></i>Mark Complete</button>
                                    </div>
                                </form>
                            </div></div>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">
                    <i class="bi bi-list-task display-6 d-block mb-2"></i>No tasks found.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tasks->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $tasks->firstItem() }}–{{ $tasks->lastItem() }} of {{ $tasks->total() }}</small>
        {{ $tasks->links() }}
    </div>
    @endif
</div>
@endsection
