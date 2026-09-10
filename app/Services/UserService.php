<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use App\Services\FaceRecognizeService;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\User;

class UserService
{
    public function __construct(
        protected FaceRecognizeService $faceService,
    ) {}

    public function getFilteredUsers(array $filters): LengthAwarePaginator
    {
        $page    = $filters['page'] ?? 1;
        $search  = $filters['search'] ?? '';

        $userVersion = Cache::get('users_cache_version', 1);

        $cacheKey = "all_user_u{$userVersion}_s" . md5($search) . "_pg{$page}";
        return Cache::remember($cacheKey, 1800, function () use ($filters) {
            $query = User::query();

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            }

            return $query
                ->orderBy('role', 'desc')
                ->paginate(10)
                ->withQueryString();
        });
    }

    public function storeUser(array $data): User
    {
        $user = User::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'role'           => $data['role'],
            'is_wfa_allowed' => $data['is_wfa_allowed'] ?? false,
            'password'       => Hash::make($data['password']),
            'face_embedding' => null,
        ]);

        if (isset($data['face_photo']) && $data['face_photo'] instanceof UploadedFile) {
            $path = $data['face_photo']->store('temp_faces');
            $this->faceService->registerUserFace($user, $path);
        }

        $this->createLog("[CREATE] {$user->name}");

        return $user;
    }

    public function updateUser(array $data, int|string $id): User
    {
        $user = User::findOrFail($id);
        $oldName = $user->name;

        $changes = [];
        if ($user->name !== $data['name']) {
            $changes[] = "name from '{$user->name}' to '{$data['name']}'";
        }
        if ($user->email !== $data['email']) {
            $changes[] = "email from '{$user->email}' to '{$data['email']}'";
        }
        if ($user->role !== $data['role']) {
            $changes[] = "role from '{$user->role}' to '{$data['role']}'";
        }
        $newIsWfa = (bool)($data['is_wfa_allowed'] ?? false);
        $oldIsWfa = (bool)$user->is_wfa_allowed;
        if ($oldIsWfa !== $newIsWfa) {
            $oldWfaText = $oldIsWfa ? 'true' : 'false';
            $newWfaText = $newIsWfa ? 'true' : 'false';
            $changes[] = "is_wfa_allowed from '{$oldWfaText}' to '{$newWfaText}'";
        }
        if (!empty($data['password'])) {
            $changes[] = "password updated";
        }
        if (isset($data['face_photo']) && $data['face_photo'] instanceof UploadedFile) {
            $changes[] = "face photo updated";
        }

        $updateData = [
            'name'           => $data['name'],
            'email'          => $data['email'],
            'role'           => $data['role'],
            'is_wfa_allowed' => $newIsWfa,
        ];
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }
        $user->update($updateData);
        if (isset($data['face_photo']) && $data['face_photo'] instanceof UploadedFile) {
            $path = $data['face_photo']->store('temp_faces');
            $this->faceService->registerUserFace($user, $path);
        }

        if (!empty($changes)) {
            $detailLog = implode(', ', $changes);
            $this->createLog("[UPDATE] user {$oldName} ({$detailLog})");
        }

        return $user;
    }

    public function wfaUser(User $user) : void 
    {
        $newStatus = !$user->is_wfa_allowed;

        $user->update([
            'is_wfa_allowed' => $newStatus,
        ]);

        $statusStr = $newStatus ? 'ACTIVE' : 'INACTIVE';

        $this->createLog("[{$statusStr}] WFA {$user->name}");
    }

    public function activeUser(User $user): void
    {
        $newStatus = !$user->isActive;

        $user->update([
            'isActive' => $newStatus,
        ]);

        $statusStr = $newStatus ? 'ACTIVE' : 'INACTIVE';

        $this->createLog("[{$statusStr}] {$user->name}");
    }

    public function createLog(string $description): void
    {
        Auth::user()->logs()->create([
            'target'        => 'user',
            'description'   => $description,
        ]);
    }
}