<?php
namespace App\Chung;

use App\Models\User;
use DB;
use DBD;
use Auth;
use Log;
use Storage;
use Sum;
use Excel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Chung\ExcelCustomExport;
use App\Chung\ExcelCustomImport;
use App\Chung\ExcelCustomSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Func;
use FastExcel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Style\Border;
class ExcelFunc
{
    /**
     * 엑셀 다운로드 항목 대상
     * 엑셀 헤더와 데이터 출력항목 선택 기능 구현으로 헤더와 데이터 간의 직접적으로 관계키를 구성할 요소가 없어서 일련번호를 기준으로 설정.
     * 엑셀 데이터 설정시 일련번호와 헤더의 일련번호를 일치시켜야함.
     * @param  호출구분
     * @return 엑셀헤더Array
     */
    public static function getExcelHeader($excelUrl, $tabsSelect="")
    {
        $arrayHeader =  array();
        
        switch($excelUrl)
        {
            default:
                break;
        }
        
        return (sizeof($arrayHeader) > 0) ? $arrayHeader : null;
    }

    /**
	* 엑셀 다운로드 로그 남기기
	*
	* @param  구분, 엑셀다운코드,파일이름,쿼리, 라인수, 일련번호
	* @return $mode ins일 경우 $no | upd 일경우 'E','Y','N'
	*/
    public static function setExcelDownLog($mode, $down_cd=null, $filename=null, $query=null, $line=0, $etc=null, $no=null, $down_filename=null, $excel_down_div="S", $request=null, $origin_filename=null)
    {
        // 실행시작 로그 
        if( $mode=='INS' ) 
        {  
            $_DATA['req_time']      = date("YmdHis");
            $_DATA['start_time']    = date("YmdHis");
            $_DATA['id']            = !empty(Auth::id())?Auth::id():"SYSTEM";
            $_DATA['branch']        = !empty(Auth::user()->branch_code)?Auth::user()->branch_code:"0000";
            $_DATA['filename']      = $filename; 
            $_DATA['down_filename'] = $down_filename; 
            $_DATA['query_string']  = $query;
            $_DATA['rsn_cd']        = $down_cd; 
            $_DATA['etc']           = $etc; 
            $_DATA['status']        = $excel_down_div;
            $_DATA['record_count']  = $line;
            if($excel_down_div == 'S'){
                $_DATA['request']  = $request;
            }
          
            $rs = DB::dataProcess($mode,'excel_down_log', $_DATA, null, $no);
            if(isset($rs) && $rs == 'Y')
            {
                return $no;
            }
            else
            {
                return false;
            }
        }
        // 바로실행완료 로그
        else if( $mode=="UPD" && isset($no) && $excel_down_div == "E" )
        {
            $_DATA['end_time']     = date("YmdHis");
            $_DATA['status']       = "E";
            $_DATA['record_count'] = $line;
            $_DATA['origin_filename'] = $origin_filename; // 이거 추가

            return DB::dataProcess($mode,'EXCEL_DOWN_LOG', $_DATA, ["NO"=>$no]);
        }
        //예약내역에서 실행으로 변경
        else if( $excel_down_div == "S" )
        {
            // 대기에서 진행으로 전환
            $_DATA['start_time']   = date("YmdHis");
            $_DATA['status']       = "A";

            return DB::dataProcess($mode,'EXCEL_DOWN_LOG', $_DATA, ["NO"=>$no]);
        }
        //예약내역에서 완료로 변경
        else if( $excel_down_div == "A" )
        {
            // 진행에서 완료로 전환
            $_DATA['end_time']     = date("YmdHis");
            $_DATA['status']       = "D";
            $_DATA['record_count'] = $line;
            $_DATA['origin_filename'] = $origin_filename; //요거 추가

            return DB::dataProcess($mode,'EXCEL_DOWN_LOG', $_DATA, ["NO"=>$no]);
        }
        else
        {
            return false;
        }
    }

/**
	* fast-excel 
	*
	* @param   excel_data(array)
	* @return boolean
	*/
    public static function fastexcelExport($excel_data,$excel_header,$file_name)
    {
        try {
            array_unshift($excel_data,$excel_header);
            return FastExcel::data($excel_data)->withoutHeaders()->export(Storage::path("excel/".$file_name));
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }
    
    /**
	* 기본 엑셀 다운로드 함수 시트하나에 data리스트를 뽑는다 
	*
	* @param  filename, head(array), data(array)
	* @return  boolean
	*/
    public static function storeExcel($fileName,$_head,$_DATA,$title='',$style=array())
    {
        try {
            return Excel::store(new ExcelCustomExport($_head,$_DATA,$title,$style),'/'.$fileName);
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    /**
	* 기본 엑셀 다운로드 함수 시트하나에 data리스트를 뽑는다 
	*
	* @param  filename, head(array), data(array)
	* @return  boolean
	*/
    public static function downExcel($fileName,$_head,$_DATA,$title='',$style=array())
    {
        try {
            return Excel::download(new ExcelCustomExport($_head,$_DATA,$title,$style),$fileName);
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

     /**
	* 기본 엑셀 다운로드 함수 시트하나에 data리스트를 뽑는다 
	*
	* @param  filename, head(array), data(array)
	* @return  boolean
	*/
    public static function downExcelSheet($_DATA,$fileName)
    {
        try {
            return (new ExcelCustomSheets($_DATA))->store('excel/'.$fileName);
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    
    /**
	*   엑셀 읽어서 전달하기
	*
	* @param  file, colum명(array),읽을 시트번호
	* @return  boolean
	*/
    public static function readExcel($file,$colNm,$headNm=1,$sheet=0,$colHeader=array(),$max_cnt=0)
    {
        $data = array();

        $results =  Excel::toArray(new ExcelCustomImport, $file);
        if(count($colNm)>1){
            foreach($results[$sheet] as $i => $v){
                if($i<$headNm) continue;
                if($i==$headNm){
                    // 엑셀 형식 검증
                    if(count($colHeader)>1){
                        foreach($colHeader as $in => $head){
                            if(str_replace(" ","",$v[$in])!=str_replace(" ","",$head)){
                                Log::debug("EXCEL ERR:".str_replace(" ","",$v[$in])."!=".str_replace(" ","",$head));
                                Log::debug($v);
                                return null;
                            } 
                        }
                    }
                }else{
                    $row = [];
                    foreach($colNm as $col=>$num){
                        $row[$col] = $v[$num] ?? ''; // 해당 index 없는 경우 공백 return
                        if(strpos($col,"date_format")!== false ) 
                        {
                            echo $col." : ".$v[$num]."\n";
                            if($v[$num]!='')
                            {
                                if(strlen($v[$num])==10)
                                {
                                    $v[$num] = str_replace('-', '', $v[$num]);
                                }
                                else if(strlen($v[$num])==8)
                                {
                                    $v[$num] = $v[$num];
                                } 
                                else 
                                {
                                    $v[$num] = Date::excelToDateTimeObject($v[$num])->format("Ymd");
                                }
                                $row[str_replace("date_format","date",$col)] = $v[$num];
                            }
                        }
                    }
                    $data[$i]=$row;
                }
                if($max_cnt>0 && $max_cnt<$i) break;
            }
        }else{
            $data = $results[$sheet];    
        }
        return $data;
    }
}