<?php

namespace App\Modules\NotesService\Providers;

use App\Modules\NotesService\Contracts\NoteServiceInterface;
use App\Modules\NotesService\Services\NoteService;
use Illuminate\Support\ServiceProvider;

class NotesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(NoteServiceInterface::class, NoteService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
