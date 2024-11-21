<?php
namespace App\Http\Controllers;
use App\Mail\ResetPassword;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{



        
        public function sendResetEmail(Request $request)
        {
            $this->forgotPassword($request);
            
        }
          //  handles forgot password request upon forgot and registry 
            public function forgotPassword(Request $request)
        {

            
             
               
                $validator = Validator::make($request->all(), [
                    'email' => ['required', 'string', 'email', 'max:255'],
                ]);

                if ($validator->fails()) {
                    return new JsonResponse(['success' => false, 'message' => $validator->errors()], 422);
                }

                $verify = User::where('email', $request->all()['email'])->exists();

                if ($verify) {
                    $verify2 =  DB::table('password_resets')->where([
                        ['email', $request->all()['email']]
                    ]);

                    if ($verify2->exists()) {
                        $verify2->delete();
                    }

                    $token = random_int(100000, 999999);
                    $password_reset = DB::table('password_resets')->insert([
                        'email' => $request->all()['email'],
                        'token' =>  $token,
                        'created_at' => Carbon::now()
                    ]);

                    if ($password_reset) {
                        Mail::to($request->all()['email'])->send(new ResetPassword($token));

                        return new JsonResponse(
                            [
                                'success' => true, 
                                'message' => "Please check your email for a 6 digit pin"
                            ], 
                            200
                        );
                    }
                } else {
                    return new JsonResponse(
                        [
                            'success' => false, 
                            'message' => "This email does not exist"
                        ], 
                        400
                    );
                }
        }





        public function resetPassword(Request $request){

            $validator = Validator::make($request->all(), [
                'email' => ['required', 'string', 'email', 'max:255'],
                'token' => ['required'],
            ]);

            if ($validator->fails()) {
                return new JsonResponse(['success' => false, 'message' => $validator->errors()], 422);
            }

            $check = DB::table('password_resets')->where([
                ['email', $request->all()['email']],
                ['token', $request->all()['token']],
            ]);

            if ($check->exists()) {
                $difference = Carbon::now()->diffInSeconds($check->first()->created_at);
                if ($difference > 86400) {
                    return new JsonResponse(['success' => false, 'message' => "Pin has Expired"], 400);
                }

                $delete = DB::table('password_resets')->where([
                    ['email', $request->all()['email']],
                    ['token', $request->all()['token']],
                ])->delete();

                $user = User::where('email',$request->email);
                $user->update([
                    'password'=>Hash::make($request->password)
                ]);
            
                // $token = $user->first()->createToken('APItoken')->plainTextToken;
                $token="new token value";
                return new JsonResponse(
                    [
                        'success' => true, 
                        'message' => "Your password has been reset", 
                        'token'=>$token
                    ], 
                    200
                );
            } else {
                return new JsonResponse(
                    [
                        'success' => false, 
                        'message' => "Invalid Pin or Email or "
                    ], 
                    400
                );
            }

            
            

        }

        public function  changePassword(Request $request){

           
            $validator=Validator::make($request->all(),[
                "new_password"=>"required|min:8",
                "current_password"=>"required",
            ]);
            if ($validator->fails()) {
                return response()-> json([ 'message' => $validator->errors()], 422);
            }

            $user = auth()->user();
          
            if (!$user) return "no user";
        
            if (!Hash::check($request->current_password,$user->password)){
                return response()->json([
                    "message"=>"Current Password is Wrong"
                ]);
            }

            $user->update([
                "password"=>Hash::make($request->new_password),
            ]);


            return   response()->json([
                "message"=>"Password changed Succesfully"
            ]);

        }

}
