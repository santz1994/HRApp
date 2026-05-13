<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\EmployeeRepository;
use App\Repositories\UserRepository;
use App\Services\EmployeeService;
use App\Services\AuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Repository implementations
        $this->app->bind('App\Repositories\EmployeeRepository', function ($app) {
            return new EmployeeRepository(new \App\Models\Employee());
        });

        $this->app->bind('App\Repositories\UserRepository', function ($app) {
            return new UserRepository(new \App\Models\User());
        });

        // Bind Service implementations
        $this->app->bind('App\Services\EmployeeService', function ($app) {
            return new EmployeeService(
                $app->make('App\Repositories\EmployeeRepository')
            );
        });

        $this->app->bind('App\Services\AuthService', function ($app) {
            return new AuthService(
                $app->make('App\Repositories\UserRepository')
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
