<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $page = $request->query('page', 1);
        $logVersion = Cache::get('logs_cache_version', 1);

        $cacheKey = "all_log_l{$logVersion}_pg{$page}";
        $logs = Cache::remember($cacheKey, 1800, function () {
            return Log::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();
        });

        return Inertia::render('Log/Index', compact('logs'));   
    }
}