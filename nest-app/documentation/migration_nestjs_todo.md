Of course. Here is a highly specialized and detailed TODO list, tailored for a small LLM to execute the migration from Laravel to Nest.js, with the specific requirement that the new project resides in a `./nest-app` subdirectory.

Each step is designed to be atomic and includes explicit commands and file paths reflecting this new directory structure.

---

# Migration Plan: Laravel to Nest.js, Prisma, and Supabase (in `./nest-app`)

**Project Goal:** To migrate the existing Laravel backend to a Nest.js backend, replacing Eloquent with Prisma ORM and leveraging Supabase for database hosting and user authentication.

**IMPORTANT NOTE:** All commands must be executed from the **project root directory**, not from within `nest-app`. All file paths will be relative to the root, e.g., `nest-app/src/main.ts`.

**Legend:**
*   `[ ]` - To Do
*   `(DB)` - Database-related task (Prisma Schema, Migrations)
*   `(AUTH)` - Authentication-related task (Supabase Auth)
*   `(API)` - API Endpoint/Controller task
*   `(LOGIC)` - Business logic/Service task
*   `(SETUP)` - Project configuration or setup task

---

## Phase 0: Project Foundation & Environment Setup

*   `[ ]` **(SETUP)** **Initialize Nest.js Project in `nest-app` Directory:**
    *   From the project root, run the NestJS CLI to create the new project inside the `nest-app` folder.
        ```bash
        npx @nestjs/cli new nest-app
        ```

*   `[ ]` **(SETUP)** **Supabase Project Setup:**
    *   `[ ]` Go to [supabase.com](https://supabase.com) and create a new project.
    *   `[ ]` Navigate to "Project Settings" > "Database" and copy the **Connection String (URI)**.
    *   `[ ]` Navigate to "Project Settings" > "API" and copy the **Project URL** and the `anon` **public key**.

*   `[ ]` **(SETUP)** **Environment Configuration:**
    *   `[ ]` Create a file at `nest-app/.env`.
    *   `[ ]` Add the following environment variables, pasting the values from Supabase and your Stripe test keys.
        ```env
        # Supabase
        DATABASE_URL="<YOUR_SUPABASE_CONNECTION_STRING_URI>"
        SUPABASE_URL="<YOUR_SUPABASE_PROJECT_URL>"
        SUPABASE_ANON_KEY="<YOUR_SUPABASE_ANON_KEY>"

        # Stripe
        STRIPE_SECRET_KEY="sk_test_..."
        STRIPE_WEBHOOK_SECRET="whsec_..."
        
        # App
        PORT=3000
        ```

*   `[ ]` **(SETUP)** **Prisma Integration:**
    *   `[ ]` Install Prisma CLI and Client within the `nest-app` project.
        ```bash
        cd nest-app && npm install prisma --save-dev && npm install @prisma/client && cd ..
        ```
    *   `[ ]` Initialize Prisma. This creates the `nest-app/prisma` directory.
        ```bash
        npx prisma init --schema-path=./nest-app/prisma/schema.prisma
        ```
    *   `[ ]` In `nest-app/prisma/schema.prisma`, ensure the `datasource db` block points to the Supabase PostgreSQL database via the environment variable.
        ```prisma
        datasource db {
          provider = "postgresql"
          url      = env("DATABASE_URL")
        }

        generator client {
          provider = "prisma-client-js"
        }
        ```

*   `[ ]` **(SETUP)** **Create Core NestJS Modules:**
    *   `[ ]` From the project root, generate a `PrismaService` for database access.
        ```bash
        nest g service nest-app/src/common/prisma --no-spec
        ```
    *   `[ ]` In `nest-app/src/common/prisma/prisma.service.ts`, implement the service to extend the Prisma Client.
        ```typescript
        import { Injectable, OnModuleInit } from '@nestjs/common';
        import { PrismaClient } from '@prisma/client';

        @Injectable()
        export class PrismaService extends PrismaClient implements OnModuleInit {
          async onModuleInit() {
            await this.$connect();
          }
        }
        ```
    *   `[ ]` From the root, generate the necessary modules.
        ```bash
        nest g module nest-app/src/authentication
        nest g module nest-app/src/user-management
        nest g module nest-app/src/subscription-billing
        nest g module nest-app/src/content-management
        nest g module nest-app/src/notes-service
        nest g module nest-app/src/protocol-engine
        nest g module nest-app/src/tracking-service
        ```

---

## Phase 1: User Authentication with Supabase

*   `[ ]` **(AUTH)** **Supabase Auth Integration:**
    *   `[ ]` Install the Supabase client library.
        ```bash
        cd nest-app && npm install @supabase/supabase-js && cd ..
        ```
    *   `[ ]` Generate a `SupabaseService` for Auth interactions.
        ```bash
        nest g service nest-app/src/common/supabase --no-spec
        ```
    *   `[ ]` Implement the `SupabaseService` in `nest-app/src/common/supabase/supabase.service.ts`.

*   `[ ]` **(DB)** **Define User Schema in Prisma:**
    *   `[ ]` In `nest-app/prisma/schema.prisma`, define the `User` model. This model will sync with `auth.users` via a trigger. **Do not add a password field.**
        ```prisma
        model User {
          id                  String    @id @default(uuid()) // Match Supabase Auth UUID
          email               String    @unique
          name                String?
          profilePictureUrl   String?   @map("profile_picture_url")
          stripeId            String?   @unique @map("stripe_id")
          appstoreTransactionId String? @unique @map("appstore_transaction_id")
          createdAt           DateTime  @default(now()) @map("created_at")
          updatedAt           DateTime  @updatedAt @map("updated_at")
          deletedAt           DateTime? @map("deleted_at")

          // Relationships will be added here

          @@map("users") // Maps to the 'users' table in the 'public' schema
        }
        ```

*   `[ ]` **(DB)** **Create User Sync Trigger in Supabase:**
    *   `[ ]` In the Supabase dashboard, go to "SQL Editor".
    *   `[ ]` Run the following SQL to create a function and trigger that automatically populates your `public.users` table whenever a new user signs up via Supabase Auth.
        ```sql
        -- SQL function to run on new user creation
        create or replace function public.handle_new_user()
        returns trigger
        language plpgsql
        security definer set search_path = public
        as $$
        begin
          insert into public.users (id, email)
          values (new.id, new.email);
          return new;
        end;
        $$;

        -- Trigger to call the function
        create or replace trigger on_auth_user_created
          after insert on auth.users
          for each row execute procedure public.handle_new_user();
        ```

*   `[ ]` **(API)** **Implement Authentication Endpoints:**
    *   `[ ]` Generate the `AuthController` in the `authentication` module.
    *   `[ ]` **Register:** Implement `POST /auth/register` to call `supabase.auth.signUp()`.
    *   `[ ]` **Login:** Implement `POST /auth/login` to call `supabase.auth.signInWithPassword()`.
    *   `[ ]` **Logout:** Implement `POST /auth/logout` to call `supabase.auth.signOut()`.

*   `[ ]` **(AUTH)** **Create Authentication Guard:**
    *   `[ ]` Create an `AuthGuard` in the `authentication` module.
    *   `[ ]` The guard should extract the JWT from the `Authorization: Bearer` header, validate it with `supabase.auth.getUser(jwt)`, and attach the user to the request.

---

## Phase 2: Database Schema Migration & Seeding

*   `[ ]` **(DB)** **Translate All Laravel Migrations to Prisma Schema:**
    *   `[ ]` Open `nest-app/prisma/schema.prisma`.
    *   `[ ]` Systematically go through every `create_*_table.php` file from the Laravel project.
    *   `[ ]` For each Laravel migration, create a corresponding `model` in `schema.prisma`. Use `@map` to keep snake_case table and column names.
        *   `[ ]` `Plan` model (`plans` table).
        *   `[ ]` `Subscription` model (`subscriptions` table).
        *   `[ ]` `Episode`, `Protocol`, `Summary` models.
        *   `[ ]` `Note`, `NoteCategory`, `NoteTag` models and their pivots.
        *   `[ ]` `UserReminder` model (`user_reminders` table).
        *   `[ ]` `Routine`, `RoutineStep` models.
        *   `[ ]` `TrackingLog` model (`user_protocol_tracking` table).
        *   `[ ]` `Post`, `Comment` models.
    *   `[ ]` Add `@relation` fields to all models to define relationships (e.g., `user User @relation(fields: [userId], references: [id])`).

*   `[ ]` **(DB)** **Run Prisma Migration:**
    *   `[ ]` Generate and apply the migration to your Supabase DB.
        ```bash
        npx prisma migrate dev --schema=./nest-app/prisma/schema.prisma --name "initial-schema"
        ```

*   `[ ]` **(DB)** **Create Seed Script:**
    *   `[ ]` In `nest-app/package.json`, add the seed script: `"prisma": { "seed": "ts-node prisma/seed.ts" }`.
    *   `[ ]` Create the file `nest-app/prisma/seed.ts`.
    *   `[ ]` Translate the logic from Laravel's seeders into this file, using the Prisma Client to create records.
    *   `[ ]` Run the seeder.
        ```bash
        npx prisma db seed --schema=./nest-app/prisma/schema.prisma
        ```

---

## Phase 3-6: Feature & API Endpoint Migration

For each feature module, follow this pattern:
1.  Generate the `Controller` and `Service`.
2.  Implement the service methods using `PrismaService`.
3.  Implement the controller methods, calling the service.
4.  Apply `AuthGuard` and the `PremiumGuard` (created in the next step) where necessary.

*   `[ ]` **(LOGIC)** **Implement Feature Gating Guard:**
    *   `[ ]` Create `PremiumGuard` in a shared location (e.g., `nest-app/src/common/guards`).
    *   `[ ]` The guard injects `PrismaService`, finds the logged-in user's subscription, and verifies their premium status (`active` or `trialing`). Throws `ForbiddenException` if not premium.

*   `[ ]` **(API)** **Subscription & Billing Module:**
    *   `[ ]` Implement `GET /plans` endpoint.
    *   `[ ]` Implement `GET /user/subscription` endpoint (protected by `AuthGuard`).
    *   `[ ]` Implement `POST /webhooks/stripe` endpoint with Stripe signature verification.

*   `[ ]` **(API)** **Notes Service Module:**
    *   `[ ]` Implement all CRUD endpoints for notes, protected by `AuthGuard`.

*   `[ ]` **(API & LOGIC)** **Protocol Engine Module:**
    *   `[ ]` Implement CRUD endpoints for `UserReminder`, protected by `AuthGuard` and `PremiumGuard`.
    *   `[ ]` Set up NestJS scheduler (`@nestjs/schedule`) with a `@Cron()` job to handle sending reminders.

*   `[ ]` **(API)** **Tracking Service Module:**
    *   `[ ]` Implement `POST /tracking/log` and `GET /tracking/summary`, protected by `AuthGuard` and `PremiumGuard`.
    *   `[ ]` Port the `calculateStreak` logic into the `TrackingService`.

---

## Phase 7: Finalization & Documentation

*   `[ ]` **(SETUP)** **API Documentation with Swagger:**
    *   `[ ]` Install `@nestjs/swagger`.
        ```bash
        cd nest-app && npm install @nestjs/swagger && cd ..
        ```
    *   `[ ]` In `nest-app/src/main.ts`, configure the Swagger module to generate interactive API documentation.
    *   `[ ]` Annotate DTOs (Data Transfer Objects) and controller methods across the application with Swagger decorators.

*   `[ ]` **(SETUP)** **Testing:**
    *   `[ ]` Write unit tests for services, mocking the `PrismaService`.
    *   `[ ]` Write E2E tests for controllers to test the full request/response flow against a separate test database instance if possible.

*   `[ ]` **(SETUP)** **Final Review & Cleanup:**
    *   `[ ]` Review all code, ensuring consistency and proper use of NestJS/Prisma patterns.
    *   `[ ]` Verify all `.env` variables are being loaded and used correctly.
    *   `[ ]` Delete all old Laravel project files and documentation from the root directory, leaving only the `nest-app` folder and root configuration files (`.gitignore`, etc.).
    *   `[ ]` Update the root `README.md` to describe the new Nest.js project.