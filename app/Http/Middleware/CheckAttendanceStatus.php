<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckAttendanceStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $isAttendanceEnabled = Cache::get('attendance_enabled', true);
        $user = $request->user();
        if (!$isAttendanceEnabled && (!$user || $user->role !== 'other')) {
            return redirect()->route('dashboard')->with('error', 'Fitur absensi sedang dinonaktifkan oleh Manager.');
        }
        return $next($request);
    }
}