Okay, here is a very detailed and simple `migration_nestjs_todo.md.md` plan designed for a 4B LLM to follow and implement the remaining tasks. Each step aims to be atomic and actionable.

**Legend:**
*   `[ ]` - To Do
*   `[x]` - Done (This will be empty initially for the LLM to fill)
*   **(File):** `path/to/file.ts` - The primary file to modify or create.
*   **(LLM Prompt):** "Specific instruction for the LLM."
*   **(Verification):** "How to check if the step was successful (often by inspecting the file or output)."

---

**`migration_nestjs_todo.md.md`**

# Huberman App - NestJS Migration - Fixes & Completion Plan

**Goal:** Complete the migration of the Huberman App backend from Laravel to NestJS, addressing all pending items from the verification phase. This includes implementing missing modules, refining business logic, completing webhook and event handling, adding full API documentation, and setting up basic testing structures.

---

## Phase FIX.0: Setup & Prerequisites

*   `[x]` **FIX.SETUP.1: Configure BullModule**
    *   **(File):** `nest-app/src/app.module.ts`
    *   **(LLM Prompt):** "In `nest-app/src/app.module.ts`, import `BullModule` from `@nestjs/bullmq` and `ConfigModule`, `ConfigService` from `@nestjs/config` (if not already imported). Add `ConfigModule.forRoot({ isGlobal: true })` to the `imports` array. Then, add `BullModule.forRootAsync({ imports: [ConfigModule], useFactory: async (configService: ConfigService) => ({ connection: { host: configService.get<string>('REDIS_HOST', 'localhost'), port: configService.get<number>('REDIS_PORT', 6379), }, }), inject: [ConfigService], })` to the `imports` array of `AppModule`. Ensure `REDIS_HOST` and `REDIS_PORT` are defined in your `.env` file (e.g., `REDIS_HOST=localhost`, `REDIS_PORT=6379`)."
    *   **(Verification):** `app.module.ts` now correctly imports and configures `BullModule` using environment variables for Redis connection.

*   `[x]` **FIX.SETUP.2: Register `ReminderProcessor` in `ProtocolEngineModule`**
    *   **(File):** `nest-app/src/protocol-engine/protocol-engine.module.ts` (Create if it doesn't exist, or update if it's just `@Module({})`)
    *   **(LLM Prompt):** "Ensure `nest-app/src/protocol-engine/protocol-engine.module.ts` exists. Import `Module` from `@nestjs/common`, `BullModule` from `@nestjs/bullmq`, `ReminderProcessor` from `./reminder.processor`, `ReminderService` from `./reminder.service`, `NotificationService` from `./notification.service`, and `PrismaService` from `../common/prisma/prisma.service`. Configure the module as follows:
        ```typescript
        import { Module } from '@nestjs/common';
        import { BullModule } from '@nestjs/bullmq';
        import { ReminderProcessor } from './reminder.processor';
        import { ReminderService } from './reminder.service';
        import { NotificationService } from './notification.service';
        import { PrismaService } from '../common/prisma/prisma.service';
        import { ReminderController } from './reminder.controller'; // Assuming controller exists

        @Module({
          imports: [
            BullModule.registerQueue({
              name: 'reminders', // Same name as used in @Processor and @InjectQueue
            }),
          ],
          controllers: [ReminderController], // Add controller if it's part of this module
          providers: [ReminderService, ReminderProcessor, NotificationService, PrismaService],
        })
        export class ProtocolEngineModule {}
        ```"
    *   **(Verification):** `protocol-engine.module.ts` is created/updated, registers the 'reminders' queue, and lists `ReminderProcessor`, `ReminderService`, `NotificationService`, `PrismaService` as providers. `ReminderController` is listed in controllers.

---

## Phase FIX.1: Implement Missing Modules (Stubs & Basic Structure)

**Module: TrackingService**

*   `[x]` **FIX.MM.TS.1: Create `tracking.service.ts`**
    *   **(File):** `nest-app/src/tracking-service/tracking.service.ts`
    *   **(LLM Prompt):** "Create `nest-app/src/tracking-service/tracking.service.ts`. Add a basic `TrackingService` class decorated with `@Injectable()`. Inject `PrismaService`. Include placeholder methods based on Laravel's `TrackingServiceInterface`: `logAdherence(user, protocolId, date, notes, metadata)`, `getUserTrackingData(user, protocolId, dateRange)`, `calculateStreak(user, protocolId)`, `getTrackingLogById(user, logId)`, `updateTrackingLog(user, logId, data)`, `deleteTrackingLog(user, logId)`. These methods can initially just log a message or throw `NotImplementedException`."
    *   **(Verification):** File created with the service class, PrismaService injection, and stubbed methods.

*   `[x]` **FIX.MM.TS.2: Create `tracking.controller.ts`**
    *   **(File):** `nest-app/src/tracking-service/tracking.controller.ts`
    *   **(LLM Prompt):** "Create `nest-app/src/tracking-service/tracking.controller.ts`. Add a `TrackingController` class decorated with `@Controller('tracking')` and `@UseGuards(SupabaseAuthGuard)`. Inject `TrackingService`. Add placeholder methods for `store(@Req() req, @Body() storeTrackingLogDto: StoreTrackingLogDto)` and `getSummary(@Req() req, @Param('protocolId') protocolId: string)`. These methods should call the respective service methods."
    *   **(Verification):** File created with the controller class, service injection, guards, and stubbed endpoint handlers.

*   `[x]` **FIX.MM.TS.3: Create `store-tracking-log.dto.ts`**
    *   **(File):** `nest-app/src/tracking-service/dto/store-tracking-log.dto.ts`
    *   **(LLM Prompt):** "Create `nest-app/src/tracking-service/dto/store-tracking-log.dto.ts`. Define `StoreTrackingLogDto` class with validation decorators (`class-validator`) based on Laravel's `StoreTrackingLogRequest.php` (protocol_id: number, tracked_at: string (date YYYY-MM-DD), notes: string?, metadata: object?)."
    *   **(Verification):** DTO file created with correct properties and validation decorators.

*   `[x]` **FIX.MM.TS.4: Create `tracking-service.module.ts`**
    *   **(File):** `nest-app/src/tracking-service/tracking-service.module.ts`
    *   **(LLM Prompt):** "Create `nest-app/src/tracking-service/tracking-service.module.ts`. Define `TrackingServiceModule` importing necessary common modules (like `PrismaModule` if you have one, or provide `PrismaService` directly). Declare `TrackingController` and `TrackingService`."
    *   **(Verification):** Module file created, controller and service declared.

*   `[x]` **FIX.MM.TS.5: Register `TrackingServiceModule` in `app.module.ts`**
    *   **(File):** `nest-app/src/app.module.ts`
    *   **(LLM Prompt):** "In `nest-app/src/app.module.ts`, import and add `TrackingServiceModule` to the `imports` array."
    *   **(Verification):** `TrackingServiceModule` is imported and added.

**Module: OfflineDataService** (Repeat MM.TS.1-5 pattern)

*   `[x]` **FIX.MM.ODS.1: Create `offline-data.service.ts`** (Methods: `getDataForUser(user)`, `syncDataForUser(user, data)`)
*   `[x]` **FIX.MM.ODS.2: Create `offline-data.controller.ts`** (Endpoints: `GET /offline-data`, `POST /offline-data/sync`)
*   `[x]` **FIX.MM.ODS.3: Create DTOs:** `sync-offline-data.dto.ts` (based on `SyncOfflineDataRequest.php`)
*   `[x]` **FIX.MM.ODS.4: Create `offline-data.module.ts`**
*   `[x]` **FIX.MM.ODS.5: Register `OfflineDataModule` in `app.module.ts`**

**Module: PostService (Social/Community Features)** (Repeat MM.TS.1-5 pattern)

*   `[ ]` **FIX.MM.PS.1: Create `post.service.ts`** (Methods: `createPost(user, data)`, `getPostsWithComments()`, `createComment(user, postId, data)`)
*   `[ ]` **FIX.MM.PS.2: Create `post.controller.ts`** (Endpoints: `GET /posts`, `POST /posts`, `POST /posts/{id}/comments`)
*   `[ ]` **FIX.MM.PS.3: Create DTOs:** `create-post.dto.ts`, `store-comment.dto.ts`
*   `[ ]` **FIX.MM.PS.4: Create `post.module.ts`**
*   `[ ]` **FIX.MM.PS.5: Register `PostModule` in `app.module.ts`**

**Module: RoutineService** (Repeat MM.TS.1-5 pattern)

*   `[ ]` **FIX.MM.RS.1: Create `routine.service.ts`** (Methods: `getAllRoutines(user)`, `createRoutine(user, data)`, `updateRoutine(user, routineId, data)`, `deleteRoutine(user, routineId)`, `getRoutineSteps(routineId)`)
*   `[ ]` **FIX.MM.RS.2: Create `routine.controller.ts`** (Endpoints: `GET /routines`, `POST /routines`, `GET /routines/{id}`, `PUT /routines/{id}`, `DELETE /routines/{id}`, `GET /routines/{id}/steps`)
*   `[ ]` **FIX.MM.RS.3: Create DTOs:** `store-routine.dto.ts`, `update-routine.dto.ts`
*   `[ ]` **FIX.MM.RS.4: Create `routine.module.ts`**
*   `[ ]` **FIX.MM.RS.5: Register `RoutineModule` in `app.module.ts`**

---

## Phase FIX.2: Business Logic & Service Implementation (Porting from Laravel)

**For each Service identified as partially complete or missing (Content, Notes, Reminders, SubscriptionBilling, Tracking, OfflineData, Post, Routine):**

*   `[ ]` **FIX.BL.CS.1: `ContentService` - Review & Complete**
    *   **(File):** `nest-app/src/content-management/content.service.ts`
    *   **(LLM Prompt):** "Review the existing `nest-app/src/content-management/content.service.ts`. Ensure all methods from Laravel's `ContentServiceInterface` (e.g., `getProtocols`, `getProtocolDetails`, `getEpisodes`, `getEpisodeDetails`, `getSummariesForEpisode`) are fully implemented using Prisma Client, matching the logic of `app/Modules/ContentManagement/Services/ContentService.php`. Pay attention to any conditional logic for free/premium content if it was handled in the Laravel service."
    *   **(Verification):** Service methods correctly query and return data using Prisma, matching Laravel's logic.

*   `[ ]` **FIX.BL.NS.1: `NoteService` - Review & Complete**
    *   **(File):** `nest-app/src/notes-service/note.service.ts`
    *   **(LLM Prompt):** "Review `nest-app/src/notes-service/note.service.ts`. Ensure all methods like `createNote`, `getNote`, `updateNote`, `deleteNote`, `getUserNotesCount`, `getPublicNotes`, `getPublicNotesForEpisode`, `attachCategoryToNote` accurately port logic from `app/Modules/NotesService/Services/NoteService.php` and `app/Services/NoteService.php` using Prisma. Ensure free note limit (`MAX_FREE_NOTES`) and ownership checks are correctly implemented, throwing `ForbiddenException` or `NotFoundException` as appropriate. The `attachCategoryToNote` method should create a record in `NoteCategoryPivot`."
    *   **(Verification):** All methods implemented with correct Prisma queries and authorization logic.

*   `[ ]` **FIX.BL.RS.1: `ReminderService` - Review & Complete**
    *   **(File):** `nest-app/src/protocol-engine/reminder.service.ts`
    *   **(LLM Prompt):** "Review `nest-app/src/protocol-engine/reminder.service.ts`. Ensure methods `setReminder`, `getUserReminders`, `getReminder`, `updateReminder`, `deleteReminder` port logic from `app/Modules/ProtocolEngine/Services/ReminderService.php` using Prisma. Ensure jobs are correctly added to the BullMQ 'reminders' queue when reminders are created/updated and active."
    *   **(Verification):** Methods implemented with Prisma and BullMQ integration.

*   `[ ]` **FIX.BL.SBS.1: `SubscriptionBillingService` - Review & Complete**
    *   **(File):** `nest-app/src/subscription-billing/subscription-billing.service.ts`
    *   **(LLM Prompt):** "Review `nest-app/src/subscription-billing/subscription-billing.service.ts`.
        1.  Ensure `userHasActivePremiumSubscription(userId: string)` correctly queries the `Subscription` and `Plan` models via Prisma to check for 'ACTIVE' or 'TRIALING' status and 'premium' plan slug.
        2.  For Stripe webhook handlers (`handleCheckoutSessionCompleted`, `handleCustomerSubscriptionUpdatedTrialEnded`, `handleInvoicePaymentSucceeded`, `handleInvoicePaymentFailed`, `handleCustomerSubscriptionUpdatedCancel`, `handleCustomerSubscriptionDeleted`): Port the detailed logic from `app/Modules/SubscriptionBilling/Http/Controllers/WebhookController.php` (Laravel Cashier's underlying logic might be complex, so focus on state changes in the `Subscription` model like status and `ends_at`, `trial_ends_at` based on webhook payload). Dispatch NestJS events (e.g., `SubscriptionRenewedEvent`) where Laravel dispatched them.
        3.  For Apple webhook handlers (`handleAppleNotification`, `handleDidChangeRenewalStatus`): Complete the logic for updating subscription state in Prisma based on decoded JWS payload for all relevant `notificationType` values like `SUBSCRIBED`, `DID_RENEW`, `EXPIRED`, `DID_FAIL_TO_RENEW` similar to the Stripe handlers. Refer to `app/Modules/SubscriptionBilling/Services/SubscriptionService.php` (Laravel) for logic on how `stripe_status` and `ends_at` were managed for Apple events."
    *   **(Verification):** Methods correctly implemented with Prisma and event dispatches.

*   **(Implement the following new services using the pattern above, porting logic from their Laravel counterparts):**
    *   `[ ]` **FIX.BL.TS.1: `TrackingService` - Implement** (from `app/Modules/TrackingService/Services/TrackingService.php`)
    *   `[ ]` **FIX.BL.ODS.1: `OfflineDataService` - Implement** (from `app/Services/OfflineDataService.php`)
    *   `[ ]` **FIX.BL.PS.1: `PostService` - Implement** (from `app/Services/PostService.php`)
    *   `[ ]` **FIX.BL.RSVC.1: `RoutineService` - Implement** (from `app/Services/RoutineService.php`)

---

## Phase FIX.3: DTO and Controller Implementation/Refinement (Porting from Laravel)

**For each new/stubbed Controller (Tracking, OfflineData, Post, Routine) and existing ones needing review:**

*   `[ ]` **FIX.DTO.TS.1: `StoreTrackingLogDto` - Review/Complete**
    *   **(File):** `nest-app/src/tracking-service/dto/store-tracking-log.dto.ts`
    *   **(LLM Prompt):** "Ensure `StoreTrackingLogDto` in `nest-app/src/tracking-service/dto/store-tracking-log.dto.ts` matches the validation rules from Laravel's `app/Modules/TrackingService/Http/Requests/StoreTrackingLogRequest.php`: `protocol_id` (required, integer, exists:protocols,id), `tracked_at` (required, date_format:Y-m-d), `notes` (nullable, string, max:10000), `metadata` (nullable, array), `metadata.*` (nullable, string, max:255). Use `class-validator` decorators. Add `@ApiProperty()` for Swagger."
    *   **(Verification):** DTO has correct properties, validation, and Swagger decorators.

*   `[ ]` **FIX.CTRL.TS.1: `TrackingController` - Implement Endpoints**
    *   **(File):** `nest-app/src/tracking-service/tracking.controller.ts`
    *   **(LLM Prompt):** "Implement the `store` and `getSummary` methods in `nest-app/src/tracking-service/tracking.controller.ts`.
        *   `store`: Use `@Post('/log')`, `@Body() storeTrackingLogDto: StoreTrackingLogDto`, `@Req() req`. Call `trackingService.logAdherence()`. Add `@ApiOperation` and `@ApiResponse` decorators.
        *   `getSummary`: Use `@Get('/summary/:protocolId')`, `@Param('protocolId') protocolId: string`, `@Req() req`. Call `trackingService.calculateStreak()`. Add `@ApiOperation` and `@ApiResponse` decorators."
    *   **(Verification):** Endpoints implemented with correct decorators, DTOs, service calls, and Swagger docs.

*   **(Repeat FIX.DTO.*.1 and FIX.CTRL.*.1 for other new/stubbed modules):**
    *   `[ ]` **FIX.DTO.ODS.1 & FIX.CTRL.ODS.1:** `OfflineDataController` & `sync-offline-data.dto.ts`
    *   `[ ]` **FIX.DTO.PS.1 & FIX.CTRL.PS.1:** `PostController` & DTOs (`create-post.dto.ts`, `store-comment.dto.ts`)
    *   `[ ]` **FIX.DTO.RS.1 & FIX.CTRL.RS.1:** `RoutineController` & DTOs (`store-routine.dto.ts`, `update-routine.dto.ts`)

*   `[ ]` **FIX.CTRL.REVIEW.1: Review Existing Controllers for Full Logic Porting**
    *   **(Files):** `protocol.controller.ts`, `note.controller.ts`, `reminder.controller.ts`, `subscription-billing.controller.ts`, `authentication.controller.ts`.
    *   **(LLM Prompt):** "For each of the following controllers, review its methods against its Laravel counterpart and ensure all route parameters, query parameters, request body handling (using DTOs), service calls, and response structures are accurately ported:
        1.  `nest-app/src/content-management/protocol.controller.ts` (compare with `app/Modules/ContentManagement/Http/Controllers/ProtocolController.php`)
        2.  `nest-app/src/notes-service/note.controller.ts` (compare with `app/Modules/NotesService/Http/Controllers/NoteController.php`)
        3.  `nest-app/src/protocol-engine/reminder.controller.ts` (compare with `app/Modules/ProtocolEngine/Http/Controllers/ReminderController.php`)
        4.  `nest-app/src/subscription-billing/subscription-billing.controller.ts` (compare with `app/Modules/SubscriptionBilling/Http/Controllers/WebhookController.php` for webhooks, and `SubscriptionController.php` for any direct user-facing billing routes if planned).
        5.  `nest-app/src/authentication/authentication.controller.ts` (compare with `app/Modules/Authentication/Http/Controllers/AuthController.php`, `ForgotPasswordController.php`, `NewPasswordController.php`). Ensure password reset endpoints are added if missing."
    *   **(Verification):** Controllers accurately reflect Laravel endpoint functionality.

---

## Phase FIX.4: Guard & Authorization Refinement

*   `[ ]` **FIX.GUARD.PG.1: `PremiumGuard` - Refine to use `SubscriptionBillingService`**
    *   **(File):** `nest-app/src/common/guards/premium.guard.ts`
    *   **(LLM Prompt):** "Modify `nest-app/src/common/guards/premium.guard.ts`. Inject `SubscriptionBillingService`. In the `canActivate` method, instead of querying Prisma directly, call `this.subscriptionBillingService.userHasActivePremiumSubscription(user.id)` (or `user.sub` depending on how Supabase user ID is populated on `request.user`). Ensure `user` and `user.id`/`user.sub` are correctly accessed from the request."
    *   **(Verification):** `PremiumGuard` now uses `SubscriptionBillingService`.

*   `[ ]` **FIX.GUARD.OWN.1: Implement Ownership Checks / Fine-grained Guards (Example: Notes)**
    *   **(File):** `nest-app/src/notes-service/note.service.ts` and/or new guards if needed.
    *   **(LLM Prompt):** "Review `nest-app/src/notes-service/note.service.ts`. For methods like `getNote`, `updateNote`, `deleteNote` that operate on a specific note, ensure there's a check: `if (note.userId !== userIdFromAuthenticatedUser) { throw new ForbiddenException('Access denied'); }`. If this logic is complex or repeated, consider creating a dedicated `NoteOwnerGuard` and apply it in `note.controller.ts` to the relevant endpoints (`@UseGuards(SupabaseAuthGuard, NoteOwnerGuard)`)."
    *   **(Verification):** Ownership checks are present for note operations, either in service or via a new guard.
    *   **(Apply this pattern FIX.GUARD.OWN.* for other modules like Reminders, Routines, TrackingLogs if they have user-owned resources and the checks are not already robust in their services.)**

---

## Phase FIX.5: Webhook Logic Enhancement

*   `[ ]` **FIX.WH.S.1: Stripe Webhook - Robust Error Handling & Event Dispatch**
    *   **(File):** `nest-app/src/subscription-billing/subscription-billing.service.ts`
    *   **(LLM Prompt):** "In `nest-app/src/subscription-billing/subscription-billing.service.ts`, for each Stripe event handler method (e.g., `handleCheckoutSessionCompleted`):
        1.  Wrap the core logic in a try-catch block. Log detailed errors using `@nestjs/common` Logger.
        2.  Ensure appropriate NestJS events (e.g., `SubscriptionStartedEvent`, `SubscriptionRenewedEvent`, `SubscriptionCanceledEvent`, `SubscriptionExpiredEvent`, `PaymentFailedEvent` - create these event classes in `nest-app/src/common/events/` if they don't exist) are dispatched using `this.eventEmitter.emit()` after successful state changes. Pass the relevant subscription or user object to the event."
    *   **(Verification):** Stripe handlers have try-catch blocks, log errors, and dispatch specific NestJS events.

*   `[ ]` **FIX.WH.A.1: Apple Webhook - Full Event Type Handling & Error Logic**
    *   **(File):** `nest-app/src/subscription-billing/subscription-billing.service.ts`
    *   **(LLM Prompt):** "In `nest-app/src/subscription-billing/subscription-billing.service.ts` within the `handleAppleNotification` method's switch statement:
        1.  Implement full logic for `SUBSCRIBED`, `DID_RENEW`, `DID_FAIL_TO_RENEW`, `EXPIRED` cases. This involves finding the user (e.g., via `originalTransactionId`), updating their subscription state in Prisma (status, `ends_at`, `trial_ends_at`), and dispatching corresponding NestJS events (similar to Stripe). Refer to Laravel's `SubscriptionService` for how these states were managed.
        2.  Add robust error handling (try-catch) within each case and for JWS verification in `apple.service.ts` if not already comprehensive."
    *   **(Verification):** All key Apple notification types are handled with Prisma updates and event dispatches. Error handling is present.

*   `[ ]` **FIX.WH.G.1: Google Play Webhook - Implement Real Logic**
    *   **(File):** `nest-app/src/subscription-billing/subscription-billing.service.ts`
    *   **(LLM Prompt):** "In `nest-app/src/subscription-billing/subscription-billing.service.ts`, replace the mock/placeholder logic for Google Play RTDN handling.
        1.  Implement logic to decode the Pub/Sub message data.
        2.  For `SUBSCRIPTION_PURCHASED`, `SUBSCRIPTION_RENEWED`, `SUBSCRIPTION_CANCELED`, `SUBSCRIPTION_EXPIRED` (and other relevant types): Find the user, update their subscription state in Prisma, and dispatch NestJS events.
        3.  Implement purchase token validation and acknowledgement using the Google Play Developer API (use `googleapis` library - `npm install googleapis`). This will require setting up a Google Cloud service account and using its credentials. Add placeholder for API client initialization if full setup is too complex for one step.
        4.  Add robust error handling."
    *   **(Verification):** Google Play webhook handling is implemented with actual validation (or clear placeholders for API client setup) and state updates.

---

## Phase FIX.6: Event, Job, Notification Enhancement

*   `[ ]` **FIX.EVENT.LSR.1: `SubscriptionRenewedListener` - Implement Cache Clearing**
    *   **(File):** `nest-app/src/subscription-billing/listeners/subscription-renewed.listener.ts`
    *   **(LLM Prompt):** "In `nest-app/src/subscription-billing/listeners/subscription-renewed.listener.ts`, uncomment and implement the actual cache clearing logic. If a generic NestJS caching module (`@nestjs/cache-manager`) is used, inject `Cache` and use `this.cacheManager.del('user:${event.userId}:premium_subscription')` or similar. If no global cache manager, this step might be deferred or use a custom solution. For now, ensure the logging shows the intent to clear cache for `event.userId`."
    *   **(Verification):** Listener attempts to clear a user-specific cache key or logs the intent clearly. User `isPremium` update is removed if it's not a direct field on User model.

*   `[ ]` **FIX.NOTIF.S.1: `NotificationService` - Implement Real FCM/APNS Sending**
    *   **(File):** `nest-app/src/protocol-engine/notification.service.ts`
    *   **(LLM Prompt):** "In `nest-app/src/protocol-engine/notification.service.ts`:
        1.  For `sendFcmNotification`: Install `firebase-admin` (`npm install firebase-admin`). Initialize Firebase Admin SDK (typically in `main.ts` or a Firebase module, then inject `admin.messaging()`). Use `admin.messaging().send({ token: deviceToken, notification: { title, body } })`.
        2.  For `sendApnsNotification`: Choose and install an APNS library (e.g., `apn` or use Firebase Admin SDK if configured for APNS via FCM). Implement sending logic using the chosen library.
        3.  Add error handling for send operations."
    *   **(Verification):** Methods use actual FCM/APNS libraries to send notifications (or have very clear SDK initialization placeholders).

---

## Phase FIX.7: Seed Script Completion

*   `[ ]` **FIX.SEED.OD.1: Add OfflineData Seeding**
    *   **(File):** `nest-app/prisma/seed.ts`
    *   **(LLM Prompt):** "In `nest-app/prisma/seed.ts`, add logic to seed `OfflineData`. Create a few sample `User` records if they don't exist or pick existing ones. For each sample user, create a few `OfflineData` entries with a `key` (string) and `value` (JSON string, e.g., `JSON.stringify({ setting: 'value' })`)."
    *   **(Verification):** `seed.ts` includes OfflineData seeding.

*   `[ ]` **FIX.SEED.PCO.1: Add Post & Comment Seeding**
    *   **(File):** `nest-app/prisma/seed.ts`
    *   **(LLM Prompt):** "In `nest-app/prisma/seed.ts`, add logic to seed `Post` and `Comment` data. Create sample Users. For each User, create a few Posts. For each Post, create a few Comments from different sample Users."
    *   **(Verification):** `seed.ts` includes Post and Comment seeding.

*   `[ ]` **FIX.SEED.RUN.1: _**(User Action)**_ Re-run Seed Script**
    *   **_**(User Action)**_** Execute `npx prisma db seed --schema=./nest-app/prisma/schema.prisma`.
    *   **(Verification):** Seed script completes without errors. Check database for new seed data.

---

## Phase FIX.8: API Documentation (Swagger)

**For every DTO file in `nest-app/src/**/dto/*.dto.ts`:**
*   `[ ]` **FIX.API_DOC.DTO.1 (Example: `create-protocol.dto.ts`)**
    *   **(File):** `nest-app/src/content-management/dto/create-protocol.dto.ts`
    *   **(LLM Prompt):** "In `nest-app/src/content-management/dto/create-protocol.dto.ts`, import `ApiProperty` and `ApiPropertyOptional` from `@nestjs/swagger`. Add `@ApiProperty()` decorator above each required field (e.g., `title`). Add `@ApiPropertyOptional()` above each optional field (e.g., `description`, `isFree`). Provide `description` and `example` values in the decorator options where appropriate (e.g. `@ApiProperty({ description: 'The title of the protocol', example: 'Morning Sunlight Viewing' })`)."
    *   **(Verification):** All properties in the DTO are decorated with `@ApiProperty` or `@ApiPropertyOptional`.
    *   **(Repeat this pattern for ALL DTOs: `create-note.dto.ts`, `update-note.dto.ts`, `attach-category.dto.ts`, `store-reminder.dto.ts`, `update-reminder.dto.ts`, `store-tracking-log.dto.ts`, and any DTOs created for OfflineData, Post, Routine modules).**

**For every Controller method in `nest-app/src/**/*.controller.ts`:**
*   `[ ]` **FIX.API_DOC.CTRL.1 (Example: `ProtocolController.index`)**
    *   **(File):** `nest-app/src/content-management/protocol.controller.ts`
    *   **(LLM Prompt):** "In `nest-app/src/content-management/protocol.controller.ts`, for the `index()` method:
        1.  Import `ApiOperation`, `ApiResponse`, `ApiBearerAuth` (if auth is used) from `@nestjs/swagger`.
        2.  Add `@ApiOperation({ summary: 'Get a list of protocols' })`.
        3.  Add `@ApiResponse({ status: 200, description: 'List of protocols (content may vary based on subscription status)', type: [ProtocolResource] })` (assuming `ProtocolResource` is or will be defined with `@ApiProperty` on its fields, or use a DTO directly).
        4.  If the endpoint requires authentication, add `@ApiBearerAuth()`.
        Repeat for the `show()` method, documenting its `@Param('id')` with `@ApiParam({ name: 'id', type: 'string', description: 'ID of the protocol' })` and appropriate `@ApiResponse` for 200 and 404."
    *   **(Verification):** Controller methods are decorated with `@ApiOperation`, `@ApiResponse`, `@ApiParam` (if needed), and `@ApiBearerAuth` (if needed).
    *   **(Repeat this pattern for ALL methods in ALL Controllers: `NoteController`, `ReminderController`, `TrackingController`, `OfflineDataController`, `PostController`, `RoutineController`, `AuthenticationController`, `SubscriptionBillingController`).**

---

## Phase FIX.9: Testing (Stubs & Guidance - Primarily User Action)

*   `[ ]` **FIX.TEST.UNIT.1: Create Service Unit Test Stubs**
    *   **(LLM Prompt):** "For each of the following services, create a basic Jest unit test file (`.spec.ts`) if one doesn't exist. Include a `describe` block and a placeholder `it('should be defined')` test:
        *   `nest-app/src/content-management/content.service.ts`
        *   `nest-app/src/notes-service/note.service.ts`
        *   `nest-app/src/protocol-engine/reminder.service.ts`
        *   `nest-app/src/protocol-engine/notification.service.ts`
        *   `nest-app/src/subscription-billing/subscription-billing.service.ts`
        *   `nest-app/src/subscription-billing/apple.service.ts`
        *   `nest-app/src/tracking-service/tracking.service.ts`
        *   `nest-app/src/offline-data/offline-data.service.ts`
        *   `nest-app/src/post/post.service.ts`
        *   `nest-app/src/routine/routine.service.ts`
        Each test file should mock `PrismaService` and any other injected dependencies."
    *   **(Verification):** `.spec.ts` files created with basic structure for each service.

*   `[ ]` **FIX.TEST.E2E.1: Create Controller E2E Test Stubs**
    *   **(LLM Prompt):** "For each of the following controllers, create a basic Jest E2E test file (`.e2e-spec.ts`) in the `nest-app/test/` directory if one doesn't exist. Include `beforeEach` to setup the Nest application testing module and a placeholder `it('/GET endpointName', () => { return request(app.getHttpServer()).get('/endpointName').expect(200); });` test for one GET endpoint if applicable:
        *   `nest-app/src/content-management/protocol.controller.ts`
        *   `nest-app/src/notes-service/note.controller.ts`
        *   `nest-app/src/protocol-engine/reminder.controller.ts`
        *   `nest-app/src/subscription-billing/subscription-billing.controller.ts` (Note: testing webhooks E2E is complex, focus on any direct GET endpoints if they exist, or just a basic app health check).
        *   `nest-app/src/tracking-service/tracking.controller.ts`
        *   `nest-app/src/offline-data/offline-data.controller.ts`
        *   `nest-app/src/post/post.controller.ts`
        *   `nest-app/src/routine/routine.controller.ts`"
    *   **(Verification):** `.e2e-spec.ts` files created with basic structure for each controller.

---

This plan is extensive. The LLM should proceed step-by-step, and you (the user) should verify each generated piece of code or file modification before instructing the LLM to move to the next step. Good luck!