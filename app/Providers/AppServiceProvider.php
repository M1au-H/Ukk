<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Project ini pakai Sanctum token (Bearer), bukan session/cookie berbasis web,
        // jadi endpoint /broadcasting/auth WAJIB divalidasi pakai guard 'sanctum',
        // bukan default 'web' (yang mengandalkan cookie session dan tidak akan
        // mengenali token Bearer yang dikirim dari Postman atau Vue app nanti).
        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        require base_path('routes/channels.php');
    }
}
