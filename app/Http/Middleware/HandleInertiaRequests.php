<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Cache;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user()?->load('skills'),
            ],

            'attendance_enabled' => Cache::get('attendance_enabled', true),

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'warning' => fn () => $request->session()->get('warning'),
                'import_errors' => fn () => $request->session()->get('import_errors'),
            ],

            'notifications' => fn () => $request->user() 
                ? $request->user()->notifications()->take(10)->get() 
                : [],
            'unreadNotificationCount' => fn () => $request->user() 
                ? $request->user()->unreadNotifications()->count() 
                : 0,
        ]);
    }
}