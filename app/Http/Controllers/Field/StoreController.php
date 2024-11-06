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

class StoreController extends Controller
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
     * 입고수량명세 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataList(Request $request)
    {
        $list = new DataList(Array("listName"=>"store","listAction"=>'/'.$request->path()));

        $list->setButtonArray("엑셀다운","excelDownModal('/field/storeexcel','form_store')","btn-success");

        $list->setViewNum(false);

        $orderInfo = [];
        $orderName = DB::table('order_info')->where('save_status', 'Y')->get();
        foreach ($orderName as $key => $value) {
            $orderInfo[$value->no] = $value->field_name ?? '';
        }

        if(!empty($orderInfo))
        {
            $list->setSearchType('store-order_info_no',$orderInfo,'현장명', '', '', '', '', 'Y', '', true);
        }

        $list->setSearchDate('날짜검색',Array('store.info_date' => '기준일자'),'searchDt','Y');

        $list->setRangeSearchDetail(Array ('store.count'=>'수량', 'store.price'=>'단가'),'','','숫자');

        $list->setSearchDetail(Array(
            'store.code'       => '코드',
            'store.name'       => '품명',
        ));

        return $list;
    }

    /**
     * 입고수량명세 정보 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function store(Request $request)
    {
        $list = $this->setDataList($request);

        $list->setlistTitleCommon(Array
        (
            'field_name'            => Array('현장명', 0, '', 'center', '', 'field_name'),
            'info_date'             => Array('날짜', 0, '', 'center', '', 'info_date'),
            'com_name'              => Array('업체명', 1, '', 'center', '', 'com_name'),
            'code'                  => Array('코드', 1, '', 'center', '', 'code'),
            'name'                  => Array('품명', 1, '', 'center', '', 'name'),
            'standard1'             => Array('규격', 1, '', 'center', '', 'standard1'),
            'type'                  => Array('단위', 1, '', 'center', '', 'type'),
            'count'                 => Array('수량', 1, '', 'center', '', 'count'),
            'price'                 => Array('단가', 1, '', 'center', '', 'price'),
            'balance'               => Array('금액', 0, '', 'center', '', ''),
            'etc'                   => Array('비고', 1, '', 'center', '', 'etc'),
        ));
        
        return view('field.store')->with('result', $list->getList());
    }   
    
    /**
     * 입고수량명세 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function storeList(Request $request)
    { 
        $list  = $this->setDataList($request);

        $param = $request->all();

        $LOAN_LIST = DB::table("store")->join("order_info", "order_info.no", "=", "store.order_info_no")
                                        ->select("store.*", "order_info.field_name")
                                        ->where('store.save_status','Y')
                                        ->where('order_info.save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('store.no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='store.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('store.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='store.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('store.name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }
        
        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E001") )
        {
            $LOAN_LIST->whereRaw("(order_info.branch_code= '".Auth::user()->branch_code."' or order_info.save_id = '".Auth::id()."')");
        }

        $LOAN_LIST = $list->getListQuery('store', 'main', $LOAN_LIST, $param);
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $sum_data = Array
        (
            ["coalesce(sum(store.count*store.price),0)", '총금액', '원'],
        );
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName, '', $sum_data);
        
        // 결과
        $LOAN_LIST = $LOAN_LIST->get();
        $LOAN_LIST = Func::chungDec(["store"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        $arrayComName = Func::getArrayPartner();

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($LOAN_LIST as $v)
        {
            $link_c       = '<a class="hand" onClick="storePop(\''.$v->order_info_no.'\', \''.$v->no.'\')">';
            $v->field_name= $link_c.($v->field_name ?? '');

            $v->info_date = Func::dateFormat($v->info_date);
            $v->com_name  = Func::getArrayName($arrayComName, $v->com_name);
            $v->count     = $v->count ?? 0;
            $v->price     = $v->price ?? 0;
            $v->balance   = $v->count * $v->price;
            $v->price     = number_format($v->price,1);
            $v->price     = rtrim($v->price,0);
            $v->price     = rtrim($v->price,'.');
            $v->balance   = number_format($v->balance);

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
     * 입고수량명세 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function storeExcel(Request $request)
    {   
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        
        $list           = $this->setDataList($request);

        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;

        $LOAN_LIST = DB::table("store")->join("order_info", "order_info.no", "=", "store.order_info_no")
                                        ->select("store.*", "order_info.field_name")
                                        ->where('store.save_status','Y')
                                        ->where('order_info.save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('store.no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='store.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('store.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='store.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('store.name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }
        
        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E001") )
        {
            $LOAN_LIST->where("order_info.branch_code", Auth::user()->branch_code);
        }

        $LOAN_LIST = $list->getListQuery('store', 'main', $LOAN_LIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.               
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN_LIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "입고수량명세_".date("YmdHis").'_'.Auth::id().'.xlsx';
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
        $LOAN_LIST = Func::chungDec(["order_info", "store"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        // 엑셀 헤더
        $excel_header   = array('현장명', '날짜', '업체명', '코드', '품명', '규격', '단위', '수량', '단가', '금액', '비고');
        $excel_data     = [];

        $array_config   = Func::getConfigArr();
        $arrManager     = Func::getUserList();
        $arrayComName   = Func::getArrayPartner();

        foreach ($LOAN_LIST as $v)
        {
            $array_data = [
                $v->field_name ?? '',                                           //현장명
                Func::dateFormat($v->info_date),                                //날짜
                Func::getArrayName($arrayComName, $v->com_name),                //업체명
                $v->code,                                                       //코드
                $v->name,                                                       //품명
                $v->standard1,                                                  //규격
                $v->type,                                                       //단위
                number_format($v->count ?? 0),                                  //수량
                number_format($v->price ?? 0),                                  //단가
                number_format(($v->count) * ($v->price)),                       //금액
                $v->etc                                                         //비고
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