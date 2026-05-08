<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Exports\AttendanceMultiSheetExport;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();
        $page = $request->query('page', 1);
        $cacheKey = "attendance_u" . ($query['user_id'] ?? 'all') . 
                    "_from_" . ($query['from'] ?? 'start') . 
                    "_to_" . ($query['to'] ?? 'end') . 
                    "_p{$page}";

        $attendances = \Cache::remember($cacheKey, 1800, function() use ($query) {
            $attendanceQuery = Attendance::with('user')->orderBy('check_in_time', 'desc');

            if(isset($query['user_id'])) {
                $attendanceQuery->where('user_id', $query['user_id']);
            }

            if(!empty($query['from']) && !empty($query['to'])) {
                try {
                    $from = Carbon::createFromFormat('d-m-Y', $query['from'])->startOfDay();
                    $to = Carbon::createFromFormat('d-m-Y', $query['to'])->endOfDay();
                    $attendanceQuery->whereBetween('check_in_time', [$from, $to]);
                } catch (\Exception $err) {}
            }

            return $attendanceQuery->paginate(10)->withQueryString();
        });

        $users = \Cache::remember('all_users_list', 1800, function() {
            return User::orderBy('name')->get();
        });

        return Inertia::render('Attendance/Index', compact('attendances', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'type' => 'required|in:check_in,check_out',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'work_type' => 'required|in:wfo,wfa', 
        ]);

        if (!$request->latitude || !$request->longitude) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi tidak ditemukan. Harap izinkan akses lokasi (GPS) pada browser/perangkat Anda.'
            ], 400);
        }

        $verification = $this->verifyFace($request->image);

        // --- PENGATURAN COOLDOWN UNTUK SEMUA STATUS ---
        // Gunakan user_id jika dikenali, jika error/wajah tidak dikenali gunakan IP Address device
        $identifier = $verification['user_id'] ?? $request->ip();
        $cooldownKey = 'attendance_cooldown_' . $identifier;

        if (Cache::has($cooldownKey)) {
            return response()->json([
                'status' => 'cooldown',
                'message' => 'Harap tunggu sebentar sebelum melakukan absensi lagi.',
            ], 429);
        }

        // Langsung set cooldown 2 detik di sini agar mencakup SEMUA status di bawahnya (Sukses, Error, Sudah Absen, dll)
        Cache::put($cooldownKey, true, 2);
        // ----------------------------------------------

        if (!$verification || !isset($verification['success'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Proses verifikasi gagal dijalankan'
            ], 500);
        }

        if (!$verification['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $verification['message'],
            ], 401);
        }

        $user = User::find($verification['user_id']);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User dengan id ' . $verification['user_id'] . ' tidak ditemukan di database',
            ], 404);
        }

        if ($request->work_type === 'wfa') {
            if (!$user->is_wfa_allowed) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses absen WFA. Harap hubungi Manager Anda.'
                ], 403);
            }
        } else {
            // Pengecekan jika user punya akses WFA tapi malah milih WFO
            if ($user->is_wfa_allowed) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda telah diberikan akses WFA. Harap ubah tipe absensi Anda menjadi WFA.'
                ], 403);
            }

            // Koordinat Kantor: 7°15'53.9"S 112°44'50.1"E (dalam desimal)
            $officeLat = -7.2649722;
            $officeLon = 112.7472500;
            $maxDistance = 100; // Jangkauan maksimal dalam meter

            // Hitung jarak user saat ini dengan lokasi kantor
            $distance = $this->calculateDistance($request->latitude, $request->longitude, $officeLat, $officeLon);

            if ($distance > $maxDistance) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda berstatus WFO tapi berada di luar jangkauan kantor (Jarak: ' . round($distance) . ' meter). Absensi WFO hanya bisa dilakukan dalam radius ' . $maxDistance . ' meter.'
                ], 403);
            }
        }

        $address = $this->getAddress($request->latitude, $request->longitude);
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)
                                ->whereDate('check_in_time', $today)
                                ->first();

        $confidence =  $verification['confidence'];

        if ($request->type === 'check_in') {
            if ($attendance) {
                $now = Carbon::parse($attendance->check_in_time)->format('H:i:s');

                return response()->json([
                    'status' => 'error',
                    'name' => $user->name,
                    'message' => "Anda Sudah Melakukan Check-In Pada Pukul $now"
                ]);
            }

            Attendance::create([
                'user_id' => $user->id,
                'check_in_time' => Carbon::now(),
                'check_in_confidence' => $confidence,
                'address' => $address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'work_type' => $request->work_type,
            ]);

            \Cache::flush();

            return response()->json([
                'status' => 'success',
                'name' => $user->name,
                'work_type' => $request->work_type,
                'message' => "Berhasil Check-In (" . strtoupper($request->work_type) . ")"
            ]);
        } else {
            if (!$attendance) {
                return response()->json([
                    'status' => 'error',
                    'name' => $user->name,
                    'message' => "Anda Belum Melakukan Check-In!",
                ]);
            }

            if ($attendance->check_out_time) {
                $now = Carbon::parse($attendance->check_out_time)->format('H:i:s');

                return response()->json([
                    'status' => 'error',
                    'name' => $user->name,
                    'message' => "Anda Sudah Melakukan Check-Out Pada Pukul $now",
                ]);
            }

            $attendance->update([
                'check_out_time' => Carbon::now(),
                'check_out_confidence' => $confidence,
                'address' => $address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            return response()->json([
                'status' => 'success',
                'name' => $user->name,
                'message' => "Berhasil Check-Out",
            ]);
        }
    }

    public function export(Request $request)
    {
        $query = $request->query();

        $isSummary = isset($query['summary']) && filter_var($query['summary'], FILTER_VALIDATE_BOOLEAN);

        $filename = $isSummary ? 'summary_attendance.xlsx' : 'attendance_report.xlsx';
        
        if (isset($query['user_id'])) {
            $user = User::where('id', $query['user_id'])->first();
            if ($user) {
                $prefix = str_replace(' ', '_', $user->name);
                $filename = $isSummary 
                    ? "{$prefix}_summary_attendance.xlsx" 
                    : "{$prefix}_attendance.xlsx";
            }
        }

        return Excel::download(new AttendanceMultiSheetExport($query), $filename);
    }

    private function verifyFace($imageBase64)
    {
        $host = env('PYTHON_SERVICE');
        $port = env('PYTHON_SERVICE_PORT');

        $users = User::whereNotNull('face_embedding')->get(['id', 'name', 'face_embedding']);

        $userData = $users->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'embedding' => $u->face_embedding,
            ];
        });
        
        try {
            $imageRaw = $imageBase64;
            if (str_contains($imageRaw, ',')) {
                $imageRaw = explode(',', $imageRaw)[1];
            }

            $response = Http::post("http://{$host}:{$port}/attendance", [
                'image' => $imageBase64,
                'users' => $userData, 
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['match'])) {
                return [
                    'success' => $result['match'],
                    'user_id' => $result['message'] ?? null,
                    'confidence' => $result['confidence'] ?? 0
                ];
            }

            return [
                'success' => false, 
                'message' => $result['message'] ?? 'Wajah tidak dikenali atau error dari server Python',
                'confidence' => 0
            ];
        } catch (\Exception $err) {
            return [
                'success' => false, 
                'message' => 'Gagal koneksi ke server: ' . $err->getMessage(),
                'confidence' => 0
            ];
        }
    }

    public function toggleStatus(Request $request)
    {
        if (auth()->user()->role !== 'other') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'is_enabled' => 'required|boolean',
        ]);

        Cache::forever('attendance_enabled', $request->is_enabled);

        $status = $request->is_enabled ? 'diaktifkan' : 'dinonaktifkan';
        
        return back()->with('success', "Fitur absensi berhasil $status.");
    }

    private function getAddress($latitude, $longitude)
    {
        if (!$latitude || !$longitude) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'AttendanceApp/1.0'
            ])->timeout(5)->get("https://nominatim.openstreetmap.org/reverse", [
                'format' => 'jsonv2',
                'lat' => $latitude,
                'lon' => $longitude,  
            ]);

            if ($response->successful()) {
                return $response->json()['display_name'] ?? 'Alamat tidak ditemukan';
            }
        } catch (\Exception $err) {
            \Log::error("Geocoding error: " . $err->getMessage());
            return null;
        }
        
        return null;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam satuan meter

        // Konversi koordinat dari derajat ke radian
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