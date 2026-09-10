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
    public function index(Request $request): Response
    {
        return Inertia::render('Profile/Index', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::back(); 
    }

    public function updateSkills(Request $request): RedirectResponse
    {
        $request->validate([
            'displayed_skills'   => ['array', 'max:5'],
            'displayed_skills.*' => ['exists:skills,id']
        ]);

        $user = $request->user();

        $user->skills()->update(['is_displayed' => false]);

        if (!empty($request->displayed_skills)) {
            $user->skills()->whereIn('id', $request->displayed_skills)->update(['is_displayed' => true]);
        }

        return Redirect::back();
    }
}