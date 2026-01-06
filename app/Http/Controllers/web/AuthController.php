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
            
            // Store user data in session for easy access
            Session::put('user', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            // Redirect based on role
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Welcome back!');
            } elseif ($user->role === 'vendor') {
                return redirect()->route('vendor.dashboard')->with('success', 'Welcome back!');
            } else {
                return redirect()->route('customer.dashboard')->with('success', 'Welcome back!');
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
        ]);

        // Auto login after registration
        Auth::login($user);

        Session::put('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->route('customer.dashboard')->with('success', 'Registration successful! Welcome!');
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
        ]);

        // Create vendor profile
        Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $request->shop_name,
            'shop_description' => $request->shop_description,
            'phone' => $request->phone,
            'address' => $request->address,
            //'is_approved' => false, // Needs admin approval
            'status' => 'pending',
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