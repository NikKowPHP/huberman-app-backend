<?php

namespace App\Modules\NotesService\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
    ];

    public function notes()
    {
        return $this->belongsToMany(Note::class, 'note_category_pivot');
    }
}
