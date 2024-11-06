<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ExcelImport extends Model
{
    public function array(array $array)
    {
        // 엑셀파일 정보가 들어오면 그걸 그대로 다시 반환해주는걸 의미한다.
        return $array;
    }

    public function headinRow(): int
    {
        return 1;
    }
}

?>