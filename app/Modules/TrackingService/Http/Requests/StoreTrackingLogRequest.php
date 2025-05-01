<?php

namespace App\Modules\TrackingService\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate; // Import Gate facade
use App\Modules\TrackingService\Models\TrackingLog; // Import the model

class StoreTrackingLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Use the policy to check if the user can create TrackingLogs.
     */
    public function authorize(): bool
    {
        // Use Gate::allows with the class name for 'create' policy check
        return Gate::allows('create', TrackingLog::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'protocol_id' => 'required|integer|exists:protocols,id', // Ensure protocol exists
            'tracked_at' => 'required|date_format:Y-m-d', // Specific date format
            'notes' => 'nullable|string|max:10000', // Optional notes, limit length
            'metadata' => 'nullable|array', // Optional metadata, must be an array if provided
            'metadata.*' => 'nullable|string|max:255', // Basic validation for metadata values (adjust as needed)
        ];
    }

    /**
     * Prepare the data for validation.
     * Convert date string to Carbon instance if needed later, or ensure format here.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('tracked_at')) {
            // Optionally ensure the date format is correct before validation runs,
            // though the rule handles the check itself.
            // Carbon::parse($this->tracked_at)->toDateString(); // Example if needed
        }

         // Ensure metadata is at least an empty array if present but null/empty string
         if ($this->filled('metadata') && !$this->input('metadata')) {
            $this->merge([
                'metadata' => [],
            ]);
        }
    }
}