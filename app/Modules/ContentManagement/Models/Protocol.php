<?php

namespace App\Modules\ContentManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Protocol extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'implementation_guide',
        'category', // Added category field
    ];

    public function episodes()
    {
        return $this->belongsToMany(Episode::class);
    }

    public function scopeFoundational($query)
    {
        return $query->where('category', 'foundational');
    }
}
