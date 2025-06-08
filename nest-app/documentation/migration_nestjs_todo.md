Understood. This is a crucial clarification for working with a code-generation-only LLM. The model will generate code and file content, but you (the user) will be responsible for running all commands.

The plan is now updated to reflect this workflow. All commands are explicitly marked as **(User Action)**, and instructions are structured as prompts you would give to the LLM.

---

# Migration Plan: Laravel to Nest.js, Prisma, and Supabase (Code Generation Only)

**Project Goal:** To generate the complete source code for migrating the existing Laravel backend to a Nest.js backend within a `./nest-app` directory.

**Workflow:**
1.  **User (You):** Execute any command-line actions marked as **(User Action)**.
2.  **LLM (AI Model):** Generate only the code or file content for each step when prompted.

**IMPORTANT NOTE:** All file paths in the instructions are relative to the project root (e.g., `nest-app/src/main.ts`).

**Legend:**
*   `[ ]` - To Do
*   `(DB)` - Database-related task
*   `(AUTH)` - Authentication-related task
*   `(API)` - API Endpoint/Controller task
*   `(LOGIC)` - Business logic/Service task
*   `(SETUP)` - Project configuration or setup task

---

## Phase 0: Project Foundation & Environment Setup

*   `[ ]` **(SETUP)** **Initialize Nest.js Project in `nest-app` Directory:**
    *   **_** (User Action)**_** From the project root, run the NestJS CLI:
        ```bash
        npx @nestjs/cli new nest-app
        ```

*   `[ ]` **(SETUP)** **Supabase Project Setup:**
    *   **_** (User Action)**_** Go to [supabase.com](https://supabase.com), create a new project, and note the following:
        *   Database Connection String (URI)
        *   Project URL
        *   `anon` public key

*   `[ ]` **(SETUP)** **Environment Configuration:**
    *   `[ ]` **Prompt to LLM:** "Generate the content for the file `nest-app/.env` with placeholders for Supabase and Stripe credentials."
        *   **Expected LLM Output:**
          ```env
          # Supabase
          DATABASE_URL="<PASTE_YOUR_SUPABASE_CONNECTION_STRING_URI_HERE>"
          SUPABASE_URL="<PASTE_YOUR_SUPABASE_PROJECT_URL_HERE>"
          SUPABASE_ANON_KEY="<PASTE_YOUR_SUPABASE_ANON_KEY_HERE>"

          # Stripe
          STRIPE_SECRET_KEY="sk_test_..."
          STRIPE_WEBHOOK_SECRET="whsec_..."
          
          # App
          PORT=3000
          ```
    *   **_** (User Action)**_** Manually paste your credentials into the generated `nest-app/.env` file.

*   `[ ]` **(SETUP)** **Prisma Integration:**
    *   **_** (User Action)**_** Install Prisma dependencies:
        ```bash
        cd nest-app && npm install prisma --save-dev && npm install @prisma/client && cd ..
        ```
    *   **_** (User Action)**_** Initialize Prisma:
        ```bash
        npx prisma init --schema-path=./nest-app/prisma/schema.prisma
        ```
    *   `[ ]` **Prompt to LLM:** "Generate the content for `nest-app/prisma/schema.prisma` to configure the PostgreSQL datasource and client generator."
        *   **Expected LLM Output:**
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
    *   **_** (User Action)**_** Generate the necessary modules, services, and controllers using the NestJS CLI from the project root.
        ```bash
        # Common Services
        nest g module nest-app/src/common
        nest g service nest-app/src/common/prisma --no-spec
        nest g service nest-app/src/common/supabase --no-spec
        
        # Feature Modules
        nest g module nest-app/src/authentication && nest g controller nest-app/src/authentication --no-spec && nest g service nest-app/src/authentication --no-spec
        nest g module nest-app/src/user-management && nest g controller nest-app/src/user-management --no-spec && nest g service nest-app/src/user-management --no-spec
        nest g module nest-app/src/subscription-billing && nest g controller nest-app/src/subscription-billing --no-spec && nest g service nest-app/src/subscription-billing --no-spec
        nest g module nest-app/src/content-management && nest g controller nest-app/src/content-management --no-spec && nest g service nest-app/src/content-management --no-spec
        nest g module nest-app/src/notes-service && nest g controller nest-app/src/notes-service --no-spec && nest g service nest-app/src/notes-service --no-spec
        nest g module nest-app/src/protocol-engine && nest g controller nest-app/src/protocol-engine --no-spec && nest g service nest-app/src/protocol-engine --no-spec
        nest g module nest-app/src/tracking-service && nest g controller nest-app/src/tracking-service --no-spec && nest g service nest-app/src/tracking-service --no-spec
        ```
    *   `[ ]` **Prompt to LLM:** "Generate the implementation for the PrismaService in `nest-app/src/common/prisma/prisma.service.ts`."
        *   **(User Action)** Place the generated code in the file.

---

## Phase 1: User Authentication with Supabase

*   `[ ]` **(AUTH)** **Supabase Client Library:**
    *   **_** (User Action)**_** Install the Supabase client library:
        ```bash
        cd nest-app && npm install @supabase/supabase-js && cd ..
        ```

*   `[ ]` **(AUTH)** **Supabase Service Implementation:**
    *   `[ ]` **Prompt to LLM:** "Generate the implementation for the SupabaseService in `nest-app/src/common/supabase/supabase.service.ts` to initialize the client using environment variables."

*   `[ ]` **(DB)** **Prisma User Schema:**
    *   `[ ]` **Prompt to LLM:** "Based on the Laravel User model and the need to sync with Supabase Auth, generate the `User` model for `nest-app/prisma/schema.prisma`." (Provide the old Laravel model for context if needed).

*   `[ ]` **(DB)** **User Sync Trigger (Manual Step):**
    *   `[ ]` **Prompt to LLM:** "Generate the SQL function and trigger needed to sync new users from `auth.users` to `public.users` in Supabase."
    *   **_** (User Action)**_** Copy the generated SQL and run it in the Supabase "SQL Editor".

*   `[ ]` **(API)** **Authentication Endpoints:**
    *   `[ ]` **Prompt to LLM:** "Generate the code for `nest-app/src/authentication/authentication.controller.ts` and `authentication.service.ts` to implement the `register`, `login`, and `logout` endpoints using the SupabaseService."

*   `[ ]` **(AUTH)** **Authentication Guard:**
    *   `[ ]` **Prompt to LLM:** "Generate a NestJS `AuthGuard` named `supabase-auth.guard.ts` inside `nest-app/src/authentication/guards/`. It should use the SupabaseService to validate the JWT from the Authorization header."

---




## Phase 2: Database Schema Migration

*   `[ ]` **(DB)** **Translate Laravel Migrations to Prisma Schema:**
    *   `[ ]` For each Laravel migration file:
        *   `[ ]` **Prompt to LLM:** "Here is the Laravel migration content for `create_plans_table`. Generate the corresponding Prisma model to be added to `nest-app/prisma/schema.prisma`."
        *   **_** (User Action)**_** Copy the generated model into your `schema.prisma` file.
        *   Repeat for all tables: `subscriptions`, `episodes`, `protocols`, `summaries`, `notes`, `note_categories`, `note_tags`, `user_reminders`, `routines`, `routine_steps`, `tracking_logs`, `posts`, `comments`, and all pivot tables. Ensure you prompt the LLM to add `@relation` attributes for foreign keys.

*   `[ ]` **(DB)** **Run Prisma Migration:**
    *   **_** (User Action)**_** Review the complete `nest-app/prisma/schema.prisma` file for accuracy.
    *   **_** (User Action)**_** Create and apply the migration against your Supabase database. This command will create the tables.
        ```bash
        npx prisma migrate dev --schema=./nest-app/prisma/schema.prisma --name "initial-schema"
        ```

*   `[ ]` **(DB)** **Create Seed Script:**
    *   `[ ]` **Prompt to LLM:** "Here is the content of the Laravel `PlanSeeder.php`. Generate the TypeScript code for a Prisma seed script in `nest-app/prisma/seed.ts` that accomplishes the same thing."
    *   `[ ]` Repeat this process for all other Laravel seeders (`EpisodeSeeder`, `ProtocolSeeder`, etc.), appending the logic to the `seed.ts` file.
    *   **_** (User Action)**_** Run the seeder to populate your database.
        ```bash
        npx prisma db seed --schema=./nest-app/prisma/schema.prisma
        ```

---

## Phase 3-6: Feature & API Endpoint Migration

**For each module (Content, Billing, Notes, etc.):**

1.  **Service Logic:**
    *   `[ ]` **Prompt to LLM:** "Here is the Laravel `ContentService.php`. Generate the equivalent `ContentService.ts` in `nest-app/src/content-management/` using PrismaService for database queries." (Repeat for each service).
2.  **Controller Logic:**
    *   `[ ]` **Prompt to LLM:** "Here is the Laravel `ProtocolController.php`. Generate the equivalent `ProtocolController.ts` in `nest-app/src/content-management/`. It should use the `ContentService` and apply the `AuthGuard` and a placeholder `PremiumGuard` where necessary." (Repeat for each controller).
3.  **DTOs (Data Transfer Objects):**
    *   `[ ]` **Prompt to LLM:** "Here is the Laravel `StoreRoutineRequest.php`. Generate the equivalent NestJS DTO class with validation decorators (`class-validator`) in a file named `nest-app/src/protocol-engine/dto/store-routine.dto.ts`." (Repeat for each request validation file).
4.  **Guards:**
    *   `[ ]` **Prompt to LLM:** "Generate the code for the `PremiumGuard` in `nest-app/src/common/guards/premium.guard.ts`. It should inject PrismaService, get the user from the request, and check their subscription status, throwing a `ForbiddenException` if they are not premium."

---

## Phase 7: Finalization & Documentation

*   `[ ]` **(SETUP)** **API Documentation with Swagger:**
    *   **_** (User Action)**_** Install Swagger dependencies:
        ```bash
        cd nest-app && npm install @nestjs/swagger && cd ..
        ```
    *   `[ ]` **Prompt to LLM:** "Generate the necessary code to add Swagger (OpenAPI) documentation setup to the `nest-app/src/main.ts` file."
    *   `[ ]` For each DTO and Controller:
        *   `[ ]` **Prompt to LLM:** "Add the necessary `@Api...` decorators from `@nestjs/swagger` to this DTO file to document its properties."
        *   `[ ]` **Prompt to LLM:** "Add the necessary `@ApiOperation` and `@ApiResponse` decorators to this Controller file to document its endpoints."

*   `[ ]` **(SETUP)** **Final Review & Cleanup:**
    *   **_** (User Action)**_** Manually review all generated code for correctness.
    *   **_** (User Action)**_** Delete the old Laravel project files and documentation.
    *   `[ ]` **Prompt to LLM:** "Generate a new `README.md` for the project root that describes the Nest.js application in the `nest-app` directory."