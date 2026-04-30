@extends('layouts.app')
@section('title', $department->name)
@section('page-title','Department Details')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>{{ $department->name }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
            <li class="breadcrumb-item active">{{ $department->name }}</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('departments.edit',$department) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="stat-icon mb-3" style="background:#E3EDF9;color:#253D90;width:52px;height:52px;border-radius:14px;"><i class="bi bi-diagram-3"></i></div>
                <h5 class="fw-700">{{ $department->name }}</h5>
                <code class="text-muted">{{ $department->code }}</code>
                <p class="text-muted small mt-2">{{ $department->description ?? 'No description.' }}</p>
                <hr>
                <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Status</span><span class="badge {{ $department->is_active?'bg-success':'bg-secondary' }}">{{ $department->is_active?'Active':'Inactive' }}</span></div>
                <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Manager</span><span class="fw-500">{{ $department->manager?->full_name ?? '—' }}</span></div>
                <div class="d-flex justify-content-between small"><span class="text-muted">Total Employees</span><span class="fw-700" style="color:#253D90;">{{ $department->employees->count() }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="bi bi-people me-2"></i>Employees in this Department</h6>
                <span class="badge bg-secondary">{{ $department->employees->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Position</th><th>Type</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($department->employees as $emp)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="emp-avatar" style="width:30px;height:30px;font-size:.7rem;">{{ strtoupper(substr($emp->first_name,0,1).substr($emp->last_name,0,1)) }}</div>
                                    <a href="{{ route('employees.show',$emp) }}" class="small fw-500 text-decoration-none">{{ $emp->full_name }}</a>
                                </div>
                            </td>
                            <td class="small">{{ $emp->position }}</td>
                            <td class="small text-muted">{{ str_replace('_',' ',ucfirst($emp->employment_type)) }}</td>
                            <td>{!! $emp->status_badge !!}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No employees in this department.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
