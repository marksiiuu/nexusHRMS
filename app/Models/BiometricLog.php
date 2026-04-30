<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricLog extends Model
{
    protected $fillable = ['employee_id','biometric_id','log_time','log_type','device_id','processed'];
    protected $casts    = ['log_time'=>'datetime','processed'=>'boolean'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
