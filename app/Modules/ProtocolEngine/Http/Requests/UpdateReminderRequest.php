<?php

namespace App\Modules\ProtocolEngine\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReminderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Policy/Middleware will handle the authorization check (e.g., ownership).
     */
    public function authorize(): bool
    {
        // Authorization is handled by the ReminderPolicy applied at the route level.
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
            'reminder_time' => ['sometimes', 'required', 'date_format:H:i:s'],
            'frequency' => ['sometimes', 'required', 'string', Rule::in(['daily', 'weekly', 'specific_days'])],
            'specific_days' => [
                'sometimes',
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    if ($this->input('frequency') === 'specific_days' && empty($value)) {
                        $fail('The '.$attribute.' field must contain at least one day when frequency is specific_days.');
                    }
                },
                Rule::prohibitedIf($this->input('frequency') !== 'specific_days'),
            ],
            'specific_days.*' => ['string', Rule::in($validDays)],
            'message' => ['sometimes', 'required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'protocol_id' => ['nullable', 'integer', 'exists:protocols,id'],
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
        if ($this->has('frequency') && $this->input('frequency') !== 'specific_days') {
            $this->merge([
                'specific_days' => null,
            ]);
        }
    }
}
