<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncOfflineDataRequest;
use App\Services\OfflineDataServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OfflineDataController extends Controller
{
    protected $offlineDataService;

    public function __construct(OfflineDataServiceInterface $offlineDataService)
    {
        $this->offlineDataService = $offlineDataService;
    }

    public function fetchData(): JsonResponse
    {
        $user = Auth::user();
        $data = $this->offlineDataService->getDataForUser($user);

        return response()->json(['data' => $data]);
    }

    public function syncData(SyncOfflineDataRequest $request): JsonResponse
    {
        $user = Auth::user();
        $this->offlineDataService->syncDataForUser($user, $request->validated()['data']);

        return response()->json(['message' => 'Data synced successfully']);
    }
}
