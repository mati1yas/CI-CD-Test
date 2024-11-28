<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;  
class FileUploadDownloadController extends Controller
{
    public function uploadFilesToServer(Request $request){
        // Validate files  

       
    
    // $request->validate([  
    //     'file1' => 'required|file|mimes:xls,xlsx|max:2048', // Validate file 1  
    //     'file2' => 'required|file|mimes:xls,xlsx|max:2048', // Validate file 2  
    // ]);    

    // Store files  

    $file1 = $request->file('file1');  
    $file2 = $request->file('file2');  

    $file1Name = $file1->getClientOriginalName();  
    $file2Name = $file2->getClientOriginalName();  

    $file1Extension = $file1->getClientOriginalExtension();  
    $file2Extension = $file2->getClientOriginalExtension();  

    // Make the filenames URL-safe without changing the extension  
    $file1NameSafe = preg_replace('/[^A-Za-z0-9\-]/', '-', strtolower(trim(pathinfo($file1Name, PATHINFO_FILENAME)))) . '.' . $file1Extension;  
    $file2NameSafe = preg_replace('/[^A-Za-z0-9\-]/', '-', strtolower(trim(pathinfo($file2Name, PATHINFO_FILENAME)))) . '.' . $file2Extension;   

    $file1Path = $request->file('file1')->storeAs('uploads', $file1NameSafe, 'public');  
    $file2Path = $request->file('file2')->storeAs('uploads', $file2NameSafe, 'public');   
  // Generate URLs for the uploaded files  
    $file1Url = Storage::url($file1Path);  
    $file2Url = Storage::url($file2Path);  
  
    $appUrl = url('/');
    return response()->json([  
        'success' => true,  
        'message' => 'Files uploaded successfully.',  
        'file1_url' => $appUrl.$file1Url, // URL for the first file  
        'file2_url' => $appUrl.$file2Url, // URL for the second file  
    ]);  
    }
}
