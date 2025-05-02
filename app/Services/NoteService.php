<?php

namespace App\Services;

use App\Models\Note;
use App\Models\NoteCategory;
use App\Models\NoteTag;

class NoteService implements \App\Services\NoteServiceInterface
{
    public function categorizeNote(int $noteId, string $category): void
    {
        $note = Note::findOrFail($noteId);
        $noteCategory = NoteCategory::firstOrCreate(['name' => $category]);
        $note->categories()->syncWithoutDetach([$noteCategory->id]);
    }

    public function tagNote(int $noteId, array $tags): void
    {
        $note = Note::findOrFail($noteId);
        $tags = array_map(function ($tag) {
            return ['name' => $tag];
        }, $tags);
        $tagId = NoteTag::firstOrCreate(['name' => $tags[0]['name']])->id;
        $note->tags()->syncWithoutDetach([$tagId]);
    }

    public function getNotesByCategory(string $category): array
    {
        return NoteCategory::where('name', $category)
            ->firstOrFail()
            ->notes()
            ->get()
            ->toArray();
    }

    public function getNotesByTag(string $tag): array
    {
        return NoteTag::where('name', $tag)
            ->firstOrFail()
            ->notes()
            ->get()
            ->toArray();
    }
}
