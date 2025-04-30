<?php

namespace App\Modules\ProtocolEngine\Http\Controllers;

use App\Modules\ProtocolEngine\Contracts\ReminderServiceInterface;
use App\Modules\ProtocolEngine\Http\Requests\StoreReminderRequest;
use App\Modules\ProtocolEngine\Http\Requests\UpdateReminderRequest;
use App\Modules\ProtocolEngine\Http\Resources\ReminderResource;
use App\Http\Controllers\Controller; // Assuming base controller exists
use App\Modules\ProtocolEngine\Models\UserReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; // Keep Request for potential future use in other methods
use Illuminate\Http\Resources\Json\AnonymousResourceCollection; // For index method later

class ReminderController extends Controller
{
    protected ReminderServiceInterface $reminderService;

    public function __construct(ReminderServiceInterface $reminderService)
    {
        $this->reminderService = $reminderService;

        // Apply middleware/policy checks if not done at route level
        // $this->authorizeResource(UserReminder::class, 'reminder'); // Example if using resource policies
    }

    /**
     * Store a newly created reminder in storage.
     *
     * @param StoreReminderRequest $request
     * @return ReminderResource
     */
    public function store(StoreReminderRequest $request): ReminderResource
    {
        // Authorization is handled by policy/middleware defined at route level
        // Validation is handled by StoreReminderRequest

        /** @var \App\Modules\UserManagement\Models\User $user */
        $user = $request->user();
        $validatedData = $request->validated();

        $reminder = $this->reminderService->setReminder($user, $validatedData);

        return new ReminderResource($reminder);
        // Laravel automatically sets status to 201 Created for resource creation responses
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $reminders = $this->reminderService->getUserReminders($user);
        return ReminderResource::collection($reminders);
    }

    /**
     * Display the specified resource.
     *
     * @param UserReminder $reminder // Route model binding
     * @return ReminderResource
     */
    public function show(UserReminder $reminder): ReminderResource
    {
        // Implementation for Show action will go here (if needed)
        // Typically handled by policy check at route/controller level
        throw new \BadMethodCallException('Not implemented yet.');
    }


    /**
     * Update the specified reminder in storage.
     *
     * @param UpdateReminderRequest $request // To be created
     * @param UserReminder $reminder // Route model binding
     * @return ReminderResource
     */
    public function update(UpdateReminderRequest $request, UserReminder $reminder): ReminderResource
    {
        // Authorization is handled by policy/middleware defined at route level
        $this->authorize('update', $reminder); // Using the policy

        $validatedData = $request->validated();

        $updatedReminder = $this->reminderService->updateReminder($reminder, $validatedData);

        return new ReminderResource($updatedReminder);
    }

    /**
     * Remove the specified reminder from storage.
     *
     * @param UserReminder $reminder // Route model binding
     * @return JsonResponse
     */
    public function destroy(UserReminder $reminder): JsonResponse
    {
        $this->authorize('delete', $reminder);
        $success = $this->reminderService->deleteReminder($reminder);

        if ($success) {
            return response()->json(null, 204); // No Content
        }

        return response()->json(['message' => 'Failed to delete reminder'], 500);
    }
}
