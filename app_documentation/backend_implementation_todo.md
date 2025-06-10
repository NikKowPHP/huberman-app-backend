
### New, Detailed Plan for Production Readiness

Here is a revised, simple, and step-by-step `backend_implementation_todo.md` that addresses the critical schema flaw first, and then outlines the remaining tasks to achieve a production-ready state. This is designed to be actionable by a 4B LLM.

### **`backend_implementation_todo.md`**

# NestJS Production Readiness Plan - V2

**Goal:** Fix the critical database schema issue and complete the final features to make the NestJS application feature-complete, robust, and ready for production.

**Legend:**
*   `[ ]` - To-Do
*   `(File)` - The primary file to modify or create.
*   `(LLM Action)` - The specific instruction for the AI agent.
*   `(Verification)` - How to confirm the step was completed successfully.

---

## Phase 0: Critical Schema & Webhook Fix

**Objective:** Correct the database schema to properly handle mobile subscriptions and complete the Google Play webhook logic.

*   `[x]` **P0.1: Update Prisma Schema for Subscriptions**
    *   **(File):** `nest-app/prisma/schema.prisma`
    *   **(LLM Action):** Modify the `User` and `Subscription` models in `nest-app/prisma/schema.prisma`. Add `appleOriginalTransactionId` and `googlePlayPurchaseToken` to the `User` model, and `googlePlaySubscriptionId` to the `Subscription` model.
        ```prisma
        // In the User model, add these fields:
        appleOriginalTransactionId  String? @unique
        googlePlayPurchaseToken     String? @unique

        // In the Subscription model, add these fields:
        googlePlaySubscriptionId    String? @unique
        appleOriginalTransactionId  String?
        ```
    *   **(Verification):** The `User` and `Subscription` models in `prisma.schema` now contain the new fields.

*   `[!]` **P0.2: Create New Database Migration**
      * *AGENT_NOTE: Skipped due to database connection issues. Last error: Can't reach database server at `localhost:5434`*
    *   **(File):** A new file in `nest-app/prisma/migrations/`
    *   **(LLM Action):** Run the following command from the `nest-app` directory to create a new migration file reflecting the schema changes:
        ```bash
        npx prisma migrate dev --name "add_mobile_subscription_ids"
        ```
    *   **(Verification):** A new migration folder is created inside `nest-app/prisma/migrations/` containing a `migration.sql` file that adds the new columns to the "User" and "Subscription" tables.

*   `[x]` **P0.3: Update Google Webhook Service Logic**
    *   **(File):** `nest-app/src/subscription-billing/subscription-billing.service.ts`
    *   **(LLM Action):** In the `handleGoogleNotification` method, update the Prisma query to use the new `googlePlaySubscriptionId` field.
        ```typescript
        // In handleGoogleNotification, find this line:
        // const subscription = await this.prisma.subscription.findFirst({
        //   where: { googlePlaySubscriptionId: subscriptionId },
        // });
        
        // Ensure it correctly uses the new field. It already does, so this is just a verification step.
        // Also, add logic to find the user via purchaseToken and associate the subscription if it's a new purchase.
        // For now, let's refine the query to be more robust.
        
        // Replace the existing handleGoogleNotification method with this updated version
        async handleGoogleNotification(message: any) {
            try {
              const dataString = Buffer.from(message.data, 'base64').toString('utf-8');
              const data = JSON.parse(dataString);
              const { subscriptionNotification } = data;
              const { notificationType, purchaseToken, subscriptionId } = subscriptionNotification;

              if (!purchaseToken) {
                this.logger.error('Google Play notification is missing purchaseToken.');
                return;
              }

              this.logger.log(`Received Google Play Notification: ${notificationType} for subscriptionId: ${subscriptionId}`);

              // TODO: Add logic to validate the purchaseToken with the Google Play Developer API here.

              const subscription = await this.prisma.subscription.findFirst({
                  where: { googlePlaySubscriptionId: subscriptionId },
                  include: { user: true },
              });

              if (!subscription) {
                  this.logger.warn(`Subscription with Google Play ID ${subscriptionId} not found.`);
                  return;
              }

              const { user } = subscription;

              switch (notificationType) {
                case 4: // SUBSCRIPTION_RENEWED
                  await this.prisma.subscription.update({
                    where: { id: subscription.id },
                    data: { stripeStatus: 'ACTIVE' },
                  });
                  this.eventEmitter.emit('subscription.renewed', { userId: user.id });
                  break;

                case 3: // SUBSCRIPTION_CANCELED
                  await this.prisma.subscription.update({
                    where: { id: subscription.id },
                    data: { stripeStatus: 'CANCELED' },
                  });
                  this.eventEmitter.emit('subscription.canceled', { userId: user.id });
                  break;

                case 12: // SUBSCRIPTION_EXPIRED
                  await this.prisma.subscription.update({
                    where: { id: subscription.id },
                    data: { stripeStatus: 'EXPIRED', endsAt: new Date() },
                  });
                   this.eventEmitter.emit('subscription.ended', { userId: user.id });
                  break;

                default:
                  this.logger.warn(`Unhandled Google Play notification type: ${notificationType}`);
              }
            } catch (error) {
              this.logger.error(`Error handling Google Play notification: ${error.message}`, error.stack);
              throw error;
            }
        }
        ```
    *   **(Verification):** The `handleGoogleNotification` method is updated with the more robust logic.

---

## Phase 1: Complete Seeding

**Objective:** Ensure the database can be fully populated with realistic sample data for all features.

*   `[x]` **P1.1: Add Post & Comment Seeding**
    *   **(File):** `nest-app/prisma/seed.ts`
    *   **(LLM Action):** "In `nest-app/prisma/seed.ts`, add logic to seed `Post` and `Comment` data. After seeding users, loop through them to create a few posts for each. Then, loop through the created posts and add a few comments from different users to each post."
    *   **(Verification):** `seed.ts` now contains logic for seeding posts and comments.

*   `[!]` **P1.2: _**(User Action)**_ Re-run Full Seed**
    *   **_**(User Action)**_** Execute `npx prisma migrate reset` to clear the database and re-run all migrations and the seed script.
    *   **(Verification):** The command completes successfully. The database contains data for users, plans, posts, comments, etc.

---

## Phase 2: Final Polish & Testing Stubs

**Objective:** Add final touches for consistency and create the remaining test file stubs.

*   `[x]` **P2.1: Implement Global Response Interceptor**
    *   **(File):** `nest-app/src/common/interceptors/transform.interceptor.ts`
    *   **(LLM Action):** "Create a new file `nest-app/src/common/interceptors/transform.interceptor.ts`. Implement a `TransformInterceptor` that wraps all successful API responses in a `{ "data": ... }` object to ensure a consistent output format."
        ```typescript
        import { Injectable, NestInterceptor, ExecutionContext, CallHandler } from '@nestjs/common';
        import { Observable } from 'rxjs';
        import { map } from 'rxjs/operators';

        export interface Response<T> {
          data: T;
        }

        @Injectable()
        export class TransformInterceptor<T> implements NestInterceptor<T, Response<T>> {
          intercept(context: ExecutionContext, next: CallHandler): Observable<Response<T>> {
            return next.handle().pipe(map(data => ({ data })));
          }
        }
        ```
    *   **(Verification):** The file `transform.interceptor.ts` is created with the specified code.

*   `[x]` **P2.2: Register Global Response Interceptor**
    *   **(File):** `nest-app/src/main.ts`
    *   **(LLM Action):** "In `nest-app/src/main.ts`, register the `TransformInterceptor` globally using `app.useGlobalInterceptors(new TransformInterceptor());`."
    *   **(Verification):** `main.ts` now includes the code to register the global interceptor.

*   `[ ]` **P2.3: Create Remaining Test Stubs**
    *   **(LLM Action):** "Following the pattern from task **P4.1** and **P4.2**, create placeholder `.spec.ts` (unit) and `.e2e-spec.ts` (E2E) files for the newly created services and controllers in the `tracking-service`, `offline-data`, `post`, and `routine` modules."
    *   **(Verification):** Basic test stub files exist for all new services and controllers.