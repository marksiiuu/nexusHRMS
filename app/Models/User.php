<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name','email','password','role','is_active','archived_at','default_password'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean','archived_at'=>'datetime'];

    const ROLE_ADMIN          = 'admin';
    const ROLE_HR_MANAGER     = 'hr_manager';
    const ROLE_PAYROLL_OFFICER= 'payroll_officer';
    const ROLE_JOB_RECRUITER  = 'job_recruiter';
    const ROLE_EMPLOYEE       = 'employee';

    const ROLES = [
        'admin'           => 'Administrator',
        'hr_manager'      => 'HR Manager',
        'payroll_officer' => 'Payroll Officer',
        'job_recruiter'   => 'Job Recruiter',
        'employee'        => 'Employee',
    ];

    public function isAdmin(): bool          { return $this->role === self::ROLE_ADMIN; }
    public function isHrManager(): bool      { return $this->role === self::ROLE_HR_MANAGER; }
    public function isPayrollOfficer(): bool { return $this->role === self::ROLE_PAYROLL_OFFICER; }
    public function isJobRecruiter(): bool   { return $this->role === self::ROLE_JOB_RECRUITER; }
    public function isEmployee(): bool       { return $this->role === self::ROLE_EMPLOYEE; }
    public function hasHrAccess(): bool      { return in_array($this->role,[self::ROLE_ADMIN,self::ROLE_HR_MANAGER]); }
    public function hasPayrollAccess(): bool { return in_array($this->role,[self::ROLE_ADMIN,self::ROLE_HR_MANAGER,self::ROLE_PAYROLL_OFFICER]); }
    public function hasRecruiterAccess(): bool { return in_array($this->role,[self::ROLE_ADMIN,self::ROLE_HR_MANAGER,self::ROLE_JOB_RECRUITER]); }
    public function canManageUsers(): bool   { return $this->role === self::ROLE_ADMIN; }
    public function isArchived(): bool       { return !is_null($this->archived_at); }

    public function getRoleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    public function employee()            { return $this->hasOne(Employee::class); }
    public function approvedLeaves()      { return $this->hasMany(Leave::class,'approved_by'); }
    public function processedPayrolls()   { return $this->hasMany(Payroll::class,'processed_by'); }
    public function jobPostings()         { return $this->hasMany(JobPosting::class,'created_by'); }

    // Scope: only active (not archived)
    public function scopeActive($query)   { return $query->whereNull('archived_at'); }
    public function scopeArchived($query) { return $query->whereNotNull('archived_at'); }
}
