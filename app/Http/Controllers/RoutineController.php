<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoutineRequest;
use App\Http\Requests\UpdateRoutineRequest;
use App\Services\RoutineService;
use App\Models\Routine;
use Illuminate\Http\JsonResponse;

class RoutineController extends Controller
{
    public function __construct(
        private RoutineService $routineService
    ) {}

    public function index(): JsonResponse
    {
        $routines = $this->routineService->getAllRoutines();
        return response()->json($routines);
    }

    public function store(StoreRoutineRequest $request): JsonResponse
    {
        $routine = $this->routineService->createRoutine($request->validated());
        return response()->json($routine, 201);
    }

    public function show(Routine $routine): JsonResponse
    {
        $this->authorize('view', $routine);
        return response()->json($routine);
    }

    public function update(UpdateRoutineRequest $request, Routine $routine): JsonResponse
    {
        $this->authorize('update', $routine);
        $updatedRoutine = $this->routineService->updateRoutine($routine, $request->validated());
        return response()->json($updatedRoutine);
    }

    public function destroy(Routine $routine): JsonResponse
    {
        $this->authorize('delete', $routine);
        $this->routineService->deleteRoutine($routine);
        return response()->json(null, 204);
    }
}
