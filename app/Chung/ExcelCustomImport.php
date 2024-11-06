<?php

namespace App\Chung;

use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToArray;
use Log;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ExcelCustomImport implements ToArray
{
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function array(array $row)
    {
        return $row;
    }

}