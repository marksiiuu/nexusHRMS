@extends('layouts.app')
@section('title','Task Details')
@section('page-title','Task Details')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Task Details</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(auth()->user()->hasHrAccess())
        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        @if($task->status === 'pending' && (auth()->user()->hasHrAccess() || (auth()->user()->employee && auth()->user()->employee->id === $task->employee_id)))
        <form method="POST" action="{{ route('tasks.update-status', $task) }}" class="m-0">
            @csrf <input type="hidden" name="status" value="in_progress">
            <button class="btn btn-info text-white"><i class="bi bi-play-fill me-1"></i>Start Task</button>
        </form>
        @endif
        @if($task->status === 'in_progress' && (auth()->user()->hasHrAccess() || (auth()->user()->employee && auth()->user()->employee->id === $task->employee_id)))
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeModal"><i class="bi bi-check-circle me-1"></i>Mark Complete</button>
        @endif
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="emp-avatar" style="width:56px;height:56px;font-size:1.3rem;">
                    {{ strtoupper(substr($task->employee->first_name,0,1).substr($task->employee->last_name,0,1)) }}
                </div>
                <div>
                    <h5 class="mb-0 fw-700">{{ $task->employee->full_name }}</h5>
                    <div class="text-muted small">{{ $task->employee->position }} — {{ $task->employee->department?->name }}</div>
                </div>
            </div>
            <div class="d-flex gap-2">
                {!! $task->priority_badge !!}
                {!! $task->status_badge !!}
            </div>
        </div>
        <hr>

        <h6 class="fw-700 mb-3" style="color:#253D90;">{{ $task->title }}</h6>

        @if($task->is_overdue)
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span class="small fw-600">This task is overdue! Due date was {{ $task->due_date->format('F d, Y') }}.</span>
        </div>
        @endif

        <div class="row g-3">
            @if($task->description)
            <div class="col-12">
                <div class="text-muted small fw-600 mb-1">Description</div>
                <div class="p-3 rounded" style="background:#f8f9fa;">{{ $task->description }}</div>
            </div>
            @endif
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1">Due Date</div>
                <div>{{ $task->due_date ? $task->due_date->format('F d, Y') : '—' }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1">Assigned By</div>
                <div>{{ $task->assigner->name ?? '—' }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1">Assigned On</div>
                <div>{{ $task->created_at->format('M d, Y h:i A') }}</div>
            </div>
            @if($task->completed_at)
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1">Completed On</div>
                <div class="text-success fw-600">{{ $task->completed_at->format('M d, Y h:i A') }}</div>
            </div>
            @endif
            @if($task->remarks)
            <div class="col-12">
                <div class="text-muted small fw-600 mb-1">Completion Remarks</div>
                <div class="p-3 rounded" style="background:#d1e7dd;">{{ $task->remarks }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
</div>
</div>

<!-- Complete Modal -->
@if($task->status === 'in_progress')
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Complete Task</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('tasks.update-status', $task) }}">
            @csrf
            <input type="hidden" name="status" value="completed">
            <div class="modal-body">
                <p class="small text-muted">Mark <strong>"{{ $task->title }}"</strong> as completed.</p>
                <label class="form-label fw-600">Completion Remarks <span class="text-muted">(optional)</span></label>
                <textarea name="remarks" class="form-control" rows="4" placeholder="Any notes about the completed task…"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Mark Complete</button>
            </div>
        </form>
    </div></div>
</div>
@endif
@endsection
