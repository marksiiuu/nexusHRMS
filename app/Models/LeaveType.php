<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;
    protected $fillable = ['name','code','max_days_per_year','is_paid','description'];
    protected $casts = ['is_paid'=>'boolean'];
    public function leaves() { return $this->hasMany(Leave::class); }
}
