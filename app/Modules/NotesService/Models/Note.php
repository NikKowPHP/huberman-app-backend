<?php

namespace App\Modules\NotesService\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'is_public',
    ];
}
