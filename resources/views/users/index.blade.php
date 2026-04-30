@extends('layouts.app')
@section('title','User Management')
@section('page-title','User Management')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1><i class="bi bi-shield-person me-2"></i>User Management</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Users</li>
        </ol></nav>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Add User</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search name or email…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    @foreach(\App\Models\User::ROLES as $val=>$lbl)
                    <option value="{{ $val }}" {{ request('role')==$val?'selected':'' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            </div>
            <div class="col-auto ms-auto">
                @if($showArchived)
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-people me-1"></i>Show Active
                    </a>
                @else
                    <a href="{{ route('users.index') }}?archived=1" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-archive me-1"></i>Archived ({{ $archivedCount }})
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($showArchived)
<div class="alert alert-warning mb-3"><i class="bi bi-archive me-2"></i>Showing <strong>archived users</strong>. Data is preserved but accounts are deactivated.</div>
@endif

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6><i class="bi bi-shield-person me-2"></i>{{ $showArchived ? 'Archived' : 'Active' }} Users</h6>
        <span class="badge bg-secondary">{{ $users->total() }} users</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>User</th><th>Role</th><th>Default Password</th><th>Status</th><th>Joined</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="emp-avatar" style="width:34px;height:34px;font-size:.78rem;background:#E3EDF9;color:#253D90;">{{ strtoupper(substr($user->name,0,2)) }}</div>
                            <div>
                                <div class="small fw-600">{{ $user->name }}</div>
                                <div class="text-muted" style="font-size:.72rem;">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php $rc=['admin'=>'danger','hr_manager'=>'primary','payroll_officer'=>'success','job_recruiter'=>'info','employee'=>'secondary'][$user->role]??'secondary'; @endphp
                        <span class="badge bg-{{ $rc }}">{{ $user->getRoleLabel() }}</span>
                    </td>
                    <td>
                        @if($user->default_password)
                            <div class="d-flex align-items-center gap-1">
                                <code class="small pw-text" id="pw_{{ $user->id }}" style="filter:blur(4px);">{{ $user->default_password }}</code>
                                <button type="button" class="btn btn-outline-secondary" style="font-size:.65rem;padding:.15rem .35rem;line-height:1;"
                                    onclick="const e=document.getElementById('pw_{{ $user->id }}');e.style.filter=e.style.filter?'':'blur(4px)'">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                            <div style="font-size:.67rem;color:#f57c00;margin-top:.15rem;">Not yet changed</div>
                        @else
                            <span class="text-muted small">Password changed</span>
                        @endif
                    </td>
                    <td>
                        @if($user->isArchived())<span class="badge bg-warning text-dark">Archived</span>
                        @elseif($user->is_active)<span class="badge bg-success">Active</span>
                        @else<span class="badge bg-danger">Inactive</span>@endif
                    </td>
                    <td class="small text-muted">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end flex-wrap">
                            @if(!$user->isArchived())
                                <a href="{{ route('users.edit',$user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('users.reset-password',$user) }}" onsubmit="return confirm('Reset password for {{ addslashes($user->name) }}?')">
                                    @csrf <button class="btn btn-sm btn-outline-secondary" title="Reset Password"><i class="bi bi-key"></i></button>
                                </form>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.archive',$user) }}" onsubmit="return confirm('Archive {{ addslashes($user->name) }}? Data will be preserved.')">
                                    @csrf <button class="btn btn-sm btn-outline-warning" title="Archive"><i class="bi bi-archive"></i></button>
                                </form>
                                @endif
                            @else
                                <form method="POST" action="{{ route('users.restore',$user) }}">
                                    @csrf <button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</small>
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
