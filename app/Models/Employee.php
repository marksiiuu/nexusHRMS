<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','department_id','employee_id','biometric_id','first_name','last_name',
        'email','phone','address','position','employment_type','status',
        'hire_date','birth_date','gender','salary','avatar',
        'emergency_contact_name','emergency_contact_phone','archived_at'
    ];

    protected $casts = ['hire_date'=>'date','birth_date'=>'date','salary'=>'decimal:2','archived_at'=>'datetime'];

    public function getFullNameAttribute(): string  { return "{$this->first_name} {$this->last_name}"; }
    public function isArchived(): bool              { return !is_null($this->archived_at); }

    public function getAvatarUrlAttribute(): string
    {
        // Use UI Avatars as fallback — no broken images
        if ($this->avatar && file_exists(storage_path('app/public/avatars/'.$this->avatar))) {
            return asset('storage/avatars/'.$this->avatar);
        }
        $name = urlencode($this->first_name.' '.$this->last_name);
        return "https://ui-avatars.com/api/?name={$name}&background=253D90&color=fff&size=80&bold=true";
    }

    public function user()             { return $this->belongsTo(User::class); }
    public function department()       { return $this->belongsTo(Department::class); }
    public function attendances()      { return $this->hasMany(Attendance::class); }
    public function leaves()           { return $this->hasMany(Leave::class); }
    public function payrolls()         { return $this->hasMany(Payroll::class); }
    public function biometricLogs()    { return $this->hasMany(BiometricLog::class); }
    public function managedDepartment(){ return $this->hasOne(Department::class,'manager_id'); }

    public function scopeActive($q)   { return $q->whereNull('archived_at'); }
    public function scopeArchived($q) { return $q->whereNotNull('archived_at'); }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active'     => '<span class="badge bg-success">Active</span>',
            'inactive'   => '<span class="badge bg-secondary">Inactive</span>',
            'terminated' => '<span class="badge bg-danger">Terminated</span>',
            'on_leave'   => '<span class="badge bg-warning text-dark">On Leave</span>',
            default      => '<span class="badge bg-secondary">'.ucfirst($this->status).'</span>',
        };
    }
}
