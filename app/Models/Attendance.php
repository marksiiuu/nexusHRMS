<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id','date','time_in','time_out','hours_worked','status','notes','archived_at'];
    protected $casts = [
        'date' => 'date',
        'archived_at' => 'datetime'
    ];

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function employee() { return $this->belongsTo(Employee::class); }

    public function calculateHoursWorked(): ?float
    {
        if ($this->time_in && $this->time_out) {
            $in = strtotime($this->time_in);
            $out = strtotime($this->time_out);
            return round(($out - $in) / 3600, 2);
        }
        return null;
    }
}
