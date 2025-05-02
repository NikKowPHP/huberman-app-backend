<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

interface OfflineDataServiceInterface
{
    public function getDataForUser(User $user): Collection;
    public function syncDataForUser(User $user, array $data): void;
}
