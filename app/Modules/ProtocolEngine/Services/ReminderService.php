<?php

namespace App\Modules\ProtocolEngine\Services;

use App\Modules\ProtocolEngine\Contracts\ReminderServiceInterface;
use App\Modules\ProtocolEngine\Models\UserReminder;
use App\Modules\UserManagement\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log; // Optional: for logging errors

class ReminderService implements ReminderServiceInterface
{
    /**
     * Create a new reminder for a user.
     *
     * @param User $user The user creating the reminder.
     * @param array $data Validated reminder data.
     * @return UserReminder The newly created reminder.
     */
    public function setReminder(User $user, array $data): UserReminder
    {
        // The validated data comes from StoreReminderRequest
        // It includes 'reminder_time', 'frequency', 'specific_days' (if applicable),
        // 'message', 'is_active', 'protocol_id' (optional)

        $reminder = $user->reminders()->create([
            'reminder_time' => $data['reminder_time'],
            'frequency' => $data['frequency'],
            'specific_days' => $data['specific_days'] ?? null, // Ensure null if not provided/applicable
            'message' => $data['message'],
            'is_active' => $data['is_active'] ?? true, // Default to true
            'protocol_id' => $data['protocol_id'] ?? null,
        ]);

        // Log::info("Reminder created for user {$user->id}: ID {$reminder->id}"); // Optional logging

        return $reminder;
    }

    /**
     * Get all reminders for a specific user.
     *
     * @param User $user The user whose reminders to fetch.
     * @return Collection<int, UserReminder>
     */
    public function getUserReminders(User $user): Collection
    {
        return UserReminder::where('user_id', $user->id)->get();
    }

    /**
     * Update an existing reminder.
     *
     * @param UserReminder $reminder The reminder to update.
     * @param array $data Validated update data.
     * @return UserReminder The updated reminder.
     */
    public function updateReminder(UserReminder $reminder, array $data): UserReminder
    {
        $reminder->update($data);
        return $reminder;
    }

    /**
     * Delete a reminder.
     *
     * @param UserReminder $reminder The reminder to delete.
     * @return bool True on success, false otherwise.
     */
    public function deleteReminder(UserReminder $reminder): bool
    {
        return $reminder->delete();
    }
}
