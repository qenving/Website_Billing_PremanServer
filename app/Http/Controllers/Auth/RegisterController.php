<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        // Check if registration is allowed
        $allowRegistration = \App\Models\Setting::where('key', 'allow_registration')->value('value') === 'true';

        if (!$allowRegistration) {
            return redirect()->route('login')->with('error', 'Registration is currently disabled.');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Check if registration is allowed
        $allowRegistration = \App\Models\Setting::where('key', 'allow_registration')->value('value') === 'true';

        if (!$allowRegistration) {
            return redirect()->route('login')->with('error', 'Registration is currently disabled.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'company_name' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            // Get client role
            $clientRole = Role::where('name', 'client')->first();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $clientRole->id,
                'is_active' => true,
            ]);

            // Create client profile
            Client::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name,
                'company_phone' => $request->company_phone,
                'company_address' => $request->company_address,
            ]);

            DB::commit();

            // Check if approval is required
            $requireApproval = \App\Models\Setting::where('key', 'registration_approval')->value('value') === 'true';

            if ($requireApproval) {
                return redirect()->route('login')->with('success', 'Registration successful! Your account is pending approval.');
            }

            // Auto-login
            Auth::login($user);

            return redirect()->route('client.dashboard')->with('success', 'Welcome to ' . config('app.name') . '!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['email' => 'Registration failed. Please try again.']);
        }
    }
}
