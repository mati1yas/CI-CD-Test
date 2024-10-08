<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Sheet;


class EmployeeExport implements FromCollection,WithStyles,WithHeadings
{
    protected $data;

    /**
     * Constructor to accept processed data.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Return collection for Excel export.
     *
     * @return Collection
     */
    public function collection()
    {
        return new Collection($this->data);
    }




    /**
     * Apply styling for the worksheet.
     *
     * @param Worksheet $sheet
     * @return  array // void
     */
    public function styles(Worksheet $sheet)
    {
        // Set the font for the entire worksheet
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Aptos Narrow');
        
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_BLACK], // Change font color to white
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => Color::COLOR_GREEN, // Fill with green color
                    ],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Center the text
                ],
            ],
        ];
    }

      /**
     * Return the headings for the Excel export.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Posting Date',
            'Document Type',
            'Document No.',
            'Account Type',
            'Account No.',
            'Fund No.',
            'Dimension Speedkey Code',
            'Dimension 1',
            'Dimension 2',
            'Dimension 3',
            'Dimension 4',
            'Dimension 5',
            'Dimension 6',
            'Dimension 7',
            'Dimension 8',
            'External Document No.',
            'Description',
            'Amount',
            'Budget Plan No.',
            'Currency Code (Express Users Leave Blank)',
            'Allocation No.',
            'Balance Account Type',
            'Balance Account No.',
            'Applies-to Document Type',
            'Applies-to Document No.',
        ];
    }

}