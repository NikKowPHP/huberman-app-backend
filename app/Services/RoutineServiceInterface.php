<?php

namespace App\Services;

use App\Models\Routine;
use App\Models\RoutineStep;

interface RoutineServiceInterface
{
    public function createRoutine(array $data): Routine;
    public function updateRoutine(Routine $routine, array $data): Routine;
    public function deleteRoutine(Routine $routine): void;
    public function executeRoutine(Routine $routine): void;
    public function scheduleRoutine(Routine $routine, string $schedule): void;
    public function getRoutineSteps(Routine $routine): array;
}
