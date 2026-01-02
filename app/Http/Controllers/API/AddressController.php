<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAddress;

class AddressController extends Controller
{
    /**
     * 1️⃣ List all user addresses
     */
    public function index(Request $request)
    {
        return $request->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->get();
    }

    /**
     * 2️⃣ Store new address
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipient_name'          => 'required|string',
            'phone'         => 'required|string',
            'address_line'  => 'required|string',
            'city'          => 'required|string',
            'state'          => 'nullable|string',
            'postal_code'   => 'nullable|string',
            'country'        => 'nullable|string|max:100',
            'is_default'    => 'boolean'
        ]);

        // If default → unset previous default
        if ($request->is_default) {
            $request->user()
                ->addresses()
                ->update(['is_default' => false]);
        }

        $address = UserAddress::create([
            'user_id'       => $request->user()->id,
            'recipient_name'          => $request->recipient_name,
            'phone'         => $request->phone,
            'address_line'  => $request->address_line,
            'city'          => $request->city,
            'state'          => $request->state,
            'postal_code'   => $request->postal_code,
            'country'        => $request->country ?? 'Bangladesh',
            'is_default'    => $request->is_default ?? false,
        ]);

        return response()->json($address, 201);
    }

    /**
     * 3️⃣ Update address
     */
    public function update(Request $request, $id)
    {
        $address = UserAddress::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'recipient_name'          => 'required|string',
            'phone'         => 'required|string',
            'address_line'  => 'required|string',
            'city'          => 'required|string',
            'state'          => 'nullable|string',
            'postal_code'   => 'nullable|string',
            'country'        => 'nullable|string|max:100',
            'is_default'    => 'boolean'
        ]);

        if ($request->is_default) {
            $request->user()
                ->addresses()
                ->update(['is_default' => false]);
        }

        $address->update($request->all());

        return response()->json($address);
    }

    /**
     * 4️⃣ Delete address
     */
    public function destroy(Request $request, $id)
    {
        $address = UserAddress::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $address->delete();

        return response()->json([
            'message' => 'Address deleted'
        ]);
    }

    /**
     * 5️⃣ Set default address
     */
    public function setDefault(Request $request, $id)
    {
        $user = $request->user();

        $address = UserAddress::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Reset all defaults
        $user->addresses()->update(['is_default' => false]);

        $address->is_default = true;
        $address->save();

        return response()->json([
            'message' => 'Default address updated'
        ]);
    }
}
