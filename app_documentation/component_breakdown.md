**Huberman App: Backend Component Breakdown (Modular Monolith)**

**1. Introduction**

This document details the proposed modular structure for the Huberman App's Laravel backend, following the Modular Monolith pattern outlined in the architecture and implementation plan (`architecture_pan.md`, `detailed_plan.md`). The goal is to organize the codebase into distinct, domain-focused modules (represented as namespaces/folders within the `app/` directory) to improve maintainability, scalability, and clarity of responsibilities. While running as a single application, these logical boundaries help manage complexity as the application grows.

Each module will encapsulate its specific domain logic, including Models, Controllers (for API endpoints related to the module), Services, Events, Listeners, Policies, Requests, etc.

**2. Proposed Backend Modules**

The initial set of proposed modules, located under `app/Modules/`, are:

*   **`app/Modules/Authentication`**
    *   **Responsibilities:** Handles user registration, login (authentication), password reset functionality, API token issuance and potentially revocation (e.g., integrating Laravel Sanctum or Passport).
    *   **Key Components:**
        *   `Http/Controllers/AuthController.php` (Handles API login, register, logout, etc.)
        *   `Http/Controllers/PasswordResetController.php` (Handles password reset flow)
        *   `Http/Requests/LoginRequest.php`, `RegisterRequest.php`
        *   Potentially `Services/AuthService.php` for complex logic.
        *   Related Routes definition.
        *   Authentication-related Middleware (e.g., `EnsureTokenIsValid`).

*   **`app/Modules/UserManagement`**
    *   **Responsibilities:** Manages user data beyond authentication, such as profile information (name, potentially picture), user settings, and serves as the owner of the core `User` model. It collaborates with `SubscriptionBilling` to understand a user's access level.
    *   **Key Components:**
        *   `Models/User.php` (The core Eloquent User model)
        *   `Http/Controllers/UserController.php` (API for fetching/updating user profile)
        *   `Http/Requests/UpdateProfileRequest.php`
        *   `Services/UserService.php`
        *   `Policies/UserPolicy.php` (Authorization for profile actions)
        *   Database migrations for the `users` table.

*   **`app/Modules/SubscriptionBilling`**
    *   **Responsibilities:** Manages subscription plans (`Plan` model), tracks user subscriptions (`Subscription` model), integrates with payment providers (Stripe/Paddle via Cashier), handles incoming webhooks for payment events (success, failure, cancellation), and provides the core logic for checking a user's subscription status and entitlements (feature gating).
    *   **Key Components:**
        *   `Models/Plan.php`
        *   `Models/Subscription.php`
        *   `Http/Controllers/SubscriptionController.php` (API for fetching plans, potentially initiating checkout flows)
        *   `Http/Controllers/WebhookController.php` (Handles Stripe/Paddle/Apple/Google webhooks)
        *   `Services/BillingService.php` or `SubscriptionService.php` (Core logic for status checks, webhook processing)
        *   `Policies/SubscriptionPolicy.php` or Middleware (e.g., `CheckPremiumAccess`) - *Crucial for Feature Gating*.
        *   Listeners for Cashier events (if applicable).
        *   Database migrations for `plans`, `subscriptions` tables.

*   **`app/Modules/ContentManagement`**
    *   **Responsibilities:** Manages the core application content: Podcast Episodes, Protocols, Summaries, Key Takeaways. Includes logic for creating, retrieving, updating, and deleting this content (potentially via admin interfaces or seeders) and exposing it through the API, respecting access levels determined by `SubscriptionBilling`.
    *   **Key Components:**
        *   `Models/Episode.php`, `Protocol.php`, `Summary.php`
        *   `Http/Controllers/EpisodeController.php` (API for episodes)
        *   `Http/Controllers/ProtocolController.php` (API for protocols)
        *   `Http/Controllers/SummaryController.php` (API for summaries)
        *   `Services/ContentService.php` (May handle complex queries or formatting)
        *   Database migrations for `episodes`, `protocols`, `summaries`, `episode_protocol` tables.
        *   Potentially Resource Collections for API responses.

*   **`app/Modules/ProtocolEngine`**
    *   **Responsibilities:** Handles the *application* of protocols, primarily focusing on the reminder and tracking features (Premium). Includes logic for setting/managing user-defined reminders, scheduling notifications, and recording user adherence to protocols.
    *   **Key Components:**
        *   `Http/Controllers/ReminderController.php` (API for CRUD operations on user reminders - Premium)
        *   `Http/Controllers/TrackingController.php` (API for logging protocol adherence - Premium)
        *   `Services/ReminderService.php` (Logic for scheduling/managing reminders)
        *   `Services/TrackingService.php` (Logic for recording/calculating adherence, streaks)
        *   Potentially `Models/UserReminder.php`, `Models/ProtocolTrack.php`
        *   Related `Jobs` (e.g., `SendProtocolReminderNotification`) pushed to the queue.
        *   Console Commands (e.g., for queue workers, potentially scheduling checks).
        *   Policies/Middleware to ensure only Premium users can access these features.

*   **`app/Modules/NotesService`**
    *   **Responsibilities:** Manages user-created notes linked to episodes. Handles CRUD operations, public/private status, and potentially organization features (tags, search - likely Premium).
    *   **Key Components:**
        *   `Models/Note.php`
        *   `Http/Controllers/NoteController.php` (API for note CRUD)
        *   `Services/NoteService.php`
        *   `Policies/NotePolicy.php` (Ensures users can only manage their own notes, checks Premium status for advanced features like unlimited notes or public sharing).
        *   Database migrations for the `notes` table.

*   **`app/Modules/CommunityFeatures`** (If implemented early)
    *   **Responsibilities:** Focuses on features involving multiple users interacting, such as viewing public notes associated with episodes or potential discussion forums. Relies heavily on `NotesService` (for public notes) and `SubscriptionBilling` (access control).
    *   **Key Components:**
        *   `Http/Controllers/PublicContentController.php` (API for fetching public notes, etc.)
        *   Services related to fetching/aggregating community content.
        *   Policies/Middleware enforcing Premium access if applicable.

*   **`app/Modules/CoreApi`** (Potentially minimal or evolves to `SharedKernel`)
    *   **Responsibilities:** This module might be less domain-specific. Initially, it could house base API controllers, shared API traits (e.g., for standardized responses), common API middleware registration, or core exception handling for the API. Alternatively, these elements might be placed directly in `app/Http` or `app/Providers`, and this module could evolve into a `SharedKernel` for truly cross-cutting domain logic (e.g., shared interfaces, base service classes) not tied to a specific module *or* the HTTP layer. API controllers for specific domains (`User`, `Content`, `Note`, etc.) reside within their respective modules (`UserManagement`, `ContentManagement`, `NotesService`).
    *   **Key Components (Potential):**
        *   `Http/Controllers/Api/BaseApiController.php`
        *   `Traits/ApiResponseHelpers.php`
        *   Centralized API middleware registration logic (if not in `app/Http/Kernel.php`).

**3. Interactions**

Modules will interact primarily through:
*   **Service Classes:** Injecting services from one module into another (e.g., `NotePolicy` using `SubscriptionService` to check status).
*   **Events and Listeners:** Decoupled communication (e.g., `SubscriptionBilling` firing `SubscriptionActivated` event, listened to by `UserManagement` or `ProtocolEngine`).
*   **Eloquent Relationships:** Defined on Models (e.g., `User` hasMany `Subscription`).

**4. Conclusion**

This modular breakdown provides a structured approach to developing the Laravel backend. It aims to create clear boundaries between different functional areas of the application, facilitating parallel development, easier testing, and long-term maintainability, while still benefiting from the simplicity of a single deployment unit (monolith).
