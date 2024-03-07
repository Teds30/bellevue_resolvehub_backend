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
}
