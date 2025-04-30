<?php

namespace App\Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserManagement\Http\Requests\UpdateDeviceTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Update the authenticated user's device token.
     */
    public function updateDeviceToken(UpdateDeviceTokenRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->devices()->updateOrCreate(
            [
                'device_token' => $request->device_token,
                'platform' => $request->platform,
            ],
            [
                'device_token' => $request->device_token,
                'platform' => $request->platform,
            ]
        );

        return response()->json(['message' => 'Device token updated successfully']);
    }
}
