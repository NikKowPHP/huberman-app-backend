<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutineStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'routine_id',
        'name',
        'description',
        'duration',
        'order',
        'is_optional'
    ];

    protected $casts = [
        'is_optional' => 'boolean',
        'duration' => 'integer',
        'order' => 'integer'
    ];

    public function routine()
    {
        return $this->belongsTo(Routine::class);
    }
}
