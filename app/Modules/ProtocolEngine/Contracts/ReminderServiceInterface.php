<?php

namespace App\Modules\ProtocolEngine\Contracts;

use App\Modules\ProtocolEngine\Models\UserReminder;
use App\Modules\UserManagement\Models\User;
use Illuminate\Database\Eloquent\Collection; // Or Paginated collection if needed later

interface ReminderServiceInterface
{
    /**
     * Create a new reminder for a user.
     *
     * @param User $user The user creating the reminder.
     * @param array $data Validated reminder data.
     * @return UserReminder The newly created reminder.
     */
    public function setReminder(User $user, array $data): UserReminder;

    /**
     * Get all reminders for a specific user.
     *
     * @param User $user The user whose reminders to fetch.
     * @return Collection<int, UserReminder>
     */
    public function getUserReminders(User $user): Collection;

    /**
     * Update an existing reminder.
     *
     * @param UserReminder $reminder The reminder to update.
     * @param array $data Validated update data.
     * @return UserReminder The updated reminder.
     */
    public function updateReminder(UserReminder $reminder, array $data): UserReminder;

    /**
     * Delete a reminder.
     *
     * @param UserReminder $reminder The reminder to delete.
     * @return bool True on success, false otherwise.
     */
    public function deleteReminder(UserReminder $reminder): bool;
}
