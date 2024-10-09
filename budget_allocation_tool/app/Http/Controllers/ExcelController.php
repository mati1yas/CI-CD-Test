<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DataImport; // Replace with your actual import class
use App\Exports\DataExport; // Replace with your actual export class
use App\Exports\EmployeeExport;
use App\Imports\BudgetAllocationImport;
use App\Imports\SpecificSheetsImport;
use App\Services\BudgetAllocationService;
use Illuminate\Support\Facades\Storage;


class ExcelController extends Controller
{
    // Method to import Excel data
    public function importExcel(Request $request)
    {   

      
        // Validate that a file has been uploaded
        $request->validate([
            'payroll_data' => 'required|mimes:xlsx',
            'loe_data' => 'required|mimes:xlsx',

            'date_picker'=> "required",
            "document_number"=>"required",
            "external_doc_reference"=>"required"
        ]);
        
        
        $sheetNames = ['Processed', 'GL Acct Lookup']; // Replace with your specific sheet names

        $payroll_file = $request->file('payroll_data');
        $loe_file = $request->file('loe_data');

        $submission_date=  $request["date_picker"];
        $doc_number= $request["document_number"];
        $external_doc_reference= $request["external_doc_reference"];

        
        
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
        // return $employees;
        
        $budgetAllocationImport = new BudgetAllocationImport("Input-2 (LoE)");
        Excel::import($budgetAllocationImport, $loe_file);
        $fundData = $budgetAllocationImport->fundData;
        
        
        // return $fundData;
        
        $service = new BudgetAllocationService();
        $processedData = $service->distributePayments($employees, $fundData,$submission_date,$doc_number,$external_doc_reference);
        // return $processedData;
        $export = new EmployeeExport($processedData);

        
        return Excel::download($export, 'distributed_salaries.csv');  //xlsx
    }
    
    
    // Method to export data to Excel
    public function export()
    {
        
    }
}
