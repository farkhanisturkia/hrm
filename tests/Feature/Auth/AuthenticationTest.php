<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['role' => 'engineer']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('task.list', absolute: false));
    }

    public function test_managers_are_redirected_to_the_dashboard_after_login(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_authenticated_users_are_redirected_from_the_root_page(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_remember_me_keeps_users_authenticated_in_a_new_session(): void
    {
        $user = User::factory()->create(['role' => 'engineer']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $this->assertAuthenticated();

        $rememberCookie = collect($response->headers->getCookies())
            ->first(fn ($cookie) => str_starts_with($cookie->getName(), 'remember_web_'));

        $this->assertNotNull($rememberCookie, 'Remember me cookie was not set.');

        // Simulasi buka tab baru: session hilang, hanya cookie remember me yang tersisa.
        $this->app['session']->flush();

        $response = $this->withCookie($rememberCookie->getName(), $rememberCookie->getValue())
            ->get('/');

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('task.list'));
    }
}
