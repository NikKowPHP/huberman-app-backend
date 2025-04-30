<?php

namespace App\Modules\ProtocolEngine\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

class StoreReminderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Policy/Middleware will handle the authorization check (e.g., premium status).
     */
    public function authorize(): bool
    {
        // Authorization is handled by the ReminderPolicy applied at the route level.
        // We just need to ensure the user is authenticated, which FormRequest does.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $validDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        return [
            'reminder_time' => ['required', 'date_format:H:i:s'],
            'frequency' => ['required', 'string', Rule::in(['daily', 'weekly', 'specific_days'])],
            'specific_days' => [
                // Required only if frequency is 'specific_days'
                Rule::requiredIf($this->input('frequency') === 'specific_days'),
                // Must not be present if frequency is not 'specific_days'
                Rule::prohibitedIf($this->input('frequency') !== 'specific_days'),
                // Must be an array if present
                'array',
                // Ensure it's not empty if frequency is 'specific_days'
                function ($attribute, $value, $fail) {
                    if ($this->input('frequency') === 'specific_days' && empty($value)) {
                        $fail('The '.$attribute.' field must contain at least one day when frequency is specific_days.');
                    }
                },
            ],
            // Validate each element within the specific_days array
            'specific_days.*' => ['string', Rule::in($validDays)],
            'message' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'], // Default to true if not provided? Service layer can handle default.
            'protocol_id' => ['nullable', 'integer', 'exists:protocols,id'], // Assuming 'protocols' table
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'specific_days.required' => 'The specific days field is required when frequency is specific_days.',
            'specific_days.prohibited' => 'The specific days field must not be present when frequency is not specific_days.',
            'specific_days.*.in' => 'Each selected day must be a valid day abbreviation (Mon, Tue, Wed, Thu, Fri, Sat, Sun).',
            'reminder_time.date_format' => 'The reminder time must be in HH:MM:SS format.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Ensure specific_days is null if frequency is not 'specific_days'
        // This helps simplify the validation rules and controller logic.
        if ($this->input('frequency') !== 'specific_days') {
            $this->merge([
                'specific_days' => null,
            ]);
        }

        // Default is_active to true if not present
        if (!$this->has('is_active')) {
            $this->merge([
                'is_active' => true,
            ]);
        }
    }
}
