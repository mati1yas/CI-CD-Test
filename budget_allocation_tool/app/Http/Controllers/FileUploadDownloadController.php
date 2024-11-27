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

    // Make the filenames URL-safe (replace spaces with dashes and remove special characters)  
    $file1NameSafe = preg_replace('/[^A-Za-z0-9\-]/', '-', strtolower(trim($file1Name)));  
    $file2NameSafe = preg_replace('/[^A-Za-z0-9\-]/', '-', strtolower(trim($file2Name)));  

   
    $file1Path = $request->file('file1')->storeAs('uploads', $file1Name, 'public');  
    $file2Path = $request->file('file2')->storeAs('uploads', $file2Name, 'public');   

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
