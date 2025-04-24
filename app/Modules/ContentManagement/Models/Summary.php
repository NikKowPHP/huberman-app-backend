<?php

namespace App\Modules\ContentManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Summary extends Model
{
    use HasFactory;

    protected $fillable = [
        'episode_id',
        'content',
    ];

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }
}
