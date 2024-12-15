<?php

namespace App\Services;
use Carbon\Carbon;
use App\Models\Payroll;
use App\Models\Employee;

class BudgetAllocationService
{
    /**
     * Distribute the payments over the funds.
     *
     * @param array $employees
     * @param array $funds
    
     * @param string $date
     * @param string $doc_reference
     * @param string $doc_number

     * @return array
     */
    public function distributePayments(array $employees, array $funds_all,$date,$doc_number,$doc_reference,$exchange_rate)
    {
        

        $result = [];  

        foreach ($employees as $employee) {  
            // Initialize temporary arrays for each category  
            $salaryRecords = [];  
            $pensionRecords = [];  
            $pfRecords = []; 
            
            
            $funds=$funds_all[$employee["id"]]??[];
            foreach ($funds as $fund) {  



                // Calculate distributions based on LOE Percentage  

                
                $salaryAllocation = $this->calculateAllocation($employee['salary'], $fund['loe_percentage']);  
                $pensionAllocation = $this->calculateAllocation($employee['pension_11'], $fund['loe_percentage']);  
                $pfAllocation = $this->calculateAllocation($employee['pf_employer'], $fund['loe_percentage']);  

                if($salaryAllocation!=0)
                $salaryRecords[] = $this->formatRecord($employee, $fund, 'Salary', $salaryAllocation,$date,$doc_number,$doc_reference,$exchange_rate);  

                // Add pension distribution record  
                if($pensionAllocation!=0)
                $pensionRecords[] = $this->formatRecord($employee, $fund, 'Pension', $pensionAllocation,$date,$doc_number,$doc_reference,$exchange_rate);  

                // Add PF distribution record  
                if($pfAllocation!=0)
                $pfRecords[] = $this->formatRecord($employee, $fund, 'PF', $pfAllocation,$date,$doc_number,$doc_reference,$exchange_rate);  
            }  

            // After finishing the inner loop, merge the temporary arrays into the result array 


            
            $deductions = [];

            if($employee["tax"]!=0){ $deductions[]=$this->formatRecord($employee,null,"Income tax",$employee["tax"],$date,$doc_number,$doc_reference,$exchange_rate);
            } 
            if($employee["pf_total"]!=0)
            { $deductions[]=$this->formatRecord($employee,null,"PF Deduct.",$employee["pf_total"],$date,$doc_number,$doc_reference,$exchange_rate);
            }
            if($employee["pension_total"]!=0){ $deductions[]=$this->formatRecord($employee,null,"Pension Deduct.",$employee["pension_total"],$date,$doc_number,$doc_reference,$exchange_rate);
                }  
            if($employee["advance_on_salary"]!=0){ $deductions[]=$this->formatRecord($employee,null,"Advance Deduct.",$employee["advance_on_salary"],$date,$doc_number,$doc_reference,$exchange_rate);
            }  
            if($employee["other_deduction"]!=0){$deductions[]=$this->formatRecord($employee,null,"Other Deduct.",$employee["other_deduction"],$date,$doc_number,$doc_reference,$exchange_rate);
            }
            
            if($employee["net_pay"]!=0){
                $deductions[]=$this->formatRecord($employee,null,"Net Pay Deduct.",$employee["net_pay"],$date,$doc_number,$doc_reference,$exchange_rate);
            }
           
            
            $result = array_merge($result, $salaryRecords, $pensionRecords, $pfRecords,$deductions);  
        }  


        return $result;
    }

    /**
     * Calculate allocation based on amount and LOE percentage.
     *
     * @param float $amount
     * @param float $loePercentage
     * @return float
     */
    private function calculateAllocation($amount, $loePercentage)
    {
        return   round($amount * (floatval($loePercentage )), 2);;
    }

    /**
     * Format the result record for each allocation.
     *
     * @param array $employee
     * @param array $fund
     * @param string $type
     * @param float $amount
     * @param string $date
     * @param string $doc_reference
     * @param string $doc_number
     * @return array
     */
    private function formatRecord($employee, $fund, $type, $amount,$date,$doc_number,$doc_reference,$exchange_rate)


    {
        
        $exchange_rate=filter_var($exchange_rate, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $list_of_dedudctions= ["Income tax","Pension Deduct.","PF Deduct.","Advance Deduct.","Other Deduct.","Net Pay Deduct."];


        
        // USER INPUTS . 
        $posting_date =  Carbon::createFromFormat('Y-m-d', $date)->format('m/d/Y'); ;
        $document_no =$this-> reformatDocumentNumber($doc_number,$employee['id']);
        $external_document_no = $doc_reference;

        // Static Fields or Fixed Values
        $account_type = 'G/L account';
        $balance_account_type = 'G/L Account';

        // DERIVED FIELDS  

        $account_no= $this->getGLAccountNumber($type,$employee);
        

        $et="ET";
        if (in_array($type, $list_of_dedudctions)){
            $fund_no="GEN";
        }else{
            $fund_no = $fund!=null ?   $et.$fund['fund_name']:"";              
        }


        $dimension_1 = $employee["location" ];
        $dimension_2 =  $this->getDepartmentSector($employee['department']); //$sector_lookup[$department];

      
        //  if salary  => Budget Line   and for PF and pension  fringe line  then for deductions it is empty . 
        if($type=="Salary"){
            $dimension_3 = $fund!=null ?$fund['budget_line']:"";       

        }else if($type == "PF"||$type=="Pension"){
            $dimension_3 = $fund!=null ?$fund['fringe_line']:"";
        } else{
            $dimension_3="";
        }
        
        // Dimension 4: Initiative (lookup + concatenation)        
        $dimension_4 = $this->getInitiative($employee['department'],$employee['id']);

        
        // Employee Info        
        $dimension_6 = $employee['id'];   // ID         
        $dimension_6=$this->getIdDimension($type,$employee);
        
  
 
        // Description Field
        $date = Carbon::parse($posting_date);

        // Extracting the month and year
        $month = $date->format('M');  // 'Oct'
        $year = $date->format('Y'); 
    
        $pos=$employee['position'];
        $percent= $fund!=null ?  $fund['loe_percentage']*100 : 1;

        //  CREATE DIFFERENT DESCRIPTION FOR DEDUCTIONS AND BENEFITS
        if (in_array($type, $list_of_dedudctions)){
            $description =  "{$employee["name"]} $type " ;  
        }else{
            $description =   "{$type} {$month} {$year}_{$pos}_{$percent}%";            
        }

       
        // DISTRIBUTED AMOUNT 
        if (in_array($type, $list_of_dedudctions)){
            $amount = -$amount;
            // if($type=="PF Deduct."){

            //     // this to convert usd based PF value to birr . 
            //     $amount*=$exchange_rate;
                
            // }
        }else{
            
            $amount = $amount;
            // if($type=="PF"){
            //     $amount*=$exchange_rate;
            // }

        }


        // Empty Fields
        $document_type = "";
        $dimension_speedkey_code = "";
        $dimension_5 = "";
        $dimension_7 = "";
        $dimension_8 = "";
        $budget_plan_no = "";
        $currency_code = "";
        $allocation_no = "";
        $balance_account_no = "";
        $applies_to_document_type = "";
        $applies_to_document_no = "";


       
        // BEFORE RETURNING THE DATA , IT WILL INSERT TO THE DATABASE . 

        $new_employee = Employee::firstOrCreate(
            ['emp_id' => $employee['id']], // Using 'id' to search for an existing employee
            [
                'emp_id'=>$employee['id'],                
                'name' => $employee['name'],
                'sector' => $dimension_2,
                'location_name' => $employee["location"],
                'first_process_date' =>$date,
                'last_process_date'=>$date,
            ]
        );

        // updating the last process date to the current one . 
        $new_employee->update([
            'last_process_date' => $date,
        ]);

        //TODO  when writing If the employee has already been calculated for that month we are going to overwrite the value. 
        
        $new_employee->payrolls()->create(['date' => $date,
            'fund_no' => $fund_no ?? null,
            'type' => $type,
            'amount_birr' => $amount,
            'amount_usd' => $amount / $exchange_rate,
            'gl_account'=>$account_no]);



        return  [
            'posting_date' => $posting_date,  // User input
            'document_type' => $document_type,  // Empty
            'document_no' => $document_no,  // User input
            'account_type' => $account_type,  // Static value (G/L account)
            'account_no' => $account_no,  // Determined based on some conditions
            'fund_no' => $fund_no,  // Concatenation of ET and fund on LOE sheet (K column)
            'dimension_speedkey_code' => $dimension_speedkey_code,  // Empty
            'dimension_1' => $dimension_1,  // Location result of lookup
            'dimension_2' => $dimension_2,  // Sector result of lookup
            'dimension_3' => $dimension_3,  // Budget Line or Fringe Line (based on condition)
            'dimension_4' => $dimension_4,  // Initiative (lookup + concatenation)
            'dimension_5' => $dimension_5,  // Empty
            'dimension_6' => $dimension_6,  // Employee ID
            'dimension_7' => $dimension_7,  // Empty
            'dimension_8' => $dimension_8,  // Empty
            'external_document_no' => $external_document_no,  // User input
            'description' => $description,  // Description with format: "Payment type month year_position_loe%age"
            'amount' => $amount,  // Allocated amount
            'budget_plan_no' => $budget_plan_no,  // Empty
            'currency_code' => $currency_code,  // Empty (Express Users Leave Blank)
            'allocation_no' => $allocation_no,  // Empty
            'balance_account_type' => $balance_account_type,  // G/L Account
            'balance_account_no' => $balance_account_no,  
            'applies_to_document_type' => $applies_to_document_type,  // Empty
            'applies_to_document_no' => $applies_to_document_no,  // Empty
        ];
        
    }

    public function reformatDocumentNumber(string $document_number,string $emp_id){
        $extracted_id = substr($emp_id, 0, 2);
        // $deptShort = "NUT"; 
        if($extracted_id=="AS"){
            $extracted_id="BG";
        } else if($extracted_id=="ST"){
            $extracted_id="WH";
        }

        return $document_number."-".$extracted_id;

    }

    public function getInitiative(string $deptShort,string $emp_id){
        $extracted_id = substr($emp_id, 0, 2);
        // $deptShort = "NUT"; 
        if($extracted_id=="AS"){
            $extracted_id="BG";
        } else if($extracted_id=="ST"){
            $extracted_id="WH";
        }

        if ($deptShort == "PRO" || $deptShort == "PON") {  
            $result = $extracted_id . "N01";  
        } elseif ($deptShort == "WASH") {  
            $result = $extracted_id . "H01";  
        } elseif ($deptShort == "PRO") {  
            $result = $extracted_id . "P01";  
        } elseif ($deptShort == "FSL") {  
            $result = $extracted_id . "F01";  
        } else {  
            $result = $extracted_id . "C01"; // For any other value  
        } 

        return $result;
    }

    /**
 * Get Department Short Code using switch statement
 *
 * @param string $department_name
 * @return string
 */
public function getDepartmentSector($department_name)
{
    // Use a switch statement to return the appropriate short code
    switch ($department_name) {
        case 'FSL':
            return 'AGR';
        case 'LOG':
        case 'OPS':
        case 'OPR':
        case 'AUDIT AND COMPLIANCE':
        case 'HR':
        case 'FIN':
        case 'Grant':
        case 'Admin':
        case 'CD':
        case 'OD':
            return 'COORD';
        case 'MEAL':
            return 'MEAL';
        case 'R2G':
        case 'NUT':
        case 'PON':
            return 'NUT';
        case 'PRO':
        case 'PROTECTION':
            return 'PROT';
        case 'ERP':
            return 'SHELT';
        case 'WASH':
            return 'WASH';
        default:
            return 'COORD';
    }
}

    /**
     * Get Department Short Code using switch statement
     *
     * @param string $type
     * @param array $employee
     * @return string
     */
    public function getGlAccountNumber($type,$employee)
    {   
        $account_no='';
        if($type=="Salary"){
            $account_no =$this-> getDepartmentSector($employee['department'])=="COORD" ?52001 : 52002;   // this is for salary . 

        }else if($type == "PF"||$type=="Pension"){
            $account_no= 53001;
     
        } else if ($type=="Income tax"){
            $account_no = 21222;
        }else if ($type=="Pension Deduct."){
            $account_no = 21223;
        }else if ($type=="PF Deduct."){
            $account_no = 21207;
        }else if ($type=="Advance Deduct."){
            $account_no = 12320 ;
        }else if ($type=="Other Deduct."){
            $account_no = 61201;
        } else if ($type == "Net Pay Deduct."){
            $account_no = 21130;
                }

        return $account_no;
    }

     /**
     * Get Department Short Code using switch statement
     *
     * @param string $type
     * @param array $employee
     * @return string
     */
    public function getIdDimension($type,$employee)    {   
        $emp_id=$employee['id'];
        $extracted_id_base = substr($emp_id, 0, 2);
        if($type=="Salary"||$type == "PF"||$type=="Pension"||$type=="Advance Deduct."||$type=="Other Deduct."){
            $dimension_6 = $employee['id'];          

        }else if($type=="Income tax"){
            $dimension_6 =  "RA-$extracted_id_base-001";
        }
        else if ($type=="Pension Deduct."){
            $dimension_6 =  "RA-$extracted_id_base-003";
        } else {
            $dimension_6 = "";
        }

        return $dimension_6;

    }


}


