<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;


    protected $fillable = [
        'room',
        'issue_id',
        'details',
        'requestor_id',
        'department_id',
        'pending_marker_id',
        'completed_marker_id',
        'pending_reason',
        'action_taken',
        'remarks',
        'assignee_id',
        'status',
        'priority',
        'schedule',
        'd_status',
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class, 'issue_id');
    }
    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
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
