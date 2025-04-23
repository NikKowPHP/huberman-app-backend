
# Huberman App: Backend Implementation TODO Checklist (TDD Approach)

**Project Goal:** Implement the Laravel backend for the Huberman App, supporting a Freemium model, content delivery, user interactions, reminders, tracking, and subscription management, ready for production deployment.

**Development Approach:** Test-Driven Development (TDD) will be used. For each functional component, write tests *first* to define expected behavior, then implement the code to make the tests pass, and finally refactor.

**Legend:**
*   `[ ]` - To Do
*   `[x]` - Done
*   `(TDD)` - Indicates steps where writing tests *before* implementation is paramount.

---

## Phase 1: Project Setup & Foundation (Milestone 1 & 2 Prep)

*   **Environment & Tooling:**
    *   `[x]` Initialize Git repository.
    *   `[ ]` Install Laravel (`11.x`) using Composer (`composer create-project laravel/laravel huberman-app-backend`).
    *   `[ ]` Configure basic `.env` file for local development (App Name, Key, Debug, Log level, DB connection defaults).
    *   `[ ]` Set up Docker environment (`docker-compose.yml`) with services:
        *   `[ ]` PHP (`8.2+`) container (e.g., with FPM).
        *   `[ ]` Web Server (Nginx or Caddy) container configured for Laravel.
        *   `[ ]` PostgreSQL (`16.x` preferred) container.
        *   `[ ]` Redis (`7.x`) container.
        *   `[ ]` Node.js (for potential build steps, though primarily frontend).
    *   `[ ]` Verify local Docker environment is running (`docker-compose up -d`) and accessible.
    *   `[ ]` Configure PHPUnit (`phpunit.xml`) for testing environment (e.g., use in-memory SQLite or separate test PG database).
    *   `[ ]` Establish coding standards (PSR-12 enforced, consider `laravel/pint` setup).
    *   `[ ]` Install & Configure SAST tools:
        *   `[ ]` `phpstan/phpstan`, `larastan/larastan`. Configure `phpstan.neon`.
        *   `[ ]` `vimeo/psalm`. Configure `psalm.xml`. (Optional if PHPStan sufficient).
    *   `[ ]` Run initial SAST checks to ensure baseline setup.

*   **Core Structure & Base Components:**
    *   `[ ]` Define base module structure: Create `app/Modules/` directory and subdirectories for initial modules (`Authentication`, `UserManagement`, `SubscriptionBilling`, `ContentManagement`, `ProtocolEngine`, `NotesService`, `CoreApi` - potentially rename/refactor `CoreApi` later).
    *   `[ ]` (TDD) Create base API test case (`tests/Feature/ApiTestCase.php`?) setting common headers (Accept: application/json).
    *   `[ ]` (TDD) Implement base API controller (`app/Modules/CoreApi/Http/Controllers/Api/BaseApiController.php`?) with common methods/traits if needed.
    *   `[ ]` (TDD) Implement standard API response structure/trait (e.g., `ApiResponseHelpers`) if deviating from simple resource responses.
    *   `[ ]` Configure API routing (`routes/api.php`): Set up version prefix (`/v1`) and include route files from modules.

---

## Phase 2: Core User & Authentication (Milestone 2 - TDD Focus)

*   **User Model & Migration:**
    *   `[ ]` (TDD) Write tests for `User` model creation, relationships (initially none needed), attributes, fillable properties, hidden properties (`password`, `remember_token`), casts (`email_verified_at`).
    *   `[ ]` Implement `User` model (`app/Modules/UserManagement/Models/User.php`) extending Authenticatable.
    *   `[ ]` Implement `create_users_table` migration based on `database_migrations_plan.md`. Ensure soft deletes, timestamps.
    *   `[ ]` Run migration (`php artisan migrate`). Ensure tests pass.

*   **Authentication (Sanctum):**
    *   `[ ]` Install Laravel Sanctum (`composer require laravel/sanctum`). Publish migrations & config.
    *   `[ ]` Run Sanctum migrations (`php artisan migrate`).
    *   `[ ]` Add `HasApiTokens` trait to `User` model.
    *   `[ ]` Configure Sanctum (token expiry, domains) in `config/sanctum.php` and `.env`.

*   **Registration:**
    *   `[ ]` (TDD) Write API tests for `POST /api/v1/register`:
        *   Test validation rules (name, email, password, confirmation).
        *   Test successful registration (user created in DB, 201 status, user data + token returned).
        *   Test email uniqueness constraint.
    *   `[ ]` Implement `RegisterRequest` Form Request (`app/Modules/Authentication/Http/Requests/`).
    *   `[ ]` Implement `AuthController::register` method (`app/Modules/Authentication/Http/Controllers/`).
    *   `[ ]` Define route in module's API routes file. Ensure tests pass.

*   **Login:**
    *   `[ ]` (TDD) Write API tests for `POST /api/v1/login`:
        *   Test validation rules (email, password).
        *   Test successful login (correct credentials -> 200 status, user data + token returned).
        *   Test incorrect credentials (401/422 status).
    *   `[ ]` Implement `LoginRequest` Form Request.
    *   `[ ]` Implement `AuthController::login` method.
    *   `[ ]` Define route. Ensure tests pass.

*   **Logout:**
    *   `[ ]` (TDD) Write API tests for `POST /api/v1/logout` (authenticated route):
        *   Test successful logout (token invalidated, 204 status).
        *   Test unauthenticated access (401 status).
    *   `[ ]` Implement `AuthController::logout` method (invalidate current token).
    *   `[ ]` Define route, protected by `auth:sanctum`. Ensure tests pass.

*   **Authenticated User Endpoint:**
    *   `[ ]` (TDD) Write API tests for `GET /api/v1/user/profile` (authenticated):
        *   Test fetching authenticated user's data.
        *   Test unauthenticated access (401).
    *   `[ ]` Implement `UserController::profile` (`app/Modules/UserManagement/Http/Controllers/`).
    *   `[ ]` Define route, protected by `auth:sanctum`. Ensure tests pass.

*   **Password Reset:**
    *   `[ ]` Configure Mail driver for local testing (e.g., Mailtrap, Log driver).
    *   `[ ]` (TDD) Write tests for password reset flow:
        *   Forgot Password Request (`POST /api/v1/forgot-password`): Test validation (email exists), successful response, notification/event fired (mock Mail).
        *   Reset Password (`POST /api/v1/reset-password`): Test validation (token, email, password, confirmation), successful reset, token consumption.
    *   `[ ]` Implement necessary controllers, requests, notifications/events for password reset.
    *   `[ ]` Define routes. Ensure tests pass.

*   **Security Controls:**
    *   `[ ]` (TDD) Write tests for rate limiting on auth endpoints.
    *   `[ ]` Apply Laravel's default rate limiting middleware to auth routes in `RouteServiceProvider` or route definitions. Configure limits appropriately.

---

## Phase 3: Subscription & Billing Foundation (Milestone 4 Prep - TDD Focus)

*   **Models & Migrations:**
    *   `[ ]` Implement `create_plans_table` migration.
    *   `[ ]` Implement `create_subscriptions_table` migration (ensure FKs, indexes, `onDelete`).
    *   `[ ]` Run migrations.
    *   `[ ]` (TDD) Write tests for `Plan` model (attributes, maybe factory).
    *   `[ ]` Implement `Plan` model (`app/Modules/SubscriptionBilling/Models/`).
    *   `[ ]` (TDD) Write tests for `Subscription` model (attributes, relationships to `User` and `Plan`, casts, scopes like `active()`).
    *   `[ ]` Implement `Subscription` model (`app/Modules/SubscriptionBilling/Models/`), add relationships to `User` model.

*   **Cashier Integration:**
    *   `[ ]` Install Laravel Cashier Stripe (`composer require laravel/cashier`). Publish migrations & config.
    *   `[ ]` Run Cashier migrations.
    *   `[ ]` Configure Cashier (`config/cashier.php`, `services.stripe.key/secret` in `.env`). Set User model.
    *   `[ ]` Add `Billable` trait to `User` model.

*   **Core Service & API Stubs:**
    *   `[ ]` Define `SubscriptionServiceInterface` contract (`app/Modules/SubscriptionBilling/Contracts/`) with key methods (`userHasActivePremiumSubscription`, `getUserPlan`, `handleWebhook`, etc.).
    *   `[ ]` Implement basic `SubscriptionService` class (`app/Modules/SubscriptionBilling/Services/`) implementing the interface (methods can initially return false/null or throw `NotImplementedException`).
    *   `[ ]` Bind interface to implementation in a service provider.
    *   `[ ]` (TDD) Write API test for `GET /api/v1/plans`.
    *   `[ ]` Implement API endpoint (`SubscriptionController::plans`) to fetch and return `Plan` data (initially, manually seed some plans or use factories).
    *   `[ ]` (TDD) Write API test for `GET /api/v1/user/subscription` (authenticated). Test fetching current subscription status (initially null/empty).
    *   `[ ]` Implement API endpoint (`SubscriptionController::userSubscription`) using `SubscriptionService` stub.

*   **Webhook Foundation:**
    *   `[ ]` (TDD) Write tests for `WebhookController` signature verification middleware/logic (mock Stripe request/signature).
    *   `[ ]` Implement `WebhookController` (`app/Modules/SubscriptionBilling/Http/Controllers/`) with route (`POST /api/webhooks/stripe`).
    *   `[ ]` Implement Stripe signature verification logic (using Cashier's built-in tools or manually).
    *   `[ ]` Define route (ensure CSRF protection is disabled for webhooks). Ensure verification tests pass.

---

## Phase 4: Basic Content Management (Milestone 3 & 5 Prep - TDD Focus)

*   **Models & Migrations:**
    *   `[ ]` Implement `create_episodes_table` migration.
    *   `[ ]` Implement `create_protocols_table` migration.
    *   `[ ]` Implement `create_summaries_table` migration.
    *   `[ ]` Implement `create_episode_protocol_table` (pivot) migration.
    *   `[ ]` Run migrations.
    *   `[ ]` (TDD) Test `Episode`, `Protocol`, `Summary` models (attributes, relationships). Test `Episode<->Protocol` many-to-many relationship.
    *   `[ ]` Implement models in `app/Modules/ContentManagement/Models/`. Add relationships.

*   **Seeding:**
    *   `[ ]` Create Seeders for `Plans` (Free, Premium Monthly, Premium Annual).
    *   `[ ]` Create Seeders for foundational `Protocols` and related `Summaries`. Link to placeholder `Episodes` if necessary.
    *   `[ ]` Run seeders (`php artisan db:seed`).

*   **Core Service & API:**
    *   `[ ]` Define `ContentServiceInterface` contract (`app/Modules/ContentManagement/Contracts/`) (e.g., `getProtocols`, `getProtocolDetails`, etc.).
    *   `[ ]` Implement basic `ContentService`. Bind interface.
    *   `[ ]` (TDD) Write API tests for listing/viewing episodes, protocols, summaries (unauthenticated, basic retrieval).
    *   `[ ]` Implement basic API controllers (`EpisodeController`, `ProtocolController`, `SummaryController`) using the `ContentService`.
    *   `[ ]` Define content API routes. Ensure tests pass.

---

## Phase 5: Feature Gating Implementation (Milestone 4 & 5 - TDD Focus)

*   **Subscription Service Logic:**
    *   `[ ]` (TDD) Write unit tests for `SubscriptionService::userHasActivePremiumSubscription` covering different scenarios: no subscription, free plan, active premium, trialing premium, canceled premium (before/after `ends_at`), expired, past_due. Use factories to set up test data.
    *   `[ ]` Implement the actual logic in `SubscriptionService` querying the `subscriptions` table, joining `plans`, checking status (`active`, `trialing`) and `ends_at`.
    *   `[ ]` (TDD) Test caching logic: test that a cache hit avoids DB query, test cache miss populates cache, test cache TTL.
    *   `[ ]` Implement caching layer within `userHasActivePremiumSubscription` (using `Cache::remember`).

*   **Middleware:**
    *   `[ ]` (TDD) Write feature tests applying `CheckPremiumAccess` middleware to a test route: test authenticated premium user passes, authenticated free user gets 403, unauthenticated user gets 401 (handled by `auth:sanctum`).
    *   `[ ]` Implement `CheckPremiumAccess` middleware using the `SubscriptionServiceInterface`.
    *   `[ ]` Register middleware in `app/Http/Kernel.php`.
    *   `[ ]` Apply middleware to relevant premium API route groups (initially, maybe just the premium content routes). Ensure tests pass.

*   **Gated Content API:**
    *   `[ ]` (TDD) Write API tests specifically testing that premium content (e.g., full protocol details including `implementation_guide`) is *only* returned to authenticated, premium users, while free users get limited data or 403 (decide strategy).
    *   `[ ]` Refactor Content API endpoints/service to incorporate checks (either via middleware or conditional logic based on `userHasActivePremiumSubscription`) and return data accordingly. Use API Resources to conditionally load attributes. Ensure tests pass.

---


## Phase 6: Full Subscription Lifecycle via Webhooks (Milestone 4 - TDD Focus)

*   **Webhook Processing Logic (Stripe via Cashier):**
    *   **Event: `checkout.session.completed`**
        *   `[ ]` (TDD) Write test simulating webhook, asserting `Subscription` created with `trialing` or `active` status, `ends_at`, `trial_ends_at` set correctly. Assert `SubscriptionStarted` event dispatched.
        *   `[ ]` Verify Cashier's built-in listener handles this correctly OR implement custom listener/logic.
        *   `[ ]` Ensure associated `User` record is updated if needed (e.g., setting `stripe_id`).
    *   **Event: `customer.subscription.updated` (Trial Ends -> Active - Often via `invoice.payment_succeeded`)**
        *   `[ ]` (TDD) Write test simulating relevant webhook (likely `invoice.payment_succeeded` post-trial), asserting `Subscription` status moves to `active`, `trial_ends_at` is nullified, `ends_at` updated.
        *   `[ ]` Verify Cashier listener handles this.
    *   **Event: `invoice.payment_succeeded` (Renewal)**
        *   `[ ]` (TDD) Write test simulating webhook, asserting `Subscription.ends_at` is updated for the next period. Assert `SubscriptionRenewed` event dispatched.
        *   `[ ]` Verify Cashier listener handles renewal date updates.
    *   **Event: `invoice.payment_failed`**
        *   `[ ]` (TDD) Write test simulating webhook, asserting `Subscription.status` updated (e.g., to `past_due` if configured, or remains `active` during grace period). Assert `PaymentFailed` event dispatched.
        *   `[ ]` Implement logic based on Stripe retry settings (Cashier might handle `past_due` state).
    *   **Event: `customer.subscription.updated` (Cancel at Period End)**
        *   `[ ]` (TDD) Write test simulating webhook (where `cancel_at_period_end` is true), asserting `Subscription.status` potentially updated (`canceled`?) and `ends_at` reflects cancellation date. Assert `SubscriptionCanceled` event dispatched.
        *   `[ ]` Verify Cashier handles `onSubscriptionUpdated` appropriately for cancellation flags.
    *   **Event: `customer.subscription.deleted` (Immediate Cancel / Final Failure)**
        *   `[ ]` (TDD) Write test simulating webhook, asserting `Subscription.status` is updated to `canceled` or `expired`, `ends_at` is set to past/now. Assert `SubscriptionExpired` or `SubscriptionCanceled` event dispatched.
        *   `[ ]` Verify Cashier handles this state transition.

*   **Webhook Processing Logic (Apple IAP - Server Notifications V2):**
    *   `[ ]` Implement endpoint/logic to receive and decode signed `JWS` payload from Apple (`POST /api/webhooks/apple`).
    *   `[ ]` Implement App Store Server API client/library for server-side receipt validation (if needed beyond notification data).
    *   `[ ]` (TDD) Test JWS signature verification & payload decoding.
    *   `[ ]` **Event: `SUBSCRIBED` / `DID_RENEW`**
        *   `[ ]` (TDD) Test handler maps event to internal state (`active`/`trialing`), updates `Subscription` record (`ends_at`, `provider_id`). Dispatches `SubscriptionStarted`/`SubscriptionRenewed`.
        *   `[ ]` Implement handler logic for `SUBSCRIBED`/`DID_RENEW`.
    *   `[ ]` **Event: `DID_FAIL_TO_RENEW`**
        *   `[ ]` (TDD) Test handler maps event to internal state (`past_due` or `expired` depending on grace period handling), updates `Subscription`. Dispatches `PaymentFailed`/`SubscriptionExpired`.
        *   `[ ]` Implement handler logic for `DID_FAIL_TO_RENEW`.
    *   `[ ]` **Event: `EXPIRED`**
        *   `[ ]` (TDD) Test handler maps event to internal state (`expired`), updates `Subscription`. Dispatches `SubscriptionExpired`.
        *   `[ ]` Implement handler logic for `EXPIRED`.
    *   `[ ]` **Event: `DID_CHANGE_RENEWAL_STATUS` (User turns off auto-renew)**
        *   `[ ]` (TDD) Test handler maps event to internal state (`canceled`), updates `Subscription` (`ends_at` remains period end). Dispatches `SubscriptionCanceled`.
        *   `[ ]` Implement handler logic for `DID_CHANGE_RENEWAL_STATUS`.
    *   `[ ]` *(Add handlers for other relevant notification types: `GRACE_PERIOD_EXPIRED`, `REVOKED`, etc.)*

*   **Webhook Processing Logic (Google Play Billing - RTDN via Pub/Sub):**
    *   `[ ]` Set up Google Cloud Pub/Sub topic and push subscription endpoint (`POST /api/webhooks/google`).
    *   `[ ]` Implement endpoint to receive Pub/Sub message, decode base64 data.
    *   `[ ]` Implement Google Play Developer API client/library for purchase validation/acknowledgement.
    *   `[ ]` (TDD) Test Pub/Sub message decoding and JSON parsing.
    *   `[ ]` **Notification Type: `SUBSCRIPTION_PURCHASED` / `SUBSCRIPTION_RENEWED`**
        *   `[ ]` (TDD) Test handler maps notification to internal state (`active`/`trialing`), updates `Subscription` (`ends_at`, `provider_id` - purchase token). Dispatches `SubscriptionStarted`/`SubscriptionRenewed`. Acknowledges purchase.
        *   `[ ]` Implement handler logic.
    *   `[ ]` **Notification Type: `SUBSCRIPTION_IN_GRACE_PERIOD`**
        *   `[ ]` (TDD) Test handler maps notification to internal state (`past_due`), updates `Subscription`. Dispatches `PaymentFailed`.
        *   `[ ]` Implement handler logic.
    *   `[ ]` **Notification Type: `SUBSCRIPTION_ON_HOLD`**
        *   `[ ]` (TDD) Test handler maps notification to internal state (`past_due` or custom `on_hold`), updates `Subscription`.
        *   `[ ]` Implement handler logic.
    *   `[ ]` **Notification Type: `SUBSCRIPTION_CANCELED`**
        *   `[ ]` (TDD) Test handler maps notification to internal state (`canceled`), updates `Subscription` (`ends_at` remains period end). Dispatches `SubscriptionCanceled`.
        *   `[ ]` Implement handler logic.
    *   `[ ]` **Notification Type: `SUBSCRIPTION_EXPIRED`**
        *   `[ ]` (TDD) Test handler maps notification to internal state (`expired`), updates `Subscription`. Dispatches `SubscriptionExpired`.
        *   `[ ]` Implement handler logic.
    *   `[ ]` *(Add handlers for other relevant notification types: `SUBSCRIPTION_REVOKED`, `SUBSCRIPTION_PAUSED`, etc.)*

*   **Scheduled Job for Status Check (Optional but Recommended):**
    *   `[ ]` (TDD) Test a scheduled job that finds `canceled` subscriptions where `ends_at` is in the past and updates status to `expired`.
    *   `[ ]` Implement `CheckExpiredSubscriptions` job/command.
    *   `[ ]` Schedule job in `Kernel.php`.

*   **Cache Invalidation:**
    *   `[ ]` Create `ClearUserEntitlementCache` Listener.
    *   `[ ]` (TDD) Write test ensuring Listener clears the correct cache key (`user:{id}:is_premium`).
    *   `[ ]` Implement cache clearing logic in Listener.
    *   `[ ]` Register Listener to listen for `SubscriptionStarted`, `SubscriptionRenewed`, `SubscriptionCanceled`, `SubscriptionExpired` events.
    *   `[ ]` (TDD) Verify event dispatch in webhook tests triggers the listener (using `Event::fake()` and assertions).

---

## Phase 7: Implementing MVP Features (Milestone 5 & 6 Prep - TDD Focus)

*   **Free Tier - Basic Reminders:**
    *   *(Keep as is, fairly simple)*
    *   `[ ]` Define logic for identifying foundational protocols.
    *   `[ ]` (TDD) Test logic for sending pre-set reminders (mock notifications).
    *   `[ ]` Implement simple scheduled command/job to send these notifications (if different from Premium).

*   **Premium Tier - Full Content Access:**
    *   *(Keep as is)*
    *   `[ ]` (TDD) Ensure existing Content API tests cover full access for premium users.
    *   `[ ]` Verify implementation returns all required data (`implementation_guide`, etc.) for premium users via API Resources.

*   **Premium Tier - Custom Reminders (MVP Scope):**
    *   **Database:**
        *   `[ ]` Implement `create_user_reminders_table` migration (user_id, protocol_id, reminder_time, frequency, timezone, last_sent_at, is_active, device_token?). Run migration.
        *   `[ ]` (TDD) Test `UserReminder` model attributes and relationships (belongsTo User, belongsTo Protocol).
        *   `[ ]` Implement `UserReminder` model (`app/Modules/ProtocolEngine/Models/`). Add relationships.
    *   **API CRUD:**
        *   `[ ]` Implement `ReminderPolicy` checking `update`, `delete`, `create` permissions (user ownership, premium status via `SubscriptionServiceInterface`).
        *   `[ ]` (TDD) Test `ReminderPolicy` denies access for free users and non-owners.
        *   `[ ]` **Create Reminder:**
            *   `[ ]` (TDD) API Test `POST /api/v1/reminders`: Valid input creates record, invalid input returns 422, free user gets 403.
            *   `[ ]` Implement `StoreReminderRequest` Form Request (validation rules for protocol_id, time, frequency, timezone?).
            *   `[ ]` Implement `ReminderController::store` calling `ReminderService::setReminder`.
            *   `[ ]` Implement `ReminderService::setReminder` (performs premium check, saves to DB).
            *   `[ ]` Define `POST /api/v1/reminders` route, apply `auth:sanctum`, `CheckPremiumAccess` middleware, use `ReminderPolicy`.
        *   `[ ]` **List Reminders:**
            *   `[ ]` (TDD) API Test `GET /api/v1/reminders`: Returns user's reminders, free user gets 403.
            *   `[ ]` Implement `ReminderController::index` calling `ReminderService::getUserReminders`.
            *   `[ ]` Implement `ReminderService::getUserReminders`.
            *   `[ ]` Define `GET /api/v1/reminders` route, apply middleware.
        *   `[ ]` **Update Reminder:**
            *   `[ ]` (TDD) API Test `PUT /api/v1/reminders/{id}`: Valid input updates record, invalid input returns 422, non-owner gets 403.
            *   `[ ]` Implement `UpdateReminderRequest` Form Request.
            *   `[ ]` Implement `ReminderController::update` calling `ReminderService::updateReminder`.
            *   `[ ]` Implement `ReminderService::updateReminder`.
            *   `[ ]` Define `PUT /api/v1/reminders/{id}` route, apply middleware/policy.
        *   `[ ]` **Delete Reminder:**
            *   `[ ]` (TDD) API Test `DELETE /api/v1/reminders/{id}`: Deletes record, non-owner gets 403.
            *   `[ ]` Implement `ReminderController::destroy` calling `ReminderService::deleteReminder`.
            *   `[ ]` Implement `ReminderService::deleteReminder`.
            *   `[ ]` Define `DELETE /api/v1/reminders/{id}` route, apply middleware/policy.
    *   **Scheduling Logic:**
        *   `[ ]` (TDD) Test `reminders:send-due` command finds reminders due now based on `reminder_time`, user's `timezone`, `frequency`, and `is_active` status. Mock `Carbon::now()`.
        *   `[ ]` Implement `reminders:send-due` console command query and filtering logic.
        *   `[ ]` Ensure command converts current UTC time to user's timezone for comparison.
        *   `[ ]` Schedule command in `app/Console/Kernel.php` (e.g., `everyMinute()`).
        *   `[ ]` Implement logic within the command to dispatch `SendProtocolReminderNotification` job for each due reminder.
    *   **Notification Sending:**
        *   `[ ]` Implement `SendProtocolReminderNotification` Job.
        *   `[ ]` (TDD) Test `SendProtocolReminderNotification` job: retrieves `UserReminder`, `User`, `Protocol`; fetches user's device token(s); constructs payload; mocks `Notification::send()`.
        *   `[ ]` Implement `ProtocolReminder` Notification class (using `database` and `fcm`/`apns` channels). Define payload structure (`toFcm`, `toApns`).
        *   `[ ]` Implement logic in the Job to fetch user's device token(s) (e.g., from `User` model or dedicated `devices` table).
        *   `[ ]` Implement Job logic to call `Notification::send()` with the user and notification instance.
        *   `[ ]` Implement logic in Job to update `UserReminder.last_sent_at` on successful dispatch/send.
    *   **Device Token Management:**
        *   `[ ]` Add `device_tokens` (JSON?) column to `users` table OR create `user_devices` table migration. Run migration.
        *   `[ ]` (TDD) Test storing and retrieving device tokens for a user.
        *   `[ ]` Implement `UserController::updateDeviceToken` method.
        *   `[ ]` Implement `UpdateDeviceTokenRequest` Form Request.
        *   `[ ]` Define API route `POST /api/v1/user/device-token`, apply `auth:sanctum`.

---




## Phase 8: Implementing Post-MVP Features (As Prioritized - TDD Focus)

*   *For each Post-MVP Feature (Notes, Tracking, Detailed Content, etc.):*
    *   `[ ]` (TDD) Define Models & Migrations. Test models. Implement & run migrations.
    *   `[ ]` (TDD) Define Service interfaces & implementations. Test core logic (e.g., streak calculation, note limits).
    *   `[ ]` (TDD) Define Policies (ownership, premium access). Test policies.
    *   `[ ]` (TDD) Define API Endpoints (Controllers, Form Requests). Test validation, responses, middleware/policy application.
    *   `[ ]` Implement all components. Define routes. Ensure all tests pass.

---

## Phase 9: API Documentation & Refinement

*   `[ ]` Install & Configure `zircote/swagger-php` (if using annotations).
*   `[ ]` Annotate all API controllers/methods/schemas OR manually update `openapi.yaml`.
    *   `[ ]` Document all endpoints, parameters, request bodies.
    *   `[ ]` Document all response schemas (success & error).
    *   `[ ]` Document security requirements (`auth:sanctum`).
    *   `[ ]` Clearly mark premium-only endpoints/features in descriptions.
*   `[ ]` Generate/Validate the `openapi.yaml` specification file.
*   `[ ]` Commit the final `openapi.yaml` to the repository.

---

## Phase 10: Testing & Quality Assurance

*   `[ ]` Review overall unit test coverage. Improve coverage for critical areas (billing, auth, gating).
*   `[ ]` Write integration tests for key end-to-end flows (e.g., register -> login -> upgrade -> access premium feature -> cancel -> lose access).
*   `[ ]` Perform manual QA against user stories (both Free & Premium flows). Test edge cases, different subscription states.
*   `[ ]` Run final SAST checks (`composer audit`, PHPStan/Psalm). Address findings.
*   `[ ]` Perform basic DAST scan (e.g., OWASP ZAP) against Staging environment. Address critical findings.

---

## Phase 11: Deployment Preparation

*   `[ ]` Finalize Staging/Production environment configuration (Forge/Vapor/other).
*   `[ ]` Provision managed PostgreSQL & Redis. Configure backups.
*   `[ ]` Set up CI/CD pipeline (e.g., GitHub Actions):
    *   `[ ]` Trigger on pushes/PRs to `main`/`develop`.
    *   `[ ]` Install dependencies (`composer install --no-dev --optimize-autoloader`).
    *   `[ ]` Run SAST checks.
    *   `[ ]` Run tests (PHPUnit).
    *   `[ ]` Build assets (if any backend assets).
    *   `[ ]` Deploy to Staging (manual trigger?).
    *   `[ ]` Deploy to Production (manual trigger).
*   `[ ]` Configure secure environment variables for Staging/Production in hosting provider.
*   `[ ]` Set up Monitoring (e.g., Forge/Vapor monitoring, Datadog, Sentry/Flare).
*   `[ ]` Set up Logging aggregation (e.g., Papertrail, CloudWatch Logs).
*   `[ ]` Set up Alerting for critical errors/events.
*   `[ ]` Configure queue workers for Staging/Production (e.g., using Supervisor).

---

## Phase 12: Production Launch & Post-Launch

*   `[ ]` Perform final deployment dry-run to Staging.
*   `[ ]` Coordinate launch with frontend team.
*   `[ ]` Deploy backend to Production environment.
*   `[ ]` Run initial database migrations and seeders (if needed) in Production.
*   `[ ]` **Monitor:** Closely watch logs, error tracking, server performance, queue lengths, payment provider dashboards immediately post-launch.
*   `[ ]` Address any critical post-launch issues promptly.
*   `[ ]` Schedule or confirm completion of external penetration testing. Remediate findings.
*   `[ ]` Establish ongoing maintenance plan (dependency updates, security patches).

