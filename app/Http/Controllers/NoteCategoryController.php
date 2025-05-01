<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\NoteCategory;
use Illuminate\Http\JsonResponse;

class NoteCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = NoteCategory::all();
        return response()->json($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = NoteCategory::create($request->validated());
        return response()->json($category, 201);
    }

    public function show(NoteCategory $category): JsonResponse
    {
        return response::json($category);
    }

    public function update(StoreCategoryRequest $request, NoteCategory $category): JsonResponse
    {
        $category->update($request->validated());
        return response()->json($category);
    }

    public function destroy(NoteCategory $category): JsonResponse
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
