<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoutineRequest;
use App\Http\Requests\UpdateRoutineRequest;
use App\Models\Routine;
use Illuminate\Http\JsonResponse;

class RoutineController extends Controller
{
    public function index(): JsonResponse
    {
        $routines = Routine::where('user_id', auth()->id())->get();

        return response()->json([
            'data' => $routines
        ]);
    }

    public function store(StoreRoutineRequest $request): JsonResponse
    {
        $routine = Routine::create([
            'user_id' => auth()->id(),
            ...$request->validated()
        ]);

        return response()->json([
            'data' => $routine
        ], 201);
    }

    public function show(Routine $routine): JsonResponse
    {
        $this->authorize('view', $routine);

        return response()->json([
            'data' => $routine
        ]);
    }

    public function update(UpdateRoutineRequest $request, Routine $routine): JsonResponse
    {
        $this->authorize('update', $routine);

        $routine->update($request->validated());

        return response()->json([
            'data' => $routine
        ]);
    }

    public function destroy(Routine $routine): JsonResponse
    {
        $this->authorize('delete', $routine);

        $routine->delete();

        return response()->json(null, 204);
    }
}
