<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

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

        $response = $this->api->login([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if (isset($response['token'])) {
            // Store token and user data in session
            Session::put('api_token', $response['token']);
            Session::put('user', $response['user']);

            // Redirect based on role
            $role = $response['user']['role'];
            
            if ($role === 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Welcome back!');
            } elseif ($role === 'vendor') {
                return redirect()->route('vendor.dashboard')->with('success', 'Welcome back!');
            } else {
                return redirect()->route('customer.dashboard')->with('success', 'Welcome back!');
            }
        }

        return back()->withErrors(['email' => $response['message'] ?? 'Invalid credentials'])->withInput();
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
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|confirmed',
        ]);

        $response = $this->api->register([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ]);

        if (isset($response['token'])) {
            Session::put('api_token', $response['token']);
            Session::put('user', $response['user']);

            return redirect()->route('customer.dashboard')->with('success', 'Registration successful!');
        }

        return back()->withErrors(['email' => $response['message'] ?? 'Registration failed'])->withInput();
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
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|confirmed',
            'shop_name' => 'required|string|max:255',
            'shop_description' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        $response = $this->api->registerVendor([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
            'shop_name' => $request->shop_name,
            'shop_description' => $request->shop_description,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        if (isset($response['message']) && strpos($response['message'], 'success') !== false) {
            return redirect()->route('login')->with('success', 'Vendor registration successful! Please wait for admin approval.');
        }

        return back()->withErrors(['email' => $response['message'] ?? 'Registration failed'])->withInput();
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        $this->api->logout();
        Session::flush();
        
        return redirect()->route('home')->with('success', 'Logged out successfully!');
    }
}