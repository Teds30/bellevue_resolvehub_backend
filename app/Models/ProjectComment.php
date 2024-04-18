<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'commentor_id',
        'project_id',
        'comment',
        'status',
    ];


    public function commentor()
    {
        return $this->belongsTo(User::class, 'commentor_id');
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
