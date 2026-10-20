<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use custom middleware so broadcasting auth can resolve Sanctum bearer tokens
        // without requiring session-based auth. This allows the SPA to authenticate
        // presence/private channels using the stored Bearer token.
        Broadcast::routes(['middleware' => ['resolve.multi.guard']]);

        require base_path('routes/channels.php');
    }
}
