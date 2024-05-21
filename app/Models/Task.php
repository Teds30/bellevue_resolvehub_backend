<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;


    protected $fillable = [
        'room',
        'issue',
        'details',
        'requestor_id',
        'department_id',
        'pending_marker_id',
        'completed_marker_id',
        'pending_reason',
        'action_taken',
        'remarks',
        'assignee_id',
        'assignor_id',
        'assigned_timestamp',
        'status',
        'priority',
        'schedule',
        'd_status',
    ];

    // public function issue()
    // {
    //     return $this->belongsTo(Issue::class, 'issue_id');
    // }
    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
    public function assignor()
    {
        return $this->belongsTo(User::class, 'assignor_id');
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

    public function task_images()
    {
        return $this->hasMany(Task_Image::class);
    }

    public static function onGoing()
    {
        return self::whereDate('schedule', '<=', Carbon::today())
            ->where('completed_marker_id', null)
            ->where('d_status', 1)
            ->where('status', '!=', 3);
    }
    public static function pending()
    {
        return self::whereDate('schedule', '>', Carbon::today())
            ->where('completed_marker_id', null)
            ->where('d_status', 1);
    }
    public static function unassigned()
    {
        return self::where('assignee_id', null)
            ->where('status', 0)->where('d_status', 1);
    }
    public static function cancelled()
    {
        return self::where('status', 3)->where('d_status', 1);
    }
    public static function done()
    {
        return self::where('status', 4)
            ->where('completed_marker_id', '!=', null)
            ->where('d_status', 1);
    }

    public static function getStatus($task)
    {
        $isOnGoing = Task::onGoing()
            ->where('id', $task->id)
            ->exists();
        $isPending = Task::pending()
            ->where('id', $task->id)
            ->exists();
        $isUnassigned = Task::unassigned()
            ->where('id', $task->id)
            ->exists();
        $isDone = Task::done()
            ->where('id', $task->id)
            ->exists();
        $isCancelled = Task::cancelled()
            ->where('id', $task->id)
            ->exists();

        if ($isOnGoing) {
            return 'on-going';
        } elseif ($isPending) {
            return 'pending';
        } elseif ($isUnassigned) {
            return 'unassigned';
        } elseif ($isDone) {
            return 'done';
        } elseif ($isCancelled) {
            return 'cancelled';
        }
    }
}
