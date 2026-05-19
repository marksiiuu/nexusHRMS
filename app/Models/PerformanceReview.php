<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'reviewer_id', 'review_period', 'review_date',
        'rating', 'strengths', 'weaknesses', 'goals', 'comments', 'status',
    ];

    protected $casts = [
        'review_date' => 'date',
        'rating'      => 'integer',
    ];

    /* ── Relationships ─────────────────────────────────── */

    public function employee() { return $this->belongsTo(Employee::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewer_id'); }

    /* ── Accessors ─────────────────────────────────────── */

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'     => '<span class="badge bg-warning text-dark">Draft</span>',
            'published' => '<span class="badge bg-success">Published</span>',
            default     => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getRatingStarsAttribute(): string
    {
        $filled = str_repeat('<i class="bi bi-star-fill text-warning"></i>', $this->rating);
        $empty  = str_repeat('<i class="bi bi-star text-muted"></i>', 5 - $this->rating);
        return $filled . $empty;
    }

    public function getRatingLabelAttribute(): string
    {
        return match ($this->rating) {
            1 => 'Needs Improvement',
            2 => 'Below Expectations',
            3 => 'Meets Expectations',
            4 => 'Exceeds Expectations',
            5 => 'Outstanding',
            default => 'N/A',
        };
    }
}
