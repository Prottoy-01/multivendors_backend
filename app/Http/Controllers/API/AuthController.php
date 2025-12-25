<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // -------------------------
    // REGISTER (general)
    // -------------------------
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:customer,vendor,admin'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    // -------------------------
    // LOGIN
    // -------------------------
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }

    // -------------------------
    // LOGOUT
    // -------------------------
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    // -------------------------
    // VENDOR REGISTRATION (Pending)
    // -------------------------
    public function registerVendor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'shop_name' => 'required|string|max:150'
        ]);

        // Create user with role vendor
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'vendor',
        ]);

        // Create vendor entry with 'pending' status
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $request->shop_name,
            'status' => 'pending',
            'commission_percentage' => 10.0
        ]);

        return response()->json([
            'message' => 'Vendor registration submitted. Waiting for admin approval.',
            'vendor' => $vendor,
            'user' => $user
        ], 201);
    }

    // -------------------------
    // ADMIN: Approve / Reject Vendor
    // -------------------------
    public function approveVendor(Request $request, $vendor_id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $vendor = Vendor::findOrFail($vendor_id);
        $vendor->status = $request->status;
        $vendor->save();

        return response()->json([
            'message' => 'Vendor status updated successfully',
            'vendor' => $vendor
        ]);
    }

    // -------------------------
// VENDOR: View Profile
// -------------------------
public function vendorProfile(Request $request)
{
    $vendor = Vendor::where('user_id', $request->user()->id)->first();

    if (!$vendor) {
        return response()->json(['message' => 'Vendor profile not found'], 404);
    }

    return response()->json([
        'user' => $request->user(),
        'vendor' => $vendor
    ]);
}

// -------------------------
// VENDOR: Update Profile
// -------------------------
public function updateVendorProfile(Request $request)
{
    $vendor = Vendor::where('user_id', $request->user()->id)->first();

    if (!$vendor) {
        return response()->json(['message' => 'Vendor profile not found'], 404);
    }

    $request->validate([
        'shop_name' => 'sometimes|string|max:150',
        'commission_percentage' => 'sometimes|numeric|min:0|max:100'
    ]);

    if ($request->has('shop_name')) {
        $vendor->shop_name = $request->shop_name;
    }

    if ($request->has('commission_percentage')) {
        $vendor->commission_percentage = $request->commission_percentage;
    }

    $vendor->save();

    return response()->json([
        'message' => 'Vendor profile updated successfully',
        'vendor' => $vendor
    ]);
}
}
