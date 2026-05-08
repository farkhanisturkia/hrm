<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Profile/Index', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        \Cache::forget('all_users_list');
        \Cache::forget('all_members');

        return Redirect::back(); 
    }

    /**
     * Update the user's displayed skills.
     */
    public function updateSkills(Request $request): RedirectResponse
    {
        $request->validate([
            'displayed_skills' => ['array', 'max:5'],
            'displayed_skills.*' => ['exists:skills,id']
        ]);

        $user = $request->user();

        // Reset semua skill milik user menjadi tidak ditampilkan (false)
        $user->skills()->update(['is_displayed' => false]);

        // Set skill yang dipilih dari modal menjadi ditampilkan (true)
        if (!empty($request->displayed_skills)) {
            $user->skills()->whereIn('id', $request->displayed_skills)->update(['is_displayed' => true]);
        }

        return Redirect::back();
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $userId = $user->id;

        Auth::logout();

        $user->delete();

        \Cache::flush();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}