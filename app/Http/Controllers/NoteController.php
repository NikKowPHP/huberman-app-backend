<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NoteController extends Controller
{
    // ... existing methods ...

    public function attachCategory(Request $request, Note $note): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:note_categories,id'
        ]);

        $category = NoteCategory::find($request->category_id);
        $note->categories()->attach($category);

        return response()->json([
            'message' => 'Category attached successfully',
            'attached' => [$category->id]
        ], 201);
    }
}
