<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Repositories\Contracts\UserRepositoryInterface::class, \App\Repositories\UserRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\PermissionRepositoryInterface::class, \App\Repositories\PermissionRepository::class);
        //:end-bindings:
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
