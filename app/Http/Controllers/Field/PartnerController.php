<?php
namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Log;
use Auth;
use Func;
use Carbon;
use DataList;
use ExcelFunc;
use App\Chung\Sms;
use App\Chung\Vars;
use App\Chung\Paging;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

// php Spreadsheet 라이브러리
##################################################
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
##################################################

class PartnerController extends Controller
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
     * 협력사정보 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataList(Request $request)
    {
        $list = new DataList(Array("listName"=>"partner","listAction"=>'/'.$request->path()));

        $list->setButtonArray("엑셀다운","excelDownModal('/field/partnerexcel','form_partner')","btn-success");

        $list->setViewNum(false);

        $list->setPlusButton("partnerForm('');");

        $list->setSearchDetail(Array(
            'partner_name'   => '협력사명',
            'manager_name'   => '담당자명',
            'etc'            => '분류',
        ));

        return $list;
    }

    /**
     * 협력사정보 정보 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function partner(Request $request)
    {
        $list = $this->setDataList($request);

        $list->setlistTitleCommon(Array
        (
            'partner_name'          => Array('협력사명', 0, '', 'center', '', 'partner_name'),
            'manager_name'          => Array('담당자명', 0, '', 'center', '', 'manager_name'),
            'manager_ph'            => Array('담당자연락처', 1, '', 'center', '', 'manager_ph'),
            'etc'                   => Array('분류', 1, '', 'center', '', 'etc'),
        ));
        
        return view('field.partner')->with('result', $list->getList());
    }   
    
    /**
     * 협력사정보 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function partnerList(Request $request)
    { 
        $list  = $this->setDataList($request);

        $param = $request->all();

        $LOAN_LIST = DB::table("partner")->select("*")->where('save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='partner_name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('partner_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='manager_name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('manager_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='etc' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('etc', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $LOAN_LIST = $list->getListQuery('partner', 'main', $LOAN_LIST, $param);
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName);
        
        // 결과
        $LOAN_LIST = $LOAN_LIST->get();
        $LOAN_LIST = Func::chungDec(["partner"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($LOAN_LIST as $v)
        {
            $link_c          = '<a class="hand" onClick="partnerForm(\''.$v->no.'\')">';
            $v->partner_name = $link_c.($v->partner_name ?? '');

            $r['v'][] = $v;

            $cnt ++;
        }
		
        // 페이징
        $r['pageList']  = $paging->getPagingHtml($request->path());
        $r['result']    = 1;
        $r['txt']       = $cnt;
        $r['totalCnt']  = $paging->getTotalCnt();

        return json_encode($r);
    }
    
    /**
     * 협력사 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function partnerForm(Request $request)
    {
        $v = [];

        $partner_no = $request->partner_no ?? 0;

        if(!empty($partner_no))
        {
            $v = DB::table("partner")->select("*")->where('no',$partner_no)->where('save_status','Y')->first();
        }

        return view('field.partnerForm')->with("v", $v);
    }
    
    /*
     *  협력사 폼 등록 액션

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function partnerFormAction(Request $request)
    {
        $_DATA = $request->all();
        
        if($_DATA['mode'] == 'DEL')
        {
            $_DATA['save_status'] = 'N';
            $_DATA['del_id']      = Auth::id();
            $_DATA['del_time']    = date('YmdHis');

            $result = DB::dataProcess('UPD', 'partner', $_DATA, ['no'=>$_DATA['partner_no'], 'save_status'=>'Y']);
        }
        else if($_DATA['mode'] == 'UPD')
        {
            $_DATA['save_id']     = Auth::id();
            $_DATA['save_time']   = date('YmdHis');

            $result = DB::dataProcess('UPD', 'partner', $_DATA, ['no'=>$_DATA['partner_no'], 'save_status'=>'Y']);
        }
        else
        {
            $_DATA['save_status'] = 'Y';
            $_DATA['save_id']     = Auth::id();
            $_DATA['save_time']   = date('YmdHis');

            $result = DB::dataProcess('INS', 'partner', $_DATA);
        }

        if( $result == "Y" )
        {
            $array_result['rs_code'] = "Y";
            $array_result['result_msg'] = "정상적으로 처리되었습니다.";
        }
        else
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "관리자에게 문의바랍니다.";
        }

        return $array_result;
    }

    /**
     * 협력사정보 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function partnerExcel(Request $request)
    {   
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        
        $list           = $this->setDataList($request);

        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;

        $LOAN_LIST = DB::table("partner")->select("*")->where('save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='partner_name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('partner_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='manager_name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('manager_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='etc' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('etc', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $LOAN_LIST = $list->getListQuery('partner', 'main', $LOAN_LIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.               
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN_LIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "협력사정보_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no))
        {
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
            $origin_filename = $excel_no.'_'.$request->work_id.'_'.date("YmdHis").'.xlsx'; 
        }
        else
        {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $target_sql, $record_count,null,null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }

            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $LOAN_LIST = $LOAN_LIST->GET();
        $LOAN_LIST = Func::chungDec(["partner"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        // 엑셀 헤더
        $excel_header   = array('협력사명', '담당자명', '담당자연락처', '분류');
        $excel_data     = [];

        $array_config   = Func::getConfigArr();
        $arrManager     = Func::getUserList();

        foreach ($LOAN_LIST as $v)
        {
            $array_data = [
                $v->partner_name ?? '',                                               // 협력사명
                $v->name ?? '',                                                       // 담당자명
                $v->standard1 ?? '',                                                  // 담당자연락처
                $v->etc ?? ''                                                         // 분류
            ];

            $excel_data[] = $array_data;
            
            $record_count++;
        }
        
        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data, $excel_header, $origin_filename);
    
        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($origin_filename);
        
        if( isset($exists) )
        {
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
}