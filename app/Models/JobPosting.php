<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    protected $fillable = [
        'title','department_id','description','requirements','employment_type',
        'slots','salary_min','salary_max','deadline','status','created_by','archived_at'
    ];
    protected $casts = ['deadline'=>'date','archived_at'=>'datetime','salary_min'=>'decimal:2','salary_max'=>'decimal:2'];

    public function department()    { return $this->belongsTo(Department::class); }
    public function creator()       { return $this->belongsTo(User::class,'created_by'); }
    public function applications()  { return $this->hasMany(JobApplication::class); }
    public function scopeActive($q) { return $q->whereNull('archived_at'); }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft'    => '<span class="badge bg-secondary">Draft</span>',
            'open'     => '<span class="badge bg-success">Open</span>',
            'closed'   => '<span class="badge bg-danger">Closed</span>',
            'archived' => '<span class="badge bg-warning text-dark">Archived</span>',
            default    => '<span class="badge bg-secondary">'.ucfirst($this->status).'</span>',
        };
    }
}
