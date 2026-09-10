<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request)
    {
        $users = $this->userService->getFilteredUsers($request->query());

        return Inertia::render('User/Index', [
            'users' => $users
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'role' => ['required', 'in:manager,leader,engineer,communicator,designer'],
            'is_wfa_allowed' => 'boolean',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'face_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $this->userService->storeUser($validated);

        return redirect(route('user.list', absolute: false))->with('success', 'The user ' . $user->name . ' was successfully created!');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $id,
            'role' => ['required', 'in:manager,leader,engineer,communicator,designer'],
            'is_wfa_allowed' => 'boolean',
            'face_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        $validated = $request->validate($rules);

        $user = $this->userService->updateUser($validated, $id);

        return redirect(route('user.list', absolute: false))
            ->with('success', 'The user ' . $user->name . ' has been successfully updated! The photo is being processed in the background.');
    }

    public function toggleWfa(User $user): RedirectResponse
    {
        $this->userService->wfaUser($user);

        return back()->with('success', 'The WFA status for ' . $user->name . ' has been successfully updated!');
    }

    public function changeIsActive(User $user): RedirectResponse
    {
        $this->userService->activeUser($user);

        return back()->with('success', 'Status has been successfully updated!');
    }
}