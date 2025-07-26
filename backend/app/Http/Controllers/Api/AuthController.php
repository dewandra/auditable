<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator; 
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        
        if (!$token = Auth::guard('api')->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token); 
    }

    public function register(Request $request)
    {
        dd($request->all());
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => Hash::make($request->password)]
        ));

        $userRole = Role::findByName('User', 'api');
        $user->assignRole($userRole);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    public function logout()
    {
        Auth::guard('api')->logout(); // <-- Lebih eksplisit
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->createNewToken(Auth::guard('api')->refresh()); // <-- Lebih eksplisit
    }

    public function userProfile()
    {
        return response()->json(Auth::guard('api')->user()); // <-- Lebih eksplisit
    }

    /**
     * Helper function untuk membuat struktur token response.
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60, // <-- Lebih eksplisit
            'user' => Auth::guard('api')->user() // <-- Lebih eksplisit
        ]);
    }
}