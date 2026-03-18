<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\ResetPassword;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    use ApiResponse;

    protected function createTokens(User $user): array
    {
        $accessToken = $user->createToken('access_token')->plainTextToken;
        $refreshToken = $user->createToken('refresh_token')->plainTextToken;

        return [$accessToken, $refreshToken];
    }

    protected function refreshCookie(string $refreshToken)
    {
        // 7 days
        return Cookie::make('refresh_token', $refreshToken, 60 * 24 * 7, null, null, false, true, false, 'Strict');
    }

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'phone' => 'nullable|string|max:20',
                'role' => 'required|in:admin,organizer,customer',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                // password will be automatically hashed via the User model cast
                'password' => $validated['password'],
                'phone' => $validated['phone'],
                'role' => $validated['role'],
            ]);

            [$accessToken, $refreshToken] = $this->createTokens($user);

            $response = $this->success([
                'user' => $user,
                'access_token' => $accessToken,
            ], 'User registered successfully');

            return $response->withCookie($this->refreshCookie($refreshToken));
        } catch (ValidationException $e) {
            return $this->error('Validation failed', $e->errors());
        } catch (\Exception $e) {
            return $this->error('Registration failed');
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if (!Auth::attempt($validated)) {
                return $this->unauthorized('Invalid credentials');
            }

            $user = Auth::user();
            [$accessToken, $refreshToken] = $this->createTokens($user);

            $response = $this->success([
                'user' => $user,
                'access_token' => $accessToken,
            ], 'Login successful');

            return $response->withCookie($this->refreshCookie($refreshToken));
        } catch (ValidationException $e) {
            return $this->error('Validation failed', $e->errors());
        } catch (\Exception $e) {
            return $this->error('Login failed');
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $refreshToken = $request->cookie('refresh_token');
            if (!$refreshToken) {
                return $this->unauthorized('Refresh token missing');
            }

            $tokenModel = PersonalAccessToken::findToken($refreshToken);
            if (!$tokenModel || !$tokenModel->tokenable) {
                return $this->unauthorized('Invalid refresh token');
            }

            $user = $tokenModel->tokenable;
            $tokenModel->delete(); // rotate refresh token

            [$accessToken, $newRefreshToken] = $this->createTokens($user);

            $response = $this->success([
                'access_token' => $accessToken,
            ], 'Token refreshed');

            return $response->withCookie($this->refreshCookie($newRefreshToken));
        } catch (\Exception $e) {
            return $this->error('Failed to refresh token');
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            $refreshToken = $request->cookie('refresh_token');
            if ($refreshToken) {
                $tokenModel = PersonalAccessToken::findToken($refreshToken);
                if ($tokenModel) {
                    $tokenModel->delete();
                }
            }

            $response = $this->success(null, 'Logged out successfully');
            return $response->withCookie(Cookie::forget('refresh_token'));
        } catch (\Exception $e) {
            return $this->error('Logout failed');
        }
    }

    public function me(Request $request)
    {
        try {
            return $this->success($request->user(), 'User data retrieved');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve user data');
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
            ]);

            $user = User::where('email', $validated['email'])->first();

            $token = Str::random(64);
            $now = now();

            \DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $validated['email']],
                ['token' => $token, 'created_at' => $now]
            );

            if ($user) {
                Notification::send($user, new ResetPassword($token, $user->email));
            }

            return $this->success(null, 'If a matching account was found, a password reset email has been sent.');
        } catch (ValidationException $e) {
            return $this->error('Validation failed', $e->errors());
        } catch (\Exception $e) {
            return $this->error('Failed to initiate password reset');
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'token' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $record = \DB::table('password_reset_tokens')
                ->where('email', $validated['email'])
                ->where('token', $validated['token'])
                ->first();

            if (!$record) {
                return $this->error('Invalid or expired token', null, 400);
            }

            $user = User::where('email', $validated['email'])->first();
            if (!$user) {
                return $this->error('User not found', null, 404);
            }

            // Password is hashed automatically via the User model cast
            $user->update(['password' => $validated['password']]);

            \DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

            return $this->success(null, 'Password has been reset successfully');
        } catch (ValidationException $e) {
            return $this->error('Validation failed', $e->errors());
        } catch (\Exception $e) {
            return $this->error('Failed to reset password');
        }
    }
}
