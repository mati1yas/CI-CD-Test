<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SpecificSheetsImport implements WithMultipleSheets
{
    protected $sheetNames;

    /**
     * Constructor to pass specific sheet names to the class.
     *
     * @param array $sheetNames
     */
    public function __construct(array $sheetNames)
    {
        $this->sheetNames = $sheetNames;
    }

    /**
     * Define which sheets to import and what import class to use for each sheet.
     *
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // Loop through the provided sheet names and assign an import class for each

        foreach ($this->sheetNames as $sheetName) {
            print_r("BGOT HERE");
            print_r($sheetName);
            // $sheets[$sheetName] = new DataImport();
        }

        return $sheets;
    }
}
