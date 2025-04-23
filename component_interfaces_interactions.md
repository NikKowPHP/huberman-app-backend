
**Huberman App: Backend Component Interfaces & Interactions**

**1. Introduction**

Following the definition of backend modules in `component_breakdown.md`, this document specifies the interfaces, contracts, and primary interaction patterns between these modules within the Laravel Modular Monolith. The goal is to ensure loose coupling where appropriate, define clear responsibilities, and illustrate how data and control flow, particularly for critical cross-cutting concerns like subscription-based feature gating.

**2. Primary Interaction Mechanisms**

Modules within the backend will primarily interact using standard Laravel patterns designed to promote maintainability and testability:

*   **Dependency Injection (DI) & Service Interfaces:** The preferred method for direct inter-module communication. Modules will define interfaces (contracts) for their core services. Other modules will type-hint these interfaces in their constructors or methods, allowing Laravel's service container to inject the concrete implementation. This decouples the modules.
*   **Events and Listeners:** Used for broadcasting significant state changes or actions without requiring the originating module to know about all interested parties. For example, when a subscription status changes, an event can be fired, and multiple listeners in different modules can react accordingly (e.g., clearing caches, enabling/disabling features).
*   **Middleware:** Used primarily for cross-cutting concerns applied at the HTTP request level, notably for authentication (handled by `Authentication` module's logic) and authorization/feature gating (checking subscription status via `SubscriptionBilling` module's logic before allowing access to specific API routes/controllers).
*   **Policies:** Used for fine-grained authorization logic, often related to specific Eloquent models. Policies within a module (e.g., `NotePolicy` in `NotesService`) can leverage services from other modules (like `SubscriptionBilling`) to make authorization decisions.
*   **Eloquent Relationships:** Models defined within modules will use standard Eloquent relationships (`hasMany`, `belongsTo`, etc.) to interact with models in other modules where direct data relationships exist (e.g., `User` model in `UserManagement` has many `Subscription` models in `SubscriptionBilling`).

**3. Key Module Interfaces & Service Contracts**

Here are *examples* of key interfaces or service methods that modules might expose or consume. Concrete implementations will reside within the respective modules.

*   **`Authentication` Module:**
    *   *Primarily Consumed:* Consumes `UserManagement` services/models for user lookup/creation.
    *   *Exposes:* Less direct service interfaces, more functionality via controllers and standard Laravel Auth mechanisms (e.g., `Auth::user()`).
    *   *Fires Events:* `UserRegistered`, `UserLoggedIn`, `PasswordResetRequested`.

*   **`UserManagement` Module:**
    *   *Exposes:*
        *   `App\Modules\UserManagement\Models\User` (The core User model, accessible throughout the application).
        *   Potentially `UserServiceInterface` for complex profile operations (if needed beyond simple CRUD).
    *   *Listens To Events:* Potentially `SubscriptionStatusChanged` (from `SubscriptionBilling`) to update cached user properties if necessary.

*   **`SubscriptionBilling` Module:**
    *   *Exposes (Crucial Interfaces):*
        *   `SubscriptionServiceInterface`:
            *   `userHasActivePremiumSubscription(User $user): bool` - *Core method for feature gating.*
            *   `getUserPlan(User $user): ?Plan`
            *   `getSubscriptionStatus(User $user): string`
            *   `handleWebhook(string $provider, array $payload): void` (Internal logic invoked by WebhookController).
            *   Methods to retrieve plan details for the frontend API.
        *   `Models\Plan`, `Models\Subscription`.
    *   *Consumes:* `UserManagement` (for `User` model).
    *   *Fires Events:* `SubscriptionStarted`, `SubscriptionRenewed`, `SubscriptionCanceled`, `SubscriptionExpired`, `PaymentFailed`, `TrialWillEndSoon`.

*   **`ContentManagement` Module:**
    *   *Exposes:*
        *   `ContentServiceInterface` (or specific interfaces like `EpisodeServiceInterface`, `ProtocolServiceInterface`):
            *   `getFreeTierProtocols(): Collection`
            *   `getAllProtocols(): Collection`
            *   `getProtocolDetails(int $protocolId): ?Protocol`
            *   `getEpisodeSummaries(int $episodeId): Collection`
            *   `getEpisodeDetails(int $episodeId): ?Episode`
        *   `Models\Episode`, `Models\Protocol`, `Models\Summary`.
    *   *Consumes:* May indirectly use `SubscriptionBilling` (via middleware/policies applied to its controllers) to restrict access to certain data retrieval methods/endpoints.

*   **`ProtocolEngine` Module:**
    *   *Exposes:*
        *   `ReminderServiceInterface`:
            *   `setReminder(User $user, int $protocolId, array $settings): UserReminder` (Requires premium check internally).
            *   `getUserReminders(User $user): Collection`
            *   `deleteReminder(User $user, int $reminderId): bool`
        *   `TrackingServiceInterface`:
            *   `logAdherence(User $user, int $protocolId, Carbon $date): ProtocolTrack` (Requires premium check internally).
            *   `getUserTrackingData(User $user, DateRange $range): array`
            *   `calculateStreaks(User $user, int $protocolId): int`
    *   *Consumes:*
        *   `SubscriptionServiceInterface` (to perform internal premium checks before executing actions).
        *   `UserManagement` (for `User` model).
        *   `ContentManagement` (for `Protocol` model details).
    *   *Listens To Events:* Potentially `SubscriptionExpired` (to disable active reminders if needed).

*   **`NotesService` Module:**
    *   *Exposes:*
        *   `NoteServiceInterface`:
            *   `createNote(User $user, int $episodeId, string $content, bool $isPublic = false): Note` (Checks limits/public permission).
            *   `updateNote(User $user, int $noteId, array $data): Note`
            *   `deleteNote(User $user, int $noteId): bool`
            *   `getUserNotes(User $user, ?int $episodeId = null): Collection`
            *   `getPublicNotesForEpisode(int $episodeId): Collection`
        *   `Models\Note`.
    *   *Consumes:*
        *   `SubscriptionServiceInterface` (within its policies/services to check free tier limits or premium permission for public notes/advanced features).
        *   `UserManagement` (for `User`).
        *   `ContentManagement` (for `Episode`).

*   **`CommunityFeatures` Module:**
    *   *Consumes:*
        *   `NoteServiceInterface` (to fetch public notes).
        *   `SubscriptionServiceInterface` (via middleware/policies to control access to community sections if they are premium).
        *   `UserManagement` (for user details).

**4. Key Interaction Flow Examples (Sequence Diagrams - Conceptual)**

*   **Flow 1: User Accesses Premium Protocol Details (API Request)**
    1.  `React Native App` -> `API Request (GET /api/protocols/{id})`
    2.  `Laravel Router` -> `CheckPremiumAccess Middleware`
    3.  `CheckPremiumAccess Middleware` -> `Auth` (Get Authenticated User)
    4.  `CheckPremiumAccess Middleware` -> `SubscriptionServiceInterface::userHasActivePremiumSubscription(User)`
    5.  `SubscriptionService` -> `Database/Cache` (Check `subscriptions` table for User) -> Returns `true`
    6.  `CheckPremiumAccess Middleware` -> `Allows Request`
    7.  `Laravel Router` -> `ProtocolController::show(protocolId)`
    8.  `ProtocolController` -> `ProtocolServiceInterface::getProtocolDetails(protocolId)`
    9.  `ProtocolService` -> `Database` (Fetch Protocol data)
    10. `ProtocolController` -> `Format Response` (e.g., using API Resource)
    11. `Laravel` -> `API Response (200 OK with Protocol Data)` -> `React Native App`

*   **Flow 2: User Sets a Custom Reminder (API Request - Premium Feature)**
    1.  `React Native App` -> `API Request (POST /api/reminders)` with protocol ID, time settings.
    2.  `Laravel Router` -> `CheckPremiumAccess Middleware`
    3.  `CheckPremiumAccess Middleware` -> ... (verifies premium as in Flow 1) -> `Allows Request`
    4.  `Laravel Router` -> `ReminderController::store(Request)`
    5.  `ReminderController` -> `SetReminderRequest` (Validation)
    6.  `ReminderController` -> `ReminderServiceInterface::setReminder(User, protocolId, settings)`
    7.  *(Defense-in-depth): `ReminderService` -> `SubscriptionServiceInterface::userHasActivePremiumSubscription(User)` -> Returns `true`*
    8.  `ReminderService` -> `Database` (Save `UserReminder` record)
    9.  `ReminderService` -> `Queue::dispatch(new ScheduleReminderJob(reminderId))` (Optional: If scheduling is complex)
    10. `ReminderController` -> `Format Response (201 Created)`
    11. `Laravel` -> `API Response` -> `React Native App`

*   **Flow 3: Payment Provider Webhook (Subscription Renewed)**
    1.  `Stripe/Paddle/App Store` -> `POST /api/webhooks/{provider}`
    2.  `Laravel Router` -> `WebhookController::handle(provider, Request)`
    3.  `WebhookController` -> `VerifyWebhookSignature Middleware/Logic`
    4.  `WebhookController` -> `SubscriptionServiceInterface::handleWebhook(provider, payload)`
    5.  `SubscriptionService` -> `Parse Payload` (Identify event type: e.g., `invoice.payment_succeeded`, `subscription.updated`)
    6.  `SubscriptionService` -> `Database` (Find corresponding `User` and `Subscription` record)
    7.  `SubscriptionService` -> `Database` (Update `Subscription` status, `ends_at` date)
    8.  `SubscriptionService` -> `Event::dispatch(new SubscriptionRenewed(Subscription))`
    9.  `SubscriptionRenewed Listener 1` (e.g., `ClearUserEntitlementCache`) -> `Cache::forget(...)`
    10. `SubscriptionRenewed Listener 2` (e.g., `SendRenewalConfirmationEmail`) -> `Queue::dispatch(...)` (Optional)
    11. `WebhookController` -> `Return Success Response (200 OK)` -> `Payment Provider`

**5. Frontend Interaction Summary**

The React Native frontend interacts with the backend *exclusively* through the defined RESTful API. It is unaware of the internal module structure, service interfaces, or events. The backend API is responsible for:
*   Authenticating the user.
*   Performing all necessary authorization and feature gating based on subscription status.
*   Returning data appropriate to the user's access level (e.g., filtering content, including flags like `is_premium_user: true` or `can_set_reminders: true`).
*   Accepting requests and triggering the relevant backend logic.

**6. Conclusion**

Defining clear interfaces (primarily through PHP Interfaces for Services) and interaction patterns (DI, Events, Middleware, Policies) allows for a decoupled yet cohesive Modular Monolith. This structure facilitates understanding responsibilities, enhances testability (by mocking dependencies), and enables more organized development and maintenance of the Huberman App backend. The key cross-cutting concern of subscription checking is handled systematically via middleware and service calls, ensuring consistent feature gating.
