@extends('layouts.app')
@section('title','Employees')
@section('page-title','Employee Management')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Employees</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Employees</li>
        </ol></nav>
    </div>
    <a href="{{ route('employees.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Add Employee</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search name, email, ID…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id')==$dept->id?'selected':'' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['active','inactive','terminated','on_leave'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ str_replace('_',' ',ucfirst($s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            </div>
            <div class="col-auto ms-auto">
                @if($showArchived)
                    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-people me-1"></i>Show Active</a>
                @else
                    <a href="{{ route('employees.index') }}?archived=1" class="btn btn-outline-warning btn-sm"><i class="bi bi-archive me-1"></i>Archived ({{ $archivedCount }})</a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($showArchived)
<div class="alert alert-warning mb-3"><i class="bi bi-archive me-2"></i>Showing <strong>archived employees</strong>. Records are preserved and can be restored.</div>
@endif

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6><i class="bi bi-people me-2"></i>{{ $showArchived?'Archived':'Active' }} Employees</h6>
        <span class="badge bg-secondary">{{ $employees->total() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>ID</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Type</th>
                    <th>Hire Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $emp->avatar_url }}" alt="{{ $emp->full_name }}"
                                 class="rounded-circle" style="width:34px;height:34px;object-fit:cover;"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($emp->full_name) }}&background=253D90&color=fff&size=34&bold=true'">
                            <div>
                                <div class="fw-600 small">{{ $emp->full_name }}</div>
                                <div class="text-muted" style="font-size:.72rem;">{{ $emp->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td><code class="small">{{ $emp->employee_id }}</code></td>
                    <td class="small">{{ $emp->department?->name ?? '—' }}</td>
                    <td class="small">{{ $emp->position }}</td>
                    <td><span class="badge bg-light text-dark border">{{ str_replace('_',' ',ucwords($emp->employment_type,'_')) }}</span></td>
                    <td class="small text-muted">{{ $emp->hire_date->format('M d, Y') }}</td>
                    <td>{!! $emp->status_badge !!}</td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('employees.show',$emp) }}" class="btn btn-sm btn-outline-secondary" title="View Profile"><i class="bi bi-person-lines-fill"></i></a>
                            @if(!$emp->isArchived())
                                <a href="{{ route('employees.edit',$emp) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('employees.archive',$emp) }}" onsubmit="return confirm('Archive {{ addslashes($emp->full_name) }}? Data will be preserved.')">
                                    @csrf <button class="btn btn-sm btn-outline-warning" title="Archive"><i class="bi bi-archive"></i></button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('employees.restore',$emp) }}">
                                    @csrf <button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-people display-6 d-block mb-2"></i>No employees found.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }}</small>
        {{ $employees->links() }}
    </div>
    @endif
</div>
@endsection
