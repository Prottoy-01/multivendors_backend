<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // ✅ STEP 6: CHECK USER STATUS
            if (isset($user->status) && $user->status === 'suspended') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been suspended. Please contact support for more information.',
                ])->withInput();
            }
            
            if (isset($user->status) && $user->status === 'banned') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been permanently banned. Please contact support.',
                ])->withInput();
            }
            
            // Store user data in session for easy access
            Session::put('user', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status ?? 'active', // ✅ Include status
            ]);

            // Redirect based on role
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Welcome back!');
            } elseif ($user->role === 'vendor') {
                return redirect()->route('vendor.dashboard')->with('success', 'Welcome back!');
            } else {
                return redirect()->route('home')->with('success', 'Welcome back!');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    /**
     * Show register form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle customer registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'status' => 'active', // ✅ Set default status
        ]);

        // Auto login after registration
        Auth::login($user);

        Session::put('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status, // ✅ Include status
        ]);

        return redirect()->route('home')->with('success', 'Registration successful! Welcome!');
    }

    /**
     * Show vendor registration form
     */
    public function showVendorRegister()
    {
        return view('auth.vendor-register');
    }

    /**
     * Handle vendor registration
     */
    public function vendorRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed',
            'shop_name' => 'required|string|max:255',
            'shop_description' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'vendor',
            'status' => 'active', // ✅ Set default status
            'phone' => $request->phone,
            
        ]);

        // Create vendor profile
        Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $request->shop_name,
            'shop_description' => $request->shop_description,
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => 'pending', // Vendor approval status
        ]);

        return redirect()->route('login')->with('success', 'Vendor registration successful! Please wait for admin approval before logging in.');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home')->with('success', 'Logged out successfully!');
    }
}