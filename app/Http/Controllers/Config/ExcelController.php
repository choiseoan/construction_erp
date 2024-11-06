<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Vars;
use Auth;
use Log;
use DataList;
use ExcelFunc;
use Cache;
use App\Chung\Paging;
use Illuminate\Support\Facades\Storage;
use Excel;
use App\Chung\ExcelCustomExport;


class ExcelController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

    }

    /**
     * 엑셀명세 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataExcelList(Request $request){

        $list   = new DataList(Array("listName"=>"excel","listAction"=>'/'.$request->path()));
        $list->setTabs([], $request->Tabs);
        $list->setButtonArray("엑셀다운","excelDownModal('/config/excelexcel','form_excel');", "btn-success");
        $list->setSearchDate('날짜검색',Array('req_time' => '요청일'),'searchDt','Y');
        $list->setSearchType('branch',Func::getBranch(),'지점','data-live-search="true" data-size="20"');
        $list->setSearchType('rsn_cd',Func::getConfigArr('excel_down_cd'),'다운로드사유','data-live-search="true" data-size="20"');
        $list->setSearchDetail(Array( 
            'EXCEL_DOWN_LOG.ID'     => '사번',
            'USERS.NAME'            => '이름',
        ));
        return $list;
    }
    

    /**
     * 엑셀 메인화면
     *
     * @param  Void
     * @return view
     */
	public function excel(Request $request)
    {

        $list   = $this->setDataExcelList($request);

        $configArr = Func::getConfigArr();
        $arrayBranchUsers = Func::getBranch();
        $list->getList();

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleCommon(Array
        (
            'no'            => Array('번호', 0, '', 'center', '', 'no'),
            'id'            => Array('사용자', 0, '', 'center', '', 'id'),
            'branch'        => Array('지점', 0, '', 'center', '', 'branch'),
            'filename'      => Array('생성파일명', 0, '', 'center', '', 'filename'),
            'req_time'      => Array('요청시간', 0, '', 'center', '', 'req_time'),
            'record_count'  => Array('라인수', 0, '', 'center', '', 'record_count'),
            'rsn_cd'        => Array('다운로드사유', 0, '', 'center', '', 'rsn_cd'),
            'etc'           => Array('상세사유', 0, '', 'center', '', 'etc'),
        ));

        return view('config.excel')->with('result', $list->getList())
                               ->with('arrayBranchUsers', $arrayBranchUsers)
                               ->with('configArr', $configArr);
    }

    /**
     * 엑셀다운 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function excelList(Request $request)
    {
        $request->isDebug = true;
        
        $list   = $this->setDataExcelList($request);
        $param  = $request->all();

        // 기본쿼리
        $EXCEL = DB::TABLE("EXCEL_DOWN_LOG")->JOIN("USERS", "EXCEL_DOWN_LOG.ID", "=", "USERS.ID")
                    ->SELECT("EXCEL_DOWN_LOG.ID,EXCEL_DOWN_LOG.DOWN_FILENAME,EXCEL_DOWN_LOG.STATUS,NO,BRANCH,ETC,FILENAME,RSN_CD,REQ_TIME,END_TIME,RECORD_COUNT","USERS.NAME")
                    ->WHERE("RSN_CD",'<>',NULL);
       
        $EXCEL = $list->getListQuery('EXCEL_DOWN_LOG', 'main', $EXCEL, $param);

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($EXCEL, $request->page, $request->listLimit, 10, $request->listName);

        $rslt = $EXCEL->GET();
        $rslt = Func::chungDec(["EXCEL_DOWN_LOG","USERS"], $rslt);	// CHUNG DATABASE DECRYPT
       

        // 뷰단 데이터 정리.
        $arrayBranch = Func::getBranch();
        $configArr   = Func::getConfigArr('excel_down_cd');

        $cnt = 0;
        foreach ($rslt as $v)
        {
            $v->no              = $v->no;
            $v->id              = $v->name."(".$v->id.")";
            $v->branch          = $arrayBranch[$v->branch] ?? '';
            $v->rsn_cd          = $configArr[$v->rsn_cd];
            $v->etc             = $v->etc;
            $v->filename        = $v->filename;
            $v->down_filename   = $v->down_filename;
            $v->req_time        = Func::dateFormat($v->req_time);
            $v->end_time        = Func::dateFormat($v->end_time);
            $r['v'][]           = $v;
            $cnt ++;
        }

        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());
        $r['result']   = 1;
        $r['txt']      = $cnt;

        return json_encode($r);
    }


    /**
     * 엑셀생성
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function excelExcel(Request $request)
    {
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $list   = $this->setDataExcelList($request);
        $param    = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;
        
        // 기본쿼리
        $EXCEL = DB::TABLE("EXCEL_DOWN_LOG")->JOIN("USERS", "EXCEL_DOWN_LOG.ID", "=", "USERS.ID")
        ->SELECT("EXCEL_DOWN_LOG.ID,NO,BRANCH,ETC,FILENAME,RSN_CD,REQ_TIME,END_TIME,RECORD_COUNT","USERS.NAME")->WHERE("RSN_CD",'<>',NULL);
        $EXCEL = $list->getListQuery('EXCEL_DOWN_LOG', 'main', $EXCEL, $param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($EXCEL, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($EXCEL);
        $file_name    = "엑셀다운로그_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no)){
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
            $origin_filename = $excel_no.'_'.$request->work_id.'_'.date("YmdHis").'.xlsx'; 
        } else {
            $excel_no       = ExcelFunc::setExcelDownLog("INS", $param['excelDownCd'], $file_name, $query, $record_count, $param['etc'], null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $rslt = $EXCEL->GET();
        $rslt = Func::chungDec(["EXCEL_DOWN_LOG","USERS"], $rslt);	// CHUNG DATABASE DECRYPT

        // 헤더
		$excel_header = array(
			'번호', '사용자', '지점','생성파일명','요청시간', '라인수', '다운로드 사유', '상세사유'		
        );

        // 뷰단 데이터 정리.
        $arrayUserId   = Func::getUserId();
        $arrayBranch   = Func::getBranch();
        $configArr     = Func::getConfigArr('excel_down_cd');

        foreach ($rslt as $v)
        {
            $array_data = [
                $v->no,
                $v->name,
                $arrayBranch[($v->branch)],
                $v->filename,
                Func::dateFormat($v->req_time),
                (int)$v->record_count,
                $configArr[$v->rsn_cd],
                $v->etc,
            ];
            $record_count++;
            $excel_data[] = $array_data;
        }

        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data, $excel_header, $origin_filename);
    
        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($origin_filename);

        if( isset($exists))
        {
            $array_result['etc']             = $etc;
            $array_result['result']          = 'Y';
            $array_result['filename']        = $file_name;
            $array_result['excel_no']        = $excel_no;
            $array_result['record_count']    = $record_count;
            $array_result['down_filename']   = $down_filename;
            $array_result['excel_down_div']  = $excel_down_div;
            $array_result['origin_filename'] = $origin_filename;
            
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div,null, $origin_filename);
        }
        else
        {
            $array_result['result']    = 'N';
            $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }

        return $array_result;
    }

    /**
     * 엑셀 다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function excelDown(Request $request)
    {
        $excel_no       = $request->input('excel_no');
        $file_name      = $request->input('filename');
        $record_count   = $request->input('record_count');
        $down_filename  = $request->input('down_filename');
        $excel_down_div = $request->input('excel_down_div');
        $origin_filename = $request->input('origin_filename');
        
        // 파일 다운
        if($excel_down_div == "E")
        {
            return response()->download(Storage::path("excel/".$origin_filename),$file_name)->deleteFileAfterSend(true);
        }        
        // 예약파일 다운
        else if($excel_down_div == "D")
        {
            return response()->download(Storage::path("excel/".$origin_filename),$down_filename);
        }
        else
        {    
            return false;
        }
    
    }
}