<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id','leave_type_id','approved_by','start_date','end_date',
        'total_days','reason','status','rejection_reason','approved_at'
    ];

    protected $casts = ['start_date'=>'date','end_date'=>'date','approved_at'=>'datetime'];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function leaveType() { return $this->belongsTo(LeaveType::class); }
    public function approver() { return $this->belongsTo(User::class,'approved_by'); }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
            'approved' => '<span class="badge bg-success">Approved</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>',
            'cancelled' => '<span class="badge bg-secondary">Cancelled</span>',
            default => '<span class="badge bg-secondary">'.ucfirst($this->status).'</span>',
        };
    }
}
