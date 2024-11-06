<?php
namespace App\Chung;

use Log;
use App\Chung\ExcelCustomExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExcelCustomSheets implements WithMultipleSheets
{

    use Exportable;

    protected $excel_data;
    
    public function __construct($excel_data)
    {
        
        $this->excel_data   = $excel_data;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        foreach($this->excel_data as $data)
        {
            $sheets[] = new ExcelCustomExport($data['header'],$data['excel_data'],$data['title'],$data['style'],($data['textCol'] ?? []));
        }

        return $sheets;
    }

}