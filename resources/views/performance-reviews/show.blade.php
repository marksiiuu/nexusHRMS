@extends('layouts.app')
@section('title','Performance Review Details')
@section('page-title','Performance Review Details')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Performance Review</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('performance-reviews.index') }}">Performance Reviews</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(auth()->user()->hasHrAccess() && $performanceReview->status === 'draft')
        <a href="{{ route('performance-reviews.edit', $performanceReview) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        <a href="{{ route('performance-reviews.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row justify-content-center">
<div class="col-lg-9">
<div class="card">
    <div class="card-body">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="emp-avatar" style="width:56px;height:56px;font-size:1.3rem;">
                    {{ strtoupper(substr($performanceReview->employee->first_name,0,1).substr($performanceReview->employee->last_name,0,1)) }}
                </div>
                <div>
                    <h5 class="mb-0 fw-700">{{ $performanceReview->employee->full_name }}</h5>
                    <div class="text-muted small">{{ $performanceReview->employee->position }} — {{ $performanceReview->employee->department?->name }}</div>
                </div>
            </div>
            {!! $performanceReview->status_badge !!}
        </div>
        <hr>

        <!-- Rating Card -->
        <div class="text-center py-4 mb-4 rounded-3" style="background:linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%);">
            <div class="mb-2" style="font-size:1.8rem;">{!! $performanceReview->rating_stars !!}</div>
            <div class="fw-800" style="font-size:2.2rem;color:#253D90;">{{ $performanceReview->rating }}/5</div>
            <div class="text-muted fw-600">{{ $performanceReview->rating_label }}</div>
            <div class="mt-1"><span class="badge bg-light text-dark border">{{ $performanceReview->review_period }}</span></div>
        </div>

        <!-- Details Grid -->
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small fw-600 mb-1">Review Period</div>
                <div class="fw-600">{{ $performanceReview->review_period }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small fw-600 mb-1">Review Date</div>
                <div>{{ $performanceReview->review_date->format('F d, Y') }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small fw-600 mb-1">Reviewed By</div>
                <div>{{ $performanceReview->reviewer->name ?? '—' }}</div>
            </div>

            @if($performanceReview->strengths)
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1"><i class="bi bi-hand-thumbs-up text-success me-1"></i>Strengths</div>
                <div class="p-3 rounded" style="background:#d1e7dd;">{{ $performanceReview->strengths }}</div>
            </div>
            @endif

            @if($performanceReview->weaknesses)
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1"><i class="bi bi-arrow-up-circle text-warning me-1"></i>Areas for Improvement</div>
                <div class="p-3 rounded" style="background:#fff3cd;">{{ $performanceReview->weaknesses }}</div>
            </div>
            @endif

            @if($performanceReview->goals)
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1"><i class="bi bi-bullseye text-primary me-1"></i>Goals for Next Period</div>
                <div class="p-3 rounded" style="background:#cfe2ff;">{{ $performanceReview->goals }}</div>
            </div>
            @endif

            @if($performanceReview->comments)
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1"><i class="bi bi-chat-left-text text-secondary me-1"></i>General Comments</div>
                <div class="p-3 rounded" style="background:#f8f9fa;">{{ $performanceReview->comments }}</div>
            </div>
            @endif

            <div class="col-12">
                <hr>
                <div class="d-flex justify-content-between text-muted small">
                    <span>Created: {{ $performanceReview->created_at->format('M d, Y h:i A') }}</span>
                    @if($performanceReview->updated_at->ne($performanceReview->created_at))
                    <span>Last Updated: {{ $performanceReview->updated_at->format('M d, Y h:i A') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
@endsection
