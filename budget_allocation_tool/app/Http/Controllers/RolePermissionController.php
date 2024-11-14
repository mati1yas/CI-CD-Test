<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolePermissionController extends Controller
{
    // method to create new roles. 
    // it is going to take role name and create it . 
    public function createRoles(Request $request)
    {
        try {
            $roles = $request->input('roles');
    
            foreach ($roles as $roleName) {
           
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            }
    
            return response()->json(['message' => 'Roles created successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create roles', 'error' => $e->getMessage()], 500);
        }
    }

    
    public function deleteRoles(Request $request)
    {
        try {
            $roles = $request->input('roles');
            foreach ($roles as $roleName) {
                $role = Role::where('name', $roleName)->first();

                if ($role) {
                    $role->delete();
                }
            }

            return response()->json(['message' => 'Roles deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete roles', 'error' => $e->getMessage()], 500);
        }
    }

    /*
    Assign role to user
    takes user id and role name 

    */ 
    public function assignRoleToUser(Request $request)
    {

        $user_email = $request->input('email');
        $roleName = $request->input('role');

        // Find the user and role
        try {
           
            $user = User::where('email', $user_email)->firstOrFail();
            $role = Role::where('name', $roleName)->firstOrFail();
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'User or role not found'], 404);
        }

        // Attach the role to the user
        $user->assignRole($role);

        return response()->json(['message' => 'Role assigned to user successfully']);
    }


    // Revoke role from user 
    public function revokeRoleFromUser(Request $request)
    {
        try {
                      

            $user_email = $request->input('email');
            $roleName = $request->input('role');

            $user = User::where('email', $user_email)->firstOrFail();
            $role = Role::where('name', $roleName)->firstOrFail();

            $user->removeRole($role);

            return response()->json(['message' => 'Role revoked from user successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to revoke role from user', ], 500);
        }
    }


    public function getRoles()
    {
        $roles = Role::all();

        return response()->json(['roles' => $roles]);
    }

    public function getRoleUsers(Request $request)
    {
        $roleName = $request->input('role');
        $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();


        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $users = $role->users()->get();

        return response()->json([
            "role"=> $role,
            'users' => $users]);
    }


    public function getUserRoles(Request $request)
    {
        try {
            $user_email = $request->input('email');
            
            $user = User::where('email',$user_email)->firstOrFail();
            $roles = $user->roles()->pluck('name');

            return response()->json(['roles' => $roles], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch user roles', 'error' => $e->getMessage()], 500);
        }
    }


}
