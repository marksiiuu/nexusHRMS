@extends('layouts.app')
@section('title','Applications')
@section('page-title','Job Applications')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Applications — {{ $jobPosting->title }}</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('recruitment.index') }}">Recruitment</a></li>
            <li class="breadcrumb-item active">Applications</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2 align-items-center">
        {!! $jobPosting->status_badge !!}
        <span class="badge bg-primary">{{ $jobPosting->applications->count() }} applicant(s)</span>
        <a href="{{ route('recruitment.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card">
    <div class="card-header"><h6><i class="bi bi-people me-2"></i>Applicants</h6></div>
    @if($jobPosting->applications->isEmpty())
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-inbox display-4 d-block mb-3"></i>No applications yet.
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Applicant</th><th>Contact</th><th>Applied</th><th>Status</th><th class="text-end">Update Status</th></tr></thead>
            <tbody>
                @foreach($jobPosting->applications as $app)
                <tr>
                    <td>
                        <div class="small fw-600">{{ $app->applicant_name }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $app->applicant_email }}</div>
                    </td>
                    <td class="small">{{ $app->applicant_phone ?? '—' }}</td>
                    <td class="small text-muted">{{ $app->created_at->format('M d, Y') }}</td>
                    <td>{!! $app->status_badge !!}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('applications.status',$app) }}" class="d-flex gap-1 justify-content-end align-items-center">
                            @csrf
                            <select name="status" class="form-select form-select-sm" style="width:130px;">
                                @foreach(['pending','reviewing','interview','hired','rejected'] as $s)
                                <option value="{{ $s }}" {{ $app->status==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
