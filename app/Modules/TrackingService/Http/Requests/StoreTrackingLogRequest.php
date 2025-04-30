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
     * @return array
     */
    public function rules()
    {
        return [
            'action' => 'required|string',
            'details' => 'nullable|string',
        ];
    }
}
