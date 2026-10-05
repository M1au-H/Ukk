<?php

use Illuminate\Support\Facades\Broadcast;

/**
 * Cuma channel PRIVATE yang perlu didaftarkan di sini. Channel publik
 * ("order.{session_token}" dan "menu-availability") tidak butuh entry
 * apa pun di file ini — Laravel otomatis izinkan siapa saja subscribe.
 */
Broadcast::channel('kitchen', function ($user) {
    return in_array($user->role, ['kitchen', 'admin']);
});
