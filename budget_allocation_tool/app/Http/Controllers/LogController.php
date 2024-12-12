<?php

namespace App\Http\Controllers;

use App\Http\Resources\LogResource;
use Illuminate\Http\Request;

use App\Models\Log;

use App\Models\User;

class LogController extends Controller
{
    public function getLogs(){

        $logs = Log::with('user')->orderBy('created_at', 'desc')->get();  
   
        return LogResource::collection($logs);

    }

    public function getUserLogs(Request $request){

          //  will be accepting user id and collect all the logs for that user . 

        $userid=$request['user_id'];
        $user= User::findOrFail($userid);


        // return $user;
        $user_logs=$user->logs();
        return LogResource::collection($user_logs);
      

    }

    public function createLog(){


        // return "about to create  test logs";

       

        Log::create([
            "user_id"=>3,
            "action"=>"action one 1"

        ]);


        Log::create([
            "user_id"=>3,
            "action"=>"action two 1"

        ]);

        Log::create([
            "user_id"=>3,
            "action"=>"action one 2"

        ]);

        Log::create([
            "user_id"=>3,
            "action"=>"action two 2"

        ]);
        
    }
}
