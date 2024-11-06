<?php
namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Log;
use PDF;
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

class OrderController extends Controller
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
     * 발주관리 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataOrderList(Request $request)
    {
        $list = new DataList(Array("listName"=>"order","listAction"=>'/'.$request->path()));

        $list->setSearchDate('날짜검색',Array('order_date' => '발주일자', 'import_date' => '반입일자'),'searchDt','Y');

        $list->setPlusButton("orderForm('');");

        $list->setSearchDetail(Array(
            'field_name'    => '현장명',
            'partner_name'  => '협력사명',
            'receiver_name' => '인수자',
        ));

        return $list;
    }

    public function order(Request $request)
    {
        $list = $this->setDataOrderList($request);

        $list->setlistTitleCommon(Array
        (
            'field_name'          => Array('현장명', 1, '', 'center', '', 'field_name'),
            'field_addr'          => Array('현장주소', 1, '', 'center', '', 'field_addr'),
            'receiver_name'       => Array('인수자', 1, '', 'center', '', 'receiver_name'),
            'receiver_ph'         => Array('인수자 연락처', 1, '', 'center', '', 'receiver_ph'),
            'contract_price'      => Array('계약 총금액', 1, '', 'center', '', ''),
            'store_price'         => Array('입고 총금액', 1, '', 'center', '', ''),
            'balance'             => Array('잔액', 1, '', 'center', '', ''),
            'branch_code'         => Array('담당부서', 1, '', 'center', '', 'branch_code'),
            'save_id'             => Array('작업자', 0, '', 'center', '', 'save_id', ['save_time'=>['저장시간', 'save_time', '<br>']]),
        ));
        
        return view('field.order')->with('result', $list->getList());
    }
    
    /**
     * 발주관리 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderList(Request $request)
    { 
        $list  = $this->setDataOrderList($request);

        $param = $request->all();

        // 메인쿼리
        $LOAN_LIST = DB::table("order_info")->select("*")->where('save_status','Y');

        if(empty($param['listOrder']) && empty($param['listOrderAsc']))
        {
            $param['listOrder'] = 'no';
            $param['listOrderAsc'] = 'desc';
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='field_name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('field_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='partner_name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('partner_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='receiver_name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('receiver_name', 'like','%'.$param['searchString'].'%');
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
            $LOAN_LIST->where("branch_code", Auth::user()->branch_code);
        }

        $LOAN_LIST = $list->getListQuery('order_info', 'main', $LOAN_LIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName);

        $rslt = $LOAN_LIST->get();
        $rslt = Func::chungDec(["order_info"], $rslt);	// CHUNG DATABASE DECRYPT

        $configArr   = Func::getConfigArr();
        $arrayBranch = Func::getBranch();
        $arrayUserId = Func::getUserId();

        $cnt = 0;

        foreach ($rslt as $v)
        {
            $v->onclick                  = 'popUpFull(\'/field/orderpop?no='.$v->no.'\', \'order'.$v->no.'\')';
            $v->line_style               = 'cursor: pointer;';
            
            $v->order_date               = Func::dateFormat($v->order_date);
            $v->import_date              = Func::dateFormat($v->import_date);

            $v->status                   = Func::getInvStatus($v->status, true);

            $v->branch_code              = Func::getArrayName($arrayBranch, $v->branch_code);
            $v->save_id                  = Func::getArrayName($arrayUserId, $v->save_id);
            $v->save_time                = Func::dateFormat($v->save_time);
 
            unset($contract);
            $contract = DB::table("contract")->select(DB::raw("coalesce(sum(count*price),0) as sum_price"))
                                            ->where('order_info_no',$v->no)
                                            ->where('save_status','Y')
                                            ->first();

            $v->contract_price           = $contract->sum_price;

            unset($store);
            $store = DB::table("store")->select(DB::raw("coalesce(sum(count*price),0) as sum_price"))
                                            ->where('order_info_no',$v->no)
                                            ->where('save_status','Y')
                                            ->first();
            $v->store_price              = $store->sum_price;

            if(($v->contract_price - $v->store_price) > 0)
            {
                $v->balance              = $v->contract_price - $v->store_price;
            }
            else
            {
                $v->balance              = 0;
            }

            $v->balance                  = number_format($v->balance);
            $v->contract_price           = number_format($v->contract_price);
            $v->store_price              = number_format($v->store_price);

            $r['v'][] = $v;
            $cnt ++;
        }
		
        // 페이징
        $r['pageList']  = $paging->getPagingHtml($request->path());
        $r['result']    = 1;
        $r['txt']       = $cnt;
        $r['targetSql'] = $target_sql;
        $r['totalCnt']  = $paging->getTotalCnt();

        return json_encode($r);
    }

    /**
     * 발주계약 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderForm(Request $request)
    {
        $arrayConfig  = Func::getConfigArr();

        return view('field.orderForm')->with("arrayConfig", $arrayConfig);
    }

    /*
     *  발주계약 등록 액션

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function orderFormAction(Request $request)
    {
        $_DATA = $request->all();
        
        if(!empty($_DATA['order_date']))
        {
            $_DATA['order_date'] = preg_replace('/[^0-9]/', '', $_DATA['order_date']);
        }
        if(!empty($_DATA['import_date']))
        {
            $_DATA['import_date'] = preg_replace('/[^0-9]/', '', $_DATA['import_date']);
        }

        $_DATA['save_status']   = 'Y';
        $_DATA['save_id']       = Auth::id();
        $_DATA['save_time']     = date('YmdHis');

        $result = DB::dataProcess('INS', 'order_info', $_DATA);
        
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
     * 발주정보 - 팝업창
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderPop(Request $request)
    {
        $status_color = "#6c757d";
        $no = $request->no;
        
        $info = DB::table("order_info")->select("*")->where("no",$no)->where("save_status", "Y")->first();
        $info = Func::chungDec(["order_info"], $info);	// CHUNG DATABASE DECRYPT

        unset($contract);
        $contract = DB::table("contract")->select(DB::raw("coalesce(sum(count*price),0) as sum_price"))
                                        ->where('order_info_no',$info->no)
                                        ->where('save_status','Y')
                                        ->first();

        $info->contract_price = $contract->sum_price;

        unset($store);
        $store = DB::table("store")->select(DB::raw("coalesce(sum(count*price),0) as sum_price"))
                                        ->where('order_info_no',$info->no)
                                        ->where('save_status','Y')
                                        ->first();
        $info->store_price    = $store->sum_price;

        if(($info->contract_price - $info->store_price) > 0)
        {
            $info->balance    = $info->contract_price - $info->store_price;
        }
        else
        {
            $info->balance    = 0;
        }

        return view('field.orderPop')->with("info", $info)->with("status_color", $status_color);
    }

    /**
     * 발주정보 팝업창 - 상세정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderInfo(Request $request)
    {
        $v  = $extra = [];

        $no = $request->order_info_no;
        
        if(is_numeric($no))
        {
            $v = DB::table("order_info")->select("*")->where("no", $no)->where('save_status','Y')->first();
            $v = Func::chungDec(["order_info"], $v);	// CHUNG DATABASE DECRYPT
            
            $orderExtra = DB::table("order_extra")
                            ->join("contract", "contract.code", "=", "order_extra.code")
                            ->select(
                                "order_extra.seq", 
                                "order_extra.code", 
                                "order_extra.volume", 
                                "order_extra.price", 
                                "order_extra.etc",
                                "contract.name", 
                                "contract.standard1", 
                                "contract.standard2", 
                                "contract.type")
                            ->where('order_extra.order_info_no', $no)
                            ->where('contract.order_info_no', $no)
                            ->where('order_extra.save_status', 'Y')
                            ->where('contract.save_status', 'Y')
                            ->orderBy('order_extra.seq', 'desc')
                            ->get();
            $orderExtra = Func::chungDec(["order_extra", "contract"], $orderExtra);

            foreach ($orderExtra as $key => $value)
            {
                $value->real_price = number_format($value->price ?? 0, 1);
                $value->real_price = rtrim($value->real_price, 0);
                $value->real_price = rtrim($value->real_price, '.');

                $extra[] = $value;
            }
        }

        return view('field.orderInfo')->with('v', $v)->with('order_extra', $extra);
    }

    /**
     * 발주정보 저장
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function orderInfoAction(Request $request)
    {
        $_DATA = $request->all();
        
        // 삭제배열
        $_DEL = array();
        $_DEL['save_status'] = 'N';
        $_DEL['del_id']      = Auth::id();
        $_DEL['del_time']    = date('YmdHis');

        // 발주서 정보
        // 수정
        if($_DATA['mode'] == 'UPD')
        {
            if(!empty($_DATA['order_date']))
            {
                $_DATA['order_date'] = preg_replace('/[^0-9]/', '', $_DATA['order_date']);
            }
            if(!empty($_DATA['import_date']))
            {
                $_DATA['import_date'] = preg_replace('/[^0-9]/', '', $_DATA['import_date']);
            }
            
            $_DATA['save_id']   = Auth::id();
            $_DATA['save_time'] = date('YmdHis');

            $result = DB::dataProcess('UPD', 'order_info', $_DATA, ['no'=>$_DATA['order_info_no']]);
        }
        // 삭제
        else
        {
            $result = DB::dataProcess('UPD', 'order_info', $_DEL, ['no'=>$_DATA['order_info_no'], 'save_status'=>'Y']);

            $result = DB::dataProcess('UPD', 'store', $_DEL, ['order_info_no'=>$_DATA['order_info_no'], 'save_status'=>'Y']);

            $result = DB::dataProcess('UPD', 'contract', $_DEL, ['order_info_no'=>$_DATA['order_info_no'], 'save_status'=>'Y']);
        }

        // 유효성 체크
        if( empty($result) || $result != "Y" )
        {
            $array_result['rs_code']    = "N";
            $array_result['result_msg'] = "등록 오류";

            return $array_result;
        }

        // 계약수량
        // 일단 매번 삭제하고 리스트 다시 저장
        $result = DB::dataProcess('UPD', 'order_extra', $_DEL, ['order_info_no'=>$_DATA['order_info_no'], 'save_status'=>'Y']);

        if($_DATA['mode'] == 'UPD')
        {
            if(!empty($_DATA['code']))
            {
                // order_info_no에 대한 최대 시퀀스 가져오기
                $maxSeq = DB::table('order_extra')
                            ->where('order_info_no', $_DATA['order_info_no'])
                            ->where('save_status', 'Y')
                            ->max('seq');

                $seq = $maxSeq ? $maxSeq + 1 : 1;

                foreach ($_DATA['code'] as $key => $val)
                {
                    if(!empty($val))
                    {
                        $_INS = array();
                        $_INS['order_info_no'] = $_DATA['order_info_no'];
                        $_INS['seq']           = $seq;
                        $_INS['code']          = $_DATA['code'][$key] ?? '';
                        if(!empty($_DATA['volume'][$key]))
                        {
                            $_INS['volume'] = round(str_replace(',', '', $_DATA['volume'][$key]));
                        }
                        if(!empty($_DATA['price'][$key]))
                        {
                            $_INS['price'] = round(str_replace(',', '', $_DATA['price'][$key]),1);
                        }
                        $_INS['etc']           = $_DATA['etc'][$key] ?? '';

                        $_INS['save_status']   = 'Y';
                        $_INS['save_id']       = Auth::id();
                        $_INS['save_time']     = date('YmdHis');

                        $result = DB::dataProcess('INS', 'order_extra', $_INS);

                        // store 테이블에 insert
                        $orderExtra = DB::table("order_extra")
                                        ->join("contract", "contract.code", "=", "order_extra.code")
                                        ->select(
                                            "order_extra.code", 
                                            "contract.name", 
                                            "contract.standard1", 
                                            "contract.standard2", 
                                            "contract.type")
                                        ->where('order_extra.order_info_no', $_DATA['order_info_no'])
                                        ->where('order_extra.save_status', 'Y')
                                        ->where('contract.save_status', 'Y')
                                        ->orderBy('order_extra.seq', 'desc')
                                        ->get();

                        $orderExtra = Func::chungDec(["order_extra", "contract"], $orderExtra);
                        $currentExtra = $orderExtra->firstWhere('code', $_DATA['code'][$key]);
                        
                        $_STORE = array();
                        $_STORE['order_info_no']    = $_DATA['order_info_no'];
                        $_STORE['info_date']        = date('Ymd');
                        $_STORE['com_name']         = $request->partner_name ?? '';
                        $_STORE['code']             = $_DATA['code'][$key] ?? '';
                        $_STORE['name']             = $currentExtra->name ?? '';
                        $_STORE['standard1']        = $currentExtra->standard1 ?? '';
                        $_STORE['standard2']        = $currentExtra->standard2 ?? '';
                        $_STORE['type']             = $currentExtra->type ?? '';
                        if(!empty($_DATA['volume'][$key]))
                        {
                            $_STORE['count'] = round(str_replace(',', '', $_DATA['volume'][$key]));
                        }
                        if(!empty($_DATA['price'][$key]))
                        {
                            $_STORE['price'] = round(str_replace(',', '', $_DATA['price'][$key]),1);
                        }
                        $_STORE['etc']              = $_DATA['etc'][$key] ?? '';
                        
                        $_STORE['save_status']      = 'Y';
                        $_STORE['save_id']          = Auth::id();
                        $_STORE['save_time']        = date('YmdHis');
                        
                        $storeResult = DB::dataProcess('INS', 'store', $_STORE);

                        $seq++;
                    }
                }
            }
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
     * 발주서 pdf 다운
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function orderInfoPdf(Request $request)
    {
        $param = $request->all();       

        $no = $request->order_info_no;

        $info = $extra = [];

        $info['field_name']    = $param['field_name'] ?? '';
        $info['field_addr']    = $param['field_addr'] ?? '';
        $info['partner_name']  = $param['partner_name'] ?? '';
        $info['order_date']    = $param['order_date'] ?? '';
        $info['import_date']   = $param['import_date'] ?? '';
        $info['receiver_name'] = $param['receiver_name'] ?? '';
        $info['receiver_ph']   = $param['receiver_ph'] ?? '';
        $info['order_memo']    = $param['order_memo'] ?? '';
       
        if(!empty($param['code']))
        {
            $seq = 1;
            foreach ($param['code'] as $key => $val)
            {
                unset($contract);
                
                $contract = DB::table('contract')->select('name', 'standard1', 'standard2', 'type')->where('save_status', 'Y')->where('order_info_no', $no)->where('code', $val)->first();

                if(empty($contract)) continue;

                $extra[] = [
                    'name'       => $contract->name ?? '',
                    'standard1'  => $contract->standard1 ?? '',
                    'standard2'  => $contract->standard2 ?? '',
                    'type'       => $contract->type ?? '',
                    'volume'     => $param['volume'][$key] ?? 0,
                    'etc'        => $param['etc'][$key] ?? '',
                ];
                
                $seq++;
            }
        }

        $pdf = Pdf::loadView('field.orderinfopdf', [
            'info' => $info,
            'extra' => $extra
        ]);

        $fileName = $info['field_name'].'_발주서.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName));
        header('Content-Transfer-Encoding: binary');
        
        return $pdf->output();
    }

    /**
     * 발주서 엑셀다운
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function orderInfoExcel(Request $request)
    {
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
    
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;

        $no = $request->order_info_no;
    
        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name = "발주서_".date("YmdHis").'_'.Auth::id().'.xlsx';
    
        $excel_no = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, '', $record_count,null,null, $down_filename, $excel_down_div);
        
        $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        
        $excel_data = [];
        $excel_style_merge = [];
    
        // 발주서 상단 info
        $excel_data[] = ['발  주  서', '', '', '', '', '', ''];
        $excel_data[] = ['', '', '', '', '', '', ''];
        $excel_data[] = ['', '', '', '', '', '', ''];
        $excel_data[] = ['현장명', $param['field_name'] ?? '', '', '발주일자', $param['order_date'] ?? '', '', ''];
        $excel_data[] = ['현장주소', $param['field_addr'] ?? '', '', '반입일자', $param['import_date'] ?? '', '', ''];
        $excel_data[] = ['협력사명', Func::getArrayName(Func::getArrayPartner(), $info['partner_name'] ?? ''), '', '인수자', $param['receiver_name'] ?? '', '' ,''];
        $excel_data[] = ['아래와 같이 발주 요청합니다.', '', '', '인수자 연락처', $param['receiver_ph'] ?? '', '', '', ''];
        $excel_data[] = ['', '', '', '', '', '', ''];
        $excel_data[] = ['', '', '', '', '', '', ''];

        $excel_style_merge = array_merge($excel_style_merge, array(
            "A1:G2",                    // 발주서
            "E4:G4",                    // 발주일자
            "E5:G5",                    // 반입일자
            "E6:G6",                    // 인수자
            "A7:B8", "D7:D8", "E7:G8"   // 아래와 같이 발주 요청합니다, 인수자 연락처
        ));
    
        // 계약수량
        $excel_data[] = ['NO', '품목', '', '규격1', '단위', '수량', '비고'];
        for ($i = 10; $i <= 27; $i++) {
            $excel_style_merge = array_merge($excel_style_merge, array("B$i:C$i"));
        }

        $total_items = count($param['code'] ?? []);
        for ($i = 0; $i < 17; $i++)
        {
            if ($i < $total_items)
            {
                $contract = DB::table('contract')
                    ->select('name', 'standard1', 'type')
                    ->where('save_status', 'Y')
                    ->where('order_info_no', $no)
                    ->where('code', $param['code'][$i])
                    ->first();

                if ($contract)
                {
                    $excel_data[] = [
                        $i + 1,                                     // NO
                        $contract->name ?? '',                      // 품명
                        '',
                        $contract->standard1 ?? '',                 // 규격(1)
                        $contract->type ?? '',                      // 단위
                        $param['volume'][$i] ?? 0,                  // 수량
                        $param['etc'][$i] ?? '',                    // 비고
                    ];

                    $record_count++;
                }
                else
                {
                    $excel_data[] = ['', '', '', '', '', '', ''];
                }
            }
            else
            {
                $excel_data[] = ['', '', '', '', '', '', ''];
            }
        }
    
        // 특이사항
        $excel_data[] = ['특이사항', $param['order_memo'] ?? '', '', '', '', '', ''];

        $excel_style_merge = array_merge($excel_style_merge, array(
            "A28:A32",           // 특이사항 제목
            "B28:G32",           // 특이사항 내용
        ));
    
        
        // 스타일
        $excel_style_center = array("A1:G32");
        $excel_style_vertical = [
            'bottom' => ["A7:B8"]
        ];
        $excel_style_horizontal = [
            'left' => ["B28:G32"]
        ];
        $excel_style_border = [
            'all' => ["A4:B6", "D4:G8", "A10:G32"],
            'outline_bold' => ["A1:G2", "A4:B6", "D4:G8", "A10:G32"]
        ];
        $excel_style_width = [
            'A' => '9',
            'B' => '26.5',
            'C' => '4.5',
            'D' => '15',
            'E' => '7.83',
            'F' => '9.17',
            'G' => '22.67'
        ];
        $excel_style_height = [
            '1' => '33',
            '2' => '33',
            '3' => '33',
            '4' => '15',
            '5' => '22',
            '6' => '22',
            '7' => '22',
            '8' => '22',
            '9' => '15',
            '10' => '15',
            '11' => '33'
        ];
        for ($i = 12; $i <= 33; $i++) {
            $excel_style_height = array_merge($excel_style_height, array($i => '22'));
        }

        $excel_style_title_font = array("A1:G2");

        $excel_style_page = array("A1:G33");

        $excel_style = [
            'merge' => $excel_style_merge, 
            'center' => $excel_style_center, 
            'vertical' => $excel_style_vertical, 
            'horizontal' => $excel_style_horizontal, 
            'border' => $excel_style_border,
            'title_font' => $excel_style_title_font,
            'width' => $excel_style_width,
            'height' => $excel_style_height,
            'page' => $excel_style_page
        ];
    
        $_EXCEL = Array();
        $_EXCEL[] = Array(
            "header"    =>  [],
            "excel_data"=>  $excel_data,
            "title"     =>  $file_name,
            "style"     =>  $excel_style,
        );
    
        // 엑셀 익스포트
        ExcelFunc::downExcelSheet($_EXCEL, $origin_filename);
    
        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($origin_filename);
    
        if(isset($exists))
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
           $array_result['result'] = 'N';
           $array_result['error_msg'] = "파일생성에 실패하였습니다.\n"; 
        }
    
        return $array_result;
    }


    /*
     *  발주관리 일괄삭제

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function orderExtraAllClear(Request $request)
    {
        $param = $request->all();
        
        $_DATA = array();
        $_DATA['save_status'] = 'N';
        $_DATA['del_id']      = Auth::id();
        $_DATA['del_time']    = date('YmdHis');

        $result = DB::dataProcess('UPD', 'order_extra', $_DATA, ['order_info_no'=>$param['order_info_no'], 'save_status'=>'Y']);

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
     * 발주정보 팝업창 - 입고수량
     * 입고 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataStoreList(Request $request)
    {
        $list = new DataList(Array("listName"=>"orderStore","listAction"=>'/'.$request->path()));

        $list->setSearchDetail(Array(
            'store.code'       => '코드',
            'store.name'       => '품명',
        ));

        return $list;
    }

    /**
     * 발주정보 팝업창 - 입고수량
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderStore(Request $request)
    {
        $list = $this->setDataStoreList($request);
        
        $list->setButtonArray("일괄삭제", "orderStoreAllClear('".$request->order_info_no."')", "btn-danger");

        $list->setButtonArray("엑셀업로드", "orderStoreExcelForm('".$request->order_info_no."')", "btn-info");

        $list->setButtonArray("엑셀다운","excelDownModal('/field/orderstoreexcel','form_orderStore')","btn-success");
        
        $list->setViewNum(false);
        
        $list->setHidden(Array('order_info_no' => $request->order_info_no));

        $list->setlistTitleCommon(Array
        (
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

        return view('field.orderStore')->with('result', $list->getList());
    }

    /**
     * 발주정보 팝업창 - 입고수량 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function orderStoreList(Request $request)
    {
        $param = $request->all();

        $LOAN_LIST = DB::table("store")->join("order_info", "order_info.no", "=", "store.order_info_no")
                            ->select("store.*")
                            ->where('order_info.no',$param['order_info_no'])
                            ->where('order_info.save_status','Y')
                            ->where('store.save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
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
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName);
        
        // 결과
        $LOAN_LIST = $LOAN_LIST->get();
        $LOAN_LIST = Func::chungDec(["store"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        $arrayComName = Func::getArrayPartner();

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($LOAN_LIST as $v)
        {
            $link_c       = '<a class="hand" onClick="orderStoreForm(\''.$v->order_info_no.'\', \''.$v->no.'\')">';
            $v->code      = $link_c.$v->code;

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
     * 입고수량 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderStoreExcel(Request $request)
    {   
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        
        $list           = $this->setDataStoreList($request);

        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;

        $LOAN_LIST = DB::table("store")->join("order_info", "order_info.no", "=", "store.order_info_no")
                                        ->select("store.*")
                                        ->where('order_info.no',$param['order_info_no'])
                                        ->where('order_info.save_status','Y')
                                        ->where('store.save_status','Y');
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
        $file_name    = "입고수량_".date("YmdHis").'_'.Auth::id().'.xlsx';
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
        $excel_header   = array('날짜', '업체명', '코드', '품명', '규격', '단위', '수량', '단가', '금액', '비고');
        $excel_data     = [];

        $array_config   = Func::getConfigArr();
        $arrManager     = Func::getUserList();
        $arrayComName   = Func::getArrayPartner();

        foreach ($LOAN_LIST as $v)
        {
            $array_data = [
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
    
    /**
     * 입고수량 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderStoreForm(Request $request)
    {
        $v = [];

        $order_info_no = $request->order_info_no;
        $store_no      = $request->store_no ?? 0;

        if(!empty($store_no))
        {
            $v = DB::table("store")->select("*")->where('no',$store_no)->where('save_status','Y')->first();

            $v->price = number_format($v->price ?? 0,1);
            $v->price = rtrim($v->price, 0);
            $v->price = rtrim($v->price, '.');
        }

        return view('field.orderStoreForm')->with("order_info_no", $order_info_no)->with("v", $v);
    }

    /*
     *  입고수량 폼 등록 액션

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function orderStoreFormAction(Request $request)
    {
        $_DATA = $request->all();
        
        if($_DATA['mode'] == 'DEL')
        {
            $_DATA['save_status'] = 'N';
            $_DATA['del_id']      = Auth::id();
            $_DATA['del_time']    = date('YmdHis');

            $result = DB::dataProcess('UPD', 'store', $_DATA, ['no'=>$_DATA['store_no'], 'save_status'=>'Y']);
        }
        else
        {
            $_DATA['save_id']   = Auth::id();
            $_DATA['save_time'] = date('YmdHis');

            if(!empty($_DATA['count']))
            {
                $_DATA['count'] = round(str_replace(',', '', $_DATA['count']));
            }
            if(!empty($_DATA['price']))
            {
                $_DATA['price'] = round(str_replace(',', '', $_DATA['price']));
            }

            $result = DB::dataProcess('UPD', 'store', $_DATA, ['no'=>$_DATA['store_no'], 'save_status'=>'Y']);
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

    /*
     *  입고수량 일괄삭제

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function orderStoreAllClear(Request $request)
    {
        $_DATA = $request->all();
        
        $_DATA['save_status'] = 'N';
        $_DATA['del_id']      = Auth::id();
        $_DATA['del_time']    = date('YmdHis');

        $result = DB::dataProcess('UPD', 'store', $_DATA, ['order_info_no'=>$_DATA['order_info_no'], 'save_status'=>'Y']);

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
     * 계약수량 엑셀업로드 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderStoreExcelForm(Request $request)
    {
        return view('field.orderStoreExcelForm')->with("order_info_no", $request->order_info_no);
    }

    /**
     * 입고수량 엑셀업로드 샘플
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderStoreExcelSample(Request $request)
    {
        if(Storage::disk('order')->exists('storeExcelSample.xlsx'))
        {
            return Storage::disk('order')->download('storeExcelSample.xlsx', '입고수량업로드예시파일.xlsx');
        }
        else
        {
            log::debug("샘플파일 없음");
        }
    }

    /**
     * 입고수량 엑셀업로드 액션
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderStoreExcelAction(Request $request)
    {
        if(empty($request->order_info_no))
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "현장번호가 존재하지 않습니다.";
            return $r;
        }

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);

        $arrayComName = Func::getArrayPartner();
        if(empty($arrayComName))
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "협력사관리에 데이터가 존재하지 않습니다.";
            return $r;
        }

        if($request->file('excel_data'))
        {
            $file_path = $request->file('excel_data')->store("upload/" . date("YmdHis"), 'order');
            
            // 저장된 파일 경로가 존재하는지 확인
            if(Storage::disk('order')->exists($file_path))
            {
                $colHeader  = array(
                    "날짜",
                    "업체명",
                    "코드",
                    "품명",
                    "규격",
                    "단위",
                    "수량",
                    "단가",
                    "비고"
                );
                
                $colNm = array(
                    "info_date"     => "0",  // 날짜
                    "com_name"      => "1",  // 업체명
                    "code"          => "2",  // 코드
                    "name"          => "3",  // 품명
                    "standard1"     => "4",  // 규격
                    "type"          => "5",  // 단위
                    "count"         => "6",  // 수량
                    "price"         => "7",  // 단가
                    "etc"           => "8"   // 비고
                );
                                    
                $file = Storage::path('/order/' . $file_path);
                
                $excelData = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader, 0);

                if(!isset($excelData))
                {
                    log::debug($file_path);

                    $r['rs_code'] = "N";
                    $r['rs_msg']  = "엑셀 유효성 검사를 실패하였습니다.";
                    return $r;
                }
                else
                {
                    $this->removeEmptyRow($excelData);

                    // 필수값이 누락된 행 정보를 저장할 배열
                    $missingFieldsRows = [];
                    $rowIndex = 2;

                    // 각 행을 처리하여 필수값이 누락된 행이 있는지 검사
                    foreach($excelData as $_DATA)
                    {
                        unset($_INS);
                        $hasData = false; // 값이 입력된 행인지 확인

                        // 데이터 정리: 공백을 제거하고 트림
                        foreach($_DATA as $key => $val)
                        {
                            $val = trim($val);
                            if ($val !== "") {
                                $hasData = true;
                            }
                            $_INS[$key] = $val;
                        }

                        // 값이 입력된 행만 처리
                        if ($hasData)
                        {
                            $missingFields = [];

                            if(empty($_INS['info_date']))
                            {
                                $missingFields[] = '날짜';
                            }
                            if(empty($_INS['com_name']))
                            {
                                $missingFields[] = '업체명';
                            }
                            if(empty($_INS['code']))
                            {
                                $missingFields[] = '코드';
                            }

                            // 누락된 필수값이 있는 경우
                            if(count($missingFields) > 0)
                            {
                                $missingFieldsRows[] = "\n[행: {$rowIndex}, 내용: " . implode(", ", $missingFields) . "]";
                            }
                        }

                        $rowIndex++;
                    }

                    // 필수값 누락이 있는 경우 에러 메시지 반환
                    if(count($missingFieldsRows) > 0)
                    {
                        $r['rs_code'] = "N";
                        $r['rs_msg']  = "필수값이 비어있습니다.\n" . implode($missingFieldsRows);
                        return $r;
                    }

                    // 각 행을 처리하여 데이터베이스에 저장
                    $rowIndex = 2; // 행 번호 초기화 (헤더가 1행이므로 데이터는 2행부터 시작)
                    foreach($excelData as $_DATA)
                    {
                        unset($_INS);
                        $hasData = false;

                        // 데이터 정리: 공백을 제거하고 트림
                        foreach($_DATA as $key => $val)
                        {
                            $val = trim($val);
                            if ($val !== "") {
                                $hasData = true;
                            }
                            $_INS[$key] = $val;
                        }

                        // 값이 입력된 행만 처리
                        if ($hasData)
                        {
                            // 빈 문자열을 null로 변환
                            if(!empty($_INS['count']))
                            {
                                $_INS['count'] = round(str_replace(',', '', $_INS['count']));
                            }
                            if(!empty($_INS['price']))
                            {
                                $_INS['price'] = round(str_replace(',', '', $_INS['price']),1);
                            }
                            $_INS['com_name']      = Func::getArrayName(array_flip($arrayComName), $_INS['com_name']);
                            $_INS['info_date']     = str_replace('-', '', $_INS['info_date']);
                            $_INS['file_path']     = $file_path;
                            $_INS['save_status']   = 'Y';
                            $_INS['save_id']       = Auth::id();
                            $_INS['save_time']     = date('YmdHis');
                            $_INS['order_info_no'] = $request->order_info_no;
                            
                            $result = DB::dataProcess('INS', 'store', $_INS);
                        }

                        $rowIndex++;
                    }

                    // 성공 메시지 반환
                    $r['rs_code'] = "Y";
                    $r['rs_msg']  = "엑셀 업로드를 성공하였습니다.";
                    return $r;
                }
            }
            else
            {
                // 파일 경로가 존재하지 않으면 로그에 기록
                log::debug($file_path ?? '파일경로 없음');

                // 에러 메시지 반환
                $r['rs_code'] = "N";
                $r['rs_msg']  = "엑셀 업로드를 실패했습니다.";
                return $r;
            }
        }
        else
        {
            // 엑셀 파일이 존재하지 않으면 에러 메시지 반환
            $r['rs_code'] = "N";
            $r['rs_msg']  = "엑셀을 등록해주세요.";
            return $r;
        }
    }

	/**
	 * 계약수량 검색 모달
	 *
	 * @param  Request $request
	 * @return view
	 */
	public function orderContractSearch(Request $request)
	{
		$query = DB::table('order_info')
                    ->join("contract", "contract.order_info_no", "=", "order_info.no")
					->select('contract.*')
					->where('order_info.no', $request->order_info_no)
					->where('order_info.save_status', 'Y')
					->where('contract.save_status', 'Y');

		if(isset($request->keyword))
		{
			$keyword = $request->keyword;
			$query = $query->where(function($q) use ($keyword)
            {
				$q->where('contract.name', 'like', '%'.$keyword.'%')
				->orWhere('contract.category', 'like', '%'.$keyword.'%')
				->orWhere('contract.code', 'like', '%'.$keyword.'%')
				->orWhere('contract.standard1', 'like', '%'.$keyword.'%')
				->orWhere('contract.standard2', 'like', '%'.$keyword.'%')
				->orWhere('contract.type', 'like', '%'.$keyword.'%')
				->orWhere('contract.etc', 'like', '%'.$keyword.'%');
			});
		}

		$query = $query->orderBy('no', 'desc');

		// 총건수
		$result['cnt']      = $query->count();
		
		// 한페이지.
		$result['contract'] = $query->limit(10)->offset((($request->page ?? 1)-1)*10)->get();
		$result['contract'] = Func::chungDec(["order_info","contract"], $result['contract']);	// CHUNG DATABASE DECRYPT

        return $result;
	}

    /**
     * 발주정보 팝업창 - 수량비교
     * 수량비교 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataCompareList(Request $request)
    {
        $list = new DataList(Array("listName"=>"orderCompare","listAction"=>'/'.$request->path()));

        $list->setSearchDetail(Array(
            'contract.category'   => '구분',
            'contract.code'       => '코드',
            'contract.name'       => '품명',
        ));

        return $list;
    }

    /**
     * 발주정보 팝업창 - 수량비교
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderCompare(Request $request)
    {
        $list = $this->setDataCompareList($request);
        
        $list->setButtonArray("엑셀다운","excelDownModal('/field/ordercompareexcel','form_orderCompare')","btn-success");
        
        $list->setViewNum(false);
        
        $list->setHidden(Array('order_info_no' => $request->order_info_no));

        $list->setlistTitleCommon(Array
        (
            'code'                  => Array('코드', 0, '', 'center', '', 'code'),
            'name'                  => Array('품명', 1, '', 'center', '', 'name'),
            'standard1'             => Array('규격(1)', 1, '', 'center', '', 'standard1'),
            'standard2'             => Array('규격(2)', 1, '', 'center', '', 'standard2'),
            'type'                  => Array('단위', 1, '', 'center', '', 'type'),
            'count'                 => Array('계약수량', 1, '', 'center', '', 'count'),
            'order_count'           => Array('입고수량', 1, '', 'center', '', ''),
            'last_count'            => Array('잔여수량', 1, '', 'center', '', ''),
            'persent_count'         => Array('%', 1, '', 'center', '', ''),
        ));

        return view('field.orderCompare')->with('result', $list->getList());
    }

    /**
     * 발주정보 팝업창 - 수량비교 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function orderCompareList(Request $request)
    {
        $param = $request->all();

        $LOAN_LIST = DB::TABLE("contract")->select('*')
                                        ->addselect(DB::raw("(select coalesce(sum(store.count),0) from store where save_status = 'Y' and store.order_info_no=contract.order_info_no and store.code=contract.code) as order_count"))
                                        ->where("order_info_no",$param['order_info_no'])
                                        ->where('save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='contract.category' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('contract.category', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='contract.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('contract.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='contract.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('contract.name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName);
        
        // 결과
        $LOAN_LIST = $LOAN_LIST->get();
        $LOAN_LIST = Func::chungDec(["order_info", "contract"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($LOAN_LIST as $v)
        {
            $v->count               = $v->count ?? 0;
            $v->order_count         = $v->order_count ?? 0;

            $v->last_count          = $v->count - $v->order_count;

            if($v->count == 0)
            {
                $v->persent_count   = 0;
            }
            else
            {
                $v->persent_count   = $v->order_count/$v->count*100;
            }

            $v->count               = number_format($v->count);
            $v->order_count         = number_format($v->order_count);
            $v->last_count          = number_format($v->last_count);
            $v->persent_count       = round($v->persent_count,1).'%';

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
     * 수량비교 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderCompareExcel(Request $request)
    {   
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        
        $list           = $this->setDataStoreList($request);

        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;

        $LOAN_LIST = DB::TABLE("contract")->select('*')
                                        ->addselect(DB::raw("(select coalesce(sum(store.count),0) from store where save_status = 'Y' and store.order_info_no=contract.order_info_no and store.code=contract.code) as order_count"))
                                        ->where("order_info_no",$param['order_info_no'])
                                        ->where('save_status','Y');
        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='contract.category' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('contract.category', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='contract.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('contract.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='contract.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('contract.name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $LOAN_LIST = $list->getListQuery('contract', 'main', $LOAN_LIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.               
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN_LIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "수량비교_".date("YmdHis").'_'.Auth::id().'.xlsx';
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
        $LOAN_LIST = Func::chungDec(["order_info", "contract"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT
        
        // 엑셀 헤더
        $excel_header   = array('코드', '품명', '규격(1)', '규격(2)','단위', '계약수량', '입고수량', '잔여수량', '%', '비고');
        $excel_data     = [];

        $array_config   = Func::getConfigArr();
        $arrManager     = Func::getUserList();

        foreach ($LOAN_LIST as $v)
        {
            $array_data = [
                $v->code,                                                       //코드
                $v->name,                                                       //품명
                $v->standard1,                                                  //규격(1)
                $v->standard2,                                                  //규격(2)
                $v->type,                                                       //단위
                number_format($v->count ?? 0),                                  //계약수량
                number_format($v->order_count ?? 0),                            //입고수량
                number_format(($v->count ?? 0) - ($v->order_count ?? 0)),       //잔여수량
                $v->count ? round(((($v->order_count ?? 0) / $v->count) * 100), 1) . '%' : '0%',  //% 
                $v->etc                                                         //기타
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

    /**
     * 발주정보 팝업창 - 계약수량
     * 계약수량 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataContractList(Request $request)
    {
        $list = new DataList(Array("listName"=>"orderContract","listAction"=>'/'.$request->path()));

        $list->setSearchDetail(Array(
            'contract.category'   => '구분',
            'contract.code'       => '코드',
            'contract.name'       => '품명',
        ));

        return $list;
    }

    /**
     * 발주정보 팝업창 - 계약수량
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderContract(Request $request)
    {
        $list = $this->setDataContractList($request);
        
        $order_info_no = $request->order_info_no;

        $list->setButtonArray("일괄삭제", "orderContractAllClear('".$order_info_no."')", "btn-danger");

        $list->setButtonArray("엑셀업로드", "orderContractExcelForm('".$order_info_no."')", "btn-info");

        $list->setButtonArray("엑셀다운","excelDownModal('/field/ordercontractexcel','form_orderContract')","btn-success");
        
        $list->setPlusButton("orderContractForm('".$order_info_no."', '');");
        
        $list->setViewNum(false);
        
        $list->setHidden(Array('order_info_no' => $order_info_no));

        $list->setlistTitleCommon(Array
        (
            'category'              => Array('구분', 1, '', 'center', '', 'category'),
            'code'                  => Array('코드', 0, '', 'center', '', 'code'),
            'name'                  => Array('품명', 1, '', 'center', '', 'name'),
            'standard1'             => Array('규격(1)', 1, '', 'center', '', 'standard1'),
            'standard2'             => Array('규격(2)', 1, '', 'center', '', 'standard2'),
            'type'                  => Array('단위', 1, '', 'center', '', 'type'),
            'count'                 => Array('수량', 1, '', 'center', '', 'count'),
            'price'                 => Array('단가', 1, '', 'center', '', 'price'),
            'balance'               => Array('금액', 0, '', 'center', '', ''),
            'etc'                   => Array('기타', 1, '', 'center', '', 'etc'),
        ));

        return view('field.orderContract')->with('result', $list->getList());
    }

    /**
     * 발주정보 팝업창 - 계약수량 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function orderContractList(Request $request)
    {
        $param = $request->all();

        $LOAN_LIST = DB::TABLE("order_info")
                            ->join("contract", "contract.order_info_no", "=", "order_info.no")
                            ->select("contract.*")
                            ->where("order_info.no",$param['order_info_no'])
                            ->where('contract.save_status','Y')
                            ->where('order_info.save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='contract.category' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('contract.category', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='contract.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('contract.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='contract.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('contract.name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName);
        
        // 결과
        $LOAN_LIST = $LOAN_LIST->get();
        $LOAN_LIST = Func::chungDec(["order_info", "contract"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($LOAN_LIST as $v)
        {
            $v->count               = $v->count ?? 0;
            $v->price               = $v->price ?? 0;
            $v->balance             = $v->count*$v->price;

            $v->count               = number_format($v->count);
            $v->price               = number_format($v->price,1);
            $v->price               = rtrim($v->price, 0);
            $v->price               = rtrim($v->price, '.');
            $v->balance             = number_format($v->balance);

            $link_c                 = '<a class="hand" onClick="orderContractForm(\''.$v->order_info_no.'\', \''.$v->no.'\')">';
            $v->code                = $link_c.$v->code;

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
     * 계약수량 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderContractExcel(Request $request)
    {   
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        
        $list           = $this->setDataContractList($request);

        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;

        $LOAN_LIST = DB::TABLE("order_info")
                            ->join("contract", "contract.order_info_no", "=", "order_info.no")
                            ->select("contract.*")
                            ->where("order_info.no",$param['order_info_no'])
                            ->where('contract.save_status','Y')
                            ->where('order_info.save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='contract.category' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('contract.category', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='contract.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('contract.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='contract.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('contract.name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $LOAN_LIST = $list->getListQuery('order_info', 'main', $LOAN_LIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.               
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN_LIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "계약수량_".date("YmdHis").'_'.Auth::id().'.xlsx';
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
        $LOAN_LIST = Func::chungDec(["order_info", "contract"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT
        
        // 엑셀 헤더
        $excel_header   = array('구분', '코드','품명', '규격(1)', '규격(2)','단위', '수량', '단가','금액','기타');
        $excel_data     = [];

        $array_config   = Func::getConfigArr();
        $arrManager     = Func::getUserList();

        foreach ($LOAN_LIST as $v)
        {
            $price = number_format($v->price ?? 0,1);
            $price = rtrim($price, 0);
            $price = rtrim($price, '.');

            $array_data = [
                $v->category,                                       //구분
                $v->code,                                           //코드
                $v->name,                                           //품명
                $v->standard1,                                      //규격(1)
                $v->standard2,                                      //규격(2)
                $v->type,                                           //단위
                number_format($v->count ?? 0),                      //수량
                $price,                                             //단가
                number_format(($v->count ?? 0)*($v->price ?? 0)),   //금액
                $v->etc,                                            //기타
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

    /**
     * 계약수량 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderContractForm(Request $request)
    {
        $v = [];

        $order_info_no = $request->order_info_no;
        $contract_no   = $request->contract_no ?? 0;

        if(!empty($contract_no))
        {
            $v = DB::table("contract")->select("*")->where('no',$contract_no)->where('save_status','Y')->first();

            $v->price = number_format($v->price ?? 0,1);
            $v->price = rtrim($v->price, 0);
            $v->price = rtrim($v->price, '.');
        }

        return view('field.orderContractForm')->with("order_info_no", $order_info_no)->with("v", $v);
    }

    /*
     *  계약수량 폼 등록 액션

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function orderContractFormAction(Request $request)
    {
        $_DATA = $request->all();
        
        if($_DATA['mode'] == 'DEL')
        {
            $_DATA['save_status'] = 'N';
            $_DATA['del_id']      = Auth::id();
            $_DATA['del_time']    = date('YmdHis');

            $result = DB::dataProcess('UPD', 'contract', $_DATA, ['no'=>$_DATA['contract_no'], 'save_status'=>'Y']);
        }
        else if($_DATA['mode'] == 'UPD')
        {
            $code = DB::table("contract")->select("no")->where('order_info_no',$_DATA['order_info_no'])->where('code',$_DATA['code'])->where('no','!=',$_DATA['contract_no'])->where('save_status','Y')->first();
            if(!empty($code->no))
            {
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "중복된 코드 입니다.";

                return $array_result;
            }

            if(!empty($_DATA['price']))
            {
                $_DATA['price'] = round(str_replace(',', '', $_DATA['price']),1);
            }

            $_DATA['save_id']     = Auth::id();
            $_DATA['save_time']   = date('YmdHis');

            $result = DB::dataProcess('UPD', 'contract', $_DATA, ['no'=>$_DATA['contract_no'], 'save_status'=>'Y']);
        }
        else
        {
            $code = DB::table("contract")->select("no")->where('order_info_no',$_DATA['order_info_no'])->where('code',$_DATA['code'])->where('save_status','Y')->first();
            if(!empty($code->no))
            {
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "중복된 코드 입니다.";

                return $array_result;
            }

            if(!empty($_DATA['price']))
            {
                $_DATA['price'] = round(str_replace(',', '', $_DATA['price']),1);
            }

            $_DATA['save_status'] = 'Y';
            $_DATA['save_id']     = Auth::id();
            $_DATA['save_time']   = date('YmdHis');

            $result = DB::dataProcess('INS', 'contract', $_DATA);
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

    /*
     *  계약수량 일괄삭제

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function orderContractAllClear(Request $request)
    {
        $_DATA = $request->all();
        
        $_DATA['save_status'] = 'N';
        $_DATA['del_id']      = Auth::id();
        $_DATA['del_time']    = date('YmdHis');

        $result = DB::dataProcess('UPD', 'contract', $_DATA, ['order_info_no'=>$_DATA['order_info_no'], 'save_status'=>'Y']);

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
     * 계약수량 엑셀업로드 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderContractExcelForm(Request $request)
    {
        return view('field.orderContractExcelForm')->with("order_info_no", $request->order_info_no);
    }

    /**
     * 계약수량 엑셀업로드 샘플
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderContractExcelSample(Request $request)
    {
        if(Storage::disk('order')->exists('contractExcelSample.xlsx'))
        {
            return Storage::disk('order')->download('contractExcelSample.xlsx', '계약수량업로드예시파일.xlsx');
        }
        else
        {
            log::debug("샘플파일 없음");
        }
    }

    /**
     * 계약수량 엑셀업로드 액션
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function orderContractExcelAction(Request $request)
    {
        if(empty($request->order_info_no))
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "현장번호가 존재하지 않습니다.";
            return $r;
        }

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);

        if($request->file('excel_data'))
        {
            $file_path = $request->file('excel_data')->store("upload/" . date("YmdHis"), 'order');
            
            // 저장된 파일 경로가 존재하는지 확인
            if(Storage::disk('order')->exists($file_path))
            {
                $colHeader  = array(
                    "구분",
                    "코드",
                    "품명",
                    "규격(1)",
                    "규격(2)",
                    "단위",
                    "수량",
                    "단가",
                    "비고"
                );
                
                $colNm = array(
                    "category"      => "0",  // 구분
                    "code"          => "1",  // 코드
                    "name"          => "2",  // 품명
                    "standard1"     => "3",  // 규격(1)
                    "standard2"     => "4",  // 규격(2)
                    "type"          => "5",  // 단위
                    "count"         => "6",  // 수량
                    "price"         => "7",  // 단가
                    "etc"           => "8"  // 비고
                );
                                    
                $file = Storage::path('/order/' . $file_path);
                
                $excelData = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader, 0);

                if(!isset($excelData))
                {
                    log::debug($file_path);

                    $r['rs_code'] = "N";
                    $r['rs_msg']  = "엑셀 유효성 검사를 실패하였습니다.";
                    return $r;
                }
                else
                {
                    $this->removeEmptyRow($excelData);

                    // 필수값이 누락된 행 정보를 저장할 배열
                    $missingFieldsRows = [];
                    $rowIndex = 2;

                    // 각 행을 처리하여 필수값이 누락된 행이 있는지 검사
                    foreach($excelData as $_DATA)
                    {
                        unset($_INS);
                        $hasData = false; // 값이 입력된 행인지 확인

                        // 데이터 정리: 공백을 제거하고 트림
                        foreach($_DATA as $key => $val)
                        {
                            $val = trim($val);
                            if ($val !== "") {
                                $hasData = true;
                            }
                            $_INS[$key] = $val;
                        }

                        // 값이 입력된 행만 처리
                        if ($hasData)
                        {
                            $missingFields = [];

                            if(empty($_INS['category']))
                            {
                                $missingFields[] = '구분';
                            }
                            if(empty($_INS['code']))
                            {
                                $missingFields[] = '코드';
                            }
                            if(empty($_INS['name']))
                            {
                                $missingFields[] = '품명';
                            }

                            // 누락된 필수값이 있는 경우
                            if(count($missingFields) > 0)
                            {
                                $missingFieldsRows[] = "\n[행: {$rowIndex}, 내용: " . implode(", ", $missingFields) . "]";
                            }
                        }

                        $rowIndex++;
                    }

                    // 필수값 누락이 있는 경우 에러 메시지 반환
                    if(count($missingFieldsRows) > 0)
                    {
                        $r['rs_code'] = "N";
                        $r['rs_msg']  = "필수값이 비어있습니다.\n" . implode($missingFieldsRows);
                        return $r;
                    }

                    // 각 행을 처리하여 데이터베이스에 저장
                    $rowIndex = 2; // 행 번호 초기화 (헤더가 1행이므로 데이터는 2행부터 시작)
                    foreach($excelData as $_DATA)
                    {
                        unset($_INS);
                        $hasData = false;

                        // 데이터 정리: 공백을 제거하고 트림
                        foreach($_DATA as $key => $val)
                        {
                            $val = trim($val);
                            if ($val !== "") {
                                $hasData = true;
                            }
                            $_INS[$key] = $val;
                        }

                        // 값이 입력된 행만 처리
                        if ($hasData)
                        {
                            // 빈 문자열을 null로 변환
                            if(!empty($_INS['count']))
                            {
                                $_INS['count'] = round(str_replace(',', '', $_INS['count']));
                            }
                            if(!empty($_INS['price']))
                            {
                                $_INS['price'] = round(str_replace(',', '', $_INS['price']),1);
                            }

                            // 중복 코드 확인
                            $code = DB::table("contract")->select("no")
                                                            ->where('order_info_no', $request->order_info_no)
                                                            ->where('code', $_INS['code'])
                                                            ->where('save_status', 'Y')
                                                            ->first();
                            
                            if(!empty($code))
                            {
                                $_INS['file_path']     = $file_path;
                                $_INS['save_id']       = Auth::id();
                                $_INS['save_time']     = date('YmdHis');

                                $result = DB::dataProcess('UPD', 'contract', $_INS, ['no'=>$code->no]);
                            }
                            else
                            {
                                $_INS['file_path']     = $file_path;
                                $_INS['save_status']   = 'Y';
                                $_INS['save_id']       = Auth::id();
                                $_INS['save_time']     = date('YmdHis');
                                $_INS['order_info_no'] = $request->order_info_no;
                                
                                $result = DB::dataProcess('INS', 'contract', $_INS);
                            }
                        }
                        $rowIndex++;
                    }

                    // 성공 메시지 반환
                    $r['rs_code'] = "Y";
                    $r['rs_msg']  = "엑셀 업로드를 성공하였습니다.";
                    return $r;
                }
            }
            else
            {
                // 파일 경로가 존재하지 않으면 로그에 기록
                log::debug($file_path ?? '파일경로 없음');

                // 에러 메시지 반환
                $r['rs_code'] = "N";
                $r['rs_msg']  = "엑셀 업로드를 실패했습니다.";
                return $r;
            }
        }
        else
        {
            // 엑셀 파일이 존재하지 않으면 에러 메시지 반환
            $r['rs_code'] = "N";
            $r['rs_msg']  = "엑셀을 등록해주세요.";
            return $r;
        }
    }

    /**
     * 엑셀 파일 읽어온 데이터(배열)에서 빈 배열 제거
     */
    private function removeEmptyRow(Array &$target)
    {
        foreach ($target as $index => $row)
        {
            $isEmpty = true;
            foreach ($row as $value)
            {
                if (!empty($value)) {
                    $isEmpty = false;
                    break;
                } 
            }

            if ($isEmpty) {
                unset($target[$index]);
                continue;
            }
        }
    }
}