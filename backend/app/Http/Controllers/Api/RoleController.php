<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $role->update(['name' => $request->name]);
        return response()->json(['message' => 'Role updated successfully', 'role' => $role]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        // Validate default role can't delete
        if (in_array($role->name, ['Administrator', 'User'])) {
            return response()->json(['error' => 'Cannot delete default role.'], 403);
        }

        $role->delete();

        return response()->json(null, 204); // 204 No Content
    }
}
