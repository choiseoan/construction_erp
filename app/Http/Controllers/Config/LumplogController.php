<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Chung\Func as ChungFunc;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use Config;
use DataList;
use Validator;
use Vars;
use ExcelFunc;
use FastExcel;
use Artisan;
use Cache;
use Decrypter;
use Excel;
use Image;
use FilFunc;
use App\Chung\Paging;
use Illuminate\Support\Facades\Storage;

// php Spreadsheet 라이브러리
##################################################
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
##################################################


class LumplogController extends Controller
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

    /**
     * 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataList(Request $request)
    {
        $list   = new DataList(Array("listName"=>"lumplog","listAction"=>'/'.$request->path()));
        $list->setTabs([], $request->Tabs);

        if (Auth::user()->branch_code == 'P99')
        {
            $finArray = Array("M", "CU", "I", "A", "GG", "LI", "LC", "VA", "SC", 'NS', 'LA', 'LN', 'CI', 'C', 'LCA', 'B', 'PLAN');
            //팝업창 리스트 
            unset($div);
            $div =  Vars::$arrayLumpLogPopList;
            foreach($div as $col => $v)
            {
                $array_pop_list[$col] = $v;

                // 일괄처리 작업완료 건 확인
                if(in_array($col, $finArray))
                {
                    $list->setButtonArray($array_pop_list[$col],"getPopUp('/config/lumplogpop/$col','lump','')","btn-success");
                }
                else
                {
                    $list->setButtonArray($array_pop_list[$col],"getPopUp('/config/lumplogpop/$col','lump','')","btn-primary");
                }
            }
        }
        else
        {
            $finArray = Array("M", "CU", "I", "A", "GG", "LI", "LC", "VA", "SC", 'NS', 'LA', 'LN', 'CI', 'C', 'LCA', 'B');
        }
       
        //검색항목 
        $list->setSearchDate('일자',Array('l.reg_time'=>'등록일'),'searchDt','Y');  //일자
        // $list->setSearchDate('날짜검색',Array('save_time' => '변경일'),'searchDt','Y', '',date("Ymd"), date("Ymd"),'L.reg_time');
        // date("Y-m-d H:i:s", strtotime($v->save_time))

        $branchlist  = Func::getBranch();

        $list->setSearchType('branch_code',$branchlist,'부서','onchange="setMemBranch(\'reg_id\',this.value)"');       //부서 
        $list->setSearchType('reg_id',[],'담당자');                                                                    //담당자
        // $list->setSearchType('division',Vars::$arrayLumpLogDivision,'배치구분');                                       //배치구분
        $list->setSearchType('status',Vars::$arrayLumpLogStatus,'진행상태');                                           //진행상태 
        $list->setSearchDetail(Array('ORIGIN_FILE' => '원본파일명'));                                                  //상세검색 array
        $list->setSearchDetailLikeOption("*");  //상세검색 부분검색 되도록 세팅 

        /* 일괄처리 엑셀다운로드는 불필요 - 추가요청시 오픈
        if( Func::funcCheckPermit("C122") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/config/lumplogexcel', 'form_lumplog')", "btn-success");
        }
        */

        return $list;
    }

    /**
     * 일괄처리로그 메인화면
     *
     * @param  Void
     * @return view
     */
	public function lumplog(Request $request)
    {
        $list           = $this->setDataList($request);

        $configArr      = Func::getConfigArr();
        $branchlist      = Func::getBranchList();

        $getUserList= Func::getUserList();
        foreach($getUserList as $code => $arr)
        {
            $arr_list[$arr->branch_code][] =  $arr->name;
            $arr_list_name[$arr->name][] =  $arr->id;
        }

        $list->setlistTitleCommon(Array
        (
            'reg_time'          => Array('등록일', 0, '', 'center', '', 'reg_time'),
            'division'          => Array('배치구분', 0, '', 'center', '', 'division'),
            'status'            => Array('진행상태', 0, '', 'center', '', 'status'),
            'origin_file'       => Array('원본파일명', 0, '', 'center', '', 'origin_file'),
            'reg_id'            => Array('작업자', 0, '', 'center', '', 'reg_id'),
            'start_time'        => Array('시작시간', 0, '', 'center', '', 'start_time'),
            'finish_time'       => Array('종료시간', 0, '', 'center', '', 'finish_time'),
            'remark'            => Array('비고', 0, '', 'center', '', 'remark'),
            'fail_file_name'    => Array('결과파일다운로드', 0, '', 'center', '', 'fail_file_name'),
            'ok_count'          => Array('성공', 0, '', 'center', '', 'ok_count'),
        ));

        return view('config.lumplog')->with("result", $list->getList())
                                     ->with('configArr', $configArr ?? '')
                                     ->with('arr_list', $arr_list ?? '')
                                     ->with('arr_list_name', $arr_list_name ?? '')
                                     ->with("array_branch", Func::getBranchList())
                                     ->with("array_manager", Func::getBranchUserList())
                                     ->with("array_user", Func::getUserId());
    }

    /**
     * 일괄처리로그 리스트
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function lumplogList(Request $request)
    {
        $list   = $this->setDataList($request);

        // 메인쿼리
        $LUMP_LOG = DB::TABLE("lump_master_log l")->SELECT("L.*") ->LEFTJOIN('users u', 'u.id', '=', 'l.reg_id')->WHERENOTNULL("l.reg_id");

        $param  = $request->all();

        if(!isset($param['listOrder']))
        {
            $param['listOrder']    = "no";
            $param['listOrderAsc'] = "desc";
        }
  
        
        $LUMP_LOG = $list->getListQuery("l", 'main', $LUMP_LOG, $param);
       
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LUMP_LOG, $request->page, $request->listLimit, 10);
        
        // 결과
        $result = $LUMP_LOG->get();
        $result = Func::chungDec(["lump_master_log"], $result);	// CHUNG DATABASE DECRYPT
        
        // 뷰단 데이터 정리.
        $cnt = 0;
        $getDivision  = Vars::$arrayLumpLogDivision;
        $getStatus  = Vars::$arrayLumpLogStatus;
        $getUserId= Func::getUserId(); //작업자 이름 리스트 

		foreach ($result as $v)
		{
            // 배치구분별 색상
            if($v->status == 'E' || $v->status == 'X')
            {
                $status_color = "<font color='red'>".Func::getArrayName($getStatus, $v->status)."</font>";
            }
            elseif($v->status == 'C')
            {
                $status_color = "<font color='blue'>".Func::getArrayName($getStatus, $v->status)."</font>";
            }
            else
            {
                $status_color = Func::getArrayName($getStatus, $v->status);
            }
            $v->no                  = $v->no;
            $v->reg_time            = date("Y-m-d H:i:s", strtotime($v->reg_time));                                     //등록일
            $v->division            = Func::getArrayName($getDivision, $v->division);                                   //배치구분
            $v->status              = $status_color;                                                                    //진행상태
            $v->line_style          = 'cursor: pointer;';
            $v->origin_file         = '<a href="/config/lumplogsample?no='.$v->no.'" >'.$v->origin_file.'</a>';
            $v->reg_id              = Func::getArrayName($getUserId, $v->reg_id);                                       //작업자
            $v->start_time          = Func::dateFormat($v->start_time);                                                 //시작시간
            $v->finish_time         = Func::dateFormat($v->finish_time);                                                //종료시간
            $v->remark              = empty($v->remark)?null:$v->remark;                                                //비고
            $v->fail_file_name      = '<a href="/config/lumplogfail?no='.$v->no.'" >'.$v->fail_file_name.'</a>';        //결과파일다운로드
            $v->ok_count            = empty($v->ok_count)?null:$v->ok_count;                                            //성공
         
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
     * 팝업리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function subDataList(Request $request)
    {
        $list   = new DataList(Array("listName"=>"lumplogpop", "listAction"=>"/config/lumplogpop/".$request->div."/"));
        
        $list->setIsPopup('Y');

        return $list;
    }
    
    /**
     * 일괄처리로그 팝업
     *
     * @param  Void
     * @return view
     */
	public function lumplogPop(Request $request)
    {
        $list           = $this->subDataList($request);

        $div            = $request->div;
        $div_flag       = $request->div;
        $configArr      = Func::getConfigArr();
        $branchlist     = Func::getBranchList();
        
        $list->setlistTitleCommon(Array
        (
            'reg_time'       => Array('등록일', 0, '', 'center', '', 'reg_time'),
            'status'         => Array('진행상태', 0, '', 'center', '', 'status'),
            'origin_file'    => Array('원본파일명', 0, '', 'center', '', 'origin_file'),
            'start_time'     => Array('시작시간', 0, '', 'center', '', 'start_time'),
            'finish_time'    => Array('종료시간', 0, '', 'center', '', 'finish_time'),
        ));
        
        return view('config.lumplogPop')->with("result", $list->getList())
                                        ->with('configArr', $configArr ?? '')
                                        ->with("div", $div);
    }

    public function lumplogPopList(Request $request)
    {
        $list   = $this->setDataList($request);

        $param  = $request->all();

        // 메인쿼리
        $LUMP_POP = DB::TABLE("lump_master_log")->SELECT("*")
                                                ->WHERE("reg_id", Auth::id())
                                                ->WHERE("division", $request->div);

        if(!isset($param['listOrder']))
        {
            $param['listOrder']    = "no";
            $param['listOrderAsc'] = "desc";
        }
        
        $LUMP_POP = $list->getListQuery("lump_master_log", 'main', $LUMP_POP, $param);
        
        // Log::debug("seller 메인 쿼리 ".Func::printQuery($LUMP_POP));
       
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LUMP_POP, $request->page, $request->listLimit, 10);
        
        // 결과
        $result = $LUMP_POP->get();
        $result = Func::chungDec(["LUMP_MASTER_LOG"], $result);	// CHUNG DATABASE DECRYPT
        
        // 뷰단 데이터 정리.
        $cnt = 0;
        $getStatus  = Vars::$arrayLumpLogStatus;
        $getUserId= Func::getUserId(); //작업자 이름 리스트 

		foreach ($result as $v)
		{
            $v->no              = $v->no;
            $v->reg_time        = date("Y-m-d H:i:s", strtotime($v->reg_time));                                 //등록일
            $v->status          = Func::getArrayName($getStatus, $v->status);                                   //진행상태
            $v->line_style      = 'cursor: pointer;';
            $v->origin_file     = '<a href="/config/lumplogsample?no='.$v->no.'" >'.$v->origin_file.'</a>';     //원본파일
            $v->start_time      = date('Y-m-d H:i:s', $v->start_time);                                          //시작시간
            $v->finish_time     = date('Y-m-d H:i:s', $v->finish_time);                                         //종료시간
         
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
     * 일괄처리로그 등록 엑셀 다운받기 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function lumplogDownFile(Request $request)
    {
        unset($no);
        $no = $request->no;

        $list = $this->setDataList($request);

        // 메인쿼리
        $main = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('no',$no);
        if(!isset($param['listOrder']))
        {
            $param['listOrder']    = "no";
            $param['listOrderAsc'] = "desc";
        }

        $main = $list->getListQuery("lump_master_log", 'main', $main, $param);
        // Log::debug("seller 메인 쿼리 ".Func::printQuery($main));
        $result = $main->get();

		foreach ($result as $v)
		{
            $path = 'public/'.$v->excute_file_path;
            $name = $v->excute_file_name;
        }

        if(Storage::disk('lumplog_S')->exists('12345.xlsx'))
        {
            Log::debug('파일 O');
            return Storage::disk('lumplog')->download('12345.xlsx');
        }
        else 
        {
            Log::debug('파일없음');
        }


    }
    
    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function lumplogUpload(Request $request)
    {
       
        $div = $request->division;

        // DB::beginTransaction();
        # 엑셀 등록 & 데이터 INSERT ##########################################################
        if( $request->file('lump_log_data') )
        {
            Log::debug(print_r($request->file('lump_log_data')->getClientOriginalName(), true));
                
            // 저장
            $param['file_path'] = Image::upload($request->file('lump_log_data'), "lumplog_upload/".$div);
          
            Log::debug('lumplog 경로 '.$param['file_path']); //경로 :/home/laravel/demo/storage/app/public/erp/lump_log/S/날짜/     
            // $folderDiv(폴더 구분)
            //변수세팅
            $getDivision  = Vars::$arrayLumpLogPopList;
            unset($file_arr, $file_path, $file_name, $idx);
            $file_arr = (array) explode('/', $param['file_path']);
            $file_path = $file_arr[0]."/".$file_arr[1]."/".$file_arr[2];
            $file_name = $file_arr[3];
            // 경로세팅 

            if( $request->file('lump_log_data') )
            {
                $extension = strtoupper($request->file('lump_log_data')->guessExtension());  // 확장자

                if(!in_array($extension, ['XLS','XLSX']))
                {
                    $r['rs_msg'] = "엑셀파일형식으로 등록해주세요.";
                    return $r;
                }

                # seller_info INSERT #####################################################
                unset($INS);
                $INS['reg_time']            = date("YmdHis");                       //등록일
                $INS['division']            = $div;                                 //배치구분
                $INS['status']              = 'W';                                  //배치구분
                $INS['origin_file']         = $request->file('lump_log_data')->getClientOriginalName(); //원본파일명
                $INS['excute_file_path']    = $file_path;                           //실행파일명
                $INS['excute_file_name']    = $file_name;                           //실행파일명
                $INS['fail_file_name']      = null;                                 //결과파일명
                $INS['reg_id']              = Auth::id();                           //작업자
                $INS['remark']              = '';                                   //비고
                $INS['fail_file_name']      = null;                                 //결과파일다운로드
                $INS['ok_count']            = null;                                 //성공
               
                $rslt = DB::dataProcess('INS', 'lump_master_log', $INS);
                $getStatus  = Vars::$arrayLumpLogStatus;

                $app['file_tot_cnt'] = 1;
                $app['reg_time'] = $INS['reg_time'];
                $app['status'] = $getStatus[$INS['status']];
                $app['origin_filename'] =  $INS['origin_file'];
                $app['finish_time'] = '';

                $r['v'][] = $app;
                $r['rs_msg'] = "파일 저장에 성공.";
                return $r;
            } 
            else 
            {
                $r['rs_msg'] = "파일 저장에 실패했습니다.";
                return $r;
            }
        }
        else
        {
            log::debug("contract_trade_manage  EMPTY");
            $r['rs_msg'] = "엑셀을 등록해주세요.";
            return $r;
        }
        
    }

    /**
     * 일괄처리로그 샘플파일 엑셀다운로드
     * or
     * 일괄처리로그 업로드파일 다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function lumplogSample(Request $request)
    {
        $list = $this->setDataList($request);

        $div = $request->division;
        $no = $request->no;
        $getDivision  = Vars::$arrayLumpLogPopList;

        if(!isset($no))
        {
            if ($div=='CONVERT')
            {
                log::debug($getDivision[$div]." 샘플다운 성공");
                return Storage::disk('lumplog')->download('sample_convert.xlsx', '기관차입등록예시파일.xlsx');
            }
            else
            {
                log::debug("샘플파일 없음");
                return redirect('/config/lumplog')->with('err', 'Y')->with('err_msg', '샘플 파일이 없습니다.');
            }
        }
        else 
        {
            // 메인쿼리
            $main = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('no',$no);
            if(!isset($param['listOrder']))
            {
                $param['listOrder']    = "no";
                $param['listOrderAsc'] = "desc";
            }

            $main = $list->getListQuery("lump_master_log", 'main', $main, $param);
            // Log::debug("seller 메인 쿼리 ".Func::printQuery($main));
            $result = $main->get();

            foreach ($result as $v)
            {
                $path = $v->excute_file_path;
                $name = $v->excute_file_name;
                $o_name = $v->origin_file;
            }

            // Log::debug('경로 = '.$path);
            // Log::debug('저장명 = '.$name);
            // Log::debug('파일명 = '.$o_name);

            if(isset($path) && isset($name) && isset($o_name))
            {
                // return Image::download( $path.'/'.$name, $o_name, $name);
                return Storage::download( 'public/'.$path.'/'.$name, $o_name);
                
            }
            else
            {
                return '정상적으로 다운되지 않았습니다. 전산실에 문의 바랍니다.';
            }
        }
    }

    /**
     * 일괄처리로그 결과파일 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
    public function lumplogFail(Request $request)
    {
        $list = $this->setDataList($request);

        $no = $request->no;

        // 메인쿼리
        $main = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('no',$no);
        if(!isset($param['listOrder']))
        {
            $param['listOrder']    = "no";
            $param['listOrderAsc'] = "desc";
        }

        $main = $list->getListQuery("lump_master_log", 'main', $main, $param);
        $result = $main->first();

        $name = $result->fail_file_name;
        $path = $result->fail_file_path."/".$result->fail_file_name;

        if(file_exists(storage_path('app/'.$path)))
        {
            return Storage::download( $path, $name);
        }
        else
        {
            Func::alertAndClose('정상적으로 다운되지 않았습니다. 전산실에 문의 바랍니다.');
        }
    }
   

    /**
     * 일괄처리로그 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function lumplogExcel(Request $request)
    {
        if( !Func::funcCheckPermit("C122") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list           = $this->setDataList($request);
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;
        
        // 메인쿼리
        // $LUMP_LOG_MAIN= DB::TABLE("lump_master_log l")->SELECT("l.*")->WHERENOTNULL("l.reg_id");
        $LUMP_LOG_MAIN = DB::TABLE("lump_master_log l")->SELECT("L.*") ->LEFTJOIN('users u', 'u.id', '=', 'l.reg_id')->WHERENOTNULL("l.reg_id");
        $LUMP_LOG_MAIN = $list->getListQuery("l", 'main', $LUMP_LOG_MAIN, $param);

        // $estate = DB::TABLE("lump_master_log L")->SELECT("l.*")->WHERENOTNULL("l.reg_id")->ORDERBY("l.no", "desc")->FIRST();

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LUMP_LOG_MAIN, $request->nowPage, $request->listLimit, 10);
        }

        // 엑셀다운 로그 시작
        $cnt          = 1;
        $record_count = 0;
        $query        = Func::printQuery($LUMP_LOG_MAIN);
        $file_name    = "일괄처리로그_".date("YmdHis").'_'.Auth::id().'.xlsx';
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

        $rslt = $LUMP_LOG_MAIN->GET();
        $rslt = Func::chungDec(["lump_master_log"], $rslt);	//암호화컬럼 복호화 처리 

		// 엑셀
		$excel_header = Array
        (
            'No','등록일','배치구분','진행상태','원본파일명','작업자','시작시간','종료시간','비고','결과파일다운로드명','성공'
		);

        $excel_data = Array();
 
        // 뷰단 데이터 정리.
        $getDivision = Vars::$arrayLumpLogDivision;
        $getStatus   = Vars::$arrayLumpLogStatus;
        $getUserId   = Func::getUserId();           //작업자 이름 리스트 
 		
        foreach ($rslt as $v)
        {
            $array_data = Array
            (
                $cnt,
                Func::dateFormat($v->reg_time),     //등록일
                Func::getArrayName($getDivision, $v->division),         //배치구분
                $getStatus[$v->status],             //진행상태
                $v->origin_file,                    //원본파일명
                // $v->excute_file_name,               //실행파일명
                // $v->fail_file_name,                 //결과파일명
                $getUserId[$v->reg_id],             //작업자
                Func::dateFormat($v->start_time),   //시작시간
                Func::dateFormat($v->finish_time),  //종료시간
                $v->remark,                         //비고
                $v->fail_file_name,                 //결과파일다운로드
                $v->ok_count,                       //성공
			);

            $cnt++;
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
}