<?php

namespace App\Modules\TrackingService\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrackingLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Authorization is handled by the policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'protocol_id' => 'required|integer|exists:protocols,id',
            'tracked_at' => 'required|date',
            'notes' => 'nullable|string',
            'metadata' => 'nullable|array',
        ];
    }
}
