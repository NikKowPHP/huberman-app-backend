<?php

namespace App\Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserManagement\Http\Requests\UpdateDeviceTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile
     *
     * @OA\Get(
     *     path="/user/profile",
     *     tags={"UserManagement"},
     *     summary="Get user profile",
     *     description="Returns the authenticated user's profile data",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/responses/UnauthorizedError")
     *     )
     * )
     */
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Update the authenticated user's device token.
     *
     * @OA\Post(
     *     path="/user/device-token",
     *     tags={"UserManagement"},
     *     summary="Update device token",
     *     description="Updates the authenticated user's device token for push notifications",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateDeviceTokenRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Device token updated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Device token updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/responses/UnauthorizedError")
     *     )
     * )
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
