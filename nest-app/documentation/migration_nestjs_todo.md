Okay, this is an excellent request. Based on my previous observations, here's a refined and more detailed TODO list (`refinement_todo.md`) to guide the full migration from Laravel to the NestJS stack. This plan aims to be comprehensive, addressing the gaps and complexities previously identified.

**Legend:**
*   `[ ]` - To Do
*   `(DB)` - Database-related task
*   `(AUTH)` - Authentication-related task
*   `(API)` - API Endpoint/Controller task
*   `(SVC)` - Service/Business Logic task
*   `(GUARD)` - Guard/Policy/Authorization task
*   `(EVENT)` - Event/Listener task
*   `(JOB)` - Job/Queue task
*   `(NOTIF)` - Notification task
*   `(SETUP)` - Project configuration or setup task
*   `(DOC)` - API Documentation task
*   **_**(User Action)**_** - Task to be performed manually by the user (e.g., running CLI commands).
*   **(LLM Prompt):** - Instruction/prompt for the AI to generate code/content.

---

**`refinement_todo.md`**

# Refined Migration Plan: Laravel to Nest.js, Prisma, Supabase

**Project Goal:** To generate the complete source code and configuration for migrating the existing Laravel backend to a Nest.js backend within the `./nest-app` directory, achieving feature parity.

**Workflow:**
1.  **User (You):** Execute any command-line actions or manual setup marked as **_**(User Action)**_**.
2.  **LLM (AI Model):** Generate only the code or file content for each step when prompted via **(LLM Prompt):**.

---

## Phase 0: Project Foundation & Environment Setup (Verification & Completion)

*   `[x]` **(SETUP)** **Initialize Nest.js Project in `nest-app` Directory (User Action)**
*   `[x]` **(SETUP)** **Supabase Project Setup & Credentials (User Action)**
*   `[x]` **(SETUP)** **Environment Configuration (`nest-app/.env`) (LLM Prompt & User Action)**
*   `[x]` **(SETUP)** **Prisma Integration (Installation & Init) (User Action)**
*   `[x]` **(SETUP)** **Prisma Schema Datasource & Generator Config (LLM Prompt)**
*   `[x]` **(SETUP)** **Core NestJS Modules, Services, Controllers (User Action: CLI Generation)**
    *   Ensure all required modules from `nest-app/documentation/migration_nestjs_todo.md` (Phase 0) are generated: `common`, `authentication`, `user-management`, `subscription-billing`, `content-management`, `notes-service`, `protocol-engine`, `tracking-service`.
*   `[x]` **(SETUP)** **Implement `PrismaService` (`nest-app/src/common/prisma/prisma.service.ts`) (LLM Prompt)**

---

## Phase 1: User Authentication with Supabase (Verification & Completion)

*   `[x]` **(AUTH)** **Install Supabase Client Library (User Action)**
*   `[x]` **(AUTH)** **Implement `SupabaseService` (`nest-app/src/common/supabase/supabase.service.ts`) (LLM Prompt)**
*   `[x]` **(DB)** **SQL User Sync Trigger (`supabase/init.sql`) (LLM Prompt & User Action in Supabase Studio)**
*   `[x]` **(API)** **Implement `AuthenticationController` & `AuthenticationService` (LLM Prompt)**
*   `[x]` **(GUARD)`Implement `SupabaseAuthGuard` (`nest-app/src/authentication/guards/supabase-auth.guard.ts`) (LLM Prompt)**

---






## Phase 2: Comprehensive Database Schema Migration to Prisma

*   **Goal:** Ensure `nest-app/prisma/schema.prisma` fully reflects the entire Laravel database structure.
*   For *each* Laravel migration and corresponding Eloquent model:
    *   `[x]` **(DB) User Model:**
        *   **(LLM Prompt):** "Refine the existing `User` model in `nest-app/prisma/schema.prisma`. Ensure it includes all fields from the Laravel `User` model (app/Modules/UserManagement/Models/User.php) and its migration (`2025_04_24_061659_create_users_table.php`), including `profile_picture_url` and `deleted_at` for soft deletes. Define relationships for `devices`, `subscriptions`, `notes`, `reminders`, and `trackingLogs`."
    *   `[x]` **(DB) Plan Model:**
        *   **(LLM Prompt):** "Refine the existing `Plan` model in `nest-app/prisma/schema.prisma` based on Laravel migration `2025_04_24_205454_create_plans_table.php` and model `app/Modules/SubscriptionBilling/Models/Plan.php`. Ensure all fields like `slug`, `price`, `interval`, `intervalCount`, `trialPeriodDays`, `isActive` are present. Define the `PlanInterval` enum. Add the `subscriptions` relation."
    *   `[x]` **(DB) Subscription Model:**
        *   **(LLM Prompt):** "Refine the existing `Subscription` model in `nest-app/prisma/schema.prisma` based on Laravel migration `2025_04_24_205510_create_subscriptions_table.php` and model `app/Modules/SubscriptionBilling/Models/Subscription.php`. Include all fields: `userId`, `planId`, `name`, `stripeId`, `stripeStatus`, `stripePrice`, `quantity`, `trialEndsAt`, `endsAt`. Define relations to `User` and `Plan`."
    *   `[x]` **(DB) Episode Model:**
        *   **(LLM Prompt):** "Refine the existing `Episode` model in `nest-app/prisma/schema.prisma` based on Laravel migration `2025_04_24_211846_create_episodes_table.php` and model `app/Modules/ContentManagement/Models/Episode.php`. Include `title`, `slug`, `description`, `content`, `duration`, `publishedAt`. Add relation for `EpisodeProtocol`."
    *   `[x]` **(DB) Protocol Model:**
        *   **(LLM Prompt):** "Refine the existing `Protocol` model in `nest-app/prisma/schema.prisma` based on Laravel migrations `2025_04_24_211928_create_protocols_table.php`, `2025_04_24_195725_add_is_free_to_protocols_table.php` and model `app/Modules/ContentManagement/Models/Protocol.php`. Include `title`, `slug`, `description`, `implementation_guide`, `category`, and `is_free`. Add relation for `EpisodeProtocol` and `UserReminder` and `TrackingLog`."
    *   `[x]` **(DB) EpisodeProtocol (Pivot) Model:**
        *   **(LLM Prompt):** "Ensure the `EpisodeProtocol` model in `nest-app/prisma/schema.prisma` correctly defines the many-to-many relationship between `Episode` and `Protocol` with a composite ID, based on Laravel migration `2025_04_24_212153_create_episode_protocol_table.php`."
    *   `[x]` **(DB) Summary Model:**
        *   **(LLM Prompt):** "Generate the Prisma model for the `Summary` table and add it to `nest-app/prisma/schema.prisma`, based on Laravel migration `2025_04_24_212127_create_summaries_table.php` and model `app/Modules/ContentManagement/Models/Summary.php`. Include `episode_id`, `content`, and relation to `Episode`."
    *   `[x]` **(DB) Note Model:**
        *   **(LLM Prompt):** "Refine the existing `Note` model in `nest-app/prisma/schema.prisma` based on Laravel migrations `2025_04_30_074905_create_notes_table.php`, `2025_04_30_110629_add_is_public_to_notes_table.php` and model `app/Modules/NotesService/Models/Note.php`. Include `userId`, `title`, `content`, `isPublic`. Add relations to `User` and potentially `Episode`, `NoteCategory`, `NoteTag`."
    *   `[x]` **(DB) NoteCategory Model:**
        *   **(LLM Prompt):** "Generate the Prisma model for `NoteCategory` and add to `nest-app/prisma/schema.prisma`, based on Laravel migration `2025_05_01_100000_create_note_categories_table.php` and model `app/Modules/NotesService/Models/NoteCategory.php`. Include `name`, `description`, `color`. Add relation for pivot table to `Note`."
    *   `[x]` **(DB) NoteTag Model:**
        *   **(LLM Prompt):** "Generate the Prisma model for `NoteTag` and add to `nest-app/prisma/schema.prisma`, based on Laravel migration `2025_05_01_100001_create_note_tags_table.php` and model `app/Models/NoteTag.php` (or `app/Modules/NotesService/Models/NoteTag.php`). Include `name`, `color`. Add relation for pivot table to `Note`."
    *   `[x]` **(DB) NoteCategoryPivot Model (Explicit or Implicit):**
        *   **(LLM Prompt):** "Define the many-to-many relationship between `Note` and `NoteCategory` in `nest-app/prisma/schema.prisma` using an explicit pivot table model named `NoteCategoryPivot` (or let Prisma handle it implicitly), based on Laravel migration `2025_05_01_100002_create_note_category_pivot_table.php`."
    *   `[x]` **(DB) NoteTagPivot Model (Explicit or Implicit):**
        *   **(LLM Prompt):** "Define the many-to-many relationship between `Note` and `NoteTag` in `nest-app/prisma/schema.prisma` using an explicit pivot table model named `NoteTagPivot` (or let Prisma handle it implicitly), based on Laravel migration `2025_05_01_100003_create_note_tag_pivot_table.php`."
    *   `[x]` **(DB) UserReminder Model:**
        *   **(LLM Prompt):** "Refine the existing `UserReminder` model in `nest-app/prisma/schema.prisma` based on Laravel migration `2025_04_30_071413_create_user_reminders_table.php` and model `app/Modules/ProtocolEngine/Models/UserReminder.php`. Include `userId`, `protocolId`, `reminder_time` (String, as in DTOs), `frequency` (String or Enum), `specific_days` (String[] or Json), `message`, `is_active`, `last_sent_at`. Define relations to `User` and `Protocol`."
    *   `[x]` **(DB) Routine Model:**
        *   **(LLM Prompt):** "Generate the Prisma model for `Routine` and add to `nest-app/prisma/schema.prisma`, based on Laravel migration `2025_05_01_000000_create_routines_table.php` and model `app/Models/Routine.php`. Include `user_id`, `name`, `description`, `frequency`, `start_time`, `end_time`, `is_active`. Define relations."
    *   `[x]` **(DB) RoutineStep Model:**
        *   **(LLM Prompt):** "Generate the Prisma model for `RoutineStep` and add to `nest-app/prisma/schema.prisma`, based on Laravel migration `2025_05_01_000001_create_routine_steps_table.php` and model `app/Models/RoutineStep.php`. Include `routine_id`, `name`, `description`, `duration`, `order`, `is_optional`. Define relations."
    *   `[x]` **(DB) TrackingLog Model (`user_protocol_tracking`):**
        *   **(LLM Prompt):** "Refine the existing `TrackingLog` model in `nest-app/prisma/schema.prisma` based on Laravel migration `2025_05_01_100004_create_user_protocol_tracking_table.php` and model `app/Modules/TrackingService/Models/TrackingLog.php`. Include `user_id`, `protocol_id`, `tracked_at` (DateTime), `notes` (String?), `metadata` (Json?). Define relations."
    *   `[x]` **(DB) Post Model:**
        *   **(LLM Prompt):** "Generate the Prisma model for `Post` and add to `nest-app/prisma/schema.prisma`, based on Laravel migration `2025_05_01_100004_create_posts_table.php` and model `app/Models/Post.php`. Include `user_id`, `title`, `content`, `status`. Define relations."
    *   `[x]` **(DB) Comment Model:**
        *   **(LLM Prompt):** "Generate the Prisma model for `Comment` and add to `nest-app/prisma/schema.prisma`, based on Laravel migration `2025_05_01_100005_create_comments_table.php` and model `app/Models/Comment.php`. Include `user_id`, `post_id`, `content`. Define relations."
    *   `[x]` **(DB) UserDevice Model:** (Already covered and seems fine)
        *   **(LLM Prompt):** "Refine the existing `UserDevice` model in `nest-app/prisma/schema.prisma` based on Laravel migration `2025_04_30_104700_create_user_devices_table.php` and model `app/Modules/UserManagement/Models/UserDevice.php`. Ensure it includes `userId`, `device_token`, `platform`.




Okay, you're right. "Review all" is too broad for an LLM that generates code based on specific instructions. Let's break down the **"(DB) Prisma Enums"** and the subsequent **"(DB) Apply Prisma Migrations"** steps into a much more granular, actionable list for your LLM.

We will go field by field for potential enums identified from the Laravel codebase.

---

## Phase 2 (Continued): Comprehensive Database Schema Migration to Prisma - Detailed Enum Implementation

**Previous Step Context:** You have already prompted the LLM to generate the basic Prisma models for most tables. Now we refine them with enums.

**Action Item:** Update the `nest-app/prisma/schema.prisma` file by adding the following Enum definitions first, and then prompting the LLM to modify existing model fields to use these enums.

---

**2.A. Define All Necessary Enums in `nest-app/prisma/schema.prisma`**

*   `[x]` **(DB) `PlanInterval` Enum (Already Done & Verified)**
    *   **Context:** This enum is already present in your `nest-app/prisma/schema.prisma` based on Laravel migration `2025_04_24_205454_create_plans_table.php`.
    *   **No LLM action needed if it's exactly:**
        ```prisma
        enum PlanInterval {
          MONTH
          YEAR
        }
        ```

*   `[ ]` **(DB) `SubscriptionStatus` Enum:**
    *   **Context:** Based on Laravel's `subscriptions.stripe_status` field and common subscription states seen in models like `app/Modules/SubscriptionBilling/Models/Subscription.php` (e.g., 'active', 'trialing', 'canceled', 'expired', 'past_due').
    *   **(LLM Prompt):** "In `nest-app/prisma/schema.prisma`, define a new enum named `SubscriptionStatus` with the following values: `ACTIVE`, `TRIALING`, `CANCELED`, `EXPIRED`, `PAST_DUE`, `INCOMPLETE`."
    *   **Expected LLM Output (to be added to schema.prisma):**
        ```prisma
        enum SubscriptionStatus {
          ACTIVE
          TRIALING
          CANCELED
          EXPIRED
          PAST_DUE
          INCOMPLETE // Added for completeness, often an initial state
        }
        ```

*   `[ ]` **(DB) `RoutineFrequency` Enum:**
    *   **Context:** Based on Laravel's `routines.frequency` field and values like 'daily', 'weekly', 'weekdays', 'custom' from `app/Http/Requests/StoreRoutineRequest.php`.
    *   **(LLM Prompt):** "In `nest-app/prisma/schema.prisma`, define a new enum named `RoutineFrequency` with the following values: `DAILY`, `WEEKLY`, `WEEKDAYS`, `CUSTOM`."
    *   **Expected LLM Output (to be added to schema.prisma):**
        ```prisma
        enum RoutineFrequency {
          DAILY
          WEEKLY
          WEEKDAYS
          CUSTOM
        }
        ```

*   `[ ]` **(DB) `PostStatus` Enum:**
    *   **Context:** Based on Laravel's `posts.status` field, which defaults to 'published'. Common statuses might include 'DRAFT', 'ARCHIVED'.
    *   **(LLM Prompt):** "In `nest-app/prisma/schema.prisma`, define a new enum named `PostStatus` with the following values: `PUBLISHED`, `DRAFT`, `ARCHIVED`."
    *   **Expected LLM Output (to be added to schema.prisma):**
        ```prisma
        enum PostStatus {
          PUBLISHED
          DRAFT
          ARCHIVED
        }
        ```

*   `[ ]` **(DB) `DevicePlatform` Enum:**
    *   **Context:** Based on Laravel's `user_devices.platform` field and validation rule `Rule::in(['ios', 'android', 'web'])` from `app/Modules/UserManagement/Http/Requests/UpdateDeviceTokenRequest.php`.
    *   **(LLM Prompt):** "In `nest-app/prisma/schema.prisma`, define a new enum named `DevicePlatform` with the following values: `IOS`, `ANDROID`, `WEB`."
    *   **Expected LLM Output (to be added to schema.prisma):**
        ```prisma
        enum DevicePlatform {
          IOS
          ANDROID
          WEB
        }
        ```

---

**2.B. Update Prisma Model Fields to Use Defined Enums**

*   **_**(User Action)**_** Ensure all enum definitions from step 2.A are present at the top level (outside any model) in your `nest-app/prisma/schema.prisma` file.

*   `[x]` **(DB) `Plan` Model - `interval` field (Already Done & Verified)**
    *   **Context:** The `Plan` model in `nest-app/prisma/schema.prisma` already correctly uses `PlanInterval`.
    *   **No LLM action needed if it's exactly:**
        ```prisma
        model Plan {
          // ... other fields
          interval         PlanInterval @default(MONTH)
          // ... other fields
        }
        ```

*   `[ ]` **(DB) `Subscription` Model - `stripeStatus` field:**
    *   **Context:** Currently `stripeStatus String`. Needs to use `SubscriptionStatus` enum.
    *   **(LLM Prompt):** "In `nest-app/prisma/schema.prisma`, modify the `Subscription` model. Change the type of the `stripeStatus` field from `String` to the `SubscriptionStatus` enum. Add a default value if appropriate (e.g., `@default(INCOMPLETE)`)."
    *   **Expected LLM Change in `Subscription` model:**
        ```diff
        model Subscription {
          // ... other fields
        -  stripeStatus String
        +  stripeStatus SubscriptionStatus @default(INCOMPLETE) // Or another appropriate default
          // ... other fields
        }
        ```

*   `[ ]` **(DB) `Routine` Model (if generated/to be generated) - `frequency` field:**
    *   **Context:** Assuming the `Routine` model will be generated (it's currently missing from `schema.prisma`). If it exists and `frequency` is `String`, it needs update.
    *   **(LLM Prompt - if Routine model exists):** "In `nest-app/prisma/schema.prisma`, modify the `Routine` model. Change the type of the `frequency` field from `String` to the `RoutineFrequency` enum."
    *   **(LLM Prompt - if Routine model needs generation, include this):** "When generating the `Routine` model for `nest-app/prisma/schema.prisma` based on Laravel migration `2025_05_01_000000_create_routines_table.php`, ensure the `frequency` field uses the `RoutineFrequency` enum."
    *   **Expected field definition in `Routine` model:**
        ```prisma
        // In Routine model
        frequency RoutineFrequency
        ```

*   `[ ]` **(DB) `Post` Model (if generated/to be generated) - `status` field:**
    *   **Context:** Assuming the `Post` model will be generated.
    *   **(LLM Prompt - if Post model exists):** "In `nest-app/prisma/schema.prisma`, modify the `Post` model. Change the type of the `status` field from `String` to the `PostStatus` enum. Set its default value to `PUBLISHED` (e.g., `@default(PUBLISHED)`)."
    *   **(LLM Prompt - if Post model needs generation, include this):** "When generating the `Post` model for `nest-app/prisma/schema.prisma` based on Laravel migration `2025_05_01_100004_create_posts_table.php`, ensure the `status` field uses the `PostStatus` enum with a default of `PUBLISHED`."
    *   **Expected field definition in `Post` model:**
        ```prisma
        // In Post model
        status PostStatus @default(PUBLISHED)
        ```

*   `[ ]` **(DB) `UserDevice` Model - `platform` field:**
    *   **Context:** Currently `platform String?` in Laravel migration. Needs to use `DevicePlatform` enum.
    *   **(LLM Prompt):** "In `nest-app/prisma/schema.prisma`, modify the `UserDevice` model. Change the type of the `platform` field from `String?` to `DevicePlatform?` (still optional)."
    *   **Expected LLM Change in `UserDevice` model:**
        ```diff
        model UserDevice {
          // ... other fields
          userId   String
          user     User     @relation(fields: [userId], references: [id])
        - platform String?  // Example: If it was previously generated as String
        + platform DevicePlatform?
          // ... other fields if any, like device_token
        + deviceToken String // Assuming this should be here based on Laravel migration
        }
        ```
        *(Self-correction: Noticed `deviceToken` was missing in the prompt context above, adding it here for completeness if the LLM hasn't added it yet for `UserDevice`)*

---

**2.C. Apply Prisma Migrations and Generate Client**

*   `[ ]` **(DB) Review Prisma Schema (User Action):**
    *   **_**(User Action)**_** Carefully review the *entire* `nest-app/prisma/schema.prisma` file.
        *   Verify all enums from step 2.A are defined correctly.
        *   Verify all model fields from step 2.B now use the correct enum types and have appropriate default values if specified.
        *   Verify all other models (Summary, NoteCategory, NoteTag, Routine, RoutineStep, Post, Comment, etc.) have been generated or refined correctly with all their fields and relations from the earlier general prompts.
        *   **Pay close attention to optional fields (`?`) and list fields (`[]`) to ensure they match Laravel's nullability and data structures (e.g., `specific_days String[]?` for UserReminder).**

*   `[ ]` **(DB) Create and Apply Prisma Migration (User Action):**
    *   **_**(User Action)**_** Once the schema looks accurate and complete:
        ```bash
        npx prisma migrate dev --schema=./nest-app/prisma/schema.prisma --name "add-enums-and-refine-schema"
        ```
        *(You can choose a more descriptive name like "complete-laravel-schema-migration-with-enums" if this step also includes the previously missing models.)*
    *   **_**(User Action)**_** Prisma will show you the SQL it's about to execute. Review this SQL to ensure it matches your expectations (e.g., `ALTER TABLE ... ALTER COLUMN ... TYPE ... USING ...::enum_name;` for changing types to enums, `CREATE TYPE ... AS ENUM (...)`). If it looks good, confirm the migration.

*   `[ ]` **(DB) Generate Prisma Client (User Action):**
    *   **_**(User Action)**_** After the migration is successfully applied:
        ```bash
        npx prisma generate --schema=./nest-app/prisma/schema.prisma
        ```
        This updates the Prisma Client in `nest-app/src/generated/prisma` to include the new enums and any other schema changes.

---

This detailed breakdown should give your LLM very specific, manageable chunks to work with for the enum part of the schema migration. Remember to integrate the LLM's output carefully into your `schema.prisma` file after each prompt related to enums and model field updates, before running the final `prisma migrate dev` command.

*   `[x]` **(DB) Expand Seed Script (`nest-app/prisma/seed.ts`):**
    *   **(LLM Prompt):** "Translate Laravel's `EpisodeSeeder.php` [paste content] into TypeScript code to be added to `nest-app/prisma/seed.ts` for seeding Episode data using Prisma client."
    *   **(LLM Prompt):** "Translate Laravel's `ProtocolSeeder.php` [paste content]..."
    *   **(LLM Prompt):** "Translate Laravel's `SummarySeeder.php` [paste content]..."
    *   **(LLM Prompt):** "Translate Laravel's `EpisodeProtocolSeeder.php` [paste content]..."
    *   **(LLM Prompt):** "Translate Laravel's `NoteTagFactory.php` and associated seeding logic (if any) ..."
    *   **(LLM Prompt):** "Translate Laravel's `UserReminderFactory.php` and associated seeding logic (if any) ..."
    *   *(Repeat for all relevant Laravel Seeders/Factories: `OfflineDataFactory`, `RoutineFactory`, `RoutineStepFactory` etc.)*
    *   **_**(User Action)**_** Run the full seeder:
        ```bash
        npx prisma db seed --schema=./nest-app/prisma/schema.prisma
        ```

---






## Phase 3: Business Logic and Core Services Migration

*   For *each* Laravel Service (`ContentService`, `NoteService`, `ReminderService`, `SubscriptionService`, `TrackingService`, `OfflineDataService`, `PostService`, `RoutineService`, etc.):
    *   `[ ]` **(SVC)** **Migrate Service Logic:**
        *   **(LLM Prompt):** "Here is the Laravel service `app/Modules/Xyz/Services/XyzService.php`: [paste content]. Migrate its business logic to the NestJS service `nest-app/src/xyz/xyz.service.ts` [paste existing stub if any].
            *   Replace Eloquent queries with Prisma Client queries.
            *   Adapt method signatures to accept an authenticated user object (e.g., `user: Request['user']` or a specific User type from Supabase/Prisma) where `Auth::id()` or `auth()->user()` was used.
            *   Ensure all public methods from the Laravel service (and its interface) are implemented.
            *   Throw appropriate NestJS exceptions (e.g., `NotFoundException`, `ForbiddenException`, `BadRequestException`) instead of Laravel exceptions or `response()->json(...)`."
        *   **_**(User Action)**_** Review and integrate the generated service code. Manually resolve any complex logic or type issues.
*   `[ ]` **(SVC) Subscription Service - Specifics:**
    *   **(LLM Prompt):** "Refine the `userHasActivePremiumSubscription` method in `nest-app/src/subscription-billing/subscription-billing.service.ts`. It should replicate the logic from Laravel's `SubscriptionServiceInterface` and `SubscriptionService.php` [paste relevant Laravel code], checking for active/trialing status against 'Premium%' plan names using Prisma."
*   `[ ]` **(SVC) Remove Placeholder User Logic:**
    *   **_**(User Action)**_** Manually search for and remove all instances of `const user = { id: 1 };` (or similar) in generated NestJS services. Ensure methods that require user context receive it as a parameter.

---

## Phase 4: API Endpoints, DTOs, and Controllers Migration

*   For *each* Laravel Controller and its associated Form Requests:
    *   `[ ]` **(API) Generate DTOs:**
        *   **(LLM Prompt):** "Here is the Laravel Form Request `app/Http/Requests/StoreXyzRequest.php`: [paste content]. Generate an equivalent NestJS DTO class named `store-xyz.dto.ts` in the appropriate `nest-app/src/module-name/dto/` directory. Use `class-validator` decorators for validation rules derived from the Laravel request."
    *   `[ ]` **(API) Migrate Controller Logic:**
        *   **(LLM Prompt):** "Here is the Laravel Controller `app/Modules/Xyz/Http/Controllers/XyzController.php`: [paste content]. Migrate its methods to the NestJS Controller `nest-app/src/xyz/xyz.controller.ts` [paste existing stub if any].
            *   Inject and use the corresponding NestJS service (e.g., `XyzService`).
            *   Use the generated DTOs for request body validation (`@Body() createXyzDto: CreateXyzDto`).
            *   Apply `SupabaseAuthGuard` to routes requiring authentication.
            *   Apply `PremiumGuard` (or other custom guards) to routes requiring premium access or specific permissions.
            *   Ensure route parameters (`@Param()`) and query parameters (`@Query()`) are correctly handled.
            *   Return appropriate data or NestJS `HttpCode` responses."
        *   **_**(User Action)**_** Review and integrate. Ensure all routes from the Laravel module's `api.php` are mapped.

---

## Phase 5: Guards and Authorization Logic Migration

*   `[ ]` **(GUARD) PremiumGuard Refinement:**
    *   **(LLM Prompt):** "Review the existing `PremiumGuard` (`nest-app/src/common/guards/premium.guard.ts`). If Laravel's `CheckPremiumAccess` middleware or `SubscriptionServiceInterface->userHasActivePremiumSubscription` contains more complex logic than a simple plan check, update `PremiumGuard` to inject and use `SubscriptionBillingService` to replicate that logic."
*   For *each* Laravel Policy (e.g., `NotePolicy`, `ReminderPolicy`, `TrackingLogPolicy`, `RoutinePolicy`, `PostPolicy`, etc.):
    *   `[ ]` **(GUARD) Migrate Policy Logic:**
        *   **(LLM Prompt):** "Analyze Laravel's `app/Policies/XyzPolicy.php`: [paste content]. Translate its authorization logic (e.g., `viewAny`, `view`, `create`, `update`, `delete`) into NestJS. This may involve:
            1.  Adding checks within the corresponding `xyz.service.ts` methods (e.g., checking ownership `if (log.userId !== user.id) throw new ForbiddenException();`).
            2.  If complex route-level checks are needed beyond `SupabaseAuthGuard` and `PremiumGuard` (e.g., resource ownership that can't be easily checked in the service before fetching), generate a new NestJS Guard (e.g., `XyzOwnerGuard.ts`) and apply it to relevant controller methods.
            3.  Ensure `ForbiddenException` or `NotFoundException` are thrown for failed authorization."

---

## Phase 6: Webhook Logic Migration

*   **Stripe Webhooks:**
    *   For each Stripe event type handled in Laravel's `WebhookController` (e.g., `checkout.session.completed`, `invoice.payment_succeeded`, `customer.subscription.updated`, `invoice.payment_failed`, `customer.subscription.deleted`):
        *   `[ ]` **(SVC) Migrate Stripe Event Handler:**
            *   **(LLM Prompt):** "Migrate the Stripe webhook handling logic for the `{EVENT_TYPE}` event from Laravel's `app/Modules/SubscriptionBilling/Http/Controllers/WebhookController.php` [paste relevant Laravel method(s) and any helper methods it uses] to a new method within `nest-app/src/subscription-billing/subscription-billing.service.ts`. This method should accept the Stripe event payload. Use Prisma Client for database operations (e.g., finding users by Stripe customer ID, creating/updating subscriptions). If Laravel dispatched events, prepare to dispatch equivalent NestJS events (to be implemented in Phase 7)."
    *   `[ ]` **(API) Stripe Webhook Controller Endpoint:**
        *   **(LLM Prompt):** "Implement the `handleStripeWebhook` method in `nest-app/src/subscription-billing/subscription-billing.controller.ts`. It should:
            1.  Verify the Stripe webhook signature (you might need a utility or to adapt Laravel's middleware logic).
            2.  Call the appropriate method in `SubscriptionBillingService` based on `event.type`.
            3.  Return a 200 OK response to Stripe."
*   **Apple App Store Server Notifications (V2):**
    *   `[ ]` **(SVC) JWS Verification & Processing:**
        *   **(LLM Prompt):** "Review the `AppleSubscriptionService.php` from Laravel [paste content]. Implement similar JWS decoding and verification logic within `nest-app/src/subscription-billing/subscription-billing.service.ts` (or a new `apple.service.ts`). Use a suitable JWT library for Node.js/NestJS (e.g., `jsonwebtoken` or NestJS's `@nestjs/jwt`). Include logic for fetching Apple's public keys."
    *   For each Apple notification type handled (e.g., `SUBSCRIBED`, `DID_RENEW`, `DID_FAIL_TO_RENEW`, `EXPIRED`, `DID_CHANGE_RENEWAL_STATUS`):
        *   `[ ]` **(SVC) Migrate Apple Notification Handler:**
            *   **(LLM Prompt):** "Migrate the Apple notification handling logic for the `{NOTIFICATION_TYPE}` event from Laravel [paste relevant Laravel method(s)] to `nest-app/src/subscription-billing/subscription-billing.service.ts`. Use the JWS verification output. Use Prisma for database operations. Prepare for NestJS event dispatches."
    *   `[ ]` **(API) Apple Webhook Controller Endpoint:**
        *   **(LLM Prompt):** "Implement an `handleAppleWebhook` method in `nest-app/src/subscription-billing/subscription-billing.controller.ts`. It should call the JWS verification and processing logic in the service and then route to specific handlers based on notification type."
*   **Google Play Billing Notifications (RTDN):**
    *   (Similar detailed steps as Apple, focusing on Pub/Sub message decoding, purchase token validation with Google Play Developer API, and handling specific notification types like `SUBSCRIPTION_PURCHASED`, `SUBSCRIPTION_RENEWED`, `SUBSCRIPTION_CANCELED`, `SUBSCRIPTION_EXPIRED`.)

---

## Phase 7: Event, Job, and Notification System Migration

*   **Events & Listeners:**
    *   **_**(User Action)**_** Install NestJS event emitter: `cd nest-app && npm install @nestjs/event-emitter && cd ..`
    *   **_**(User Action)**_** Import and register `EventEmitterModule.forRoot()` in `nest-app/src/app.module.ts`.
    *   For each Laravel Event/Listener pair (e.g., `SubscriptionRenewed` / `ClearUserEntitlementCache`):
        *   `[ ]` **(EVENT) Define NestJS Event Class/Interface:**
            *   **(LLM Prompt):** "Define a simple class or interface for a NestJS event equivalent to Laravel's `SubscriptionRenewed` event [paste Laravel event class if it has properties]."
        *   `[ ]` **(EVENT) Implement NestJS Listener:**
            *   **(LLM Prompt):** "Translate the Laravel listener `app/Listeners/ClearUserEntitlementCache.php` [paste content] into a NestJS listener class using `@OnEvent('event.name')` from `@nestjs/event-emitter`. It should handle the NestJS event defined above. Implement cache clearing logic appropriate for NestJS (e.g., using NestJS Caching module if adopted, or a custom cache service)."
        *   `[ ]` **(SVC) Dispatch NestJS Events:**
            *   **(LLM Prompt):** "In the NestJS services where the original Laravel code dispatched an event (e.g., `SubscriptionBillingService` after a renewal), inject `EventEmitter2` and replace `Event::dispatch(...)` with `this.eventEmitter.emit('event.name', new EventPayload(...));`."
*   **Queued Jobs:**
    *   **_**(User Action)**_** Install BullMQ and its NestJS integration: `cd nest-app && npm install @nestjs/bullmq bullmq && cd ..`
    *   **_**(User Action)**_** Configure BullModule in `app.module.ts` and relevant feature modules (e.g., `ProtocolEngineModule` for reminder jobs).
    *   For each Laravel Queued Job (e.g., `SendProtocolReminderNotification`):
        *   `[ ]` **(JOB) Create BullMQ Processor:**
            *   **(LLM Prompt):** "Translate the Laravel Job `app/Jobs/SendProtocolReminderNotification.php` [paste handle() method content] into a NestJS BullMQ Processor class (e.g., `ReminderProcessor.ts`). It should define a handler method decorated with `@Process('jobName')`. The logic should fetch necessary data using Prisma and send the notification."
        *   `[ ]` **(SVC) Add Job to Queue:**
            *   **(LLM Prompt):** "In the NestJS service that originally dispatched the Laravel job (e.g., `ReminderService` in `protocol-engine.service.ts` after creating a reminder, or the `reminders:send-due` command equivalent), inject the BullMQ Queue (`@InjectQueue('queueName')`) and add a job to it (e.g., `await this.reminderQueue.add('jobName', { reminderId: ... });`)."
*   **Notifications:**
    *   For each Laravel Notification class (e.g., `ProtocolReminder`, `ResetPasswordNotification`):
        *   `[ ]` **(NOTIF) Implement Notification Sending Logic:**
            *   **(LLM Prompt):** "Translate the Laravel Notification `app/Notifications/ProtocolReminder.php` [paste `via()` and `toFcm()`/`toApns()`/`toMail()` methods] into methods within a NestJS service (e.g., `NotificationService.ts` or directly in the service that needs to send it, like `ReminderProcessor` or `AuthenticationService`).
                *   For push notifications, this method should use a library like `firebase-admin` (for FCM) or an HTTP client to send requests to APNS/FCM gateways. Assume device tokens are available on the Prisma `User` or `UserDevice` model.
                *   For email notifications (like password reset), use a mailer library (e.g., `@nestjs-modules/mailer` with Nodemailer, or a transactional email service SDK like SendGrid/Postmark)."
            *   **_**(User Action)**_** Install any necessary SDKs (e.g., `firebase-admin`, mailer libraries).

---

## Phase 8: API Documentation (Swagger/OpenAPI)

*   `[x]` **(DOC) Setup Swagger in `main.ts` (LLM Prompt)** (Already seems done)
*   For *each* DTO generated in Phase 4:
    *   `[ ]` **(DOC) Add DTO Property Decorators:**
        *   **(LLM Prompt):** "For the DTO file `nest-app/src/module-name/dto/xyz.dto.ts` [paste DTO content], add `@ApiProperty()` (and `@ApiPropertyOptional()` where applicable) decorators from `@nestjs/swagger` to each property to document it for Swagger/OpenAPI."
*   For *each* Controller method generated in Phase 4:
    *   `[ ]` **(DOC) Add Controller Operation & Response Decorators:**
        *   **(LLM Prompt):** "For the NestJS Controller `nest-app/src/module-name/xyz.controller.ts` [paste controller content], add `@ApiOperation({ summary: '...' })` and `@ApiResponse({ status: 200, description: '...', type: XyzDto })` (and for other statuses like 201, 400, 401, 403, 404, 422) decorators from `@nestjs/swagger` to each endpoint method to thoroughly document its purpose, parameters, and responses."

---

## Phase 9: Final Review, Testing & Cleanup

*   **_**(User Action)**_** **Code Review:** Manually review all generated NestJS code for:
    *   Correctness of logic.
    *   Proper use of NestJS conventions and decorators.
    *   Type safety.
    *   Security considerations (ensure guards are applied, input is validated).
    *   Performance (e.g., avoiding N+1 query issues with Prisma includes).
*   **_**(User Action)**_** **Implement NestJS Testing:**
    *   Write unit tests for services and complex logic (using Jest).
    *   Write integration tests for controllers/endpoints (using Jest and Supertest).
    *   Consider E2E tests for critical user flows.
*   **_**(User Action)**_** **Configuration Validation:** Ensure all environment variables in `.env` are correctly used by the NestJS application.
*   **_**(User Action)**_** **Linting & Formatting:** Run `npm run lint --prefix nest-app` and `npm run format --prefix nest-app`.
*   **(LLM Prompt):** "Generate an updated `README.md` for the project root, focusing on the Nest.js application in the `nest-app` directory. Include instructions for setup, running in development, running tests, and accessing the Swagger API documentation."
*   **_**(User Action)**_** Remove old Laravel project files and documentation (once fully satisfied with the migration).

---

This refined TODO list should provide a much more granular and complete path for the migration. Remember to provide the LLM with as much context as possible (especially the original Laravel code snippets) for each prompt. Good luck!