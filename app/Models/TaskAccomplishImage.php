<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAccomplishImage extends Model
{
    use HasFactory;

    protected $table = 'task_accomplish_images';

    protected $fillable = [
        'task_id',
        'url',
        'd_status',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
