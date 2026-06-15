<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'company'    => $user->company,
            'role'       => $user->role,
            'created_at' => $user->created_at,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'                  => ['sometimes', 'string', 'max:255'],
            'email'                 => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'company'               => ['nullable', 'string', 'max:255'],
            'role'                  => ['nullable', 'string', 'max:100'],
            'current_password'      => ['required_with:password', 'current_password'],
            'password'              => ['nullable', 'confirmed', Password::defaults()],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        unset($validated['current_password'], $validated['password_confirmation']);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'company'    => $user->company,
                'role'       => $user->role,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $user->currentAccessToken()->delete();
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully.']);
    }
}
