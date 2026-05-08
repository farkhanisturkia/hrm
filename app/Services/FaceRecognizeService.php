<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FaceRecognizeService
{
    protected $host;
    protected $port;

    public function __construct()
    {
        $this->host = env("PYTHON_SERVICE");
        $this->port = env("PYTHON_SERVICE_PORT");
    }

    public function registerUserFace(User $user, string $path): bool
    {
        if (!Storage::exists($path)) {
            Log::error("File not found at storage: ", $path);
            return false;
        }

        try {
            $fullPath = Storage::path($path);
            $photo = fopen($fullPath, 'r');
            $response = Http::attach('file', $photo, 'face.jpg')
                ->post("http://{$this->host}:{$this->port}/registration");
            
            if (is_resource($photo)) fclose($photo);

            if ($response->successful() && $response->json('success')) {
                $user->update(['face_embedding' => $response->json('embedding')]);
                Cache::forget('all_users_list');
                return true;
            }

            return false;
        } catch (\Exception $err) {
            Log::error("Registration error: " . $err->message());
            return false;
        } finally {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        }
    }
}
