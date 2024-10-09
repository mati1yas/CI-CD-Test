<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BudgetAllocationImport implements ToCollection, WithMultipleSheets
{   

    public $fundData = [];
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
   

    /**
     * Read fund allocation data.
     *
     * @param Collection $rows
     */

   
    public function collection(Collection $rows)
    {
        $count=0;
        $data = [ ];
        foreach ($rows as $row) {

            
            $count+=1;
            if ($count<2) continue;  // skip the heading 

            if( $row[10]==null) continue; // if the fund does not exist we skip
            if (!array_key_exists($row[1], $this->fundData)){
                $this->fundData[$row[1]]=[];
            };
            $user_id=$row[1];
            $fund_name = $row[10];
            $budget_line = $row[11];    
            $fringe_line=$row[12];
            $loe_percentage=$row[13];


            $this->fundData[$user_id][] = [
                'fund_name' => $fund_name,      // Fund Name
                'budget_line' =>  $budget_line, 
                'fringe_line' => $fringe_line, 
                'loe_percentage' => $loe_percentage, // LOE Percentage
            ];

            // $data[$user_id][]=
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
