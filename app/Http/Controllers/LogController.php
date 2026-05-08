<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Inertia\Inertia;
use App\Models\Log;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $cacheKey = "user_logs_page_{$page}";
        
        $logs = \Cache::remember($cacheKey, 1800, function() use ($page) {
            return Log::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();
        });

        return Inertia::render('Log/Index', compact('logs'));   
    }
}