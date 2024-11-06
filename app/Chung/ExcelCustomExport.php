<?php
namespace App\Chung;

use Log;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;


class ExcelCustomExport extends DefaultValueBinder implements FromArray,WithHeadings,WithTitle,WithStyles,WithCustomValueBinder
{

    protected $excel_data;
    protected $heading;
    protected $title;
    protected $style;

    public function __construct(array $heading,array $excel_data,$title='sheet1',$style=array(), $textCol=array())
    {
        $this->excel_data   = $excel_data;
        $this->heading      = $heading;
        $this->title        = $title;
        $this->style        = $style;
        $this->textCol      = $textCol;
    }

    public function array(): array
    {
        return $this->excel_data;
    }

    public function headings(): array
    {
        return $this->heading;
    }

    public function title(): String
    {
        return $this->title;
    }

    //style custom
    public function styles(Worksheet $sheet){
        //colspan , rowspan
        if(isset($this->style['merge'])){
            // Log::alert($this->style['merge']);
            foreach($this->style['merge'] as $val){
                $sheet->mergeCells($val);
            }
        }

        // 페이지설정
        if( isset($this->style['page']))
        {
            foreach($this->style['page'] as $val)
            {
                $sheet->getPageSetup()->setPrintArea('A1:G33');
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
            }
        }

        // center
        if( isset($this->style['center']))
        {
            foreach($this->style['center'] as $val)
            {
                // 가로 정렬 => 가운데(HORIZONTAL_CENTER)
                $sheet->getStyle($val)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // 세로 정렬 => 가운데(VERTICAL_CENTER)
                $sheet->getStyle($val)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            }
        }
        
        //border
        // if(isset($this->style['border'])){
        //     foreach($this->style['border'] as $col => $location){
        //         $this->style['custom'][$col]['borders']= [ $location =>['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]];
        //     }
        // }
        // if( isset($this->style['number']))
        // {
        //     foreach($this->style['number'] as $idx => $val)
        //     {
        //         $sheet->getStyle($val)->getNumberFormat()->setFormatCode('0');
        //     }
        // }

        //vertical
        if (isset($this->style['vertical']))
        {
            foreach ($this->style['vertical'] as $type => $cells)
            {
                foreach ($cells as $val)
                {
                    if ($type == 'bottom')
                    {
                        // 세로 정렬 => 아래쪽(VERTICAL_BOTTOM)
                        $sheet->getStyle($val)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM);
                    }
                    elseif ($type == 'center')
                    {
                        // 세로 정렬 => 가운데(VERTICAL_CENTER)
                        $sheet->getStyle($val)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    }
                    elseif ($type == 'top')
                    {
                        // 세로 정렬 => 위쪽(VERTICAL_TOP)
                        $sheet->getStyle($val)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                    }
                }
            }
        }

        // horizontal
        if (isset($this->style['horizontal'])) {
            foreach ($this->style['horizontal'] as $type => $cells) {
                foreach ($cells as $val) {
                    if ($type == 'left') {
                        // 수평 정렬 => 왼쪽(HORIZONTAL_LEFT)
                        $sheet->getStyle($val)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    } elseif ($type == 'center') {
                        // 수평 정렬 => 가운데(HORIZONTAL_CENTER)
                        $sheet->getStyle($val)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    } elseif ($type == 'right') {
                        // 수평 정렬 => 오른쪽(HORIZONTAL_RIGHT)
                        $sheet->getStyle($val)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    }
                }
            }
        }
        
        //border
        if(isset($this->style['border']))
        {
            foreach($this->style['border'] as $type => $cells)
            {
                foreach($cells as $val)
                {
                    if($type == 'outline_bold')
                    {
                        // 바깥쪽 bold
                        $sheet->getStyle($val)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
                    }
                    elseif($type == 'all')
                    {
                        // 셀 전체
                        $sheet->getStyle($val)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    }
                }
            }
        }

        // title_font
        if (isset($this->style['title_font']))
        {
            foreach ($this->style['title_font'] as $val)
            {
                // 폰트 크기 설정
                $sheet->getStyle($val)->getFont()->setSize(22);
            
                // // 폰트 굵게 설정
                // $sheet->getStyle($val)->getFont()->setBold(true);
            }
            
        }

        //cell 너비 설정
        if(isset($this->style['width']))
        {
            foreach($this->style['width'] as $type => $cells)
            {
                $sheet->getColumnDimension($type)->setWidth($cells);                        
            }
        }

        //cell 높이 설정
        if(isset($this->style['height']))
        {
            foreach($this->style['height'] as $type => $cells)
            {
                $sheet->getRowDimension($type)->setRowHeight($cells);                        
            }
        }

        // 수동설정
        if(!isset($this->style['custom'])){
            $this->style['custom'] = [
                1 => [
                    // 'font' => ['bold'=>true], 
                    // 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFA6A6A6']],
                    // 'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]
            ];
        }

        return $this->style['custom'];
    }

    // 설정값 중 텍스트 포맷 지정한 컬럼은 텍스트로 입력
    public function bindValue(Cell $cell, $value)
    {
        if (in_array($cell->getColumn(), $this->textCol))
        {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        else
        {
            return parent::bindValue($cell, $value);
        }
    }

/* 
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],'fill' => ['color'=>'#dbdbdb']],

        ];
    } */

}