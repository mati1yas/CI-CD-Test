<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use Spatie\Permission\Models\Role;


class AuthController extends Controller
{
    public function register(Request $request)
        {
            
           
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt( "abcdABCD1234!@#$"),
            ]);

            $forgotPassordController=new ForgotPasswordController();

            // this is to send reset password .
            $forgotPassordController->sendResetEmail($request);

            $role = Role::where('name', "user")->first();
            

            $user->assignRole($role);
            $user['role']=[$role->name];
            /// use App\Models\Log; 
            Log::create([
                "user_id"=>auth()->user()->id,
                "action"=>"Created a new user with email :" .$request->email,
            ]);           

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

                try {
                    $roles = $user->roles()->pluck('name');
    
                    
                } catch (\Throwable $th) {
                    $roles = "could not fetch roles";
                    $permissions = "could not fetch permissions";
                }
                // $token="token-value";
               
                
                return response()->json(['token' => $token, 'user'=>$user, 'roles' => $roles, ]);
            }


}
