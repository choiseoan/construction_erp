<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;  
use Auth;
use Log; 
use DataList;
use Vars;
use ExcelFunc;
use Storage;
use App\Chung\Paging;
use Carbon\Carbon;

class ReservationDetailController extends Controller
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
     * 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataList(Request $request)
    {
        $list    = new DataList(Array("listName"=>"reservationdetail","listAction"=>'/'.$request->path()));
        $param   = $request->all();

        if(!isset($request->tabs))
        {
            $request->tabs = "Excel";
        }
        
        // $list->setTabs(Array('Excel'=>'엑셀', 'Lump'=>'일괄처리'), $request->tabs);
        $list->setTabs(Array('Excel'=>'엑셀'), $request->tabs);
        $list->setSearchDate('기준일자','','searchDt','Y','N', date("Y-m-d"), date("Y-m-d"));

        return $list;
    }

    /**
     * 예약내역 메인화면
     *
     * @param  request
     * @return view
     */
	public function reservationDetail(Request $request)
    {
        $list   = $this->setDataList($request);
        $param  = $request->all();

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 
        //             리스트 세팅([0] key=>타이틀,[1]사용X ,[2] 넓이 - % 또는 px,[3] text 정렬,[4] rightline 여부,[5] data정렬,
        //                        [6] 한칸에 여러데이터 중첩표시 array([컬럼]=>array(text,data 정렬,txet앞에 표시될html( / , <br> ..) )))
 
        // $list->setlistTitleCommon(Array
        $list->setlistTitleTabs('Excel', Array
        (
            'req_time'             => Array('저장시간', 0, '', 'center', '', 'end_time'),
            'id'                   => Array('작업자', 0, '', 'center', '', 'id'),
            'down_filename'        => Array('다운파일명', 0, '', 'center', '', 'down_filename'),
            'status'               => Array('상태', 0, '', 'center', '', 'status'),
        ));

        $list->setlistTitleTabs('Lump', Array
        (
            'no'                   => Array('번호', 0, '', 'center', '', 'no'),
            'reg_time'             => Array('등록일', 0, '', 'center', '', 'reg_time'),
            'division'             => Array('배치구분', 0, '', 'center', '', 'branch'),
            'status'               => Array('진행상태', 0, '', 'center', '', 'status'),
            'origin_file'          => Array('원본파일명', 0, '', 'center', '', 'origin_file'),
            'name'                 => Array('작업자', 0, '', 'center', '', 'name'),
            'start_time'           => Array('시작시간', 0, '', 'center', '', 'start_time'),
            'finish_time'          => Array('종료시간', 0, '', 'center', '', 'finish_time'),
            'remark'               => Array('비고', 0, '', 'center', '', 'remark'),
            'fail_file_name'       => Array('결과파일다운로드', 0, '', 'center', '', 'fail_file_name'),
            'ok_count'             => Array('성공', 0, '', 'center', '', 'ok_count'),
        ));

        return view('intranet.reservationDetail')->with('result', $list->getList());
    }
    
    /**
     * 예약내역 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function reservationList(Request $request)
    {
        $list       = $this->setDataList($request);
        $param      = $request->all();
        $reg_sdate  = str_replace('-', '', $request->searchDtString);
        $reg_edate  = str_replace('-', '', $request->searchDtStringEnd);
        $rslt_string = "";
        
        if($param['tabsSelect'] == "Excel")
        {
            // 기본쿼리
            $EXCEL = DB::TABLE("EXCEL_DOWN_LOG")->JOIN("USERS", "EXCEL_DOWN_LOG.ID", "=", "USERS.ID")
                        ->SELECT("EXCEL_DOWN_LOG.ID,EXCEL_DOWN_LOG.DOWN_FILENAME,EXCEL_DOWN_LOG.STATUS,NO,BRANCH,ETC,FILENAME,RSN_CD,REQ_TIME,END_TIME,RECORD_COUNT","USERS.NAME","EXCEL_DOWN_LOG.ORIGIN_FILENAME")
                        ->WHERE("RSN_CD",'<>',NULL)->WHERE("USERS.ID", "=", Auth::user()->id)
                        ->whereIn('EXCEL_DOWN_LOG.STATUS', ['S','A','D']); // "S"=>"대기", "A"=>"진행", "D"=>"완료", "E"=>"바로실행", "F"=>"실패"
           
            // 기준일자 검색
            if($reg_sdate && $reg_edate)
            {
                $EXCEL->WHERERAW("SUBSTRING(EXCEL_DOWN_LOG.REQ_TIME, 1, 8) >= '".$reg_sdate."' AND SUBSTRING(EXCEL_DOWN_LOG.REQ_TIME, 1, 8) <= '".$reg_edate."'");
            }
            elseif($reg_sdate)
            {
                $EXCEL->WHERE("SUBSTRING(EXCEL_DOWN_LOG.REQ_TIME, 1, 8)", ">=", $reg_sdate);
            }
            elseif($reg_edate)
            {
                $EXCEL->WHERE("SUBSTRING(EXCEL_DOWN_LOG.REQ_TIME, 1, 8)", "<=", $reg_edate);
            }

            if(isset($param['listOrder']) && $param['listOrder'] == 'down_filename') {
                if(isset($param['listOrderAsc'])  && $param['listOrderAsc']=='desc') {
                    $EXCEL = $EXCEL->orderBy('EXCEL_DOWN_LOG.filename', 'desc');
                }
                else if(isset($param['listOrderAsc'])  && $param['listOrderAsc']=='asc') {
                    $EXCEL = $EXCEL->orderBy('EXCEL_DOWN_LOG.filename', 'asc');
                }
                unset($param['listOrder']);
                unset($param['listOrderAsc']);
            }
    
            $EXCEL = $list->getListQuery('EXCEL_DOWN_LOG', 'main', $EXCEL, $param);
    
    
            // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
            $paging = new Paging($EXCEL, $request->page, $request->listLimit, 10);
    
            $rslt = $EXCEL->GET();
            $rslt = Func::chungDec(["EXCEL_DOWN_LOG","USERS"], $rslt);	// CHUNG DATABASE DECRYPT
           
    
            // 뷰단 데이터 정리
            $arrayBranch = Func::getBranch();
            $configArr   = Func::getConfigArr('excel_down_cd');
    
            $cnt = 0;
            foreach ($rslt as $v)
            {
                $v->cnt             = $cnt;
                $v->no              = $v->no;
                $v->id              = $v->name."(".$v->id.")";
                $v->branch          = $arrayBranch[$v->branch];
                $v->rsn_cd          = $configArr[$v->rsn_cd];
                $v->etc             = $v->etc;
                $v->filename        = $v->filename;
                if(!isset($v->down_filename)){
                    $v->down_filename = $v->filename;
                }
                if($v->status == "D")
                {
                    $v->down_filename = sprintf("<a href=\"javascript:excelDownload('%s', '%s', '%s', '%s', '%s', '%s');\">%s</a>", $v->no, $v->filename, $v->record_count, $v->down_filename, $v->status, $v->origin_filename, $v->down_filename);
                }
                $v->req_time        = Func::dateFormat($v->req_time);
                $v->status          = Vars::$arrExcelDownStatus[$v->status];
                $v->end_time        = Func::dateFormat($v->end_time);
                $r['v'][]           = $v;

                $cnt ++;
            }

        }
        elseif($param['tabsSelect'] == "Lump")
        {
            // 기본쿼리
            $LUMP = DB::TABLE("LUMP_MASTER_LOG")->JOIN("USERS", "LUMP_MASTER_LOG.REG_ID", "=", "USERS.ID")
            ->SELECTRAW("LUMP_MASTER_LOG.NO, LUMP_MASTER_LOG.REG_ID, USERS.NAME, USERS.BRANCH_CODE, LUMP_MASTER_LOG.STATUS, LUMP_MASTER_LOG.REG_TIME, LUMP_MASTER_LOG.REMARK, LUMP_MASTER_LOG.FAIL_FILE_NAME, LUMP_MASTER_LOG.OK_COUNT, LUMP_MASTER_LOG.START_TIME, LUMP_MASTER_LOG.FINISH_TIME, LUMP_MASTER_LOG.DIVISION, LUMP_MASTER_LOG.ORIGIN_FILE, LUMP_MASTER_LOG.EXCUTE_FILE_NAME")
            ->WHERE("USERS.ID", "=", Auth::user()->id)->WHERE("LUMP_MASTER_LOG.STATUS", "=", "W");

            // 기준일자 검색
            if($reg_sdate && $reg_edate)
            {
                $LUMP->WHERERAW("SUBSTRING(LUMP_MASTER_LOG.REG_TIME, 1, 8) >= '".$reg_sdate."' AND SUBSTRING(LUMP_MASTER_LOG.REG_TIME, 1, 8) <= '".$reg_edate."'");
            }
            elseif($reg_sdate)
            {
                $LUMP->WHERE("SUBSTRING(LUMP_MASTER_LOG.REG_TIME, 1, 8)", ">=", $reg_sdate);
            }
            elseif($reg_edate)
            {
                $LUMP->WHERE("SUBSTRING(LUMP_MASTER_LOG.REG_TIME, 1, 8)", "<=", $reg_edate);
            }

            $LUMP = $list->getListQuery('LUMP_MASTER_LOG', 'main', $LUMP, $param);

            
            // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
            $paging = new Paging($LUMP, $request->page, $request->listLimit, 10);
            
            $rslt = $LUMP->GET();
            $rslt = Func::chungDec(["LUMP_MASTER_LOG","USERS"], $rslt);	// CHUNG DATABASE DECRYPT
            
            

            $cnt = 0;
            $finish_time = "";
            foreach ($rslt as $v)
            {
                $v->cnt             = $cnt;
                $v->reg_time        = Func::dateFormat($v->reg_time);
                $v->division        = Vars::$arrayLumpLogPopList[$v->division];
                $v->status          = Vars::$arrayLumpLogStatus[$v->status];
                $v->origin_file     = $v->origin_file;
                $v->name            = $v->name."(".$v->reg_id.")";
                $v->start_time      = Func::dateFormat($v->start_time);

                $finish_time        = Func::dateFormat(Carbon::createFromTimestamp($v->finish_time));
                $v->finish_time     = substr($finish_time,0,4)."-".substr($finish_time,4,2)."-".substr($finish_time,6,2).substr($finish_time,8);
                
                $v->remark          = $v->remark;
                $v->fail_file_name  = $v->fail_file_name;
                $v->ok_count        = $v->ok_count;
                $r['v'][]           = $v;

                $cnt ++;
            }

        }

        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());
        $r['result']   = 1;
        $r['txt']      = $cnt;


        return json_encode($r);
    }
}


?>