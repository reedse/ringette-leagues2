<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect to provider for authentication.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle callback from provider.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(string $provider)
    {
        try {
            $socialiteUser = Socialite::driver($provider)->user();
            
            // Check if user exists with this provider_id
            $user = User::where('provider', $provider)
                ->where('provider_id', $socialiteUser->getId())
                ->first();
                
            // If user doesn't exist with this provider, try to find by email
            if (!$user) {
                $user = User::where('email', $socialiteUser->getEmail())->first();
                
                // If user exists with this email, update their provider details
                if ($user) {
                    $user->update([
                        'provider' => $provider,
                        'provider_id' => $socialiteUser->getId(),
                    ]);
                } else {
                    // Create a new user
                    $user = User::create([
                        'name' => $socialiteUser->getName(),
                        'email' => $socialiteUser->getEmail(),
                        'password' => Hash::make(Str::random(24)),
                        'provider' => $provider,
                        'provider_id' => $socialiteUser->getId(),
                    ]);
                    
                    // By default, assign the player role
                    $playerRole = Role::where('name', 'player')->first();
                    if ($playerRole) {
                        $user->roles()->attach($playerRole);
                    }
                }
            }
            
            // Log the user in
            Auth::login($user, true);
            
            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'There was a problem authenticating with ' . ucfirst($provider));
        }
    }
}
