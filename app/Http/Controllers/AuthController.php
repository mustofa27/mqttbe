<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home.dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()?->hasVerifiedEmail()) {
                return redirect()->route('home.dashboard')->with('success', 'Welcome back!');
            }

            return redirect()->route('verification.notice')->with('success', 'Please verify your email address before continuing.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
    }

    /**
     * Show the registration form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('home.dashboard');
        }
        return view('auth.register');
    }

    /**
     * Redirect the user to Google for authentication.
     */
    public function redirectToGoogle(Request $request)
    {
        $googleClientId = config('services.google.client_id');
        $googleRedirectUri = config('services.google.redirect');

        if (! $googleClientId || ! $googleRedirectUri) {
            return back()->withErrors([
                'email' => 'Google sign-in is not configured.',
            ]);
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => $googleClientId,
            'redirect_uri' => $googleRedirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account',
            'state' => $state,
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    /**
     * Handle Google callback.
     */
    public function handleGoogleCallback(Request $request)
    {
        $state = $request->session()->pull('google_oauth_state');

        if (! $state || ! hash_equals($state, (string) $request->string('state'))) {
            return redirect()->route('login')->withErrors([
                'email' => 'Invalid Google login session. Please try again.',
            ]);
        }

        if ($request->filled('error')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in was cancelled or denied.',
            ]);
        }

        $googleClientId = config('services.google.client_id');
        $googleClientSecret = config('services.google.client_secret');
        $googleRedirectUri = config('services.google.redirect');

        if (! $googleClientId || ! $googleClientSecret || ! $googleRedirectUri) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in is not configured.',
            ]);
        }

        if (! $request->filled('code')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google did not return an authorization code.',
            ]);
        }

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $googleClientId,
            'client_secret' => $googleClientSecret,
            'redirect_uri' => $googleRedirectUri,
            'grant_type' => 'authorization_code',
            'code' => $request->string('code'),
        ]);

        if (! $tokenResponse->successful()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to complete Google sign-in. Please try again.',
            ]);
        }

        $accessToken = $tokenResponse->json('access_token');

        if (! $accessToken) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google did not return an access token.',
            ]);
        }

        $profileResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');

        if (! $profileResponse->successful()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to read Google profile information.',
            ]);
        }

        $googleProfile = $profileResponse->json();
        $googleId = $googleProfile['id'] ?? null;
        $googleEmail = $googleProfile['email'] ?? null;
        $googleName = $googleProfile['name'] ?? null;
        $googleVerified = (bool) ($googleProfile['verified_email'] ?? false);

        if (! $googleId || ! $googleEmail) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google account information is incomplete.',
            ]);
        }

        if (! $googleVerified) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your Google email address must be verified first.',
            ]);
        }

        $user = User::where('google_id', $googleId)->orWhere('email', $googleEmail)->first();

        if ($user) {
            $updates = [];

            if (! $user->google_id) {
                $updates['google_id'] = $googleId;
            }

            if (! $user->email_verified_at) {
                $updates['email_verified_at'] = now();
            }

            if (! $user->name && $googleName) {
                $updates['name'] = $googleName;
            }

            if (! empty($updates)) {
                $user->forceFill($updates)->save();
            }
        } else {
            $user = User::create([
                'name' => $googleName ?: $googleEmail,
                'email' => $googleEmail,
                'google_id' => $googleId,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(40)),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home.dashboard')->with('success', 'Signed in with Google successfully!');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->sendEmailVerificationNotification();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')->with('success', 'Account created! Please check your email to verify your address.');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully.');
    }
}
