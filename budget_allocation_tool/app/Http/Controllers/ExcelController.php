<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DataImport; // Replace with your actual import class
use App\Imports\EmployeeDataImportForTaxTemplate;
use App\Exports\DataExport; // Replace with your actual export class
use App\Exports\EmployeeExport;
use App\Imports\BudgetAllocationImport;
use App\Imports\SpecificSheetsImport;
use App\Services\BudgetAllocationService;
use Illuminate\Support\Facades\Storage;
use App\Models\Log;
use Illuminate\Validation\ValidationException;  



class ExcelController extends Controller
{
    // Method to import Excel data
    public function importExcel(Request $request)
    {   

      
        // Validate that a file has been uploaded
        try {  
            $request->validate([  
                'payroll_data' => 'required|mimes:xlsx',  
                'loe_data' => 'required|mimes:xlsx',  
                'date_picker' => 'required',  
                'document_number' => 'required',  
                'external_doc_reference' => 'required',  
                'exchange_rate' => 'required',  
            ], [  
                'payroll_data.required' => 'The payroll data file is required.',  
                'payroll_data.mimes' => 'The payroll data must be an Excel file (xlsx).',  
                'loe_data.required' => 'The loe data file is required.',  
                'loe_data.mimes' => 'The loe data must be an Excel file (xlsx).',  
                'date_picker.required' => 'Please select a date.',  
                'document_number.required' => 'Document number is required.',  
                'external_doc_reference.required' => 'External document reference is required.',  
                'exchange_rate.required' => 'Exchange rate is required.',  
            ]);  
        
        } catch (ValidationException $e) {  
            return response()->json([  
                'message' => 'Validation failed.',  
                'errors' => $e->errors()  
            ], 422); // Return a 422 Unprocessable Entity response with validation errors  
        }
        
        
        $sheetNames = ['Processed', 'GL Acct Lookup']; // Replace with your specific sheet names

        $payroll_file = $request->file('payroll_data');
        $loe_file = $request->file('loe_data');

        $submission_date=  $request["date_picker"];
        $doc_number= $request["document_number"];
        $external_doc_reference= $request["external_doc_reference"];
        $exchange_rate=  $request["exchange_rate"];


        
        
        // VALIDATIONS THAT NEEDS TO BE MADE ARE , VALID INFORMATION AS VALID INPUT . 
        // 

        
        // Import the Excel file, passing the list of sheets to the import class
        // Excel::import(new SpecificSheetsImport($sheetNames), $file);
        // Get the file from the request
        
        
        // Import the EMPLOYEE RELATED Excel file
        $employeeImport = new DataImport("Payroll Data");
        Excel::import($employeeImport, $payroll_file);
        $employees = $employeeImport->employees;
        
        // return $employees;

        $budgetAllocationImport = new BudgetAllocationImport("LOE Data");
        Excel::import($budgetAllocationImport, $loe_file);
        $fundData = $budgetAllocationImport->fundData;
        
        
        // return  ($fundData);
      


        // return response()->json(['emp'=>count($employees),'loe'=>count($fundData)]);
        
        $service = new BudgetAllocationService();
        $processedData = $service->distributePayments($employees, $fundData,$submission_date,$doc_number,$external_doc_reference,$exchange_rate);
        
        Log::create([
            "user_id"=>auth()->user()->id,
            "action"=>"Generate payroll allocation data"

        ]);
        return $processedData;
        $export = new EmployeeExport($processedData);
        
        
        return Excel::download($export, 'distributed_salaries.csv');  //xlsx
    }
    
    public function generateTaxDeclarationTemplate(Request $request)
    {

        
        // $request->validate([
        //     'payroll_data' => 'required|mimes:xlsx',
        //     "exchange_rate"=>"required", 
        // 'date_picker'=> "required",]);


        $payroll_file = $request->file('payroll_data');
        $exchange_rate=  $request["exchange_rate"];
        $submission_date=  $request["date_picker"];
        // return intval($exchange_rate);


        // return "here";
        $employeeImport = new EmployeeDataImportForTaxTemplate("Payroll Data",$exchange_rate,$submission_date);
        Excel::import($employeeImport, $payroll_file);
        $employees = $employeeImport->employees;
        Log::create([
            "user_id"=>auth()->user()->id,
            "action"=>"Generate Tax template"

        ]);
        return $employees;
        
        
    }
    // Method to export data to Excel
    public function export()
    {
        
    }
}
