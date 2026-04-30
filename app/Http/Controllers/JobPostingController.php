<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobPostingController extends Controller
{
    public function index(Request $request)
    {
        $showArchived = $request->has('archived');
        
        $query = JobPosting::with(['department','creator','applications']);

        if ($showArchived) {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }

        if ($request->status)        $query->where('status',$request->status);
        if ($request->department_id) $query->where('department_id',$request->department_id);
        if ($request->search) {
            $query->where('title','like',"%{$request->search}%");
        }

        $postings      = $query->latest()->paginate(15)->withQueryString();
        $departments   = Department::whereNull('archived_at')->get();
        $archivedCount = JobPosting::whereNotNull('archived_at')->count();

        return view('recruitment.index', compact('postings','departments', 'showArchived', 'archivedCount'));
    }

    public function create()
    {
        $departments = Department::whereNull('archived_at')->get();
        return view('recruitment.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:200',
            'department_id'   => 'nullable|exists:departments,id',
            'description'     => 'required|string',
            'requirements'    => 'nullable|string',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'slots'           => 'required|integer|min:1',
            'salary_min'      => 'nullable|numeric|min:0',
            'salary_max'      => 'nullable|numeric|min:0|gte:salary_min',
            'deadline'        => 'nullable|date|after_or_equal:today',
            'status'          => 'required|in:draft,open,closed',
        ]);

        $validated['created_by'] = auth()->id();
        JobPosting::create($validated);

        return redirect()->route('recruitment.index')->with('success','Job posting created successfully!');
    }

    public function show(JobPosting $jobPosting)
    {
        $jobPosting->load(['department','creator','applications']);
        return view('recruitment.show', compact('jobPosting'));
    }

    public function edit(JobPosting $jobPosting)
    {
        $departments = Department::whereNull('archived_at')->get();
        return view('recruitment.edit', compact('jobPosting','departments'));
    }

    public function update(Request $request, JobPosting $jobPosting)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:200',
            'department_id'   => 'nullable|exists:departments,id',
            'description'     => 'required|string',
            'requirements'    => 'nullable|string',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'slots'           => 'required|integer|min:1',
            'salary_min'      => 'nullable|numeric|min:0',
            'salary_max'      => 'nullable|numeric|min:0',
            'deadline'        => 'nullable|date',
            'status'          => 'required|in:draft,open,closed',
        ]);

        $jobPosting->update($validated);
        return redirect()->route('recruitment.index')->with('success','Job posting updated!');
    }

    public function archive(JobPosting $jobPosting)
    {
        $jobPosting->update(['archived_at' => now(), 'status' => 'archived']);
        return redirect()->route('recruitment.index')->with('success','Job posting archived!');
    }

    // Applications
    public function applications(JobPosting $jobPosting)
    {
        $jobPosting->load('applications');
        return view('recruitment.applications', compact('jobPosting'));
    }

    public function updateApplicationStatus(Request $request, JobApplication $application)
    {
        $request->validate(['status' => 'required|in:pending,reviewing,interview,hired,rejected']);
        $application->update(['status' => $request->status, 'notes' => $request->notes]);
        return back()->with('success','Application status updated!');
    }
}
