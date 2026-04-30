@extends('layouts.app')
@section('title', $jobPosting->title)
@section('page-title','Job Posting Details')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>{{ $jobPosting->title }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('recruitment.index') }}">Recruitment</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('recruitment.applications',$jobPosting) }}" class="btn btn-primary">
            <i class="bi bi-people me-1"></i>Applications ({{ $jobPosting->applications->count() }})
        </a>
        <a href="{{ route('recruitment.edit',$jobPosting) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form method="POST" action="{{ route('recruitment.archive',$jobPosting) }}" onsubmit="return confirm('Archive this posting?')">
            @csrf
            <button class="btn btn-outline-warning"><i class="bi bi-archive me-1"></i>Archive</button>
        </form>
        <a href="{{ route('recruitment.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-file-text me-2"></i>Job Description</h6></div>
            <div class="card-body">
                <div style="white-space: pre-line; font-size: .88rem; line-height: 1.7;">{{ $jobPosting->description }}</div>
            </div>
        </div>
        @if($jobPosting->requirements)
        <div class="card">
            <div class="card-header"><h6><i class="bi bi-check2-square me-2"></i>Requirements</h6></div>
            <div class="card-body">
                <div style="white-space: pre-line; font-size: .88rem; line-height: 1.7;">{{ $jobPosting->requirements }}</div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-info-circle me-2"></i>Job Details</h6></div>
            <div class="card-body">
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted small">Status</span>
                    {!! $jobPosting->status_badge !!}
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted small">Department</span>
                    <span class="small fw-500">{{ $jobPosting->department?->name ?? '—' }}</span>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted small">Type</span>
                    <span class="small">{{ str_replace('_',' ',ucfirst($jobPosting->employment_type)) }}</span>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted small">Slots</span>
                    <span class="small fw-600" style="color:#253D90;">{{ $jobPosting->slots }}</span>
                </div>
                @if($jobPosting->salary_min)
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted small">Salary Range</span>
                    <span class="small">₱{{ number_format($jobPosting->salary_min) }} – ₱{{ number_format($jobPosting->salary_max) }}</span>
                </div>
                @endif
                @if($jobPosting->deadline)
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted small">Deadline</span>
                    <span class="small {{ $jobPosting->deadline->isPast() ? 'text-danger' : '' }}">
                        {{ $jobPosting->deadline->format('M d, Y') }}
                        @if($jobPosting->deadline->isPast()) <span class="badge bg-danger ms-1">Expired</span>@endif
                    </span>
                </div>
                @endif
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Posted by</span>
                    <span class="small">{{ $jobPosting->creator?->name ?? '—' }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6><i class="bi bi-bar-chart me-2"></i>Applications Summary</h6></div>
            <div class="card-body">
                @php
                    $apps = $jobPosting->applications;
                    $statusCounts = $apps->groupBy('status')->map->count();
                @endphp
                @foreach(['pending'=>'warning','reviewing'=>'info','interview'=>'primary','hired'=>'success','rejected'=>'danger'] as $st=>$cls)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">{{ ucfirst($st) }}</span>
                    <span class="badge bg-{{ $cls }}">{{ $statusCounts[$st] ?? 0 }}</span>
                </div>
                @endforeach
                <hr>
                <div class="d-flex justify-content-between fw-600 small">
                    <span>Total</span>
                    <span style="color:#253D90;">{{ $apps->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
