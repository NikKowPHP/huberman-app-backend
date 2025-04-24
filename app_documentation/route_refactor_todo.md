**Refactor Route Registration to Use Service Providers**

**Goal:** Replace dynamic route loading in `routes/api.php` with Module Service Providers for improved structure and compatibility with Laravel route caching.

---

**Phase 1: Create Module Route Service Providers**

*   `[ ]` **Identify Modules:** List all modules in `app/Modules/` that contain a `routes/api.php` file (or will need API routes).
    *   *Initial List:* `Authentication`, `UserManagement`, `SubscriptionBilling`, `ContentManagement`, `NotesService`, `ProtocolEngine`. Add others if they exist/are planned.
*   `[ ]` **Create Provider (Authentication Module):**
    *   `[ ]` Create the directory `app/Modules/Authentication/Providers/`.
    *   `[ ]` Create the file `app/Modules/Authentication/Providers/RouteServiceProvider.php`.
    *   `[ ]` Populate `Authentication/Providers/RouteServiceProvider.php` with the basic structure, extending `Illuminate\Foundation\Support\Providers\RouteServiceProvider`.
*   `[ ]` **Create Provider (UserManagement Module):**
    *   `[ ]` Create the directory `app/Modules/UserManagement/Providers/`.
    *   `[ ]` Create the file `app/Modules/UserManagement/Providers/RouteServiceProvider.php`.
    *   `[ ]` Populate `UserManagement/Providers/RouteServiceProvider.php` with the basic structure.
*   `[ ]` **Create Provider (SubscriptionBilling Module):**
    *   `[ ]` Create the directory `app/Modules/SubscriptionBilling/Providers/`.
    *   `[ ]` Create the file `app/Modules/SubscriptionBilling/Providers/RouteServiceProvider.php`.
    *   `[ ]` Populate `SubscriptionBilling/Providers/RouteServiceProvider.php` with the basic structure.
*   `[ ]` **Create Provider (ContentManagement Module):**
    *   `[ ]` Create the directory `app/Modules/ContentManagement/Providers/`.
    *   `[ ]` Create the file `app/Modules/ContentManagement/Providers/RouteServiceProvider.php`.
    *   `[ ]` Populate `ContentManagement/Providers/RouteServiceProvider.php` with the basic structure.
*   `[ ]` **Create Provider (NotesService Module):**
    *   `[ ]` Create the directory `app/Modules/NotesService/Providers/`.
    *   `[ ]` Create the file `app/Modules/NotesService/Providers/RouteServiceProvider.php`.
    *   `[ ]` Populate `NotesService/Providers/RouteServiceProvider.php` with the basic structure.
*   `[ ]` **Create Provider (ProtocolEngine Module):**
    *   `[ ]` Create the directory `app/Modules/ProtocolEngine/Providers/`.
    *   `[ ]` Create the file `app/Modules/ProtocolEngine/Providers/RouteServiceProvider.php`.
    *   `[ ]` Populate `ProtocolEngine/Providers/RouteServiceProvider.php` with the basic structure.
*   `[ ]` *(Repeat for any other modules identified)*

---

**Phase 2: Implement Route Loading in Module Providers**

*   `[ ]` **Implement (Authentication Module):**
    *   `[ ]` Edit `Authentication/Providers/RouteServiceProvider.php`.
    *   `[ ]` Implement the `boot()` method.
    *   `[ ]` Inside `boot()`, use `Route::middleware('api')->prefix('v1/auth')->group(base_path('app/Modules/Authentication/routes/api.php'));` (Adjust prefix `v1/auth` if a different convention is desired, e.g., just `v1`).
*   `[ ]` **Implement (UserManagement Module):**
    *   `[ ]` Edit `UserManagement/Providers/RouteServiceProvider.php`.
    *   `[ ]` Implement `boot()`.
    *   `[ ]` Inside `boot()`, use `Route::middleware('api')->prefix('v1/user')->group(base_path('app/Modules/UserManagement/routes/api.php'));` (Adjust prefix).
*   `[ ]` **Implement (SubscriptionBilling Module):**
    *   `[ ]` Edit `SubscriptionBilling/Providers/RouteServiceProvider.php`.
    *   `[ ]` Implement `boot()`.
    *   `[ ]` Inside `boot()`, use `Route::middleware('api')->prefix('v1/billing')->group(base_path('app/Modules/SubscriptionBilling/routes/api.php'));` (Adjust prefix, consider separate prefixes for `/plans`, `/user/subscription`, `/webhooks`). *Note: Webhook routes might need CSRF exemption applied here or in the route file.*
*   `[ ]` **Implement (ContentManagement Module):**
    *   `[ ]` Edit `ContentManagement/Providers/RouteServiceProvider.php`.
    *   `[ ]` Implement `boot()`.
    *   `[ ]` Inside `boot()`, use `Route::middleware('api')->prefix('v1')->group(base_path('app/Modules/ContentManagement/routes/api.php'));` (Using just `/v1` as routes likely are `/protocols`, `/episodes`).
*   `[ ]` **Implement (NotesService Module):**
    *   `[ ]` Edit `NotesService/Providers/RouteServiceProvider.php`.
    *   `[ ]` Implement `boot()`.
    *   `[ ]` Inside `boot()`, use `Route::middleware('api')->prefix('v1/notes')->group(base_path('app/Modules/NotesService/routes/api.php'));` (Adjust prefix).
*   `[ ]` **Implement (ProtocolEngine Module):**
    *   `[ ]` Edit `ProtocolEngine/Providers/RouteServiceProvider.php`.
    *   `[ ]` Implement `boot()`.
    *   `[ ]` Inside `boot()`, use `Route::middleware('api')->prefix('v1/engine')->group(base_path('app/Modules/ProtocolEngine/routes/api.php'));` (Adjust prefix, e.g., `/reminders`, `/tracking`).
*   `[ ]` *(Repeat for any other modules identified)*

---

**Phase 3: Register Module Service Providers**

*   `[ ]` Edit the main application providers file: `bootstrap/providers.php`.
*   `[ ]` Add the fully qualified class name for each created module RouteServiceProvider to the `return [...]` array. Example:
    ```php
    return [
        App\Providers\AppServiceProvider::class,
        App\Modules\Authentication\Providers\RouteServiceProvider::class,
        App\Modules\UserManagement\Providers\RouteServiceProvider::class,
        App\Modules\SubscriptionBilling\Providers\RouteServiceProvider::class,
        App\Modules\ContentManagement\Providers\RouteServiceProvider::class,
        App\Modules\NotesService\Providers\RouteServiceProvider::class,
        App\Modules\ProtocolEngine\Providers\RouteServiceProvider::class,
        // ... add other module providers here
    ];
    ```

---

**Phase 4: Clean Up Main Route File**

*   `[ ]` Edit the main API routes file: `routes/api.php`.
*   `[ ]` Delete the `glob(...)` loop and the `require $routeFilePath;` line within it.
*   `[ ]` Remove the `Route::prefix('v1')->group(...)` wrapper *unless* you decided module providers should *not* handle the `v1` prefix themselves. (Based on Phase 2, the wrapper can likely be removed).
*   `[ ]` Keep the default `Route::middleware('auth:sanctum')->get('/user', ...);` route if still needed at the top level.

---

**Phase 5: Verification**

*   `[ ]` **Clear Caches:** Run `php artisan route:clear` and `php artisan config:clear`.
*   `[ ]` **List Routes:** Run `php artisan route:list`. Verify that all routes from the modules (e.g., `POST v1/auth/register`, `GET v1/user/profile`, `GET v1/protocols`, etc.) are listed correctly with the expected prefixes and middleware (`api`).
*   `[ ]` **Test Caching:**
    *   `[ ]` Run `php artisan route:cache`. Ensure it completes without errors.
    *   `[ ]` Run `php artisan route:list` again. Verify the routes are still listed correctly from the cache.
    *   `[ ]` Run `php artisan route:clear` again to leave the cache clear for development/testing.
*   `[ ]` **Run Tests:** Execute the full test suite (`composer test` or `php artisan test`). Pay close attention to the `RegistrationTest` and any other API feature tests. They should now pass (assuming no other underlying issues).

---