<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;


    protected $fillable = [
        'position_id',
        'access_name',
        'has_access',
        'd_status',
    ];

    
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
}
