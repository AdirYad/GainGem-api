<?php

namespace App\Providers;

use App\Models\CompletedTask;
use App\Models\LoginLog;
use App\Observers\CompletedTaskObserver;
use App\Observers\LoginLogObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        CompletedTask::observe(CompletedTaskObserver::class);
        LoginLog::observe(LoginLogObserver::class);
    }
}
