<?php

namespace App\Models;

use Illuminate\Console\View\Components\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_Image extends Model
{
    use HasFactory;

    protected $table = 'task_images';

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
