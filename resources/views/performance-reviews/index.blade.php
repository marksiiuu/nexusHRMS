@extends('layouts.app')
@section('title','Performance Reviews')
@section('page-title','Performance Reviews')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Performance Reviews</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Performance Reviews</li>
        </ol></nav>
    </div>
    @if(auth()->user()->hasHrAccess())
    <a href="{{ route('performance-reviews.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>New Review</a>
    @endif
</div>

<!-- Stats -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Reviews</div>
                </div>
                <div class="stat-icon" style="background:#E3EDF9;color:#253D90;"><i class="bi bi-graph-up-arrow"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value" style="color:#198754;">{{ $stats['published'] }}</div>
                    <div class="stat-label">Published</div>
                </div>
                <div class="stat-icon" style="background:#d1e7dd;color:#198754;"><i class="bi bi-check-circle"></i></div>
            </div>
        </div>
    </div>
    @if(auth()->user()->hasHrAccess())
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value" style="color:#ffc107;">{{ $stats['draft'] }}</div>
                    <div class="stat-label">Drafts</div>
                </div>
                <div class="stat-icon" style="background:#fff3cd;color:#ffc107;"><i class="bi bi-pencil-square"></i></div>
            </div>
        </div>
    </div>
    @endif
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value" style="color:#253D90;">{{ $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : '—' }}</div>
                    <div class="stat-label">Avg. Rating</div>
                </div>
                <div class="stat-icon" style="background:#fff3cd;color:#ffc107;"><i class="bi bi-star-fill"></i></div>
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
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status')=='draft'?'selected':'' }}>Draft</option>
                    <option value="published" {{ request('status')=='published'?'selected':'' }}>Published</option>
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <select name="rating" class="form-select form-select-sm">
                    <option value="">All Ratings</option>
                    @for($i=5;$i>=1;$i--)
                    <option value="{{ $i }}" {{ request('rating')==$i?'selected':'' }}>{{ $i }} Star{{ $i>1?'s':'' }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('performance-reviews.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6><i class="bi bi-graph-up-arrow me-2"></i>Performance Reviews</h6>
        <span class="badge bg-secondary">{{ $reviews->total() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Period</th>
                    <th>Review Date</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Reviewer</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="emp-avatar" style="width:30px;height:30px;font-size:.7rem;">{{ strtoupper(substr($review->employee->first_name,0,1).substr($review->employee->last_name,0,1)) }}</div>
                            <div>
                                <div class="small fw-500">{{ $review->employee->full_name }}</div>
                                <div class="text-muted" style="font-size:.7rem;">{{ $review->employee->department?->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ $review->review_period }}</span></td>
                    <td class="small">{{ $review->review_date->format('M d, Y') }}</td>
                    <td>
                        <div>{!! $review->rating_stars !!}</div>
                        <div class="text-muted" style="font-size:.65rem;">{{ $review->rating_label }}</div>
                    </td>
                    <td>{!! $review->status_badge !!}</td>
                    <td class="small text-muted">{{ $review->reviewer->name ?? '—' }}</td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end flex-wrap align-items-center">
                            <a href="{{ route('performance-reviews.show', $review) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                            @if(auth()->user()->hasHrAccess())
                                @if($review->status === 'draft')
                                <a href="{{ route('performance-reviews.edit', $review) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('performance-reviews.destroy', $review) }}" class="m-0" onsubmit="return confirm('Delete this draft review?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">
                    <i class="bi bi-graph-up-arrow display-6 d-block mb-2"></i>No performance reviews found.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reviews->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $reviews->firstItem() }}–{{ $reviews->lastItem() }} of {{ $reviews->total() }}</small>
        {{ $reviews->links() }}
    </div>
    @endif
</div>
@endsection
