<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;


    protected $fillable = [
        'title',
        'details',
        'location',
        'coordinates',
        'schedule',
        'deadline',
        'type',
        'requestor_id',
        'department_id',
        'incharge_id',
        'pending_marker_id',
        'completed_marker_id',
        'pending_reason',
        'remarks',
        'status',
        'd_status',
    ];


    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }
    public function incharge()
    {
        return $this->belongsTo(User::class, 'incharge_id');
    }
    public function pending_marker()
    {
        return $this->belongsTo(User::class, 'pending_marker_id');
    }
    public function completed_marker()
    {
        return $this->belongsTo(User::class, 'completed_marker_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
