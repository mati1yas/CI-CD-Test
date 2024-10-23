<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    
    
    
    
    
    
    
    // method to return all users with their respective role . 
    public function getAllUsersWithRolesAndPermissions(){

        try {
            $users = User::with('roles.permissions')->get()
                ->map(function ($user) {
                    $roles = $user->roles->pluck('name');
    
                    // $permissions = $user->getAllPermissions()->pluck('name');
                    
                    return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'roles' => $roles,
                    ];
                });
    
            return response()->json([ "message " =>"users fetched succesfuly ",
                'users' => $users,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Failed to fetch users', 'message' => $th->getMessage()], 500);
        }
    }

}
