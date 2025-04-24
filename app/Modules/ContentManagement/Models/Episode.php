<?php

namespace App\Modules\ContentManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'duration',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function protocols()
    {
        return $this->belongsToMany(Protocol::class);
    }

    public function summaries()
    {
        return $this->hasMany(Summary::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
