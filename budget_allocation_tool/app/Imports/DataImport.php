<?php

namespace App\Imports;

use App\Models\YourModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;


class DataImport implements ToCollection, WithMultipleSheets,WithCalculatedFormulas
{   
    
    public $employees = [];
    private $sheetName;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function __construct($sheetName)
    {
        // Save the sheet name to be used later in the process
        $this->sheetName = $sheetName;
    }
    public function collection(Collection $rows)
    {

        $count=0;
        foreach ($rows as $row) 
        {
            // Example: Process each row
            // Assuming your Excel has columns like 'name' and 'email'
            $count+=1;
            if ($count<8) continue;
            if ($row[0]==null) continue;
            $id = $row[0];
            $name = $row[1];    
            $working_place=$row[2];
            $position=$row[4];
            $department=$row[5];
            $location= $this->getLocation($id);//  $row[];
            $salary_ETB=$row[37];
            $pension_7=$row[32];
            $pension_11=$row[33];
            $pension_total= $pension_7+$pension_11;
            $PF_employee=$row[34];
            $PF_employer=$row[35];
            $PF_total=$PF_employee+$PF_employer;
            $net_pay= $row[41];
            $tax_ETB=$row[31]??0;
            $advance_on_salary =$row[39]??0;
            $other_deduction =$row[40]??0;

            
            // net pay and difference to be calculated ;

            $this->employees[] = [
                "id" => $id,
                "name" => $name,
                "working_place" => $working_place,
                "position" => $position,
                "department" => $department,
                "location" => $location,
                "salary" => $salary_ETB,
                "pension_11"=> $pension_11,
                "pension_total" => $pension_total,
                "pf_employer"=>$PF_employer,
                "pf_total" => $PF_total,
                "tax" => $tax_ETB,
                "net_pay"=>$net_pay,
                "advance_on_salary" => $advance_on_salary,
                "other_deduction" => $other_deduction
            ];
            
          
           
        }
    }

    public function  getLocation(string $emp_id){
         $extracted_id = substr($emp_id, 0, 2);
         switch ($extracted_id) {  
            case "AA":  
                $result = "ACFUS-ET02";  
                break;  
            case "BO":  
                $result = "ACFUS-ET03";  
                break;  
            case "GA":  
                $result = "ACFUS-ET04";  
                break;  
            case "HA":  
                $result = "ACFUS-ET05";  
                break;  
            case "SO":  
                $result = "ACFUS-ET06";  
                break;  
            case "WH":  
                $result = "ACFUS-ET07";  
                break;  
            case "WO":  
                $result = "ACFUS-ET08";  
                break;  
            case "TG":  
                $result = "ACFUS-ET09";  
                break;  
            default:  
                $result = "Invalid ID"; // Handle unexpected values  
        }  
        

         return $result;
    }

    public function sheets(): array
    {
        // This method is used for multi-sheet imports
        return [
            $this->sheetName => $this,
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // Log or handle the unknown sheet (this is optional)
        logger()->warning("Skipped unknown sheet: {$sheetName}");
    }

}
