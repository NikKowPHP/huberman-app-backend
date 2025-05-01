<?php

namespace App\Modules\NotesService\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
    ];

    public function notes()
    {
        return $this->belongsToMany(Note::class, 'note_tag_pivot');
    }
}
