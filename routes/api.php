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
    // Include route files from modules
    $modulesPath = app_path('Modules');
    $moduleDirectories = glob($modulesPath . '/*', GLOB_ONLYDIR);

    foreach ($moduleDirectories as $moduleDirectory) {
        $moduleName = basename($moduleDirectory);
        $routeFilePath = $moduleDirectory . '/routes/api.php';

        if (file_exists($routeFilePath)) {
            Route::group(['prefix' => strtolower($moduleName)], function () use ($routeFilePath) {
                require $routeFilePath;
            });
        }
    }
});
