<?php

namespace App\Imports;

use App\Models\YourModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;


class EmployeeDataImportForTaxTemplate implements ToCollection, WithMultipleSheets,WithCalculatedFormulas
{   
    
    public $employees = [];
    private $sheetName;
    private $exchangeRate;
    private $date;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function __construct($sheetName,$exchangeRate,$date)
    {
        // Save the sheet name to be used later in the process
        $this->sheetName = $sheetName;
        $this->exchangeRate = $exchangeRate;
        $this->date = $date;


    }
    public function collection(Collection $rows)
    {

        $count=0;
        $this->exchangeRate=filter_var($this->exchangeRate, FILTER_SANITIZE_NUMBER_INT);;
        $row_no=1;
        foreach ($rows as $row) 
        {
            // Example: Process each row
            // Assuming your Excel has columns like 'name' and 'email'
            $count+=1;
            if ($count<5) continue;
            if ($row[0]==null) continue;

            $name = $row[2];
            $date = $this->date;
            $basic_salary=($row[8] )* $this->exchangeRate;
           
            $transport_allowance=($row[10]??0)* $this->exchangeRate;
            $over_time=($row[9]??0)*$this->exchangeRate;
            $temp_inflation=($row[13]??0)*$this->exchangeRate;
            $relocation_payment=($row[12]??0)*$this->exchangeRate;
            $acting_allowance =($row[14]??0)*$this->exchangeRate;
            $seniority_bonus = ($row[15]??0)*$this->exchangeRate;
            $total_taxable=$basic_salary+$transport_allowance+$over_time+$temp_inflation+$relocation_payment+$acting_allowance+$seniority_bonus;
           
            $tax_whitheld=$this->calculateTax($total_taxable );
            $cost_sharing="";
            $net_pay=$total_taxable-$tax_whitheld;
            $employee_signature="";
            /*
            name 
            date 
            I -> Basic Salary
            K -> taxable transportation allowance 
            J -> OT 
            N -> temp inflation 
            M - > relocation payment 
            O -> acting allowance 
            P - > seniority bonus . 

            total taxable 

            tax whithold 
            cost sharing -> empty 
            net page -> toal -tax whithhel


            */



           

            
            // net pay and difference to be calculated ;

            $this->employees[] = [  
                "row_no"=>$row_no,               
                "name" => $name,  
                "date" => $date,  
                "basic_salary" => $basic_salary,  
                "transport_allowance" => $transport_allowance,  
                "over_time" => $over_time,  
                "temp_inflation" => $temp_inflation,  
                "relocation_payment" => $relocation_payment,  
                "acting_allowance" => $acting_allowance,  
                "seniority_bonus" => $seniority_bonus,  
                "total_taxable" => $total_taxable,  
                "tax_withheld" => $tax_whitheld, 
                "cost_sharing" => $cost_sharing, 
                "net_pay" => $net_pay,  
                "employee_signature" => $employee_signature,  
                  
               
            ];
          
            $row_no+=1;
           
        }
    }


    function calculateTax($L12) {  
        if ($L12 < 601) {  
            return $L12 * 0;  
        } elseif ($L12 >= 601 && $L12 < 1651) {  
            return $L12 * 0.1 - 60;  
        } elseif ($L12 >= 1651 && $L12 < 3201) {  
            return $L12 * 0.15 - 142.5;  
        } elseif ($L12 >= 3201 && $L12 < 5251) {  
            return $L12 * 0.2 - 302.5;  
        } elseif ($L12 >= 5251 && $L12 < 7801) {  
            return $L12 * 0.25 - 565;  
        } elseif ($L12 >= 7801 && $L12 < 10901) {  
            return $L12 * 0.3 - 955;  
        } else {  
            return $L12 * 0.35 - 1500;  
        }  
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
