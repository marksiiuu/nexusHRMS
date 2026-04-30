<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_posting_id','applicant_name','applicant_email','applicant_phone',
        'cover_letter','resume','status','notes','archived_at'
    ];
    protected $casts = ['archived_at'=>'datetime'];

    public function jobPosting() { return $this->belongsTo(JobPosting::class); }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'   => '<span class="badge bg-warning text-dark">Pending</span>',
            'reviewing' => '<span class="badge bg-info">Reviewing</span>',
            'interview' => '<span class="badge bg-primary">Interview</span>',
            'hired'     => '<span class="badge bg-success">Hired</span>',
            'rejected'  => '<span class="badge bg-danger">Rejected</span>',
            default     => '<span class="badge bg-secondary">'.ucfirst($this->status).'</span>',
        };
    }
}
