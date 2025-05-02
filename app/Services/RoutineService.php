<?php

namespace App\Services;

use App\Models\Routine;
use App\Models\RoutineStep;
use Illuminate\Support\Facades\Auth;

class RoutineService implements RoutineServiceInterface
{
    public function getAllRoutines(): array
    {
        return Routine::with('steps')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function createRoutine(array $data): Routine
    {
        return Routine::create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'frequency' => $data['frequency'] ?? 'daily'
        ]);
    }

    public function updateRoutine(Routine $routine, array $data): Routine
    {
        $routine->update($data);
        return $routine->fresh();
    }

    public function deleteRoutine(Routine $routine): void
    {
        $routine->delete();
    }

    public function executeRoutine(Routine $routine): void
    {
        $routine->steps()->where('is_completed', false)->each(function ($step) {
            $step->update(['is_completed' => true]);
        });
    }

    public function scheduleRoutine(Routine $routine, string $schedule): void
    {
        $routine->update(['schedule' => $schedule]);
    }

    public function getRoutineSteps(Routine $routine): array
    {
        return $routine->steps()->orderBy('order')->get()->toArray();
    }
}
