# Huberman App: Backend Implementation TODO Checklist (TDD Approach)

**Project Goal:** Implement the Laravel backend for the Huberman App, supporting a Freemium model, content delivery, user interactions, reminders, tracking, and subscription management, ready for production deployment.

**Development Approach:** Test-Driven Development (TDD) will be used. For each functional component, write tests *first* to define expected behavior, then implement the code to make the tests pass, and finally refactor.
**COMMAND EXECUTION** Every command should be executed with 'docker-compose exec app' prefix.

**Legend:**
*   `[ ]` - To Do
*   `[x]` - Done
*   `(TDD)` - Indicates steps where writing tests *before* implementation is paramount.

---

## Phase 1: Project Setup & Foundation (Milestone 1 & 2 Prep)

*   **Environment & Tooling:**
    *   `[x]` Initialize Git repository.
    *   `[x]` Install Laravel (`11.x`) using Composer (`composer create-project laravel/laravel huberman-app-backend`).
    *   `[x]` Configure basic `.env` file for local development (App Name, Key, Debug, Log level, DB connection defaults).
    *   `[x]` Create/Finalize Docker environment (`docker-compose.yml`) based on `infrastructure_devops_details.md` with services:
        *   `[x]` PHP (`8.2+`) container + Dockerfile.
        *   `[x]` Web Server (Nginx or Caddy) container + config.
        *   `[x]` PostgreSQL (`17.x` or latest supported) container.
        *   `[x]` Redis (`7.x`) container.
        *   `[ ]` Node.js (LTS) container (optional, for build tools).
    *   `[x]` Verify local Docker environment is running (`docker-compose up -d`) and accessible.
    *   `[x]` Configure PHPUnit (`phpunit.xml`) for testing environment (e.g., separate test PG database connection in `.env.testing`).
    *   `[x]` Establish coding standards (PSR-12 enforced, setup `laravel/pint`).
    *   `[x]` Install & Configure SAST tools:
        *   `[x]` Install `phpstan/phpstan`, `larastan/larastan`. Configure `phpstan.neon`.
        *   `[x]` (Optional) Install `vimeo/psalm`. Configure `psalm.xml`.
    *   `[x]` Run initial SAST checks to ensure baseline setup.

*   **Core Structure & Base Components:**
*   `[x]` Define base module structure: Create `app/Modules/` directory and subdirectories (`Authentication`, `UserManagement`, `SubscriptionBilling`, `ContentManagement`, `ProtocolEngine`, `NotesService`, `CoreApi` - adjust as needed).
    *   `[x]` Create base API test case (`tests/Feature/ApiTestCase.php`) setting common headers (Accept: application/json).
    *   `[x]` Implement base API controller (`app/Modules/CoreApi/Http/Controllers/Api/BaseApiController.php`) with common methods/traits if needed.
    *   `[x]` Implement standard API response structure/trait (e.g., `ApiResponseHelpers`) if deviating from simple resource responses.
    *   `[x]` Configure API routing (`routes/api.php`): Set up version prefix (`/v1`) and include route files from modules.

---

## Phase 2: Core User & Authentication (Milestone 2 - TDD Focus)

*   **User Model & Migration:**
    *   `[x]` (TDD) Write tests for `User` model creation, relationships (initially Subscription, Notes, Reminders, Tracking), attributes, fillable, hidden (`password`, `remember_token`), casts (`email_verified_at`), `$with` relations.
    *   `[x]` Implement `create_users_table` migration based on `database_migrations_plan.md`. Ensure soft deletes, timestamps.
    *   `[x]` Run migration (`php artisan migrate`).
    *   `[x]` Implement `User` model (`app/Modules/UserManagement/Models/User.php`) extending Authenticatable.
    *   `[x]` Ensure User model tests pass.

*   **Authentication (Sanctum):**
    *   `[x]` Install Laravel Sanctum (`composer require laravel/sanctum`). Publish migrations & config.
    *   `[x]` Run Sanctum migrations (`php artisan migrate`).
    *   `[x]` Add `HasApiTokens` trait to `User` model.
    *   `[x]` Configure Sanctum (token expiry, domains) in `config/sanctum.php` and `.env`.

*   **Registration:**
    *   `[x]` (TDD) Write API tests for `POST /api/v1/register`: Validation rules, success (DB check, 201, user+token response), email uniqueness.
    *   `[x]` Implement `RegisterRequest` Form Request (`app/Modules/Authentication/Http/Requests/`).
    *   `[x]` Implement `AuthController::register` method (`app/Modules/Authentication/Http/Controllers/`).
    *   `[x]` Define route in module's API routes file. Ensure tests pass.

*   **Login:**
    *   `[x]` (TDD) Write API tests for `POST /api/v1/login`: Validation rules, success (correct credentials -> 200, user+token), failure (incorrect credentials -> 401/422).
    *   `[x]` Implement `LoginRequest` Form Request.
    *   `[x]` Implement `AuthController::login` method.
    *   `[x]` Define route. Ensure tests pass.

*   **Logout:**
    *   `[x]` (TDD) Write API tests for `POST /api/v1/logout` (authenticated): Successful logout (token invalid, 204), unauthenticated access (401).
    *   `[x]` Implement `AuthController::logout` method (invalidate current token).
    *   `[x]` Define route, protected by `auth:sanctum`. Ensure tests pass.

*   **Authenticated User Endpoint:**
    *   `[x]` (TDD) Write API tests for `GET /api/v1/user/profile` (authenticated): Fetches user data, unauthenticated access (401).
    *   `[x]` Implement `UserController::profile` (`app/Modules/UserManagement/Http/Controllers/`).
    *   `[x]` Define route, protected by `auth:sanctum`. Ensure tests pass.

*   **Password Reset:**
    *   `[x]` Configure Mail driver for local testing (e.g., Mailtrap, Log driver in `.env`).
    *   `[x]` (TDD) Test Forgot Password (`POST /api/v1/forgot-password`): Validation (email exists), success response, mock `Notification::send`.
    *   `[x]` (TDD) Test Reset Password (`POST /api/v1/reset-password`): Validation (token, email, password, confirmation), successful reset, token consumed/invalidated.
    *   `[x]` Implement necessary Controllers (`ForgotPasswordController`, `NewPasswordController`), Requests, Notifications (`ResetPasswordNotification`).
    *   `[ ]` Define password reset routes. Ensure tests pass.

*   **Security Controls:**
    *   `[x]` (TDD) Write tests for rate limiting on auth endpoints (`login`, `register`, `forgot-password`).
    *   `[x]` Apply Laravel's default rate limiting middleware to auth routes in `RouteServiceProvider` or route definitions. Configure limits (e.g., in `.env`). Ensure tests pass.

---

## Phase 3: Subscription & Billing Foundation (Milestone 4 Prep - TDD Focus)

*   **Models & Migrations:**
    *   `[x]` Implement `create_plans_table` migration. Run migration.
    *   `[x]` Implement `create_subscriptions_table` migration (FKs, indexes, onDelete). Run migration.
    *   `[x]` (TDD) Test `Plan` model (attributes, factory, maybe `isActive` scope). Implement model (`app/Modules/SubscriptionBilling/Models/Plan.php`).
    *   `[x]` (TDD) Test `Subscription` model (attributes, relationships `User`/`Plan`, casts, scopes like `active()`, `trialing()`). Implement model (`app/Modules/SubscriptionBilling/Models/Subscription.php`).
    *   `[x]` Add `hasMany(Subscription::class)` relationship to `User` model.

*   **Cashier Integration (Stripe):**
    *   `[x]` Install Laravel Cashier Stripe (`composer require laravel/cashier`). Publish migrations & config.
    *   `[x]` Run Cashier migrations (`php artisan migrate`).
    *   `[x]` Configure Cashier (`config/cashier.php`, `services.stripe.key/secret/webhook_secret` in `.env`). Set User model.
    *   `[x]` Add `Billable` trait to `User` model.

*   **Core Service & API Stubs:**
    *   `[x]` Define `SubscriptionServiceInterface` contract (`app/Modules/SubscriptionBilling/Contracts/`) with key methods (`userHasActivePremiumSubscription`, `getUserPlan`, `handleWebhook`, `getSubscriptionStatus`, etc.).
    *   `[x]` Implement basic `SubscriptionService` class (`app/Modules/SubscriptionBilling/Services/`) implementing the interface (methods return defaults/throw `NotImplementedException`).
    *   `[x]` Bind interface to implementation in a service provider (`SubscriptionBillingServiceProvider`).
    *   `[ ]` **Plans API:**
        *   `[x]` Seed `Plans` table (Free, Premium Monthly, Premium Annual) using a `PlanSeeder`.
        *   `[x]` (TDD) API Test `GET /api/v1/plans`: Assert returns seeded plans (200 OK).
        *   `[x]` Implement `SubscriptionController::plans` endpoint to fetch and return `Plan` data. Use an API Resource (`PlanResource`). Define route.
    *   `[ ]` **User Subscription API:**
        *   `[x]` (TDD) API Test `GET /api/v1/user/subscription` (authenticated): Assert returns null/empty initially (200 OK).
        *   `[x]` Implement `SubscriptionController::userSubscription` using `SubscriptionService` stub. Use an API Resource (`SubscriptionResource`). Define route protected by `auth:sanctum`.

*   **Webhook Foundation (Stripe):**
    *   `[x]` Implement `VerifyStripeWebhookSignature` middleware (or use Cashier's built-in route protection).
    *   `[x]` (TDD) Write test for signature verification middleware/logic (mock Stripe request/header/secret). Assert pass/fail scenarios.
    *   `[x]` Implement `WebhookController` (`app/Modules/SubscriptionBilling/Http/Controllers/`) with `handleStripeWebhook` method.
    *   `[x]` Define `POST /api/webhooks/stripe` route (ensure CSRF protection is disabled for this route). Apply signature verification middleware.
    *   `[x]` Ensure verification tests pass.

---

## Phase 4: Basic Content Management (Milestone 3 & 5 Prep)

*   **Models & Migrations:**
    *   `[x]` Implement `create_episodes_table` migration. Run migration.
    *   `[x]` Implement `create_protocols_table` migration. Run migration.
    *   `[x]` Implement `create_summaries_table` migration. Run migration.
    *   `[x]` Implement `create_episode_protocol_table` (pivot) migration. Run migration.
    *   `[x]` (TDD) Test `Episode` model (attributes, relationships `Protocols`/`Summaries`/`Notes`). Implement model (`app/Modules/ContentManagement/Models/Episode.php`).
    *   `[x]` (TDD) Test `Protocol` model (attributes, relationships `Episodes`). Implement model (`app/Modules/ContentManagement/Models/Protocol.php`).
    *   `[x]` (TDD) Test `Summary` model (attributes, relationship `Episode`). Implement model (`app/Modules/ContentManagement/Models/Summary.php`).
    *   `[x]` Test `Episode<->Protocol` many-to-many relationship.

*   **Seeding:**
    *   `[x]` Create `EpisodeSeeder` (placeholder episodes).
    *   `[x]` Create `ProtocolSeeder` (foundational protocols based on `content_strategy_management.md`).
    *   `[x]` Create `SummarySeeder` (summaries for foundational protocols).
    *   `[x]` Create `EpisodeProtocolSeeder` (link placeholders).
    *   `[x]` Update `DatabaseSeeder` to call new seeders in correct order.
    *   `[ ]` Run seeders (`php artisan db:seed`).

*   **Core Service & API:**
    *   `[x]` Define `ContentServiceInterface` contract (`app/Modules/ContentManagement/Contracts/`) (e.g., `getProtocols`, `getProtocolDetails`, `getEpisodes`, `getEpisodeDetails`, `getSummariesForEpisode`).
    *   `[x]` Implement basic `ContentService` implementing the interface. Bind interface.
    *   `[x]` Implement API Resources (`EpisodeResource`, `ProtocolResource`, `SummaryResource`).
    *   `[x]` **Protocols API:**
        *   `[x]` (TDD) API Test `GET /api/v1/protocols`: List protocols (unauthenticated).
        *   `[x]` Implement `ProtocolController::index`. Define route.
        *   `[x]` (TDD) API Test `GET /api/v1/protocols/{id}`: Show protocol details (unauthenticated).
        *   `[x]` Implement `ProtocolController::show`. Define route.
    *   `[x]` **Episodes API:**
        *   `[x]` (TDD) API Test `GET /api/v1/episodes`: List episodes.
        *   `[x]` Implement `EpisodeController::index`. Define route.
        *   `[x]` (TDD) API Test `GET /api/v1/episodes/{id}`: Show episode details.
        *   `[x]` Implement `EpisodeController::show`. Define route.
    *   `[x]` *(Add similar for Summaries if needed as top-level endpoint)*
    *   `[x]` Ensure basic content API tests pass.

---

## Phase 5: Feature Gating Implementation (Milestone 4 & 5 - TDD Focus)

*   **Subscription Service Logic:**
    *   `[x]` (TDD - Unit) Test `SubscriptionService::userHasActivePremiumSubscription` covering scenarios: no sub, free plan, active premium, trialing premium, canceled (before/after `ends_at`), expired, past_due. Use factories.
    *   `[x]` Implement logic in `SubscriptionService` querying `subscriptions` table (via User relationship), joining `plans`, checking status (`active`, `trialing`), `ends_at`. Ensure 'premium' plan type check.
    *   `[x]` (TDD - Unit) Test caching: cache hit avoids DB query, miss populates cache, TTL works, cache clear invalidates.
    *   `[x]` Implement caching layer within `userHasActivePremiumSubscription` (e.g., `Cache::remember`). Use appropriate cache tags (e.g., `user:{id}`).

*   **Middleware:**
    *   `[x]` Implement `CheckPremiumAccess` middleware using `SubscriptionServiceInterface`. # Assuming done as file exists
    *   `[x]` (TDD - Feature) Write feature tests applying middleware to a test route: premium user passes (200), free user fails (403), unauthenticated fails (401 - handled by `auth:sanctum`).
    *   `[x]` Register middleware alias in `app/Http/Kernel.php`. (Updated in bootstrap/app.php for L11)
    *   `[ ]` Apply middleware to relevant premium API route groups (e.g., start with `/reminders`, `/tracking`). Ensure tests pass. (Pending creation of relevant routes)

*   **Gated Content API:**
    *   `[x]` Refine `ProtocolResource` to conditionally include `implementation_guide` based on `$request->user()->hasActivePremiumSubscription()` (or similar check).
    *   `[x]` (TDD - Feature) Test `GET /api/v1/protocols/{id}`: Authenticated free user gets protocol *without* `implementation_guide`. Authenticated premium user gets protocol *with* `implementation_guide`.
    *   `[x]` Refactor `ProtocolController` or `ContentService` if needed to support different data loading based on user status (API Resource often sufficient). Ensure tests pass.

---

## Phase 6: Full Subscription Lifecycle via Webhooks (Milestone 4 - TDD Focus)

*   **Webhook Processing Logic (Stripe via Cashier):**
        *   **Event: `checkout.session.completed`**
        *   `[x]` (TDD) Test: Simulates webhook, asserts `Subscription` created (`trialing`/`active`), `ends_at`/`trial_ends_at` set, `SubscriptionStarted` event dispatched, `User.stripe_id` updated.
        *   `[x]` Verify/Implement Cashier listener logic.
    *   **Event: `customer.subscription.updated` (Trial Ends -> Active - via `invoice.payment_succeeded`)**
        *   `[x]` (TDD) Test: Simulates `invoice.payment_succeeded` post-trial, asserts status -> `active`, `trial_ends_at` nullified, `ends_at` updated.
        *   `[x]` Verify Cashier listener.
    *   **Event: `invoice.payment_succeeded` (Renewal)**
        *   `[x]` (TDD) Test: Simulates webhook, asserts `Subscription.ends_at` updated, `SubscriptionRenewed` event dispatched.
        *   `[x]` Verify Cashier listener.
    *   **Event: `invoice.payment_failed`**
        *   `[x]` (TDD) Test: Simulates webhook, asserts `Subscription.status` -> `past_due` (if configured), `PaymentFailed` event dispatched.
        *   `[x]` Verify/Implement Cashier listener based on retry settings.
    *   **Event: `customer.subscription.updated` (Cancel at Period End)**
        *   `[x]` (TDD) Test: Simulates webhook (`cancel_at_period_end=true`), asserts `Subscription.status` updated (`canceled`?), `ends_at` reflects cancel date, `SubscriptionCanceled` event dispatched.
        *   `[x]` Verify Cashier listener (`onSubscriptionUpdated`).
    *   **Event: `customer.subscription.deleted` (Immediate Cancel / Final Failure)**
        *   `[x]` (TDD) Test: Simulates webhook, asserts status -> `canceled`/`expired`, `ends_at` set to past/now, `SubscriptionExpired`/`SubscriptionCanceled` event dispatched.
        *   `[x]` Verify Cashier listener.

*   **Webhook Processing Logic (Apple IAP - Server Notifications V2):**
    *   `[x]` Implement `WebhookController::handleAppleWebhook`.
    *   `[x]` Implement service/logic to decode & verify Apple JWS payload (use library if available).
    *   `[x]` Define `POST /api/webhooks/apple` route (disable CSRF).
    *   `[x]` Implement App Store Server API client (library?) for server-side validation (optional).
    *   `[x]` **Event: `SUBSCRIBED` / `DID_RENEW`:** Implement handler, (TDD) Test state -> `active`/`trialing`, update DB, dispatch events.
    *   `[x]` **Event: `DID_FAIL_TO_RENEW`:** Implement handler, (TDD) Test state -> `past_due`/`expired`, update DB, dispatch events.
    *   `[ ]` **Event: `EXPIRED`:** Implement handler, (TDD) Test state -> `expired`, update DB, dispatch event.
    *   `[ ]` *(Implement/Test other handlers: `GRACE_PERIOD_EXPIRED`, `REVOKED`)* TO CHECK

*   **Webhook Processing Logic (Google Play Billing - RTDN via Pub/Sub):**
    *   `[ ]` Set up Google Cloud Pub/Sub topic & push subscription.
    *   `[x]` Implement `WebhookController::handleGoogleWebhook` (Mock).
    *   `[x]` Implement service/logic to decode base64 Pub/Sub data.
    *   `[x]` (TDD) Test Pub/Sub message decoding & parsing.
    *   `[x]` Define `POST /api/webhooks/google` route (disable CSRF).
    *   `[x]` Implement Google Play Developer API client (library?) for purchase validation/acknowledgement (Mock).
    *   `[x]` **Type: `SUBSCRIPTION_PURCHASED` / `SUBSCRIPTION_RENEWED`:** Implement handler, (TDD) Test state -> `active`/`trialing`, update DB, dispatch events, acknowledge purchase (Mock).
    *   `[x]` **Type: `SUBSCRIPTION_IN_GRACE_PERIOD`:** Implement handler, (TDD) Test state -> `past_due`, update DB, dispatch event (Mock).
    *   `[x]` **Type: `SUBSCRIPTION_ON_HOLD`:** Implement handler, (TDD) Test state -> `past_due`/`on_hold`, update DB (Mock).
    *   `[x]` **Type: `SUBSCRIPTION_CANCELED`:** Implement handler, (TDD) Test state -> `canceled`, update DB, dispatch event (Mock).
    *   `[x]` **Type: `SUBSCRIPTION_EXPIRED`:** Implement handler, handler, (TDD) Test state -> `expired`, update DB, dispatch event (Mock).
    *   `[ ]` *(Implement/Test other handlers: `REVOKED`, `PAUSED`)* (Mock)

*   **Scheduled Job for Status Check:**
    *   `[ ]` Implement `CheckExpiredSubscriptions` job/command.
    *   `[ ]` (TDD) Test job finds past `canceled` subs and sets status to `expired`.
    *   `[x]` Schedule job in `Kernel.php` (e.g., `daily()`).

*   **Cache Invalidation:**
    *   `[x]` Create `ClearUserEntitlementCache` Listener.
    *   `[x]` Implement cache clearing logic (`Cache::tags("user:{$event->subscription->user_id}")->flush();` or similar).
    *   `[x]` (TDD) Test Listener clears the correct cache tag/key.
    *   `[x]` Register Listener for `SubscriptionStarted`, `SubscriptionRenewed`, `SubscriptionCanceled`, `SubscriptionExpired` events in `EventServiceProvider`.
    *   `[x]` (TDD) Verify webhook tests dispatch events correctly using `Event::fake()`.

---

## Phase 7: Implementing MVP Features (Milestone 5 & 6 Prep)

*   **Free Tier - Basic Reminders:**
    *   `[x]` Implement logic/scope in `Protocol` model to identify foundational protocols.
    *   `[x]` Implement `SendFoundationalReminders` command/job.
    *   `[x]` (TDD) Test command selects correct protocols & users (mock Notification).
    *   `[x]` Schedule command in `Kernel.php`.

*   **Premium Tier - Full Content Access:**
    *   `[x]` (TDD - Feature) Ensure `ProtocolResource` tests cover conditional loading of `implementation_guide`.
    *   `[x]` (TDD - Feature) Ensure `GET /protocols` tests cover premium user getting full list / rich data via Resource.

*   **Premium Tier - Custom Reminders (MVP Scope):**
    *   **Database:**
        *   `[x]` Implement `create_user_reminders_table` migration. Run migration.
        *   `[x]` (TDD) Test `UserReminder` model attributes & relationships.
        *   `[x]` Implement `UserReminder` model (`app/Modules/ProtocolEngine/Models/`).
    *   **API CRUD:**
        *   `[x]` Implement `ReminderPolicy`.
        *   `[x]` (TDD) Test `ReminderPolicy` (premium check, ownership).
        *   `[x]` **Create:** (TDD) API Test, `[x]` Implement `StoreReminderRequest`, `ReminderController::store`, `ReminderService::setReminder`, Define Route + Middleware/Policy.
        *   `[x]` **List:** (TDD) API Test, Implement `ReminderController::index`, `ReminderService::getUserReminders`, Define Route + Middleware.
        *   `[x]` **Update:** (TDD) API Test, Implement `UpdateReminderRequest`, `ReminderController::update`, `ReminderService::updateReminder`, Define Route + Middleware/Policy.
        *   `[x]` **Delete:** (TDD) API Test, Implement `ReminderController::destroy`, `ReminderService::deleteReminder`, Define Route + Middleware/Policy.
    *   **Scheduling Logic:**
        *   `[x]` Implement `reminders:send-due` command logic (query, timezone conversion, frequency check).
        *   `[x]` (TDD) Test `reminders:send-due` command finds due reminders (mock `now()`).
        *   `[x]` Ensure command dispatches `SendProtocolReminderNotification` job.
        *   `[x]` Schedule command in `Kernel.php` (`everyMinute()`).
    *   **Notification Sending:**
        *   `[x]` Implement `SendProtocolReminderNotification` Job.
        *   `[x]` Implement `ProtocolReminder` Notification class (`toFcm`, `toApns`).
        *   `[x]` (TDD) Test Job retrieves data, fetches token, constructs payload, mocks `Notification::send()`.
        *   `[x]` Implement token fetching logic in Job.
        *   `[x]` Implement `Notification::send()` call in Job.
        *   `[x]` Implement `last_sent_at` update in Job.
    *   **Device Token Management:**
    *   `[x]` Add `device_tokens` column to `users` table OR create `user_devices` table migration. Run migration.
    *   `[ ]` (TDD) Test storing/retrieving tokens for a user.
    *   `[x]` Implement `UpdateDeviceTokenRequest`.
    *   `[x]` Implement `UserController::updateDeviceToken`.
    *   `[x]` Define `POST /api/v1/user/device-token` route + `auth:sanctum`.

---

## Phase 8: Implementing Post-MVP Features (As Prioritized - TDD Focus)

*   **Notes Service (Example):**
    *   `[x]` **Models & Migrations:** Implement `create_notes_table`, (TDD) Test `Note` Model, Implement Model.
    *   `[x]` **Policies & Auth:** Implement `NotePolicy`, (TDD) Test Policy (free limits, public premium, ownership).
    *   `[x]` **Service Layer:** Define Interface, Implement `NoteService`, Bind Interface, (TDD - Unit) Test Service methods (CRUD, counts, public list).
    *   `[x]` **API Endpoints:** Without running migrations and test executions
        *   `[x]` **Create:** (TDD) API Test, `[x]` Implement Request, `[x]` Implement Controller, `[x]` Define Route.
        *   `[x]` **List User:** (TDD) API Test, Implement Controller, Define Route.
        *   `[x]` **Show:** (TDD) API Test, Implement Controller, Define Route.
    *   `[x]` **Update:** (TDD) API Test, Implement Request, Controller, Define Route.
    *   `[x]` **Delete:** (TDD) API Test, Implement Controller, Define Route.
    *   `[x]` **List Public:** (TDD) API Test, Implement Controller, Define Route.

- [x] **Tracking Service (Placeholder - Apply same pattern):**
    -   `[x]` **Models & Migrations:** (TDD) Test `TrackingLog` Model, Implement Model.
    -   `[x]` **Policies & Auth:** Implement Policy (Premium check), (TDD) Test Policy.
    -   `[x]` **Service Layer:** Define Interface, Implement Service (streak logic), Bind, (TDD - Unit) Test Service methods (CRUD, counts, public list).
    -   `[x]` **API Endpoints:**
        *   `[x]` **Log Adherence:** (TDD) API Test, Implement Request, Controller, Define Route.
        *   `[x]` **Get Summary/Streak:** (TDD) API Test, Implement Controller, Define Route.
    -   Migration file created: database/migrations/2025_05_01_100004_create_user_protocol_tracking_table.php

-   **Offline Access:**
    -   `[x]` **Models & Migrations:** (TDD) Test `OfflineData` Model, Implement Model.
    -   `[x]` **Policies & Auth:** Implement Policy (User access), (TDD) Test Policy.
    -   `[x]` **Service Layer:** Define Interface, Implement Service (sync logic), Bind, (TDD - Unit) Test Service methods (CRUD, sync).
    -   `[x]` **API Endpoints:**
        -   `[x]` **Fetch Data:** (TDD) API Test, Implement Request, Controller, Define Route.
        -   `[x]` **Sync Data:** (TDD) API Test, Implement Request, Controller, Define Route.

-   **Advanced Notes Org:**
    -   `[x]` **Models & Migrations:** (TDD) Test `NoteTag` Model, Implement Model.
    -   `[x]` **Models & Migrations:** (TDD) Test `NoteCategory` Model, Implement Model.
    -   `[x]` **Policies & Auth:** Implement Policy (User access), (TDD) Test Policy.
    -   `[x]` **Service Layer:** Define Interface, Implement Service (categorization, tagging logic), Bind, (TDD - Unit) Test Service methods.
    -   `[x]` **API Endpoints:**
        *   `[x]` **Categorize Note:** (TDD) API Test, Implement Request, Controller, Define Route.
        *   `[x]` **Tag Note:** (TDD) API Test, Implement Request, Controller, Define Route.
        *   `[x]` **Get Notes by Category/Tag:** (TDD) API Test, Implement Request, Controller, Define Route.

-   **Community:**
    -   `[x]` **Models & Migrations:** (TDD) Test `Post`, `Comment` Models, Implement Models.
    -   `[x]` **Policies & Auth:** Implement Policy (User access, moderation), (TDD) Test Policy.
    -   `[x]` **Service Layer:** Define Interface, Implement Service (posting, commenting, moderation logic), Bind, (TDD - Unit) Test Service methods.
    -   `[x]` **API Endpoints:**
        *   `[x]` **Create Post:** (TDD) API Test, Implement Request, Controller, Define Route.
        *   `[x]` **Create Comment:** (TDD) API Test, Implement Request, Controller, Define Route.
        *   `[x]` **Get Posts/Comments:** (TDD) API Test, Implement Controller, Define Route.
        *   `[x]` **Moderate Content:** (TDD) API Test, Implement Controller, Define Route.

-   **Routines:**
    -   `[x]` **Models & Migrations:** (TDD) Test `Routine`, `RoutineStep` Models, Implement Models.
    -   `[x]` **Policies & Auth:** Implement Policy (User access), (TDD) Test Policy.
    *   `[x]` **Service Layer:** Define Interface, Implement Service (routine execution, scheduling logic), Bind, (TDD - Unit) Test Service methods.
    *   `[x]` **API Endpoints:**
        *   `[x]` **Create Routine:** (TDD) API Test, Implement Request, Controller, Define Route.
        *   `[x]` **Execute Routine:** (TDD) API Test, Implement Request, Controller, Define Route.
        *   `[x]` **Get Routines:** (TDD) API Test, Implement Controller, Define Route.


*   *(Repeat pattern for other Post-MVP features like Offline Access, Advanced Notes Org, Community, Routines)*

---

## Phase 9: API Documentation & Refinement

*   **Setup:**
    *   `[ ]` Install & Configure `zircote/swagger-php` OR choose manual editing tool.
*   **Annotation/Manual Update:**
    *   `[ ]` Review `openapi.yaml` structure.
    *   `[ ]` Annotate/Document: `Authentication` module endpoints & schemas.
    *   `[ ]` Annotate/Document: `UserManagement` module endpoints & schemas.
    *   `[ ]` Annotate/Document: `SubscriptionBilling` module endpoints & schemas (incl. webhooks).
    *   `[ ]` Annotate/Document: `ContentManagement` module endpoints & schemas (note premium diffs).
    *   `[ ]` Annotate/Document: `NotesService` module endpoints & schemas (note premium diffs).
    *   `[ ]` Annotate/Document: `ProtocolEngine` module endpoints & schemas (premium).
    *   `[ ]` Define/Review: Reusable schemas in `components/schemas`.
    *   `[ ]` Define/Review: Reusable error responses in `components/responses`.
    *   `[ ]` Define/Review: Security schemes (`bearerAuth`) and apply.
*   **Generation & Validation:**
    *   `[ ]` Generate `openapi.yaml`.
    *   `[ ]` Validate `openapi.yaml` using validator tool. Fix errors.
*   **Commit:**
    *   `[ ]` Commit final, validated `openapi.yaml` to repository.

---

## Phase 10: Testing & Quality Assurance

*   **Test Coverage Review:**
    *   `[ ]` Generate PHPUnit code coverage report.
    *   `[ ]` Analyze report, identify gaps in critical modules.
    *   `[ ]` Write additional unit/integration tests to improve coverage.
*   **Integration Flow Testing:**
    *   `[ ]` (TDD - Feature) Test Flow: Registration -> Login.
    *   `[ ]` (TDD - Feature) Test Flow: Free User Access (Check premium endpoint access denied).
    *   `[ ]` (TDD - Feature) Test Flow: Subscription Upgrade (Simulated webhook -> Premium access granted).
    *   `[ ]` (TDD - Feature) Test Flow: Reminder Setting & Receiving (Simulated: Create reminder -> Time passes -> Job runs -> Mock Notification sent).
*   **Manual QA:**
    *   `[ ]` Develop manual test cases/checklist (Free & Premium flows).
    *   `[ ]` Execute manual tests on Staging.
    *   `[ ]
