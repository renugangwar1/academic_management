<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;     // <-- Add this
use Illuminate\Support\Facades\Auth;     // <-- Add this
use App\Models\Message;                   // <-- Add this

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot()
    {
       View::composer('*', function ($view) {
    $unreadCount = 0;

    if (Auth::guard('web')->check()) {  // or 'admin' if you use admin guard
        $user = Auth::guard('web')->user();

        // For debug, remove or adjust role check as needed
        // if ($user->hasRole('admin')) {  
            $unreadCount = \App\Models\Message::where('is_read', false)->count();
        // }
    }

    $view->with('unreadMessageCount', $unreadCount);
});
    }
}
