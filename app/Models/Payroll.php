<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id','period_month','year','month','basic_salary','overtime_pay',
        'allowances','gross_salary','tax_deduction','sss_deduction',
        'philhealth_deduction','pagibig_deduction','other_deductions',
        'total_deductions','net_salary','days_worked','days_absent',
        'status','pay_date','notes','processed_by', 'archived_at'
    ];

    protected $casts = [
        'pay_date'=>'date',
        'basic_salary'=>'decimal:2','overtime_pay'=>'decimal:2',
        'allowances'=>'decimal:2','gross_salary'=>'decimal:2',
        'tax_deduction'=>'decimal:2','sss_deduction'=>'decimal:2',
        'philhealth_deduction'=>'decimal:2','pagibig_deduction'=>'decimal:2',
        'other_deductions'=>'decimal:2','total_deductions'=>'decimal:2',
        'net_salary'=>'decimal:2',
        'archived_at'=>'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function employee() { return $this->belongsTo(Employee::class); }
    public function processor() { return $this->belongsTo(User::class,'processed_by'); }

    public function getMonthNameAttribute(): string
    {
        return date('F', mktime(0,0,0,$this->month,1));
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'processed' => '<span class="badge bg-primary">Processed</span>',
            'paid' => '<span class="badge bg-success">Paid</span>',
            default => '<span class="badge bg-secondary">'.ucfirst($this->status).'</span>',
        };
    }
}
