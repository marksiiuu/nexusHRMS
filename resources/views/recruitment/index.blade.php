@extends('layouts.app')
@section('title','Recruitment')
@section('page-title','Recruitment')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Job Postings</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Recruitment</li>
        </ol></nav>
    </div>
    <a href="{{ route('recruitment.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>New Job Posting</a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search job title…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['draft','open','closed'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('recruitment.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            </div>
            <div class="col-auto ms-auto">
                @if($showArchived)
                    <a href="{{ route('recruitment.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-briefcase me-1"></i>Show Active</a>
                @else
                    <a href="{{ route('recruitment.index') }}?archived=1" class="btn btn-outline-warning btn-sm"><i class="bi bi-archive me-1"></i>Archived ({{ $archivedCount }})</a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($showArchived)
<div class="alert alert-warning mb-3"><i class="bi bi-archive me-2"></i>Showing <strong>archived job postings</strong>. These are no longer visible to applicants.</div>
@endif

<div class="row g-3">
    @forelse($postings as $post)
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <h6 class="fw-700 mb-1">{{ $post->title }}</h6>
                        <div class="text-muted small">{{ $post->department?->name ?? 'No Department' }}</div>
                    </div>
                    {!! $post->status_badge !!}
                </div>
                <p class="text-muted small mb-3">{{ Str::limit(strip_tags($post->description), 100) }}</p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-light text-dark border">{{ str_replace('_',' ',ucfirst($post->employment_type)) }}</span>
                    <span class="badge bg-light text-dark border">{{ $post->slots }} slot(s)</span>
                    @if($post->salary_min)
                    <span class="badge bg-light text-dark border">₱{{ number_format($post->salary_min) }}–{{ number_format($post->salary_max) }}</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <div>
                        <span class="badge bg-primary">{{ $post->applications->count() }} applicant(s)</span>
                        @if($post->deadline)
                        <div class="text-muted" style="font-size:.72rem;margin-top:.2rem;">Deadline: {{ $post->deadline->format('M d, Y') }}</div>
                        @endif
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('recruitment.applications',$post) }}" class="btn btn-sm btn-outline-primary" title="Applications">
                            <i class="bi bi-people"></i>
                        </a>
                        <a href="{{ route('recruitment.edit',$post) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('recruitment.archive',$post) }}" onsubmit="return confirm('Archive this job posting?')">
                            @csrf
                            <button class="btn btn-sm btn-outline-warning" title="Archive"><i class="bi bi-archive"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="bi bi-briefcase display-4 d-block mb-3"></i>No {{ $showArchived ? 'archived' : '' }} job postings found.
            @if(!$showArchived)
            <a href="{{ route('recruitment.create') }}" class="btn btn-primary mt-2">Create First Posting</a>
            @endif
        </div></div>
    </div>
    @endforelse
</div>
@if($postings->hasPages())
<div class="mt-3 d-flex justify-content-center">{{ $postings->links() }}</div>
@endif
@endsection
