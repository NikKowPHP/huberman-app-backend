<?php

namespace App\Http\Controllers;

use App\Models\NoteTag;
use Illuminate\Http\Request;

class NoteTagController extends Controller
{
    public function index()
    {
        return NoteTag::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7'
        ]);

        return NoteTag::create($validated);
    }

    public function show(NoteTag $tag)
    {
        return $tag;
    }

    public function update(Request $request, NoteTag $tag)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|max:7'
        ]);

        $tag->update($validated);
        return $tag;
    }

    public function destroy(NoteTag $tag)
    {
        $tag->delete();
        return response()->noContent();
    }
}
