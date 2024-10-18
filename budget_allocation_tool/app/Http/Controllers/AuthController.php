<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
        {
            
           
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

           
            // Assign a role to the user
            // $user->assignRole('user');

            return response()->json(['message' => 'User registered successfully']);
        }


        public function login(Request $request)
            {
               
                $request->validate([
                    'email' => 'required|email',
                    'password' => 'required|string',
                ]);

                if (!Auth::attempt($request->only('email', 'password'))) {
                    return response()->json(['message' => 'Invalid login details'], 401);
                }

                $user = Auth::user();
                $token =$user->createToken('auth_token')->plainTextToken;
                // $token="token-value";

                return response()->json(['token' => $token, 'user'=>$user]);
            }


}
