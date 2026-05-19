<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'assigned_by', 'title', 'description',
        'priority', 'status', 'due_date', 'completed_at', 'remarks',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'completed_at' => 'datetime',
    ];

    /* ── Relationships ─────────────────────────────────── */

    public function employee() { return $this->belongsTo(Employee::class); }
    public function assigner() { return $this->belongsTo(User::class, 'assigned_by'); }

    /* ── Accessors ─────────────────────────────────────── */

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'     => '<span class="badge bg-warning text-dark">Pending</span>',
            'in_progress' => '<span class="badge bg-info text-dark">In Progress</span>',
            'completed'   => '<span class="badge bg-success">Completed</span>',
            'cancelled'   => '<span class="badge bg-secondary">Cancelled</span>',
            default       => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match ($this->priority) {
            'low'    => '<span class="badge bg-light text-dark border">Low</span>',
            'medium' => '<span class="badge bg-primary">Medium</span>',
            'high'   => '<span class="badge bg-warning text-dark">High</span>',
            'urgent' => '<span class="badge bg-danger">Urgent</span>',
            default  => '<span class="badge bg-secondary">' . ucfirst($this->priority) . '</span>',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && !in_array($this->status, ['completed', 'cancelled'])
            && $this->due_date->isPast();
    }

    /* ── Scopes ────────────────────────────────────────── */

    public function scopeForEmployee($q, $employeeId) { return $q->where('employee_id', $employeeId); }
}
