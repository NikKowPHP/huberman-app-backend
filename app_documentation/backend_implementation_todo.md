Of course. Based on my analysis and your decision to proceed with the existing Supabase integration, here is a detailed, simplified, and step-by-step plan in a markdown todo list format.

This plan is specifically designed to be executed by a 4B LLM. Each step is atomic, explicit, and includes verification criteria.

---

### **`prod_ready_nestjs_todo.md`**

# NestJS Production Readiness Plan

**Goal:** Finalize the NestJS application to be feature-complete, robust, and ready for production. This plan addresses all identified gaps from the migration analysis, embracing the existing Supabase authentication structure.

**Legend:**
*   `[ ]` - To-Do
*   `(File)` - The primary file to modify or create.
*   `(LLM Action)` - The specific instruction for the AI agent.
*   `(Verification)` - How to confirm the step was completed successfully.

---

## Phase 1: Authentication & User Synchronization

**Objective:** Fully integrate NestJS with the Supabase authentication system, including user sync and password reset functionality.

*   `[x]` **P1.1: Create Supabase DB Trigger for User Sync**
    *   **(File):** `supabase/init.sql`
    *   **(LLM Action):** In the `supabase/` directory, create a new file named `init.sql`. Populate this file with the following SQL script. This script defines a trigger that automatically copies a new user from Supabase's `auth.users` table into your public `User` table upon sign-up.
        ```sql
        -- Ensures new users in Supabase auth are copied to the public users table
        create or replace function public.handle_new_user()
        returns trigger
        language plpgsql
        security definer set search_path = public
        as $$
        begin
          insert into public."User" (id, email, name) -- Ensure table and column names match your Prisma schema exactly ("User")
          values (new.id, new.email, new.raw_user_meta_data->>'name');
          return new;
        end;
        $$;

        -- drop trigger if exists on_auth_user_created on auth.users; -- uncomment to reset
        create or replace trigger on_auth_user_created
          after insert on auth.users
          for each row execute procedure public.handle_new_user();
        ```
    *   **(Verification):** The file `supabase/init.sql` exists and contains the correct SQL code. (Note: A human must run this SQL in their Supabase dashboard's SQL Editor).

*   `[x]` **P1.2: Implement Password Reset Logic**
    *   **(File):** `nest-app/src/authentication/authentication.service.ts`
    *   **(LLM Action):** In the `AuthenticationService`, add a new method `resetPassword` that uses the Supabase client to trigger a password reset email.
        ```typescript
        // Add this method inside the AuthenticationService class
        async resetPassword(email: string) {
          const supabase = this.supabaseService.getSupabaseClient();
          const { error } = await supabase.auth.resetPasswordForEmail(email, {
            redirectTo: process.env.PASSWORD_RESET_URL, // You'll need to add this to your .env
          });
          if (error) throw error;
          return { message: 'Password reset email sent successfully. Please check your inbox.' };
        }
        ```
    *   **(Verification):** The `resetPassword` method exists in `authentication.service.ts`.

*   `[x]` **P1.3: Expose Password Reset Endpoint**
    *   **(File):** `nest-app/src/authentication/authentication.controller.ts`
    *   **(LLM Action):** In the `AuthenticationController`, add a new endpoint to handle password reset requests.
        ```typescript
        // Add this method inside the AuthenticationController class
        @Post('reset-password')
        async resetPassword(@Body('email') email: string) {
          return this.authenticationService.resetPassword(email);
        }
        ```
    *   **(Verification):** The `resetPassword` endpoint exists in `authentication.controller.ts` with the `@Post('reset-password')` decorator.

---

## Phase 2: Complete Webhook Logic

**Objective:** Implement the missing subscription logic for Google Play to ensure all payment providers are handled.

*   `[x]` **P2.1: Implement Google Play Webhook Logic**
    *   **(File):** `nest-app/src/subscription-billing/subscription-billing.service.ts`
    *   **(LLM Action):** In the `handleGoogleNotification` method, replace the placeholder `// TODO` logic with a `switch` statement that handles different notification types by updating the subscription status in the Prisma database.
        ```typescript
        // Replace the existing handleGoogleNotification method with this
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

              // In a real app, you would find the user/subscription via the purchaseToken or subscriptionId
              // For now, we will log the intent.
              this.logger.log(`Received Google Play Notification: ${notificationType} for subscriptionId: ${subscriptionId}`);

              // Find the subscription linked to this Google Play ID
              const subscription = await this.prisma.subscription.findFirst({
                  where: { googlePlaySubscriptionId: subscriptionId },
              });

              if (!subscription) {
                  this.logger.warn(`Subscription with Google Play ID ${subscriptionId} not found.`);
                  return;
              }

              switch (notificationType) {
                case 4: // SUBSCRIPTION_RENEWED
                  await this.prisma.subscription.update({
                    where: { id: subscription.id },
                    data: { stripeStatus: 'ACTIVE' },
                  });
                  this.eventEmitter.emit('subscription.renewed', { userId: subscription.userId });
                  break;

                case 3: // SUBSCRIPTION_CANCELED
                  await this.prisma.subscription.update({
                    where: { id: subscription.id },
                    data: { stripeStatus: 'CANCELED' },
                  });
                  this.eventEmitter.emit('subscription.canceled', { userId: subscription.userId });
                  break;

                case 12: // SUBSCRIPTION_EXPIRED
                  await this.prisma.subscription.update({
                    where: { id: subscription.id },
                    data: { stripeStatus: 'EXPIRED', endsAt: new Date() },
                  });
                   this.eventEmitter.emit('subscription.ended', { userId: subscription.userId });
                  break;

                // Add other cases as needed (e.g., 2: PURCHASED, 5: ON_HOLD)
                default:
                  this.logger.warn(`Unhandled Google Play notification type: ${notificationType}`);
              }
            } catch (error) {
              this.logger.error(`Error handling Google Play notification: ${error.message}`, error.stack);
              throw error;
            }
        }
        ```
    *   **(Verification):** The `handleGoogleNotification` method in `subscription-billing.service.ts` is updated with the new `switch` statement and Prisma logic.

---

## Phase 3: Application Polish & Consistency

**Objective:** Improve the application's robustness and developer experience by standardizing responses, error handling, and environment setup.

*   `[x]` **P3.1: Create Global Exception Filter**
    *   **(File):** `nest-app/src/common/filters/all-exceptions.filter.ts`
    *   **(LLM Action):** Create a new file `nest-app/src/common/filters/all-exceptions.filter.ts` and add the following code to create a global filter for consistent JSON error responses.
        ```typescript
        import {
          ExceptionFilter,
          Catch,
          ArgumentsHost,
          HttpException,
          HttpStatus,
        } from '@nestjs/common';
        import { HttpAdapterHost } from '@nestjs/core';

        @Catch()
        export class AllExceptionsFilter implements ExceptionFilter {
          constructor(private readonly httpAdapterHost: HttpAdapterHost) {}

          catch(exception: unknown, host: ArgumentsHost): void {
            const { httpAdapter } = this.httpAdapterHost;
            const ctx = host.switchToHttp();

            const httpStatus =
              exception instanceof HttpException
                ? exception.getStatus()
                : HttpStatus.INTERNAL_SERVER_ERROR;

            const responseBody = {
              statusCode: httpStatus,
              timestamp: new Date().toISOString(),
              path: httpAdapter.getRequestUrl(ctx.getRequest()),
              message: exception instanceof HttpException ? exception.message : 'Internal server error',
            };

            httpAdapter.reply(ctx.getResponse(), responseBody, httpStatus);
          }
        }
        ```
    *   **(Verification):** The file `all-exceptions.filter.ts` is created with the specified content.

*   `[x]` **P3.2: Register Global Exception Filter**
    *   **(File):** `nest-app/src/main.ts`
    *   **(LLM Action):** In `nest-app/src/main.ts`, register the `AllExceptionsFilter` globally.
        ```typescript
        // Add these imports at the top
        import { HttpAdapterHost } from '@nestjs/core';
        import { AllExceptionsFilter } from './common/filters/all-exceptions.filter';

        // Inside the bootstrap() function, after `const app = ...` and before `app.listen()`
        const { httpAdapter } = app.get(HttpAdapterHost);
        app.useGlobalFilters(new AllExceptionsFilter(httpAdapter));
        ```
    *   **(Verification):** `main.ts` now includes the code to register the global filter.

*   `[ ]` **P3.3: Create `.env.example` file**
    *   **(File):** `nest-app/.env.example`
    *   **(LLM Action):** Create a new file `nest-app/.env.example` and populate it with all the necessary environment variables for the project.
        ```
        # App
        PORT=3000

        # Database (Prisma)
        DATABASE_URL="postgresql://user:password@host:port/database?schema=public"

        # Supabase
        SUPABASE_URL=
        SUPABASE_KEY=
        PASSWORD_RESET_URL=

        # Redis (BullMQ)
        REDIS_HOST=localhost
        REDIS_PORT=6379

        # Stripe
        STRIPE_SECRET_KEY=
        STRIPE_WEBHOOK_SECRET=

        # Firebase (for Push Notifications)
        FIREBASE_PROJECT_ID=
        FIREBASE_CLIENT_EMAIL=
        FIREBASE_PRIVATE_KEY=
        ```
    *   **(Verification):** The file `nest-app/.env.example` exists with the required variables.

---

## Phase 4: Foundational Testing

**Objective:** Establish a clear testing pattern by implementing one basic unit and one basic E2E test.

*   `[ ]` **P4.1: Implement a Basic Unit Test**
    *   **(File):** `nest-app/src/app.service.spec.ts`
    *   **(LLM Action):** Open the existing file `nest-app/src/app.service.spec.ts` and ensure the test correctly checks the behavior of the `getHello()` method.
        ```typescript
        import { Test, TestingModule } from '@nestjs/testing';
        import { AppService } from './app.service';

        describe('AppService', () => {
          let service: AppService;

          beforeEach(async () => {
            const module: TestingModule = await Test.createTestingModule({
              providers: [AppService],
            }).compile();

            service = module.get<AppService>(AppService);
          });

          it('should be defined', () => {
            expect(service).toBeDefined();
          });

          it('should return "Hello World!"', () => {
            expect(service.getHello()).toBe('Hello World!');
          });
        });
        ```
    *   **(Verification):** The unit test `nest-app/src/app.service.spec.ts` contains the updated, correct code.

*   `[ ]` **P4.2: Implement a Basic E2E Test**
    *   **(File):** `nest-app/test/app.e2e-spec.ts`
    *   **(LLM Action):** Open `nest-app/test/app.e2e-spec.ts` and ensure the E2E test for the root endpoint (`GET /`) is correctly implemented.
        ```typescript
        import { Test, TestingModule } from '@nestjs/testing';
        import { INestApplication } from '@nestjs/common';
        import * as request from 'supertest';
        import { AppModule } from './../src/app.module';

        describe('AppController (e2e)', () => {
          let app: INestApplication;

          beforeEach(async () => {
            const moduleFixture: TestingModule = await Test.createTestingModule({
              imports: [AppModule],
            }).compile();

            app = moduleFixture.createNestApplication();
            await app.init();
          });

          afterAll(async () => {
            await app.close();
          });

          it('/ (GET)', () => {
            return request(app.getHttpServer())
              .get('/')
              .expect(200)
              .expect('Hello World!');
          });
        });
        ```
    *   **(Verification):** The E2E test `nest-app/test/app.e2e-spec.ts` contains the updated, correct code, including an `afterAll` hook to close the app.
*   `[ ]` **P4.3: _**(User Action)**_ Run Tests**
    *   **_**(User Action)**_** Execute `npm run test` and `npm run test:e2e` from the `nest-app` directory.
    *   **(Verification):** Both test suites should run and pass.