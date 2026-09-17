<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends ApiController
{
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        if ($validated['role'] === 'vendor') {
            $geo = app(\App\Services\WorldPlaceService::class)->normalize(
                $validated['country_code'] ?? 'TR',
                $validated['city'] ?? null,
                $validated['district'] ?? null,
            );
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']).'-'.$user->id,
                'email' => $validated['email'],
                'country_code' => $geo['country_code'],
                'city' => $geo['city'],
                'district' => $geo['district'],
                'is_active' => false,
            ]);
            $user->update(['vendor_id' => $vendor->id]);
            $vendor->businessTypes()->sync($request->input('business_type_ids', []));
        }

        $token = $user->createToken($validated['device_name'])->plainTextToken;

        return $this->ok([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user->fresh()),
        ], null, null, 201);
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->fail('Geçersiz giriş bilgileri.', null, 422);
        }

        $user->tokens()->delete();
        $token = $user->createToken($validated['device_name'])->plainTextToken;

        return $this->ok([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ]);
    }

    public function user(Request $request)
    {
        return $this->ok($this->userPayload($request->user()));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->ok(null, 'Çıkış yapıldı.');
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'public_id' => $user->public_id,
            'public_code' => $user->publicCode(),
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'vendor_id' => $user->vendor_id,
            'is_freelancer' => (bool) $user->is_freelancer,
            'email_verified' => (bool) $user->email_verified_at,
            'phone_verified' => (bool) $user->phone_verified_at,
        ];
    }
}
