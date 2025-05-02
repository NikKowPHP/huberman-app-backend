<?php

namespace App\Services;

interface NoteServiceInterface
{
    public function categorizeNote(int $noteId, string $category): void;
    public function tagNote(int $noteId, array $tags): void;
    public function getNotesByCategory(string $category): array;
    public function getNotesByTag(string $tag): array;
}
