<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use Excel;
use DataList;
use ExcelFunc;
use FastExcel;
use App\Chung\Paging;
use Illuminate\Support\Facades\Storage;

class BatchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
    }

    // 배치실행상태
    public $arrayEndYn = ['N'=>'실행', 'Y'=>'종료'];

    // 배치사용여부
    public $arrayUseYn = ['Y'=>'사용', 'N'=>'미사용'];
    
    /**
     * 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setBatchList(Request $request){

        $list   = new DataList(Array("listName"=>"batch","listAction"=>'/'.$request->path()));

        if(!isset($request->tabs)) $request->tabs = 'Y';

        // $list->setTabs(['Y'=>'유효', 'N'=>'삭제'], $request->tabs);
        $list->setTabs(Array('Y'=>'유효', 'N'=>'삭제'),$request->tabs);


        $list->setSearchDate('날짜검색',Array('reg_date'=>'생성일', 'save_time'=>'저장일'),'searchDt','Y','Y');

        //$list->setSearchType('use_yn', $this->arrayUseYn, '사용여부');        // 검색폼이랑 id가 같아서 자꾸 검색이 되버려서 빼버림

        $list->setPlusButton("setBatchForm('')");

        $list->setSearchDetail(Array( 
            'sch_name'  => '작업명',
            'sch_command'  => '작업명령',
        ));
        $list->setSearchDetailLikeOption("%%");

        return $list;
    }
    

    /**
     * 배치관리 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function batch(Request $request)
    {
        $list = $this->setBatchList($request);
        
        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleCommon(Array
        (
            'no'            =>     Array('작업ID', 1, '', 'center', '', 'no'),
            'use_yn'        =>     Array('사용여부', 1, '', 'center', '', 'use_yn'),
            'reg_date'      =>     Array('생성일', 0, '', 'center', '', 'reg_date'),
            'sch_name'      =>     Array('작업명', 0, '', 'center', '', 'sch_name'),
            'sch_command'   =>     Array('작업명령', 0, '', 'center', 'Y', 'sch_command'),
            'sch_minute'    =>     Array('실행(분)', 0, '', 'center', 'Y', 'sch_minute'),
            'sch_hour'      =>     Array('실행(시)', 0, '', 'center', 'Y', 'sch_hour'),
            'sch_day'       =>     Array('실행(일)', 0, '', 'center', 'Y', 'sch_day'),
            'sch_month'     =>     Array('실행(월)', 0, '', 'center', 'Y', 'sch_month'),
            'sch_week'      =>     Array('실행(주)', 0, '', 'center', 'Y', 'sch_week'),
            'save_time'     =>     Array('저장일', 0, '', 'center', '', 'save_time'),
            'save_id'       =>     Array('저장자', 0, '', 'center', '', 'save_id'),
            'sch_note'      =>     Array('작업비고', 0, '', 'left', '', ''),
            's_time'        =>     Array('마지막 실행시간', 0, '', 'center', '',),
            // 추후추가 'execute'       =>     Array('실행', 1, '', 'center', '', ''),
            
        ));

        return view('config.batch')->with("result", $list->getList());
    }

    /**
     * 배치 리스트
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function batchList(Request $request)
    {

        $list   = $this->setBatchList($request);
        $param  = $request->all();
        $arrayUserId    = Func::getUserId();

        if(!isset($param['listOrder']))
        {
            $param['listOrderAsc'] = "desc";
            $param['listOrder']    = "use_yn, no";
        }

        // 메인쿼리
        $BAT = DB::TABLE("conf_batch")->SELECT("conf_batch.*", "(select s_time from conf_batch_log cbl where conf_batch.no = cbl.conf_batch_no order by no desc fetch first 1 row only) s_time", "(select end_yn from conf_batch_log cbl where conf_batch.no = cbl.conf_batch_no order by no desc fetch first 1 row only) end_yn");
        $BAT = $list->getListQuery("conf_batch", 'main', $BAT, $param);
    
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($BAT, $request->page, $request->listLimit, 10, $request->listName);
                
        // 결과
        $result = $BAT->get();
        $result = Func::chungDec(["CONF_BATCH"], $result);	// CHUNG DATABASE DECRYPT
        
        // 뷰단 데이터 정리.
        $cnt = 0;
		foreach ($result as $v)
		{
            //추후추가$v->execute      = ' <a onclick="execBatch(\''.$v->no.'\');" class="hand ">'.$v->no.'</a>';

            $v->no          = '<a onclick="setBatchForm(\''.$v->no.'\');" class="hand text-primary">'.$v->no.'</a>';
            $v->use_yn      = '<input type="checkbox" data-size="mini" name="v_use_yn" id="v_use_yn" data-bootstrap-switch '.(($v->use_yn=='Y') ? 'checked':'').' readonly>';
            //$v->use_yn      = Func::getArrayName($this->arrayUseYn, $v->use_yn);
            if ($v->end_yn == 'Y')
                $v->s_time   = '<span class="text-blue">' . Func::dateformat($v->s_time) . '</span>';
            else
                $v->s_time   = '<span class="text-red">' . Func::dateformat($v->s_time) . '</span>';
            
            $v->reg_date = Func::dateformat($v->reg_date);

            if($v->status=='N')
            {
                $v->save_time = Func::dateformat($v->del_time);
                $v->save_id = Func::getArrayName($arrayUserId, $v->del_id);
            }
            else
            {
                $v->save_time = Func::dateformat($v->save_time);
                $v->save_id = Func::getArrayName($arrayUserId, $v->save_id);
            }
            
            $r['v'][] = $v;
			$cnt ++;
        }
        
        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());

		$r['result'] = 1;
		$r['txt'] = $cnt;

		return json_encode($r);
    }

    /**
     * 배치 입력폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function batchForm(Request $request)
    {
        if(!$request->no)
        {
            $mode = "INS";
            $read = "";

            return view('config.batchForm')->with(["mode"=>$mode, "read"=>$read]);
        }
        else
        {
            $mode = "UPD";
            $read = "readonly";
            $v = DB::TABLE("conf_batch")->SELECT("*")->WHERE('no', $request->no)->FIRST();

            return view('config.batchForm')->with(["mode"=>$mode, "read"=>$read, "v"=>$v]);
        }
    }

    /**
     * 배치 입력폼 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function batchFormAction(Request $request)
    {
        // 변수정리
        unset($ARR);
        $ARR                = $request->all();
        Log::debug($ARR);

        if($ARR['mode'] == "DEL")
        {
            $ARR['status'] = "N";
            $ARR['del_time']    = date("YmdHis");
            $ARR['del_id']      = Auth::id();
        }
        else
        {
            $ARR['status'] = "Y";
            $ARR['save_time']   = date("YmdHis");
            $ARR['save_id']     = Auth::id();

            if(empty($ARR['use_yn'])) 
                $ARR['use_yn'] = 'N';
        }

        if($request->mode == "INS")
        {
            $ARR['reg_date']   = date("Ymd");
            $ARR['reg_id']     = Auth::id();
            unset($ARR['no']);
            $rslt = DB::dataProcess('INS', 'conf_batch', $ARR);
        }
        else
        {
            $rslt = DB::dataProcess('UPD', 'conf_batch', $ARR, ["NO"=>$request->no]);
        }

        // 결과데이터
        $RS['rs_code'] = $rslt;
        if($RS['rs_code'] == "Y")
        {
            $RS['rs_msg'] = "정상적으로 처리되었습니다.";
        }
        else
        {
            $RS['rs_msg'] = "처리에 실패하였습니다.";
        }

        return $RS;
    }

    /**
     * 로그 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setBatchLogList(Request $request){

        $list   = new DataList(Array("listName"=>"batchLog","listAction"=>'/'.$request->path()));
        $list->setSearchDate('날짜검색',Array('s_time'=>'시작일', 'e_time'=>'종료일'),'searchDt','Y','Y', date("Y-m-d"), date("Y-m-d"),'s_time');
        //$list->setPlusButton("setBatchForm('')");
        
        $list->setSearchDetail(Array( 
            'sch_name'  => '작업명',
            'sch_command'  => '작업명령',
            'note'  => '실행비고',
        ));
        $list->setSearchDetailLikeOption(" %");

        // 배치 select 검색
        $result = DB::TABLE("conf_batch")->SELECT("*")->orderBy('status', 'desc')->orderBy('no')->get();
        $result = Func::chungDec(["CONF_BATCH"], $result);	// CHUNG DATABASE DECRYPT

        foreach($result as $v)
        {
            $end_yn = '';
            if($v->status=='N')
                $end_yn = '(삭제)';
            
            $arrayBatch[$v->no] = $end_yn.$v->sch_name;
        }

        $list->setSearchType('end_yn', $this->arrayEndYn, '배치상태');
        
        $list->setSearchType('conf_batch_no', $arrayBatch, '작업명');

        $list->setRangeSearchDetail(Array ('exec_times' => '실행시간'),'','','단위(초)');

        if( Func::funcCheckPermit("C122") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/config/batchlogexcel', 'form_batchLog')", "btn-success");
        }

        return $list;
    }
    

    /**
     * 배치관리 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function batchLog(Request $request)
    {
        $list = $this->setBatchLogList($request);
        $list->setTabs([], $request->Tabs);
        
        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleCommon(Array
        (
            'no'        =>     Array('배치번호', 1, '', 'center', '', 'no'),
            'sch_name'  =>     Array('작업명', 1, '', 'center', '', 'sch_name'),
            'end_yn'    =>     Array('배치상태', 0, '', 'center', '', 'end_yn'),
            's_time'    =>     Array('시작시간', 0, '', 'center', '', 's_time'),
            'e_time'    =>     Array('종료시간', 0, '', 'center', '', 'e_time'),
            'exec_times'=>     Array('실행시간(초)', 0, '', 'center', '', 'exec_times'),
            'note'      =>     Array('실행비고', 0, '', 'left', '', 'note'),            
            
        ));

        return view('config.batchLog')->with("result", $list->getList());
    }

    /**
     * 배치 리스트
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function batchLogList(Request $request)
    {
        $list   = $this->setBatchLogList($request);
        $param  = $request->all();
        $arrayUserId    = Func::getUserId();

        // 메인쿼리
        $BAT = DB::TABLE("conf_batch")->Join('conf_batch_log', 'conf_batch.no', '=', 'conf_batch_log.conf_batch_no')
        ->SELECT("conf_batch.sch_name, conf_batch_log.*");
        
        $BAT = $list->getListQuery("conf_batch_log", 'main', $BAT, $param);
        Log::debug(DB::getQueryLog());
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($BAT, $request->page, $request->listLimit, 10, $request->listName);
                
        // 결과
        $result = $BAT->get();
        $result = Func::chungDec(["CONF_BATCH"], $result);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $cnt = 0;
		foreach ($result as $v)
		{
            if($v->end_yn=='N')
                $v->line_style       = 'background-color:#fa9898';

            $v->s_time = Func::dateformat($v->s_time);
            $v->e_time = Func::dateformat($v->e_time);
            $v->save_id = Func::getArrayName($arrayUserId, $v->save_id);
            $v->end_yn = Func::getArrayName($this->arrayEndYn, $v->end_yn);
            $v->exec_times = number_format($v->exec_times);
            
            $r['v'][] = $v;
			$cnt ++;
        }
        
        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());

		$r['result'] = 1;
		$r['txt'] = $cnt;

		return json_encode($r);
    }

    /**
     * 배치 리스트 엑셀 다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function batchLogExcel(Request $request)
    {
        if( !Func::funcCheckPermit("C122") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }

        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list           = $this->setBatchLogList($request);
        $param          = $request->all();
        $arrayUserId    = Func::getUserId();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        // 메인쿼리
        $BAT = DB::TABLE("conf_batch")->Join('conf_batch_log', 'conf_batch.no', '=', 'conf_batch_log.conf_batch_no')
        ->SELECT("conf_batch.sch_name, conf_batch_log.*");
        
        $BAT = $list->getListQuery("conf_batch_log", 'main', $BAT, $param);
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($BAT, $request->nowPage, $request->listLimit, 10);
        }         

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($BAT);
        $file_name    = "배치로그_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no)){
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
        } else {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $query, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
        }

        // 결과
        $result = $BAT->get();
        $result = Func::chungDec(["CONF_BATCH"], $result);	// CHUNG DATABASE DECRYPT

        // 엑셀
		$excel_header = Array
        (
            '배치번호','작업명','배치상태','시작시간','종료시간','실행시간(초)','실행비고'
        );

        $excel_data = Array();

        // 뷰단 데이터 정리.
		foreach ($result as $v)
		{
            $array_data = Array(
                $v->no,
                $v->sch_name,
                Func::getArrayName($this->arrayEndYn, $v->end_yn),
                Func::dateformat($v->s_time),
                Func::dateformat($v->e_time),
                number_format($v->exec_times),
                $v->note
            );
            
            $record_count++;
            $excel_data[] = $array_data;
        }
        
        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data,$excel_header,$file_name);

        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($file_name);

        if( isset($exists) )
        {
            $array_result['etc']             = $etc;
            $array_result['result']          = 'Y';
            $array_result['filename']        = $file_name;
            $array_result['excel_no']        = $excel_no;
            $array_result['record_count']    = $record_count;
            $array_result['down_filename']   = $down_filename;
            $array_result['excel_down_div']  = $excel_down_div;
            
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
        }
        else
        {
            $array_result['result']    = 'N';
            $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }
        return $array_result;
    }

    /**
     * 배치 실행 기록
     *
     * @param  batchNo : 배치 번호
     * @return bool
     */
    public static function setBatchLog($batchNo, $batchLogNo=0, $note='', $stime)
    {    
        // 배치 시작
        if($batchLogNo==0)
        {
            $ARR['conf_batch_no'] = $batchNo;
            $ARR['s_time'] = $ARR['save_time'] = date("YmdHis", $stime);
            $ARR['save_id'] = (empty(Auth::id())) ? 'SYSTEM':Auth::id();
            $ARR['save_status'] = 'Y';
            $ARR['end_yn'] = 'N';
            unset($ARR['no']);

            $rslt = DB::dataProcess('INS', 'conf_batch_log', $ARR, null, $confBatchLogNo);
            if(isset($rslt) && $rslt=="Y")
            {
                return $confBatchLogNo;
            }
            else
            {
                Log::info("배치 시작로그 저장 오류 : 배치번호 ".$batchNo);
            }
        }
        // 배치끝 업데이트
        else
        {
            $etime = time();
            $ARR['e_time'] = date("YmdHis", $etime);
            $ARR['note'] = $note;
            $ARR['end_yn'] = 'Y';
            $ARR['no'] = $batchLogNo;
            $ARR['exec_times'] = $etime-$stime;
                        
            $rslt = DB::dataProcess('UPD', 'conf_batch_log', $ARR);

            if(!isset($rslt) || $rslt=="N")
            {
                Log::info("배치 종료로그 저장 오류 : 배치번호 ".$batchNo);
            }
        }
    }
}
