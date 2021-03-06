<?php

namespace App\Providers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        Route::bind('supplier', static function ($value) {
            return User::where('id', $value)
                ->where('role', User::ROLE_SUPPLIER)
                ->firstOrFail();
        });

        Route::bind('giftCardTransaction', static function ($value) {
            return Transaction::where('id', $value)
                ->where('type', Transaction::TYPE_GIFT_CARD)
                ->firstOrFail();
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: get_ip());
        });
    }
}
