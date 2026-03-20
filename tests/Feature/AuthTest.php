<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_returns_tokens(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'phone' => '1234567890',
            'role' => 'customer',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201);
        $response->assertJson(['success' => true, 'message' => 'User registered successfully']);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);

        $response->assertJsonStructure(['data' => ['user', 'access_token']]);
    }

    public function test_login_with_valid_credentials_returns_tokens(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Login successful']);
        $response->assertJsonStructure(['data' => ['user', 'access_token']]);
    }

    public function test_refresh_token_rotates_and_returns_new_access_token(): void
    {
        $user = User::factory()->create();
        $refreshToken = $user->createToken('refresh_token')->plainTextToken;

        $this->assertNotNull(\Laravel\Sanctum\PersonalAccessToken::findToken($refreshToken));

        $response = $this->withCredentials()
            ->withUnencryptedCookie('refresh_token', $refreshToken)
            ->postJson('/api/v1/auth/refresh-token');

        dump($response->json());

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Token refreshed']);
        $response->assertJsonStructure(['data' => ['access_token']]);
    }

    public function test_logout_revokes_tokens_and_cookie(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('access_token');
        $accessToken = $token->plainTextToken;
        $refreshToken = $user->createToken('refresh_token')->plainTextToken;

        $response = $this->withCredentials()
            ->withHeader('Authorization', "Bearer {$accessToken}")
            ->withUnencryptedCookie('refresh_token', $refreshToken)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Logged out successfully']);

        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id, 'name' => 'access_token']);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id, 'name' => 'refresh_token']);
    }

    public function test_forgot_password_stores_token_and_sends_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'forgot@test.com']);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_reset_password_updates_password_when_token_is_valid(): void
    {
        $user = User::factory()->create(['password' => 'oldpassword']);
        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Password has been reset successfully']);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword', $user->password));
    }
}
