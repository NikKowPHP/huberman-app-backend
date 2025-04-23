
**Huberman App: Database Migrations Plan**

**1. Introduction**

This document outlines the plan for translating the conceptual SQL schema defined in `database_plan.md` into executable Laravel migration files. These migrations will provide a version-controlled, repeatable, and database-agnostic way (within Laravel's supported databases) to define and evolve the application's database structure.

**2. Process Overview**

1.  **Review SQL Schema:** Thoroughly review the `database_plan.md` file to understand table structures, data types, constraints (NOT NULL, UNIQUE), foreign key relationships, `ON DELETE` behavior, and indexes.
2.  **Map SQL to Laravel Schema Builder:** Translate each `CREATE TABLE` and `CREATE INDEX` statement into the corresponding Laravel `Schema` facade methods (`Schema::create`, `$table->id()`, `$table->string()`, `$table->foreignId()`, `$table->constrained()`, `$table->unique()`, `$table->index()`, `$table->timestamps()`, `$table->softDeletes()`, etc.).
3.  **Determine Migration Order:** Create migrations in an order that respects foreign key constraints. Tables must exist before they can be referenced by a foreign key.
    *   Order: `users`, `plans`, `episodes`, `protocols` -> `subscriptions`, `episode_protocol`, `summaries`, `notes` -> `user_protocol_tracking` (if implemented). Standard Laravel tables (`password_resets`, `jobs`, `failed_jobs`) can usually be created anytime or might already exist.
4.  **Generate Migration Files:** Use the `php artisan make:migration create_table_name_table` command for each table.
5.  **Implement `up()` Method:** Populate the `up()` method in each migration file with the `Schema::create(...)` logic derived in step 2.
6.  **Implement `down()` Method:** Populate the `down()` method with the corresponding `Schema::dropIfExists('table_name')` to ensure migrations can be rolled back cleanly.
7.  **Refine Constraints and Indexes:** Double-check:
    *   **Data Types:** Use appropriate types (`string`, `text`, `integer`, `bigInteger`, `boolean`, `date`, `timestamp`, `jsonb` for Postgres).
    *   **Nullability:** Apply `->nullable()` where needed.
    *   **Uniqueness:** Use `->unique()`.
    *   **Defaults:** Use `->default(...)`.
    *   **Foreign Keys:** Use `->foreignId('column')->constrained('related_table')->onDelete('action')`. Choose `cascade`, `restrict`, `set null`, or `no action` carefully based on relationships (e.g., `cascade` for user-owned data like notes/subscriptions, `restrict` for plans linked to active subscriptions).
    *   **Indexes:** Add `->index()` for frequently queried columns (FKs often indexed automatically by `constrained()`, status fields, categories, dates). Use array syntax for composite indexes (`->index(['col1', 'col2'])`).
    *   **Timestamps/Soft Deletes:** Use `$table->timestamps()` and `$table->softDeletes()` where appropriate.
8.  **Testing:** Run `php artisan migrate` on a local development database to verify syntax and execution. Test rollbacks using `php artisan migrate:rollback`.
9.  **Version Control:** Commit the generated migration files to the Git repository.

**3. Migration File Structure (Conceptual)**

Below is the conceptual structure for each required migration file. Specific commands to generate them are included.

**(Note:** Assumes a fresh Laravel project structure. Some standard migrations like `users`, `password_resets`, `failed_jobs` might pre-exist and need modification instead of creation.)

---

**1. Create Users Table**

*   **Command:** `php artisan make:migration create_users_table`
*   **File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Bigint, unsigned, auto-increment, primary key
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profile_picture_url', 2048)->nullable();
            $table->rememberToken();
            $table->timestamps(); // created_at, updated_at (nullable timestamps)
            $table->softDeletes(); // deleted_at (nullable timestamp)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

---

**2. Create Plans Table**

*   **Command:** `php artisan make:migration create_plans_table`
*   **File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_plans_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id(); // Standard auto-incrementing primary key
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('stripe_product_id')->nullable()->unique(); // Ensure unique if provider used
            $table->string('apple_product_id')->nullable()->unique();
            $table->string('google_product_id')->nullable()->unique();
            $table->jsonb('features')->nullable(); // Use jsonb for PostgreSQL efficiency
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
```

---

**3. Create Subscriptions Table**

*   **Command:** `php artisan make:migration create_subscriptions_table`
*   **File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_subscriptions_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id(); // Bigint primary key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // FK to users
            $table->foreignId('plan_id')->constrained('plans')->onDelete('restrict'); // FK to plans
            $table->string('status', 50)->index(); // e.g., active, canceled
            $table->string('provider', 50); // e.g., stripe, apple, google
            $table->string('provider_id'); // Subscription ID from provider
            $table->string('provider_plan_id')->nullable(); // Plan ID from provider
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable()->index(); // Current period end or cancellation date
            $table->timestamps();
            $table->softDeletes(); // Keep record even if canceled by user action immediately

            // Composite index for efficient webhook lookups
            $table->index(['provider', 'provider_id']);
            // Optional: Unique constraint depending on provider ID handling
            // $table->unique(['user_id', 'provider', 'provider_id']); // Re-evaluate if needed
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
```

---

**4. Create Episodes Table**

*   **Command:** `php artisan make:migration create_episodes_table`
*   **File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_episodes_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 512);
            $table->string('podcast_source')->default('Huberman Lab');
            $table->integer('episode_number')->nullable();
            $table->date('published_at')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('audio_url', 2048)->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
```

---

**5. Create Protocols Table**

*   **Command:** `php artisan make:migration create_protocols_table`
*   **File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_protocols_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('protocols', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description');
            $table->string('category', 100)->nullable()->index();
            $table->text('implementation_guide')->nullable();
            $table->jsonb('research_links')->nullable(); // Use jsonb for PostgreSQL
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocols');
    }
};
```

---

**6. Create Episode-Protocol Pivot Table**

*   **Command:** `php artisan make:migration create_episode_protocol_table`
*   **File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_episode_protocol_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episode_protocol', function (Blueprint $table) {
            $table->foreignId('episode_id')->constrained('episodes')->onDelete('cascade');
            $table->foreignId('protocol_id')->constrained('protocols')->onDelete('cascade');

            // Define composite primary key
            $table->primary(['episode_id', 'protocol_id']);

            // No timestamps by default for basic pivot
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episode_protocol');
    }
};
```

---

**7. Create Summaries Table**

*   **Command:** `php artisan make:migration create_summaries_table`
*   **File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_summaries_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summaries', function (Blueprint $table) {
            $table->id();
            // Foreign key automatically indexed by constrained()
            $table->foreignId('episode_id')->constrained('episodes')->onDelete('cascade');
            // Optional: Protocol ID if summary is protocol-specific within episode
            // $table->foreignId('protocol_id')->nullable()->constrained('protocols')->onDelete('set null');
            $table->text('content');
            $table->string('type', 50)->default('summary')->index(); // Index type for filtering
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summaries');
    }
};
```

---

**8. Create Notes Table**

*   **Command:** `php artisan make:migration create_notes_table`
*   **File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_notes_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('episode_id')->constrained('episodes')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            // Composite index for fetching user's notes for an episode
            $table->index(['user_id', 'episode_id']);
            // Index for finding public notes for an episode (community feature)
            $table->index(['episode_id', 'is_public']); // Basic index; partial index requires raw SQL if essential
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
```

---

**9. (Optional) Create User Protocol Tracking Table**

*   **Command:** `php artisan make:migration create_user_protocol_tracking_table`
*   **File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_user_protocol_tracking_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Needed for default date

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_protocol_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('protocol_id')->constrained('protocols')->onDelete('cascade');
            // Use default date function appropriate for the database
            $table->date('tracked_at')->default(DB::raw('CURRENT_DATE'));
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable(); // Use jsonb for PostgreSQL
            $table->timestamps(); // Use standard created_at/updated_at

            // Prevent duplicate tracking entries for the same user, protocol, and day
            $table->unique(['user_id', 'protocol_id', 'tracked_at']);
            // Note: The unique constraint implicitly creates an index covering (user_id, protocol_id, tracked_at)
            // A separate index on (user_id, protocol_id) is usually redundant.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_protocol_tracking');
    }
};
```

---

**10. Standard Laravel Tables (If needed)**

*   **Password Resets:** `php artisan make:migration create_password_resets_table` (Check if exists first)
*   **Jobs:** `php artisan queue:table` then `php artisan migrate`
*   **Failed Jobs:** `php artisan make:migration create_failed_jobs_table` (Check if exists first)

---

**4. Execution**

After creating and reviewing these files:

1.  Ensure your `.env` file is configured with the correct database connection details.
2.  Run `php artisan migrate` from your project root in the terminal.
3.  Laravel will execute the `up()` method of each pending migration in chronological order.

**5. Outcome**

The result will be a database schema matching the application's requirements, managed entirely through version-controlled Laravel migration files. This allows for consistent setup across development, staging, and production environments, and provides a clear history of schema changes.
