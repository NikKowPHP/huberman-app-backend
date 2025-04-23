**Huberman App: Complex Workflow & Algorithm Design**

**1. Introduction**

This document details the specific logic and design patterns for key complex workflows within the Huberman App backend. It elaborates on the processes outlined in `detailed_plan.md` and builds upon the modular structure defined in `component_breakdown.md` and `component_interfaces_interactions.md`. The goal is to provide clear implementation guidance for developers working on these critical features.

**2. Subscription Lifecycle Management**

*   **Goal:** To accurately track the state of a user's subscription across different providers (Stripe, Apple IAP, Google Play Billing) based on webhook events and scheduled checks.
*   **Key Components:**
    *   `SubscriptionBilling` Module: `SubscriptionService`, `WebhookController`, `Subscription` Model.
    *   `plans` table, `subscriptions` table (`status`, `ends_at`, `trial_ends_at` columns are crucial).
    *   Payment Providers: Stripe/Paddle/Apple/Google sending webhooks/notifications.
*   **Core Logic (State Machine):** The `subscriptions.status` field represents the current state. Transitions are triggered primarily by provider webhooks handled by the `WebhookController` which delegates processing to the `SubscriptionService`.

    *   **States:**
        *   `incomplete`: Initial state after checkout initiation but before successful payment (relevant for Stripe Checkout/Paddle).
        *   `trialing`: User is in a free trial period (requires `trial_ends_at` to be set).
        *   `active`: User has a paid, currently valid subscription. `ends_at` indicates the end of the current paid period.
        *   `past_due`: Payment failed, provider is retrying (Stripe/Paddle specific). Grace period might apply.
        *   `canceled`: User explicitly canceled *or* payment failed definitively. Subscription may remain active until `ends_at`. Feature access depends on whether `ends_at` is in the future.
        *   `expired`: The subscription period (`ends_at`) has passed, and it was not renewed (either due to cancellation or final payment failure). No access.

    *   **State Transitions (Conceptual - Provider events may vary):**
        *   **(Checkout) -> `incomplete`**: User initiates checkout (Stripe/Paddle).
        *   `incomplete` -> `active`/`trialing`: `checkout.session.completed` (Stripe), `subscription_created` (Paddle) webhook received with successful payment or trial setup. Update `status`, `ends_at`, `trial_ends_at`.
        *   `trialing` -> `active`: `invoice.payment_succeeded` (Stripe) or `subscription_payment_succeeded` (Paddle) after trial ends. Update `status`, `ends_at`. *OR* could happen via scheduled job checking `trial_ends_at`.
        *   `trialing` -> `expired`: Trial ends, no payment method, or user canceled during trial. `customer.subscription.updated` (Stripe), `subscription_canceled` (Paddle), `DID_FAIL_TO_RENEW` (Apple), `SUBSCRIPTION_EXPIRED` (Google). Update `status`.
        *   `active` -> `active`: Renewal successful. `invoice.payment_succeeded` (Stripe), `subscription_payment_succeeded` (Paddle), `DID_RENEW` (Apple), `SUBSCRIPTION_RENEWED` (Google). Update `ends_at` for the next period.
        *   `active` -> `past_due`: Renewal payment fails. `invoice.payment_failed` (Stripe - requires action), `subscription_payment_failed` (Paddle). Update `status`.
        *   `active` -> `canceled`: User cancels via provider portal/app. `customer.subscription.updated` (Stripe - `cancel_at_period_end=true`), `subscription_canceled` (Paddle), `DID_CHANGE_RENEWAL_STATUS` (Apple - off), `SUBSCRIPTION_CANCELED` (Google). Update `status`. Subscription *might* remain usable until `ends_at`.
        *   `past_due` -> `active`: Successful payment after retry. `invoice.payment_succeeded` (Stripe), `subscription_payment_succeeded` (Paddle). Update `status`, `ends_at`.
        *   `past_due` -> `canceled`: Final retry fails. `customer.subscription.updated` (Stripe - status `canceled`), `subscription_payment_failed` (Paddle - after retries). Update `status`. Set `ends_at` to now/past if not already.
        *   `canceled` -> `expired`: `ends_at` date is reached for a canceled subscription. Can be handled by a scheduled job checking `subscriptions` where `status = canceled` and `ends_at <= NOW()`. Update `status`.

    *   **Webhook Handling:** `WebhookController` receives POST request, verifies signature (critical!), determines provider and event type, and calls `SubscriptionService::handleWebhook()`. Service parses payload, finds relevant `User`/`Subscription`, updates DB state, and fires internal Laravel events (e.g., `SubscriptionRenewed`, `SubscriptionCanceled`) for other modules to react to.
    *   **Provider Differences:** Apple/Google require server-side receipt validation and parsing specific notification types (App Store Server Notifications V2 / Google Play Developer API RTDN). Logic needs to map their events to the internal state machine.

**3. Feature Gating Logic**

*   **Goal:** To restrict access to premium API routes and potentially limit usage of certain features (e.g., number of notes) based on the user's active subscription status.
*   **Key Components:**
    *   Laravel Middleware (e.g., `CheckPremiumAccess`).
    *   Laravel Policies (e.g., `NotePolicy`).
    *   `SubscriptionBilling` Module: `SubscriptionServiceInterface::userHasActivePremiumSubscription()`.
    *   `subscriptions` table.
    *   Redis Cache.
*   **Core Logic (Middleware Example - `CheckPremiumAccess`):**
    ```php
    // App/Http/Middleware/CheckPremiumAccess.php (Conceptual)
    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Cache;
    use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface; // Use Interface

    class CheckPremiumAccess
    {
        protected $subscriptionService;

        public function __construct(SubscriptionServiceInterface $subscriptionService) {
            $this->subscriptionService = $subscriptionService;
        }

        public function handle(Request $request, Closure $next)
        {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401); // Should be handled by 'auth:sanctum' earlier
            }

            // 1. Check Cache First
            $cacheKey = "user:{$user->id}:is_premium";
            $isPremium = Cache::get($cacheKey);

            if ($isPremium === null) {
                // 2. Cache Miss: Ask the Subscription Service
                $isPremium = $this->subscriptionService->userHasActivePremiumSubscription($user);
                // 3. Update Cache (e.g., for 15 minutes)
                Cache::put($cacheKey, $isPremium, now()->addMinutes(15));
            }

            if (!$isPremium) {
                // 4. Deny Access if not Premium
                return response()->json(['message' => 'Premium subscription required.'], 403);
            }

            // 5. Allow Access
            return $next($request);
        }
    }
    ```
*   **Core Logic (`SubscriptionServiceInterface::userHasActivePremiumSubscription`):**
    *   Takes a `User` object as input.
    *   Queries the `subscriptions` table:
        *   `WHERE user_id = ?`
        *   `WHERE status IN ['active', 'trialing']` (Or just `active` if trials don't grant full access).
        *   *(Optional but recommended)* `WHERE (ends_at IS NULL OR ends_at > NOW())` - Handles cases where status might be `canceled` but still within the paid period.
        *   Needs join/check against `plans` table to ensure the active subscription is for a *premium* plan type.
    *   Returns `true` if such a subscription exists, `false` otherwise.
*   **Policy Integration:** Policies (e.g., `NotePolicy::create(User $user)`) can inject `SubscriptionServiceInterface` and call `userHasActivePremiumSubscription()` or check feature flags (e.g., note limits based on plan) as part of their authorization logic.
*   **Application:** Apply the `CheckPremiumAccess` middleware to API routes/groups in `routes/api.php` that correspond to premium features. Use Policies for model-specific authorization checks within controllers.

**4. Customizable Reminders (Premium)**

*   **Goal:** Allow premium users to set custom reminders for specific protocols, triggering a push notification at the user-defined time/frequency in their local timezone.
*   **Key Components:**
    *   `ProtocolEngine` Module: `ReminderController`, `ReminderService`, `UserReminder` Model (stores `user_id`, `protocol_id`, `reminder_time`, `frequency`, `timezone`, `last_sent_at`, `is_active`, `device_token` - *CRITICAL*).
    *   Laravel Task Scheduling (`app/Console/Kernel.php`).
    *   Laravel Queues & Workers.
    *   `SendProtocolReminderNotification` Job & Notification class.
    *   Push Notification Service (e.g., FCM, APNS via Laravel Notifications or specific SDKs).
    *   React Native App (responsible for getting and sending the device token to the backend).
*   **Core Logic:**

    1.  **Device Token Registration:** When the user logs in or enables notifications, the React Native app gets the unique device push token (FCM/APNS) and sends it to the backend API (e.g., `POST /api/user/device-token`). The backend stores this token, likely associated with the `User` model or a dedicated `devices` table. *Storing multiple tokens per user is necessary if they use multiple devices.*
    2.  **Reminder Creation (API):** User interacts with RN UI -> `POST /api/reminders` -> `ReminderController` -> `ReminderService::setReminder()`.
        *   Service validates input (requires premium check).
        *   Saves reminder details to `user_reminders` table, including user's selected time, frequency (daily, weekly, specific days), protocol, and user's current timezone (stored e.g., on `users` table or passed in request). *Crucially, it needs access to the user's valid device token(s) when the notification job runs.*
    3.  **Scheduled Task (`Kernel.php`):** A console command is scheduled to run frequently (e.g., every minute).
        ```php
        // app/Console/Kernel.php
        protected function schedule(Schedule $schedule)
        {
            $schedule->command('reminders:send-due')->everyMinute();
            // OR use $schedule->job(new ProcessDueReminders)->everyMinute();
        }
        ```
    4.  **Processing Due Reminders (`reminders:send-due` Command / `ProcessDueReminders` Job):**
        *   Get the current time (UTC).
        *   Query the `user_reminders` table for active reminders (`is_active = true`).
        *   **Crucially, for each reminder:**
            *   Convert the *current UTC time* to the *user's stored timezone*.
            *   Check if the user's local time matches the `reminder_time` stored for the reminder.
            *   Check if the frequency matches (e.g., is it the correct day of the week?).
            *   Check `last_sent_at` to avoid double-sending if the job runs slightly late.
        *   For each reminder determined to be *due now*:
            *   Dispatch a `SendProtocolReminderNotification` job to the queue, passing the `user_reminder_id` or necessary details.
            ```php
            // Conceptual Command/Job Logic
            $nowUtc = now();
            $dueReminders = UserReminder::where('is_active', true)->cursor(); // Use cursor for memory efficiency

            foreach ($dueReminders as $reminder) {
                $userTimezone = $reminder->user->timezone ?? 'UTC'; // Get user timezone
                $nowInUserTz = $nowUtc->copy()->setTimezone($userTimezone);
                $reminderTime = Carbon::parse($reminder->reminder_time, $userTimezone); // Time stored without date

                // Compare times (HH:MM) and check frequency/day constraints
                if ($nowInUserTz->format('H:i') == $reminderTime->format('H:i') && /* check frequency match */ && /* check not recently sent */) {
                   SendProtocolReminderNotification::dispatch($reminder);
                   // Optionally update last_sent_at here or in the job
                }
            }
            ```
    5.  **Sending Notification (`SendProtocolReminderNotification` Job):**
        *   Executed by a queue worker.
        *   Retrieves the `UserReminder` and associated `User` and `Protocol`.
        *   Fetches the target user's valid device token(s) (from `users` or `devices` table).
        *   Constructs the notification payload (title, body with protocol name).
        *   Uses Laravel's Notification system (e.g., `Notification::send($user, new ProtocolReminder($protocol))`) or a specific service (FCM/APNS) to send the push notification to the user's device token(s).
        *   Handles potential errors (e.g., invalid token). Updates `last_sent_at` on the `UserReminder`.

**5. Tracking & Visualization Logic (Premium)**

*   **Goal:** Allow premium users to log their adherence to protocols and view progress visualizations like streaks or completion rates.
*   **Key Components:**
    *   `ProtocolEngine` Module: `TrackingController`, `TrackingService`.
    *   `user_protocol_tracking` table (`id`, `user_id`, `protocol_id`, `tracked_at` (DATE), `notes`, `metadata`, `created_at`, `updated_at`).
*   **Data Storage:** Each time a user marks a protocol as completed for a specific day, a record is created in `user_protocol_tracking`. The `tracked_at` field (DATE type) is key. The unique constraint `(user_id, protocol_id, tracked_at)` prevents duplicates.
*   **Logging Adherence (API):** User taps "Mark Done" -> `POST /api/tracking/log` (with `protocol_id` and optional `date`) -> `TrackingController` -> `TrackingService::logAdherence()`.
    *   Service validates input (requires premium check).
    *   Inserts or updates record in `user_protocol_tracking`. Use `updateOrCreate` based on the unique constraint.
*   **Visualization - Streak Calculation (Example):**
    *   **API:** `GET /api/tracking/summary?protocol_id={id}&metric=streak` -> `TrackingController` -> `TrackingService::calculateStreak()`.
    *   **`TrackingService::calculateStreak(User $user, int $protocolId)` Logic (Conceptual Pseudo-code):**
        ```pseudo
        function calculateStreak(user, protocolId):
          streak = 0
          today = currentDate() // Or maybe the latest tracked date for this user/protocol? Decide requirement.

          // Get relevant tracking dates, ordered descending
          trackedDates = query database:
             SELECT tracked_at FROM user_protocol_tracking
             WHERE user_id = user.id AND protocol_id = protocolId
             ORDER BY tracked_at DESC

          if trackedDates is empty:
             return 0

          // Check if the most recent tracking is today or yesterday (allows continuation)
          latestTrackedDate = trackedDates[0]
          if latestTrackedDate is not today AND latestTrackedDate is not yesterday:
             return 0 // Streak broken if >1 day missed

          // Check consecutive days backward
          expectedDate = today
          if latestTrackedDate is yesterday:
             expectedDate = yesterday // Start checking from yesterday if today wasn't logged

          for date in trackedDates:
             if date == expectedDate:
                streak = streak + 1
                expectedDate = expectedDate.subtract(1 day) // Look for the previous day
             else if date < expectedDate:
                 // Gap detected, streak broken before this date
                 break
             // else if date > expectedDate: This shouldn't happen with ORDER BY DESC

          return streak
        ```
*   **Other Visualizations:**
    *   **Completion % (e.g., Monthly):** Query `user_protocol_tracking` for the user/protocol within the date range. Count distinct `tracked_at` days. Divide by the total number of days in the month.
    *   **Calendar Heatmap:** Query all `tracked_at` dates for the user/protocol within a visible range. Frontend uses this data to color calendar days.

**6. Conclusion**

These detailed workflow descriptions provide a blueprint for implementing the core complex features of the Huberman App. Adhering to these designs, leveraging appropriate Laravel features (Middleware, Policies, Queues, Scheduling, Events), and carefully handling state transitions and edge cases (especially with external providers) will be crucial for building a robust and reliable backend.
