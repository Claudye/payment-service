<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\Wallets\WalletController;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Gate::define('invite-members', function ($user, $team) {
            return $user->id === $team->user_id || $user->hasRole('Owner', $team);
        });

        Gate::define('manage-team', function ($user, $team) {
            return $user->id === $team->user_id || $user->hasRole('Owner', $team);
        });
    }
}
