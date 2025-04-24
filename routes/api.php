<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('v1')->group(function () {
    $modulesPath = app_path('Modules'); // Should resolve to /var/www/html/app/Modules inside Docker
    $moduleDirectories = glob($modulesPath . '/*', GLOB_ONLYDIR); // Finds subdirectories like 'Authentication'

    // Add a debug statement here temporarily if needed:
    // Log::info('Module Directories Found: ' . print_r($moduleDirectories, true));

    foreach ($moduleDirectories as $moduleDirectory) {
        $routeFilePath = $moduleDirectory . '/routes/api.php'; // Correctly constructs path

        // Add a debug statement here temporarily if needed:
        // Log::info('Checking for route file: ' . $routeFilePath);

        if (file_exists($routeFilePath)) { // Checks if the file exists
            Log::info('Attempting to include route file: ' . $routeFilePath);
            require $routeFilePath; // Includes the file (like the Authentication one)
            Log::info('Successfully included route file: ' . $routeFilePath);
        } else {
            Log::warning('Route file NOT found: ' . $routeFilePath);
        }
    }
});
