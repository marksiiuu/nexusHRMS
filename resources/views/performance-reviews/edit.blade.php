@extends('layouts.app')
@section('title','Edit Performance Review')
@section('page-title','Edit Performance Review')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Edit Performance Review</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('performance-reviews.index') }}">Performance Reviews</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
    </div>
    <a href="{{ route('performance-reviews.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
<div class="col-lg-9">
<div class="card">
    <div class="card-header"><h6><i class="bi bi-pencil me-2"></i>Edit Review</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('performance-reviews.update', $performanceReview) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Employee <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id',$performanceReview->employee_id)==$emp->id?'selected':'' }}>{{ $emp->full_name }} — {{ $emp->position }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Review Period <span class="text-danger">*</span></label>
                    <input type="text" name="review_period" class="form-control" value="{{ old('review_period',$performanceReview->review_period) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Review Date <span class="text-danger">*</span></label>
                    <input type="date" name="review_date" class="form-control" value="{{ old('review_date',$performanceReview->review_date->format('Y-m-d')) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Overall Rating <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center gap-2">
                        <select name="rating" class="form-select" required style="max-width:140px;">
                            <option value="">Select</option>
                            @for($i=1;$i<=5;$i++)
                            <option value="{{ $i }}" {{ old('rating',$performanceReview->rating)==$i?'selected':'' }}>{{ $i }} — {{ ['','Needs Improvement','Below Expectations','Meets Expectations','Exceeds Expectations','Outstanding'][$i] }}</option>
                            @endfor
                        </select>
                        <div id="ratingPreview" class="ms-1">{!! $performanceReview->rating_stars !!}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="draft" {{ old('status',$performanceReview->status)=='draft'?'selected':'' }}>Draft</option>
                        <option value="published" {{ old('status',$performanceReview->status)=='published'?'selected':'' }}>Published (visible to employee)</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Strengths</label>
                    <textarea name="strengths" class="form-control" rows="4">{{ old('strengths',$performanceReview->strengths) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Areas for Improvement</label>
                    <textarea name="weaknesses" class="form-control" rows="4">{{ old('weaknesses',$performanceReview->weaknesses) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Goals for Next Period</label>
                    <textarea name="goals" class="form-control" rows="4">{{ old('goals',$performanceReview->goals) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">General Comments</label>
                    <textarea name="comments" class="form-control" rows="4">{{ old('comments',$performanceReview->comments) }}</textarea>
                </div>
            </div>
            <hr>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('performance-reviews.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Update Review</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.querySelector('select[name="rating"]')?.addEventListener('change',function(){
    const v=parseInt(this.value)||0, p=document.getElementById('ratingPreview');
    if(!p) return;
    let s='';
    for(let i=1;i<=5;i++) s+=`<i class="bi bi-star${i<=v?'-fill':''} ${i<=v?'text-warning':'text-muted'}"></i>`;
    p.innerHTML=s;
});
</script>
@endpush
