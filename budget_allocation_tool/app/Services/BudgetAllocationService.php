<?php

namespace App\Services;
use Carbon\Carbon;


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
    public function distributePayments(array $employees, array $funds_all,$date,$doc_number,$doc_reference)
    {
        // $result = [];

        // foreach ($employees as $employee) {

            
        //     foreach ($funds as $fund) {
        //         // Calculate distributions based on LOE Percentage
        //         $salaryAllocation = $this->calculateAllocation($employee['salary'], $fund['loe_percentage']);
        //         $pensionAllocation = $this->calculateAllocation($employee['pension'], $fund['loe_percentage']);
        //         $pfAllocation = $this->calculateAllocation($employee['pf'], $fund['loe_percentage']);

        //         // Add salary distribution record
        //         $result[] = $this->formatRecord($employee, $fund, 'Salary', $salaryAllocation);

        //         // Add pension distribution record
        //         $result[] = $this->formatRecord($employee, $fund, 'Pension', $pensionAllocation);

        //         // Add PF distribution record
        //         $result[] = $this->formatRecord($employee, $fund, 'PF', $pfAllocation);
        //     }
        // }

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

                // Add salary distribution record  
                $salaryRecords[] = $this->formatRecord($employee, $fund, 'Salary', $salaryAllocation,$date,$doc_number,$doc_reference);  

                // Add pension distribution record  
                $pensionRecords[] = $this->formatRecord($employee, $fund, 'Pension', $pensionAllocation,$date,$doc_number,$doc_reference);  

                // Add PF distribution record  
                $pfRecords[] = $this->formatRecord($employee, $fund, 'PF', $pfAllocation,$date,$doc_number,$doc_reference);  
            }  

            // After finishing the inner loop, merge the temporary arrays into the result array 
            
            $deductions = [];
            // $deductions[]=$this->formatRecord($employee,null,"deductions tax",$employee["tax"],$date,$doc_number,$doc_reference);
            // $deductions[]=$this->formatRecord($employee,null,"deductions pf",$employee["pf_total"],$date,$doc_number,$doc_reference);
            // $deductions[]=$this->formatRecord($employee,null,"deductions pension",$employee["pension_total"],$date,$doc_number,$doc_reference);
            // $deductions[]=$this->formatRecord($employee,null,"deductions advance",$employee["advance_on_salary"],$date,$doc_number,$doc_reference);

            // $deductions[]=$this->formatRecord($employee,null,"deductions others",$employee["other_deduction"],$date,$doc_number,$doc_reference);


            
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
    private function formatRecord($employee, $fund, $type, $amount,$date,$doc_number,$doc_reference)


    {


        
                // User Inputs
        $posting_date =  Carbon::createFromFormat('Y-m-d', $date)->format('m/d/Y'); ;
        $document_no = $doc_number;
        $external_document_no = $doc_reference;

        // Static Fields or Fixed Values
        $account_type = 'G/L account';
        $balance_account_type = 'G/L Account';

        // Derived Fields   //  TODO   needs further conditioning for the benefits(pension and Pf) and deduction . 
        $account_no =$this-> getDepartmentSector($employee['department'])=="COORD"?52001 : 52002;   // this is for salary . 

        $et="ET";
        $fund_no = $fund!=null ?  $et . $fund['fund_name']:"";
        $dimension_1 = $employee["location" ];
        $dimension_2 =  $this->getDepartmentSector($employee['department']); //$sector_lookup[$department];

        // Dimension 3: Check whether it's Budget Line or Fringe Line based on a condition
        $condition=true;   //  if salary  => Budget Line   and for PF and pension  fringe line  then for deductions it is empty . 
        if ($condition) {
            $dimension_3 = $fund!=null ?$fund['fringe_line']:"";
        } else {
            $dimension_3 = $fund!=null ?$fund['budget_line']:"";
        }

        // Dimension 4: Initiative (lookup + concatenation)
        
        $dimension_4 = $this->getInitiative($employee['department'],$employee['id']);

        // Employee Info
        $dimension_6 = $employee['id'];
 
        // Description Field
        $date = Carbon::parse($posting_date);

        // Extracting the month and year
        $month = $date->format('M');  // 'Oct'
        $year = $date->format('Y'); 
    
        $pos=$employee['position'];
        $percent= $fund!=null ?  $fund['loe_percentage']:"";
        $description =  $type === "deductions" ? "Deduction": "{$type} {$month} {$year}_{$pos}_{$percent}%";  

        // $description = {$type} {$month} {$year}_{$employee['position']}_{$employee["loe_percentage"]}%;

        // Amount
        $amount =$amount;

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
            'balance_account_no' => $balance_account_no,  // Empty
            'applies_to_document_type' => $applies_to_document_type,  // Empty
            'applies_to_document_no' => $applies_to_document_no,  // Empty
        ];
        
    }


    public function getInitiative(string $deptShort,string $emp_id){
        $extracted_id = substr($emp_id, 0, 2);
        // $deptShort = "NUT";  

        if ($deptShort == "PRoTCETion" || $deptShort == "PON") {  
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

}
