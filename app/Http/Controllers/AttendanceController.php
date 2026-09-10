<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceMultiSheetExport;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = $request->query();
        $page = $request->query('page', 1);
        
        $attendanceVersion = Cache::get('attendances_cache_version', 1);
        $userId = $query['user_id'] ?? 'all';
        $fromStr = $query['from'] ?? 'start';
        $toStr = $query['to'] ?? 'end';

        $cacheKey = "all_attendance_a{$attendanceVersion}_u{$userId}_from{$fromStr}_to{$toStr}_pg{$page}";
        $attendances = Cache::remember($cacheKey, 1800, function () use ($query) {
            $attendanceQuery = Attendance::with('user')->orderBy('check_in_time', 'desc');

            if (isset($query['user_id'])) {
                $attendanceQuery->where('user_id', $query['user_id']);
            }

            if (!empty($query['from']) && !empty($query['to'])) {
                try {
                    $from = Carbon::createFromFormat('d-m-Y', $query['from'])->startOfDay();
                    $to = Carbon::createFromFormat('d-m-Y', $query['to'])->endOfDay();
                    $attendanceQuery->whereBetween('check_in_time', [$from, $to]);
                } catch (\Exception $err) {
                    // Ignore format error and fallback to default query
                }
            }

            return $attendanceQuery->paginate(10)->withQueryString();
        });

        $userVersion = Cache::get('users_cache_version', 1);
        $users = Cache::remember("all_user_u{$userVersion}", 1800, function () {
            return User::orderBy('name')->get();
        });

        return Inertia::render('Attendance/Index', compact('attendances', 'users'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image'     => 'required|string',
            'type'      => 'required|in:check_in,check_out',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'work_type' => 'required|in:wfo,wfa', 
        ]);

        if (!$request->latitude || !$request->longitude) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Location not found. Please allow location access (GPS) on your browser or device.'
            ], 400);
        }

        $verification = $this->verifyFace($request->image);

        $identifier = $verification['user_id'] ?? $request->ip();
        $cooldownKey = 'attendance_cooldown_' . $identifier;

        if (Cache::has($cooldownKey)) {
            return response()->json([
                'status'  => 'cooldown',
                'message' => 'Please wait a moment before checking in again.',
            ], 429);
        }

        Cache::put($cooldownKey, true, 2);

        if (!$verification || !isset($verification['success'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'The verification process failed to run'
            ], 500);
        }

        if (!$verification['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => $verification['message'],
            ], 401);
        }

        $user = User::find($verification['user_id']);

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'The user with ID' . $verification['user_id'] . ' was not found in the database',
            ], 404);
        }

        if ($request->work_type === 'wfa') {
            if (!$user->is_wfa_allowed) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'You do not have access to the WFA attendance system. Please contact your manager.'
                ], 403);
            }
        } else {
            if ($user->is_wfa_allowed) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'You have been granted WFA access. Please change your attendance type to WFA.'
                ], 403);
            }

            $officeLat = -7.2649722;
            $officeLon = 112.7472500;
            $maxDistance = 100;

            $distance = $this->calculateDistance($request->latitude, $request->longitude, $officeLat, $officeLon);

            if ($distance > $maxDistance) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Your status is WFO, but you are outside the office`s range (Distance: ' . round($distance) . ' meters). WFO check-ins can only be made within a radius of ' . $maxDistance . ' meters.'
                ], 403);
            }
        }

        $address = $this->getAddress($request->latitude, $request->longitude);
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $today)
            ->first();

        $confidence = $verification['confidence'];

        if ($request->type === 'check_in') {
            if ($attendance) {
                $now = Carbon::parse($attendance->check_in_time)->format('H:i:s');

                return response()->json([
                    'status'  => 'error',
                    'name'    => $user->name,
                    'message' => 'You Have Already Checked In at ' . $now
                ]);
            }

            Attendance::create([
                'user_id'              => $user->id,
                'check_in_time'        => Carbon::now(),
                'check_in_confidence' => $confidence,
                'address'              => $address,
                'latitude'             => $request->latitude,
                'longitude'            => $request->longitude,
                'work_type'            => $request->work_type,
            ]);

            return response()->json([
                'status'    => 'success',
                'name'      => $user->name,
                'work_type' => $request->work_type,
                'message'   => 'Check-In Successful (' . strtoupper($request->work_type) . ')'
            ]);
        } else {
            if (!$attendance) {
                return response()->json([
                    'status'  => 'error',
                    'name'    => $user->name,
                    'message' => "You Haven't Checked In Yet!",
                ]);
            }

            if ($attendance->check_out_time) {
                $now = Carbon::parse($attendance->check_out_time)->format('H:i:s');

                return response()->json([
                    'status'  => 'error',
                    'name'    => $user->name,
                    'message' => 'You have already checked out at ' . $now,
                ]);
            }

            $attendance->update([
                'check_out_time'        => Carbon::now(),
                'check_out_confidence' => $confidence,
                'address'              => $address,
                'latitude'             => $request->latitude,
                'longitude'            => $request->longitude,
            ]);

            return response()->json([
                'status'  => 'success',
                'name'    => $user->name,
                'message' => "Checkout Successful",
            ]);
        }
    }

    public function export(Request $request): BinaryFileResponse
    {
        $query = $request->query();
        $isSummary = isset($query['summary']) && filter_var($query['summary'], FILTER_VALIDATE_BOOLEAN);

        $filename = $isSummary ? 'summary_attendance.xlsx' : 'attendance_report.xlsx';
        
        if (isset($query['user_id'])) {
            $user = User::find($query['user_id']);
            if ($user) {
                $prefix = str_replace(' ', '_', $user->name);
                $filename = $isSummary 
                    ? "{$prefix}_summary_attendance.xlsx" 
                    : "{$prefix}_attendance.xlsx";
            }
        }

        return Excel::download(new AttendanceMultiSheetExport($query), $filename);
    }

    public function toggleStatus(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        if ($authUser->role !== 'manager') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'is_enabled' => 'required|boolean',
        ]);

        Cache::forever('attendance_enabled', $request->is_enabled);

        $status = $request->is_enabled ? 'enabled' : 'disabled';
        
        return back()->with('success', 'The attendance feature was successful' . $status . '.');
    }

    private function verifyFace(string $imageBase64): array
    {
        $host = env('PYTHON_SERVICE');
        $port = env('PYTHON_SERVICE_PORT');

        $users = User::whereNotNull('face_embedding')->get(['id', 'name', 'face_embedding']);

        $userData = $users->map(fn($u) => [
            'id'        => $u->id,
            'name'      => $u->name,
            'embedding' => $u->face_embedding,
        ]);
        
        try {
            $response = Http::post("http://{$host}:{$port}/attendance", [
                'image' => $imageBase64,
                'users' => $userData, 
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['match'])) {
                return [
                    'success'    => $result['match'],
                    'user_id'    => $result['message'] ?? null,
                    'confidence' => $result['confidence'] ?? 0
                ];
            }

            return [
                'success'    => false, 
                'message'    => $result['message'] ?? 'Unrecognized face or Python server error',
                'confidence' => 0
            ];
        } catch (\Exception $err) {
            return [
                'success'    => false, 
                'message'    => 'Failed to connect to the server: ' . $err->getMessage(),
                'confidence' => 0
            ];
        }
    }

    private function getAddress(?float $latitude, ?float $longitude): ?string
    {
        if (!$latitude || !$longitude) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'AttendanceApp/1.0'
            ])->timeout(5)->get("https://nominatim.openstreetmap.org/reverse", [
                'format' => 'jsonv2',
                'lat'    => $latitude,
                'lon'    => $longitude,  
            ]);

            if ($response->successful()) {
                return $response->json()['display_name'] ?? 'Address not found';
            }
        } catch (\Exception $err) {
            Log::error("Geocoding error: " . $err->getMessage());
            return null;
        }
        
        return null;
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}