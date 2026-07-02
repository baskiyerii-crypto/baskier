<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:customer,vendor'],
            'device_name' => ['required', 'string', 'max:255'],
            'business_type_ids' => ['nullable', 'array'],
            'business_type_ids.*' => ['exists:business_types,id'],
        ];
        if ($request->input('role') === 'vendor' && BusinessType::query()->exists()) {
            $rules['business_type_ids'] = ['required', 'array', 'min:1'];
        }
        $validated = $request->validate($rules);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        if ($validated['role'] === 'vendor') {
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'slug' => \Illuminate\Support\Str::slug($validated['name']) . '-' . $user->id,
                'email' => $validated['email'],
                'is_active' => false,
            ]);
            $user->update(['vendor_id' => $vendor->id]);
            $vendor->businessTypes()->sync($request->input('business_type_ids', []));
        }

        $token = $user->createToken($validated['device_name'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user->fresh()),
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Geçersiz giriş bilgileri.'], 422);
        }

        $user->tokens()->delete();
        $token = $user->createToken($validated['device_name'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($this->userPayload($request->user()));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Çıkış yapıldı.']);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'vendor_id' => $user->vendor_id,
            'is_freelancer' => (bool) $user->is_freelancer,
        ];
    }
}
