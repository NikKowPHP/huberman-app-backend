<?php

namespace App\Modules\TrackingService\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingLog extends Model
{
    use HasFactory;

    protected $table = 'user_protocol_tracking';

    protected $fillable = [
        'user_id',
        'protocol_id',
        'tracked_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'tracked_at' => 'date',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Modules\UserManagement\Models\User::class);
    }

    public function protocol()
    {
        return $this->belongsTo(\App\Modules\ContentManagement\Models\Protocol::class);
    }
}
