<?php

namespace App\Modules\UserManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateDeviceTokenRequest",
 *     required={"device_token", "platform"},
 *     @OA\Property(
 *         property="device_token",
 *         type="string",
 *         description="Device token for push notifications",
 *         example="fcm_token_or_apns_token"
 *     ),
 *     @OA\Property(
 *         property="platform",
 *         type="string",
 *         description="Device platform",
 *         enum={"ios", "android", "web"},
 *         example="ios"
 *     )
 * )
 */
/**
 * @OA\Schema(
 *     schema="UpdateDeviceTokenRequest",
 *     required={"device_token", "platform"},
 *     @OA\Property(
 *         property="device_token",
 *         type="string",
 *         description="Device token for push notifications",
 *         example="fcm_token_or_apns_token"
 *     ),
 *     @OA\Property(
 *         property="platform",
 *         type="string",
 *         description="Device platform",
 *         enum={"ios", "android", "web"},
 *         example="ios"
 *     )
 * )
 */
class UpdateDeviceTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'device_token' => ['required', 'string'],
            'platform' => ['required', 'string', Rule::in(['ios', 'android', 'web'])],
        ];
    }
}
