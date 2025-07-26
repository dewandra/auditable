<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); 
        }

        $user = User::create($validator->validated());

        if ($request->has('roles')) {
            $roles = Role::WhereIn('name', $request->roles)->get();
            $user->syncRoles($roles);
        }

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user =User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6|confirmed',
            'roles' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }   

        $user->update($validator->validated());

        if ($request->has('roles')) {
            $roles = Role::WhereIn('name', $request->roles)->get();
            $user->syncRoles($roles);
        }

        return response()->json($user->load('roles'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return response()->json(['error' => 'You cannot delete your own account.'], 403);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
