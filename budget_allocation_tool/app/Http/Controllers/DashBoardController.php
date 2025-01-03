<?php

namespace App\Http\Controllers;
use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon;



class DashBoardController extends Controller
{
    /**
     * Get the count of employees in a given sector.
     *
     * @param string $sector
     * @return int
     */
    public function getEmployeeCountBySector(Request $request): \Illuminate\Support\Collection
    
    {
        // Count employees by sector
        // if we wanna make this time based filter we can keep track of the first time that an employee p
        // payroll is processed and last time , which will be updated everymonth . 
        // when time filter  apply , we count all those that last process after the entered time . 
        

        $filter_date = $request['filter_date'];
        $filter_date = Carbon::parse($filter_date);
        return Employee::select('sector', DB::raw('COUNT(*) as employee_count'))
        ->where('first_process_date', '<=', $filter_date)
        ->where('last_process_date', '>=', $filter_date)
        ->groupBy('sector')
        ->get();
        
    }

    public function getAmountsByGLAccount(Request $request): \Illuminate\Support\Collection
    {
        

        // this 
       
        $startDate = Carbon::parse($request['filter_start_date']);
        
        $endDate= Carbon::parse($request['filter_end_date']);
        $selected_fund_codes=$request['selected_fund_codes'];
        $locationNames=$request['location_name'];

        $query= Payroll::select('gl_account', 
        DB::raw('SUM(amount_birr) as total_amount_birr'),
        DB::raw('SUM(amount_birr) as total_amount_usd'))
            ->whereBetween('date', [$startDate, $endDate]);
            if ($selected_fund_codes && count($selected_fund_codes) > 0) {
                $query->whereIn('fund_no', $selected_fund_codes);
            }
           return $query->groupBy('gl_account')
            ->get();
    }




    public function getTypeBasedTotalForLocations(Request $request)
    {
        

                // this is for section where it can be shown using bar chart (SECTION Three using dropdown for location . ) . 
        $startDate = Carbon::parse($request['filter_start_date']);
        $endDate = Carbon::parse($request['filter_end_date']);
        $selected_fund_codes = $request['selected_fund_codes'];

        $query = Payroll::select(
                'employees.location_name as location',
                'type',
                DB::raw('SUM(amount_birr) as total_amount_birr'),
                DB::raw('SUM(amount_usd) as total_amount_usd')
            )
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->whereBetween('date', [$startDate, $endDate]);

        if ($selected_fund_codes && count($selected_fund_codes) > 0) {
            $query->whereIn('fund_no', $selected_fund_codes);
        }

        $results = $query->groupBy('employees.location_name', 'type')->get();

        // Restructure the response
        $formattedResults = [];

        foreach ($results as $result) {
            $location = $result->location;

            // Initialize location in the array if it doesn't exist
            if (!isset($formattedResults[$location])) {
                $formattedResults[$location] = [
                    'location' => $location,
                    'data' => []
                ];
            }

            // Append each type, amount_birr, and amount_usd to the location's data
            $formattedResults[$location]['data'][] = [
                'type' => $result->type,
                'amount_in_birr' => $result->total_amount_birr,
                'amount_in_usd' => $result->total_amount_usd
            ];
        }

        // Convert to a simple array of locations
        $response = array_values($formattedResults);

    return response()->json($response);
   
    }
    public function getTypeBasedTotal(Request $request): \Illuminate\Support\Collection
    {
        

        // this is for section where it can be shown using bar chart (SECTION ONe) . 
        $startDate = Carbon::parse($request['filter_start_date']);        
        $endDate= Carbon::parse($request['filter_end_date']);
        $selected_fund_codes=$request['selected_fund_codes'];
        $selected_locations=$request['selected_locations'];

        $query=  Payroll::select(
            'type',
            DB::raw('SUM(amount_birr) as total_amount_birr'),
            DB::raw('SUM(amount_usd) as total_amount_usd')
        ) 
        ->whereBetween('date', [$startDate, $endDate]) 
        ->join('employees', 'payrolls.employee_id', '=', 'employees.id');
        
        
        if ($selected_fund_codes && count($selected_fund_codes) > 0) {
            $query->whereIn('fund_no', $selected_fund_codes);
        }

        if (!empty($selected_locations) && count($selected_locations) > 0) {
            $query->whereIn('employees.location_name', $selected_locations);
        }

        
       return  $query->groupBy('type')->get();  
    }

    /**
     * Get aggregated payroll data based on a given time range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getAggregatedPayrollData(Request $request)
    {

        //  THIS IS FOR THE TREND which is sum off all the expenses. () 
        $startDate = Carbon::parse($request['filter_start_date']);
        
        $endDate= Carbon::parse($request['filter_end_date']);
        $selected_fund_codes=$request['selected_fund_codes'];
        $selected_locations=$request['selected_locations'];


        //  determine the grouping period
        $yearsDiff = $endDate->diffInYears($startDate);
        // return ["year"=> $yearsDiff];
        $groupByFormat = '';
        $selectFormat = '';
        if ($yearsDiff < 1) {
            // Monthly aggregation
            $groupByFormat = 'DATE_FORMAT(date, "%Y-%m")';
        } elseif ($yearsDiff >= 1 && $yearsDiff <= 6) {
            // 6-month aggregation
            // First half: Jan-Jun, Second half: Jul-Dec
            $groupByFormat = 'DATE_FORMAT(date, "%Y-")';
            $selectFormat = 'CONCAT(DATE_FORMAT(date, "%Y-"), IF(MONTH(date) <= 6, "H1", "H2"))';
        } else {
            // Yearly aggregation
            $groupByFormat = 'DATE_FORMAT(date, "%Y")';
        }

        $query =  Payroll::select(
                            DB::raw(($selectFormat ?: $groupByFormat) . ' as period'),
                            DB::raw('SUM(amount_birr) as total_amount_birr'),
                            DB::raw('SUM(amount_usd) as total_amount_usd')
                        )
                      ->whereIn('type', ['salary', 'pf', 'pension'])
                      ->whereBetween('date', [$startDate, $endDate])
                      ->join('employees', 'payrolls.employee_id', '=', 'employees.id');
                      
                      

            if ($selected_fund_codes && count($selected_fund_codes) > 0) {
                $query->whereIn('fund_no', $selected_fund_codes);
            }
            if (!empty($selected_locations) && count($selected_locations) > 0) {
                $query->whereIn('employees.location_name', $selected_locations);
            }
            return $query->groupBy('period')->get();
    }



    public function getReconcillationData(Request $request){

        $first_month = $request['first_month'];        
        $second_month= $request['second_month'];

        $results1 = $this->runReconcillationQuery($first_month);
        $results2 = $this->runReconcillationQuery($second_month);
        
        
        return response()->json(
            [
                    "first_month" => $results1??[],
                    "second_month" => $results2??[]
                ]);

       
    }



    public function runReconcillationQuery(string $inputDate){
        
        $month = date('m', strtotime($inputDate));
        $year = date('Y', strtotime($inputDate));

        $results = Payroll::select(
                'employees.location_name as location_code',
                DB::raw('SUM(CASE WHEN type IN ("Income tax", "Pension Deduct.", "PF Deduct.", "Advance Deduct.", "Other Deduct.") THEN amount_birr END) as total_deductions_birr'),
                DB::raw('SUM(CASE WHEN type IN ("Income tax", "Pension Deduct.", "PF Deduct.", "Advance Deduct.", "Other Deduct.") THEN amount_usd END) as total_deductions_usd'),
                DB::raw('SUM(CASE WHEN type IN ("Salary", "Pension", "PF") THEN amount_birr END) as total_benefits_birr'),
                DB::raw('SUM(CASE WHEN type IN ("Salary", "Pension", "PF") THEN amount_usd END) as total_benefits_usd'),
                DB::raw('SUM(CASE WHEN type = "Net Pay Deduct." THEN amount_birr END) as total_net_pay_birr'),
                DB::raw('SUM(CASE WHEN type = "Net Pay Deduct." THEN amount_usd END) as total_net_pay_usd')
            )
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->whereMonth('payrolls.date', '=', $month)
            ->whereYear('payrolls.date', '=', $year)
            ->groupBy('employees.location_name')
            ->get();

            $results = $results->map(function ($item) use ($month, $year) {
                $customLocationName = $this->getCustomLocationName($item->location_code);

                $item->custom_location_name ="{$month}-{$year} Payroll {$customLocationName}";
                return $item;
            });

            return $results;

    }


    public function  getCustomLocationName(string $location_code){
        switch ($location_code) {  
           
            case "ACFUS-ET02":  
                $result = "Addis Ababa Staff";  
                break;  
            case "ACFUS-ET03":  
                $result = "Borena Staff";  
                break;  
            case "ACFUS-ET04":  
                $result = " Gambella Office";  
                break;  
            case "ACFUS-ET05":  
                $result = "Hararge";  
                break;  
            case "ACFUS-ET06":  
                $result = "Somalia";  
                break;  
            case "ACFUS-ET07":  
                $result = "WH";  
                break;  
            case "ACFUS-ET08":  
                $result = "Wollega Staffs";  
                break;  
            case "ACFUS-ET09":  
                $result = "Tigray Staffs";  
                break;  
            default:  
                $result = "Invalid Code";  
        }
             // Handle unexpected values  
        
       

        return $result;
   }


   public function getFilteringData(){

        return response()->json(
        [
                "fund_codes" => ["ETB2I", "ETD3O", "ETD3N", "ETD5EE", "ETE2E", "ETA1BB", "ETB1I", "ETD3P", "ETD5FF", "ETA1CC", "ETF6E", "ETZ1Q"],
               "location_Names" => [  
                        [  
                            "location_name" => "AA",  
                            "location_code" => "ACFUS-ET02"  
                        ],  
                        [  
                            "location_name" => "BO",  
                            "location_code" => "ACFUS-ET03"  
                        ],  
                        [  
                            "location_name" => "GA",  
                            "location_code" => "ACFUS-ET04"  
                        ],  
                        [  
                            "location_name" => "HA",  
                            "location_code" => "ACFUS-ET05"  
                        ],  
                        [  
                            "location_name" => "SO",  
                            "location_code" => "ACFUS-ET06"  
                        ],  
                        [  
                            "location_name" => "WH",  
                            "location_code" => "ACFUS-ET07"  
                        ],  
                        [  
                            "location_name" => "WO",  
                            "location_code" => "ACFUS-ET08"  
                        ],  
                        [  
                            "location_name" => "TG",  
                            "location_code" => "ACFUS-ET09"  
                        ]  
                    ]  
            ]);



   }



}


