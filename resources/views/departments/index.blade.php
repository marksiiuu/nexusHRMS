@extends('layouts.app')
@section('title','Departments')
@section('page-title','Department Management')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Departments</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Departments</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        @if($showArchived)
            <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-diagram-3 me-1"></i>Show Active</a>
        @else
            <a href="{{ route('departments.index') }}?archived=1" class="btn btn-outline-warning btn-sm"><i class="bi bi-archive me-1"></i>Archived ({{ $archivedCount }})</a>
        @endif
        <a href="{{ route('departments.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Department</a>
    </div>
</div>

<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search departments…" value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
        </div>
    </form>
</div></div>

@if($showArchived)
<div class="alert alert-warning mb-3"><i class="bi bi-archive me-2"></i>Showing <strong>archived departments</strong>. Records are preserved.</div>
@endif

<div class="row g-3">
    @forelse($departments as $dept)
    <div class="col-md-6 col-xl-4">
        <div class="card h-100 {{ $dept->archived_at ? 'border-warning' : '' }}">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:#E3EDF9;color:#253D90;width:44px;height:44px;border-radius:11px;"><i class="bi bi-diagram-3"></i></div>
                        <div>
                            <h6 class="mb-0 fw-700">{{ $dept->name }}</h6>
                            <code class="small text-muted">{{ $dept->code }}</code>
                        </div>
                    </div>
                    @if($dept->archived_at)<span class="badge bg-warning text-dark">Archived</span>
                    @elseif($dept->is_active)<span class="badge bg-success">Active</span>
                    @else<span class="badge bg-secondary">Inactive</span>@endif
                </div>
                <p class="text-muted small mb-3">{{ $dept->description ?? 'No description.' }}</p>
                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <div>
                        <div class="fw-700" style="color:#253D90;font-size:1.3rem;">{{ $dept->employees_count }}</div>
                        <div class="text-muted" style="font-size:.72rem;">Employees</div>
                    </div>
                    <div class="text-end">
                        <div class="small fw-500">{{ $dept->manager?->full_name ?? '—' }}</div>
                        <div class="text-muted" style="font-size:.72rem;">Manager</div>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('departments.show',$dept) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-info-circle"></i></a>
                        @if(!$dept->archived_at)
                            <a href="{{ route('departments.edit',$dept) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('departments.archive',$dept) }}" onsubmit="return confirm('Archive {{ addslashes($dept->name) }}?')">
                                @csrf <button class="btn btn-sm btn-outline-warning"><i class="bi bi-archive"></i></button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('departments.restore',$dept) }}">
                                @csrf <button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">
        <i class="bi bi-diagram-3 display-4 d-block mb-3"></i>No departments found.
    </div></div></div>
    @endforelse
</div>
@if($departments->hasPages())
<div class="mt-3 d-flex justify-content-center">{{ $departments->links() }}</div>
@endif
@endsection
