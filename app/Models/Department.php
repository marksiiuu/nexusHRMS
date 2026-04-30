<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name','code','description','manager_id','is_active','positions'];
    protected $casts = ['is_active' => 'boolean', 'positions' => 'array'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function getActiveEmployeesCountAttribute(): int
    {
        return $this->employees()->where('status','active')->count();
    }
}
