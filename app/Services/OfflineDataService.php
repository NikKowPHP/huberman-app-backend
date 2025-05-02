<?php

namespace App\Services;

use App\Models\User;
use App\OfflineData;
use Illuminate\Support\Collection;

class OfflineDataService implements OfflineDataServiceInterface
{
    public function getDataForUser(User $user): Collection
    {
        return OfflineData::where('user_id', $user->id)->get();
    }

    public function syncDataForUser(User $user, array $data): void
    {
        foreach ($data as $item) {
            OfflineData::updateOrCreate(
                ['user_id' => $user->id, 'key' => $item['key']],
                ['value' => $item['value']]
            );
        }
    }
}
