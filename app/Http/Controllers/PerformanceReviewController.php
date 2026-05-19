<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReview;
use App\Models\Employee;
use Illuminate\Http\Request;

class PerformanceReviewController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = PerformanceReview::with(['employee.department', 'reviewer']);

        // Employees see only their own published reviews
        if ($user->isEmployee()) {
            $query->where('employee_id', $user->employee->id)
                  ->where('status', 'published');
        }

        if ($request->status && !$user->isEmployee())  $query->where('status', $request->status);
        if ($request->employee_id)                       $query->where('employee_id', $request->employee_id);
        if ($request->rating)                            $query->where('rating', $request->rating);

        $reviews   = $query->latest('review_date')->paginate(15)->withQueryString();
        $employees = Employee::where('status', 'active')->get();

        // Stats
        $statsQuery = PerformanceReview::query();
        if ($user->isEmployee()) {
            $statsQuery->where('employee_id', $user->employee->id)->where('status', 'published');
        }
        $stats = [
            'total'     => (clone $statsQuery)->count(),
            'published' => (clone $statsQuery)->where('status', 'published')->count(),
            'draft'     => $user->isEmployee() ? 0 : (clone $statsQuery)->where('status', 'draft')->count(),
            'avg_rating'=> (clone $statsQuery)->where('status', 'published')->avg('rating'),
        ];

        return view('performance-reviews.index', compact('reviews', 'employees', 'stats'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->get();
        return view('performance-reviews.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'review_period' => 'required|string|max:50',
            'review_date'   => 'required|date',
            'rating'        => 'required|integer|min:1|max:5',
            'strengths'     => 'nullable|string|max:2000',
            'weaknesses'    => 'nullable|string|max:2000',
            'goals'         => 'nullable|string|max:2000',
            'comments'      => 'nullable|string|max:2000',
            'status'        => 'required|in:draft,published',
        ]);

        $validated['reviewer_id'] = auth()->id();

        PerformanceReview::create($validated);

        $msg = $validated['status'] === 'published'
            ? 'Performance review published successfully!'
            : 'Performance review saved as draft!';

        return redirect()->route('performance-reviews.index')->with('success', $msg);
    }

    public function show(PerformanceReview $performanceReview)
    {
        $user = auth()->user();
        // Employees can only see their own published reviews
        if ($user->isEmployee()) {
            if ($performanceReview->employee_id !== $user->employee->id || $performanceReview->status !== 'published') {
                abort(403);
            }
        }
        $performanceReview->load(['employee.department', 'reviewer']);
        return view('performance-reviews.show', compact('performanceReview'));
    }

    public function edit(PerformanceReview $performanceReview)
    {
        if ($performanceReview->status === 'published') {
            return back()->with('error', 'Published reviews cannot be edited. Create a new review instead.');
        }
        $employees = Employee::where('status', 'active')->get();
        return view('performance-reviews.edit', compact('performanceReview', 'employees'));
    }

    public function update(Request $request, PerformanceReview $performanceReview)
    {
        if ($performanceReview->status === 'published' && $request->status !== 'published') {
            return back()->with('error', 'Published reviews cannot be reverted to draft.');
        }

        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'review_period' => 'required|string|max:50',
            'review_date'   => 'required|date',
            'rating'        => 'required|integer|min:1|max:5',
            'strengths'     => 'nullable|string|max:2000',
            'weaknesses'    => 'nullable|string|max:2000',
            'goals'         => 'nullable|string|max:2000',
            'comments'      => 'nullable|string|max:2000',
            'status'        => 'required|in:draft,published',
        ]);

        $performanceReview->update($validated);

        return redirect()->route('performance-reviews.index')->with('success', 'Review updated successfully!');
    }

    public function destroy(PerformanceReview $performanceReview)
    {
        if ($performanceReview->status === 'published') {
            return back()->with('error', 'Published reviews cannot be deleted.');
        }
        $performanceReview->delete();
        return redirect()->route('performance-reviews.index')->with('success', 'Review deleted successfully!');
    }
}
