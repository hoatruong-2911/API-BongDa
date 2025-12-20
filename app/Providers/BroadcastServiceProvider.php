<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Kích hoạt các routes cần thiết cho Pusher xác thực token
        Broadcast::routes(); 

        // Load các định nghĩa kênh từ routes/channels.php
        require base_path('routes/channels.php');
    }
}
