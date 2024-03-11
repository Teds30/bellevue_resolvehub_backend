<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department_id',
        'd_status',
    ];

    public function people()
    {
        return $this->hasMany(User::class);
    }
}
