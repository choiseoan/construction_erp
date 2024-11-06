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

class ManagementController extends Controller
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
     * 현장관리 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataManagementList(Request $request)
    {
        $list   = new DataList(Array("listName"=>"management","listAction"=>'/'.$request->path()));

        $list->setSearchDate('날짜검색',Array('contract_date' => '공사시작일', 'contract_end_date' => '공사종료일'),'searchDt','Y');

        $list->setSearchType('div',Func::getConfigArr('management_div'),'현장구분', '', '', '', '', 'Y', '', true);
        
        $list->setPlusButton("managementForm('');");

        $list->setSearchDetail(Array(
            'code'          => '코드',
            'orderer'       => '발주처',
            'name'          => '현장명',
        ));

        return $list;
    }

    public function management(Request $request)
    {
        $list = $this->setDataManagementList($request);

        $list->setlistTitleCommon(Array
        (
            'code'                      => Array('코드', 1, '', 'center', '', 'code'),
            'name'                      => Array('현장명', 1, '', 'center', '', 'name'),
            'div'                       => Array('구분', 1, '', 'center', '', 'div'),
            'orderer'                   => Array('발주처', 1, '', 'center', '', 'orderer'),
            'balance'                   => Array('공사금액', 0, '', 'center', '', ''),
            'contract_date'             => Array('공사시작일', 1, '', 'center', '', 'contract_date'),
            'contract_end_date'         => Array('공사종료일', 1, '', 'center', '', 'contract_end_date'),
            'branch_code'               => Array('담당부서', 1, '', 'center', '', 'branch_code'),
            'save_id'                   => Array('작업자', 0, '', 'center', '', 'save_id', ['save_time'=>['저장시간', 'save_time', '<br>']]),
        ));
        
        return view('field.management')->with('result', $list->getList());
    }   
    
    /**
     * 현장관리 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementList(Request $request)
    { 
        $list  = $this->setDataManagementList($request);

        $param = $request->all();

        // 메인쿼리
        $LOAN_LIST = DB::table("contract_info")->select("*")->where('save_status','Y');

        if(empty($param['listOrder']) && empty($param['listOrderAsc']))
        {
            $param['listOrder'] = 'no';
            $param['listOrderAsc'] = 'desc';
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='orderer' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('orderer', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('name', 'like','%'.$param['searchString'].'%');
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

        $LOAN_LIST = $list->getListQuery('contract_info', 'main', $LOAN_LIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName);

        $rslt = $LOAN_LIST->get();
        $rslt = Func::chungDec(["contract_info"], $rslt);	// CHUNG DATABASE DECRYPT

        $configArr   = Func::getConfigArr();
        $arrayBranch = Func::getBranch();
        $arrayUserId = Func::getUserId();

        $cnt = 0;

        foreach ($rslt as $val)
        {
            $val->onclick            = 'popUpFull(\'/field/managementpop?no='.$val->no.'\', \'management'.$val->no.'\')';
            $val->line_style         = 'cursor: pointer;';
            
            $val->name               = $val->name; 
            $val->contract_date      = Func::dateFormat($val->contract_date);

            $val->status             = Func::getInvStatus($val->status, true);
            $val->contract_end_date  = Func::dateFormat($val->contract_end_date);
            $val->save_time          = Func::dateFormat($val->save_time);
            $val->branch_code        = Func::getArrayName($arrayBranch, $val->branch_code);
            $val->save_id            = Func::getArrayName($arrayUserId, $val->save_id);
            $val->div                = Func::getArrayName($configArr['management_div'], $val->div);

            $val->balance = 0;

            $report = DB::table("report")->select("*")
                                        ->where('contract_info_no',$val->no)
                                        ->where('save_status','Y')
                                        ->get();

            foreach ($report as $k => $v)
            {
                $v->volume      = $v->volume ?? 0;
                $sum_price      = 0;
                $v->extra_price = $v->extra_price ?? 0;

                for($i=1; $i<=10; $i++)
                {
                    unset($cost, $cost_extra);
    
                    if(!empty($v->{'code'.$i}))
                    {
                        $cost = DB::table('cost')->select('*')
                                                ->where('contract_info_no', $val->no)
                                                ->where('code', $v->{'code'.$i})
                                                ->where('save_status', 'Y')
                                                ->first();
                        $cost = Func::chungDec(["cost"], $cost);	// CHUNG DATABASE DECRYPT
                        
                        if(!empty($cost))
                        {
    
                            $cost_extra = DB::table('cost_extra')->select('*')
                                                                ->where('contract_info_no', $val->no)
                                                                ->where('cost_no', $cost->no)
                                                                ->where('save_status', 'Y')
                                                                ->get();
                            $cost_extra = Func::chungDec(["cost_extra"], $cost_extra);	// CHUNG DATABASE DECRYPT
    
                            foreach($cost_extra as $v2)
                            {
                                $material = DB::table('material')->select('*')
                                                                ->where('contract_info_no', $val->no)
                                                                ->where('code', $v2->code)
                                                                ->where('save_status', 'Y')
                                                                ->first();
                                $sum_price += ($material->price ?? 0);
                            }
                        }
                    }
                }
    
                $val->balance += ($v->volume * $sum_price);

                $val->balance += ($v->volume * $v->extra_price);
            }
            
            $report_detail = DB::table("report_detail")->select('*')
                                                        ->where('contract_info_no',$val->no)
                                                        ->where('save_status','Y')
                                                        ->get();

            foreach ($report_detail as $k => $v)
            {
                $v->volume      = $v->volume ?? 0;
                $v->price       = $v->price ?? 0;
                $v->extra_price = $v->extra_price ?? 0;

                $val->balance += ($v->volume * $v->price);

                $val->balance += ($v->volume * $v->extra_price);
            }

            $val->balance = number_format($val->balance);

            $r['v'][] = $val;
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
     * 현장계약 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementForm(Request $request)
    {
        $arrayConfig  = Func::getConfigArr();

        return view('field.managementForm')->with("arrayConfig", $arrayConfig);
    }

    /*
     *  현장계약 등록 액션

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function managementFormAction(Request $request)
    {
        $_DATA = $request->all();
        
        if(!empty($_DATA['contract_date']))
        {
            $_DATA['contract_date'] = preg_replace('/[^0-9]/', '', $_DATA['contract_date']);
        }
        if(!empty($_DATA['contract_end_date']))
        {
            $_DATA['contract_end_date'] = preg_replace('/[^0-9]/', '', $_DATA['contract_end_date']);
        }

        $_DATA['save_status']   = 'Y';
        $_DATA['save_id']       = Auth::id();
        $_DATA['save_time']     = date('YmdHis');

        $result = DB::dataProcess('INS', 'contract_info', $_DATA);
        
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
     * 현장정보 - 팝업창
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementPop(Request $request)
    {
        $status_color = "#6c757d";

        $no = $request->no;
        
        $info = DB::table("contract_info")->select("*")->where("no",$no)->where("save_status", "Y")->first();
        $info = Func::chungDec(["contract_info"], $info);	// CHUNG DATABASE DECRYPT

        $info->balance = 0;

        $report = DB::table("report")->select("*")
                                    ->where('contract_info_no',$no)
                                    ->where('save_status','Y')
                                    ->get();

        foreach ($report as $k => $v)
        {
            $v->volume      = $v->volume ?? 0;
            $sum_price      = 0;
            $v->extra_price = $v->extra_price ?? 0;

            for($i=1; $i<=10; $i++)
            {
                unset($cost, $cost_extra);

                if(!empty($v->{'code'.$i}))
                {
                    $cost = DB::table('cost')->select('*')
                                            ->where('contract_info_no', $no)
                                            ->where('code', $v->{'code'.$i})
                                            ->where('save_status', 'Y')
                                            ->first();
                    $cost = Func::chungDec(["cost"], $cost);	// CHUNG DATABASE DECRYPT
                    
                    if(!empty($cost))
                    {

                        $cost_extra = DB::table('cost_extra')->select('*')
                                                            ->where('contract_info_no', $no)
                                                            ->where('cost_no', $cost->no)
                                                            ->where('save_status', 'Y')
                                                            ->get();
                        $cost_extra = Func::chungDec(["cost_extra"], $cost_extra);	// CHUNG DATABASE DECRYPT

                        foreach($cost_extra as $v2)
                        {
                            $material = DB::table('material')->select('*')
                                                            ->where('contract_info_no', $no)
                                                            ->where('code', $v2->code)
                                                            ->where('save_status', 'Y')
                                                            ->first();
                            $sum_price += ($material->price ?? 0);
                        }
                    }
                }
            }

            $info->balance += ($v->volume * $sum_price);

            $info->balance += ($v->volume * $v->extra_price);
        }
        
        $report_detail = DB::table("report_detail")->select('*')
                                                    ->where('contract_info_no',$no)
                                                    ->where('save_status','Y')
                                                    ->get();

        foreach ($report_detail as $k => $v)
        {
            $v->volume      = $v->volume ?? 0;
            $v->price       = $v->price ?? 0;
            $v->extra_price = $v->extra_price ?? 0;

            $info->balance += ($v->volume * $v->price);

            $info->balance += ($v->volume * $v->extra_price);
        }

        return view('field.managementPop')->with("info", $info)->with("status_color", $status_color);
    }

    /**
     * 현장정보 팝업창 - 상세정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementInfo(Request $request)
    {
        $array_config = Func::getConfigArr();

        $info = [];
        $no = $request->contract_info_no;
        
        if(is_numeric($no))
        {
            $info = DB::table("contract_info")->select("*")->where("no", $no)->where('save_status','Y')->first();
            $info = Func::chungDec(["contract_info"], $info);	// CHUNG DATABASE DECRYPT

            $info->balance = 0;

            $report = DB::table("report")->select("*")
                                        ->where('contract_info_no',$no)
                                        ->where('save_status','Y')
                                        ->get();

            foreach ($report as $k => $v)
            {
                $v->volume      = $v->volume ?? 0;
                $sum_price      = 0;
                $v->extra_price = $v->extra_price ?? 0;

                for($i=1; $i<=10; $i++)
                {
                    unset($cost, $cost_extra);
    
                    if(!empty($v->{'code'.$i}))
                    {
                        $cost = DB::table('cost')->select('*')
                                                ->where('contract_info_no', $no)
                                                ->where('code', $v->{'code'.$i})
                                                ->where('save_status', 'Y')
                                                ->first();
                        $cost = Func::chungDec(["cost"], $cost);	// CHUNG DATABASE DECRYPT
                        
                        if(!empty($cost))
                        {
    
                            $cost_extra = DB::table('cost_extra')->select('*')
                                                                ->where('contract_info_no', $no)
                                                                ->where('cost_no', $cost->no)
                                                                ->where('save_status', 'Y')
                                                                ->get();
                            $cost_extra = Func::chungDec(["cost_extra"], $cost_extra);	// CHUNG DATABASE DECRYPT
    
                            foreach($cost_extra as $v2)
                            {
                                $material = DB::table('material')->select('*')
                                                                ->where('contract_info_no', $no)
                                                                ->where('code', $v2->code)
                                                                ->where('save_status', 'Y')
                                                                ->first();
                                $sum_price += ($material->price ?? 0);
                            }
                        }
                    }
                }
    
                $info->balance += ($v->volume * $sum_price);

                $info->balance += ($v->volume * $v->extra_price);
            }
            
            $report_detail = DB::table("report_detail")->select('*')
                                                        ->where('contract_info_no',$no)
                                                        ->where('save_status','Y')
                                                        ->get();

            foreach ($report_detail as $k => $v)
            {
                $v->volume      = $v->volume ?? 0;
                $v->price       = $v->price ?? 0;
                $v->extra_price = $v->extra_price ?? 0;

                $info->balance += ($v->volume * $v->price);

                $info->balance += ($v->volume * $v->extra_price);
            }
        }

        return view('field.managementInfo')->with('v', $info)->with("configArr", $array_config);
    }

    /**
     * 현장정보 저장
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function managementInfoAction(Request $request)
    {
        $_DATA = $request->all();

        $contract_info_no = $_DATA['contract_info_no'];

        if(!empty($_DATA['contract_date']))
        {
            $_DATA['contract_date'] = preg_replace('/[^0-9]/', '', $_DATA['contract_date']);
        }
        if(!empty($_DATA['contract_end_date']))
        {
            $_DATA['contract_end_date'] = preg_replace('/[^0-9]/', '', $_DATA['contract_end_date']);
        }
        
        if($_DATA['mode'] == 'UPD')
        {
            $_DATA['save_id']   = Auth::id();
            $_DATA['save_time'] = date('YmdHis');

            $result = DB::dataProcess('UPD', 'contract_info', $_DATA, ['no'=>$_DATA['contract_info_no']]);
        }
        else
        {
            $_DATA = array();
            $_DATA['save_status'] = 'N';
            $_DATA['del_id']      = Auth::id();
            $_DATA['del_time']    = date('YmdHis');

            $result = DB::dataProcess('UPD', 'contract_info', $_DATA, ['no'=>$contract_info_no, 'save_status'=>'Y']);

            $result = DB::dataProcess('UPD', 'report', $_DATA, ['no'=>$contract_info_no, 'save_status'=>'Y']);

            $result = DB::dataProcess('UPD', 'report_detail', $_DATA, ['no'=>$contract_info_no, 'save_status'=>'Y']);

            $result = DB::dataProcess('UPD', 'cost', $_DATA, ['no'=>$contract_info_no, 'save_status'=>'Y']);

            $result = DB::dataProcess('UPD', 'cost_extra', $_DATA, ['no'=>$contract_info_no, 'save_status'=>'Y']);

            $result = DB::dataProcess('UPD', 'material', $_DATA, ['no'=>$contract_info_no, 'save_status'=>'Y']);
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
     * 실행내역서 팝업창 - 상세정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementHistory(Request $request)
    {
        $array_config = Func::getConfigArr();

        $contract_info_no = $request->contract_info_no;
        
        $report = DB::table("report")->join("contract_info", "contract_info.no", "=", "report.contract_info_no")
                            ->select("report.*")
                            ->where('contract_info.no',$contract_info_no)
                            ->where('contract_info.save_status','Y')
                            ->where('report.save_status','Y')
                            ->get();

        $report_main = array();
        foreach ($report as $k => $v)
        {
            $v->price = 0;

            for($i=1; $i<=10; $i++)
            {
                unset($cost, $cost_extra);

                if(!empty($v->{'code'.$i}))
                {
                    $cost = DB::table('cost')->select('*')
                                            ->where('contract_info_no', $contract_info_no)
                                            ->where('code', $v->{'code'.$i})
                                            ->where('save_status', 'Y')
                                            ->first();
                    $cost = Func::chungDec(["cost"], $cost);	// CHUNG DATABASE DECRYPT
                    
                    if(!empty($cost))
                    {
                        $price = 0;

                        $cost_extra = DB::table('cost_extra')->select('*')
                                                            ->where('contract_info_no', $contract_info_no)
                                                            ->where('cost_no', $cost->no)
                                                            ->where('save_status', 'Y')
                                                            ->get();
                        $cost_extra = Func::chungDec(["cost_extra"], $cost_extra);	// CHUNG DATABASE DECRYPT

                        foreach($cost_extra as $v2)
                        {
                            $material = DB::table('material')->select('*')
                                                            ->where('contract_info_no', $contract_info_no)
                                                            ->where('code', $v2->code)
                                                            ->where('save_status', 'Y')
                                                            ->first();
                            $price += ($material->price ?? 0);
                        }

                        $v->price += $price;
                    }
                }
            }

            $v->volume = rtrim($v->volume, 0);
            $v->volume = rtrim($v->volume, '.');

            $report_main[] = $v;
        }
        
        $report_detail = DB::table("report_detail")->join("contract_info", "contract_info.no", "=", "report_detail.contract_info_no")
                            ->select("report_detail.*")
                            ->where('contract_info.no',$contract_info_no)
                            ->where('contract_info.save_status','Y')
                            ->where('report_detail.save_status','Y')
                            ->get();

        $report_extra = array();
        foreach ($report_detail as $key => $value)
        {
            $value->volume = rtrim($value->volume, 0);
            $value->volume = rtrim($value->volume, '.');

            $report_extra[] = $value;
        }

        return view('field.managementHistory')->with('contract_info_no', $contract_info_no)
                                                ->with('v', $report_extra)
                                                ->with('v2', $report_main)
                                                ->with("configArr", $array_config);
    }

    /**
     * 실행내역서 저장
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function managementHistoryAction(Request $request)
    {
        $_DATA = $request->all();
        
        // 일단 전체삭제 전제저장
        $_DEL = array();
        $_DEL['save_status'] = 'N';
        $_DEL['del_id']      = Auth::id();
        $_DEL['del_time']    = date('YmdHis');
        $result = DB::dataProcess('UPD', 'report', $_DEL, ['contract_info_no'=>$_DATA['contract_info_no'], 'save_status'=>'Y']);
        $result = DB::dataProcess('UPD', 'report_detail', $_DEL, ['contract_info_no'=>$_DATA['contract_info_no'], 'save_status'=>'Y']);

        if(isset($_DATA['detail_code']))
        {
            foreach ($_DATA['detail_code'] as $k => $v)
            {
                $_INS = array();
                $_INS['save_status']  = 'Y';
                $_INS['save_id']      = Auth::id();
                $_INS['save_time']    = date('YmdHis');

                $_INS['contract_info_no'] = $_DATA['contract_info_no'];

                $_INS['code1']  = $_DATA['detail_code1'][$k] ?? '';
                $_INS['code2']  = $_DATA['detail_code2'][$k] ?? '';
                $_INS['code3']  = $_DATA['detail_code3'][$k] ?? '';
                $_INS['code4']  = $_DATA['detail_code4'][$k] ?? '';
                $_INS['code5']  = $_DATA['detail_code5'][$k] ?? '';
                $_INS['code6']  = $_DATA['detail_code6'][$k] ?? '';
                $_INS['code7']  = $_DATA['detail_code7'][$k] ?? '';
                $_INS['code8']  = $_DATA['detail_code8'][$k] ?? '';
                $_INS['code9']  = $_DATA['detail_code9'][$k] ?? '';
                $_INS['code10'] = $_DATA['detail_code10'][$k] ?? '';

                $_INS['name']     = $_DATA['detail_name'][$k] ?? '';
                $_INS['standard'] = $_DATA['detail_standard'][$k] ?? '';
                $_INS['type']     = $_DATA['detail_type'][$k] ?? '';
                $_INS['volume']   = sprintf('%0.3f', round(str_replace(',', '', $_DATA['detail_volume'][$k] ?? 0),3));
                $_INS['price']    = round(str_replace(',', '', $_DATA['detail_price'][$k] ?? 0));

                $_INS['extra_price'] = round(str_replace(',', '', $_DATA['detail_extra_price'][$k] ?? 0));
                $_INS['etc']         = $_DATA['detail_etc'][$k] ?? '';

                $result = DB::dataProcess('INS', 'report_detail', $_INS);
            }
        }

        if(isset($_DATA['code']))
        {
            foreach ($_DATA['code'] as $k => $v)
            {
                $_INS = array();
                $_INS['save_status']  = 'Y';
                $_INS['save_id']      = Auth::id();
                $_INS['save_time']    = date('YmdHis');

                $_INS['contract_info_no'] = $_DATA['contract_info_no'];

                $_INS['code1']  = $_DATA['code1'][$k] ?? '';
                $_INS['code2']  = $_DATA['code2'][$k] ?? '';
                $_INS['code3']  = $_DATA['code3'][$k] ?? '';
                $_INS['code4']  = $_DATA['code4'][$k] ?? '';
                $_INS['code5']  = $_DATA['code5'][$k] ?? '';
                $_INS['code6']  = $_DATA['code6'][$k] ?? '';
                $_INS['code7']  = $_DATA['code7'][$k] ?? '';
                $_INS['code8']  = $_DATA['code8'][$k] ?? '';
                $_INS['code9']  = $_DATA['code9'][$k] ?? '';
                $_INS['code10'] = $_DATA['code10'][$k] ?? '';

                $_INS['name']     = $_DATA['name'][$k] ?? '';
                $_INS['standard'] = $_DATA['standard'][$k] ?? '';
                $_INS['type']     = $_DATA['type'][$k] ?? '';
                $_INS['volume']   = sprintf('%0.3f', round(str_replace(',', '', $_DATA['volume'][$k] ?? 0),3));

                $_INS['extra_price'] = round(str_replace(',', '', $_DATA['extra_price'][$k] ?? 0));
                $_INS['etc']         = $_DATA['etc'][$k] ?? '';

                $result = DB::dataProcess('INS', 'report', $_INS);
            }
        }

        
        if( $result == "Y" )
        {
            $array_result['save_time'] = Func::dateFormat(date('YmdHis'));

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
     * 실행내역서 코드리스트
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function managementHistoryList(Request $request)
    {
        $_DATA = $request->all();

        $result = array();
        $result['sum_price'] = 0;

        for($i=1; $i<=10; $i++)
        {
            unset($cost, $cost_extra);

            if(!empty($_DATA['code'.$i]))
            {
                $cost = DB::table('cost')->select('*')
                                        ->where('contract_info_no', $_DATA['contract_info_no'])
                                        ->where('code', $_DATA['code'.$i])
                                        ->where('save_status', 'Y')
                                        ->first();
                $cost = Func::chungDec(["cost"], $cost);	// CHUNG DATABASE DECRYPT
                
                if(!empty($cost))
                {
                    $result['code'.$i]      = $cost->code ?? '';
                    $result['name'.$i]      = $cost->name ?? '';
                    $result['standard1'.$i] = $cost->standard1 ?? '';
                    $result['standard2'.$i] = $cost->standard2 ?? '';
                    $result['type'.$i]      = $cost->type ?? '';
                    $result['price'.$i]     = 0;
                    $result['etc'.$i]       = $cost->etc ?? '';

                    $cost_extra = DB::table('cost_extra')->select('*')
                                                        ->where('contract_info_no', $_DATA['contract_info_no'])
                                                        ->where('cost_no', $cost->no)
                                                        ->where('save_status', 'Y')
                                                        ->get();
                    $cost_extra = Func::chungDec(["cost_extra"], $cost_extra);	// CHUNG DATABASE DECRYPT

                    foreach($cost_extra as $v2)
                    {
                        $material = DB::table('material')->select('*')
                                                        ->where('contract_info_no', $_DATA['contract_info_no'])
                                                        ->where('code', $v2->code)
                                                        ->where('save_status', 'Y')
                                                        ->first();
                        $result['price'.$i] += ($material->price ?? 0);
                    }

                    $result['sum_price'] += $result['price'.$i];
                }
		    }
        }

        return $result;
    }

	/**
	 * 일위대가 검색 모달
	 *
	 * @param  Request $request
	 * @return view
	 */
	public function managementHistorySearch(Request $request)
	{
		$query = DB::table('cost')
					->select('*')
					->where('contract_info_no', $request->contract_info_no)
					->where('save_status', 'Y');

		if(isset($request->keyword))
		{
			$keyword = $request->keyword;
			$query = $query->where(function($q) use ($keyword) {
				$q->where('cost.name', 'like', '%'.$keyword.'%')
				->orWhere('cost.code', 'like', '%'.$keyword.'%')
				->orWhere('cost.standard1', 'like', '%'.$keyword.'%')
				->orWhere('cost.standard2', 'like', '%'.$keyword.'%')
				->orWhere('cost.type', 'like', '%'.$keyword.'%')
				->orWhere('cost.etc', 'like', '%'.$keyword.'%');
			});
		}

		$query = $query->orderBy('no', 'desc');

		// 총건수
		$result['cnt']  = $query->count();
		
		// 한페이지.
		$result['cost'] = $query->limit(10)->offset((($request->page ?? 1)-1)*10)->get();
		$result['cost'] = Func::chungDec(["cost"], $result['cost']);	// CHUNG DATABASE DECRYPT

        return $result;
	}

    /**
     * 실행내역서 일위대가 코드
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function managementCostCode(Request $request)
    {
        $_DATA = $request->all();

        $result = array();

        if(!empty($_DATA['code']))
        {
            $cost = DB::table('cost')->select('no')
                                    ->where('contract_info_no', $_DATA['contract_info_no'])
                                    ->where('code', $_DATA['code'])
                                    ->where('save_status', 'Y')
                                    ->first();
            $cost = Func::chungDec(["cost"], $cost);	// CHUNG DATABASE DECRYPT

            $result['price'] = 0;

            if(!empty($cost))
            {
                $cost_extra = DB::table('cost_extra')->select('*')
                                                    ->where('contract_info_no', $_DATA['contract_info_no'])
                                                    ->where('cost_no', $cost->no)
                                                    ->where('save_status', 'Y')
                                                    ->get();
                $cost_extra = Func::chungDec(["cost_extra"], $cost_extra);	// CHUNG DATABASE DECRYPT
    
                foreach($cost_extra as $v2)
                {
                    $material = DB::table('material')->select('*')
                                                    ->where('contract_info_no', $_DATA['contract_info_no'])
                                                    ->where('code', $v2->code)
                                                    ->where('save_status', 'Y')
                                                    ->first();
                    $result['price'] += ($material->price ?? 0);
                }
            }
        }

        return $result;
    }

    /**
     * 실행내역서 엑셀업로드 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementHistoryExcelForm(Request $request)
    {
        return view('field.managementHistoryExcelForm')->with("contract_info_no", $request->contract_info_no);
    }

    /**
     * 실행내역서 엑셀업로드 샘플
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementHistoryExcelSample(Request $request)
    {
        if(Storage::disk('management')->exists('historyExcelSample.xlsx'))
        {
            return Storage::disk('management')->download('historyExcelSample.xlsx', '실행내역서엑셀업로드예시파일.xlsx');
        }
        else
        {
            log::debug("샘플파일 없음");
        }
    }

    /**
     * 실행내역서 엑셀업로드 액션
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementHistoryExcelAction(Request $request)
    {
        if(empty($request->contract_info_no))
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "현장번호가 존재하지 않습니다.";

            return $r;
        }

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);

        if( $request->file('excel_data') )
        {
            // 엑셀 저장
            $file_path = $request->file('excel_data')->store("upload/".date("YmdHis"), 'management');
            
            // 경로세팅 
            if(Storage::disk('management')->exists($file_path))
            {
                $colHeader  = array(
                    "내역내품명",
                    "규격",
                    "단위",
                    "수량"
                );
                $colNm = array(
                    "name"      => "0",	      // 내역내품명
                    "standard"	=> "1",	      // 규격
                    "type"      => "2",       // 단위
                    "volume"    => "3"        // 수량
                );
                                    
                $file = Storage::path('/management/'.$file_path);
                
                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 파일경로
                    log::debug($file_path);

                    $r['rs_code'] = "N";
                    $r['rs_msg']  = "엑셀 유효성 검사를 실패하였습니다.";

                    return $r;
                }
                else
                {
                    $this->removeEmptyRow($excelData);

                    foreach($excelData as $_DATA) 
                    {
                        unset($_INS);

                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_INS[$key] = $val;
                        }

                        // 데이터 추출 및 정리
                        foreach($_INS as $key => $val)
                        {
                            // 값이 없으면 unset
                            if($val == "")
                            {
                                unset($_INS[$key]);
                                continue;
                            }

                            // 값이 null 이면 빈값으로 업데이트
                            if($val == "null" || $val == "NULL")
                            {
                                $_INS[$key] = '';
                                continue;
                            }
                        }

                        $_INS['contract_info_no'] = $request->contract_info_no;
                        $_INS['file_path']        = $file_path;

                        $_INS['save_status']      = 'Y';
                        $_INS['save_id']          = Auth::id();
                        $_INS['save_time']        = date('YmdHis');

                        $rslt = DB::dataProcess('INS', 'report', $_INS);
                    }
                }

                $r['rs_code'] = "Y";
                $r['rs_msg']  = "엑셀 업로드를 성공하였습니다.";

                return $r;
            }
            else 
            {
                log::debug($file_path ?? '파일경로 없음');

                $r['rs_code'] = "N";
                $r['rs_msg']  = "엑셀 업로드를 실패했습니다.";

                return $r;
            }
        }
        else
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "엑셀을 등록해주세요.";

            return $r;
        }
    }

    /**
     * 실행내역서 엑셀다운
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function managementHistoryExcel(Request $request)
    {
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "실행내역서_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no))
        {
            $file_name = $request->file_name;
            $excel_no  = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
            $origin_filename = $excel_no.'_'.$request->work_id.'_'.date("YmdHis").'.xlsx'; 
        }
        else
        {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, '', $record_count,null,null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }

            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $no = $request->contract_info_no;

        // 엑셀 헤더
        $excel_header[] = array('코드', '품명', '규격', '단위', '실행내역서', '', '', '', '', '', '', '비고');
        $excel_header[] = array('', '', '', '', '수량', '재료비', '','노무비', '', '합계', '', '');
        $excel_header[] = array('', '', '', '', '', '단가', '금액','단가', '금액', '단가', '', '');

        // 내역외 소계 금액 초기화
        $detail_sum_balance = $detail_sum_extra_balance = $detail_sum_sum_balance = 0;

        // 내역내 소계 금액 초기화
        $sum_balance = $sum_extra_balance = $sum_sum_balance = 0;

        // 코드들 초기화
        $detail_code = $main_code = '';

        // 엑셀데이터 초기화
        $excel_data = $array_data = [];

        $excel_style_merge = array('A1:A3', 'B1:B3', 'C1:C3', 'D1:D3', 'E1:K1', 'E2:E3', 'L1:L3', 'F2:G2', 'H2:I2', 'J2:K2', 'J3:K3');
        
        if(!empty($param['detail_code1']))
        {
            foreach ($param['detail_code1'] as $key => $val)
            {
                $detail_code = '';

                for ($i=1; $i<=10; $i++) { 
                    $detail_code .= (isset($param['detail_code'.$i][$key]) ? ($param['detail_code'.$i][$key].', ') : '');
                }

                $detail_sum_balance += (str_replace(",","",$param['detail_balance'][$key]) ?? 0);
                $detail_sum_extra_balance += (str_replace(",","",$param['detail_extra_balance'][$key]) ?? 0);
                $detail_sum_sum_balance += (str_replace(",","",$param['detail_sum_balance'][$key]) ?? 0);

                $detail_code = rtrim($detail_code, ', ');

                $array_data = [
                    $detail_code,                                   // 코드
                    $param['detail_name'][$key] ?? '',              // 품명
                    $param['detail_standard'][$key] ?? '',          // 규격
                    $param['detail_type'][$key] ?? '',              // 단위
                    $param['detail_volume'][$key] ?? '',            // 수량
                    $param['detail_price'][$key] ?? '',             // 재료비 - 단가
                    $param['detail_balance'][$key] ?? '',           // 재료비 - 금액
                    $param['detail_extra_price'][$key] ?? '',       // 노무비 - 단가
                    $param['detail_extra_balance'][$key] ?? '',     // 노무비 - 금액
                    $param['detail_sum_price'][$key] ?? '',         // 합계 - 단가
                    $param['detail_sum_balance'][$key] ?? '',       // 합계 - 금액
                    $param['detail_etc'][$key] ?? '',               // 비고
                ];

                $excel_data[] = $array_data;

                $record_count++;
            }
        }

        $excel_header2 = array('[소계]', '', '', '', '', '', number_format($detail_sum_balance), '', number_format($detail_sum_extra_balance), '', number_format($detail_sum_sum_balance), '');

        $excel_data[] = $excel_header2;

        $record_count++;

        $excel_style_merge = array_merge($excel_style_merge, array("A".($record_count+3).":E".($record_count+3)));

        if(!empty($param['code1']))
        {
            foreach ($param['code1'] as $key => $val)
            {
                $main_code = '';

                for ($i=1; $i<=10; $i++) { 
                    $main_code .= (isset($param['code'.$i][$key]) ? ($param['code'.$i][$key].', ') : '');
                }

                $main_code = rtrim($main_code, ', ');

                $sum_balance += (str_replace(",","",$param['balance'][$key]) ?? 0);
                $sum_extra_balance += (str_replace(",","",$param['extra_balance'][$key]) ?? 0);
                $sum_sum_balance += (str_replace(",","",$param['sum_balance'][$key]) ?? 0);

                $array_data = [
                    $main_code,                              // 코드
                    $param['name'][$key] ?? '',              // 품명
                    $param['standard'][$key] ?? '',          // 규격
                    $param['type'][$key] ?? '',              // 단위
                    $param['volume'][$key] ?? '',            // 수량
                    $param['price'][$key] ?? '',             // 재료비 - 단가
                    $param['balance'][$key] ?? '',           // 재료비 - 금액
                    $param['extra_price'][$key] ?? '',       // 노무비 - 단가
                    $param['extra_balance'][$key] ?? '',     // 노무비 - 금액
                    $param['sum_price'][$key] ?? '',         // 합계 - 단가
                    $param['sum_balance'][$key] ?? '',       // 합계 - 금액
                    $param['etc'][$key] ?? '',               // 비고
                ];

                $excel_data[] = $array_data;

                $record_count++;
            }
        }

        // 내역내 소계
        $excel_header3 = array('[소계]', '', '', '', '', '', number_format($sum_balance), '', number_format($sum_extra_balance), '', number_format($sum_sum_balance), '');

        $excel_data[] = $excel_header3;

        $record_count++;

        $excel_style_merge = array_merge($excel_style_merge, array("A".($record_count+3).":E".($record_count+3)));

        $excel_style = ['merge' => $excel_style_merge];

        // 합계
        $excel_header4 = array('[합계]', '', '', '', '', '', number_format($detail_sum_balance + $sum_balance), '', number_format($detail_sum_extra_balance + $sum_extra_balance), '', number_format($detail_sum_sum_balance + $sum_sum_balance), '');

        $excel_data[] = $excel_header4;

        $record_count++;

        $excel_style_merge = array_merge($excel_style_merge, array("A".($record_count+3).":E".($record_count+3)));

        $excel_style_center = array("A1:L".($record_count+3));

        $excel_style = ['merge' => $excel_style_merge, 'center' => $excel_style_center];

        $_EXCEL = Array();
        $_EXCEL[] = Array(
            "header"    =>  $excel_header,
            "excel_data"=>  $excel_data,
            "title"     =>  $file_name,
            "style"     =>  $excel_style,
        );
        
        // 엑셀 익스포트
        ExcelFunc::downExcelSheet($_EXCEL, $origin_filename);
    
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

    /**
     * 실행내역서 내역내 삭제
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementHistoryExcelRemove(Request $request)
    {
        $r = array();

        if(empty($request->contract_info_no))
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "현장번호가 존재하지 않습니다.";
        }
        else
        {
            $_DATA = array();
            $_DATA['save_status'] = 'N';
            $_DATA['del_id']      = Auth::id();
            $_DATA['del_time']    = date('YmdHis');
            
            $result = DB::dataProcess('UPD', 'report', $_DATA, ['contract_info_no'=>$request->contract_info_no, 'save_status'=>'Y']);

            if($result == 'Y')
            {
                $r['rs_code'] = "Y";
                $r['rs_msg']  = "삭제를 성공하였습니다.";
            }
            else
            {
                $r['rs_code'] = "N";
                $r['rs_msg']  = "삭제를 실패했습니다.";
            }
        }

        return $r;
    }

    /**
     * 현장정보 팝업창 - 일위대가
     * 일위대가 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setCostDataList(Request $request)
    {
        $list = new DataList(Array("listName"=>"managementCost","listAction"=>'/'.$request->path()));

        $list->setSearchDetail(Array(
            'cost.code'       => '코드',
            'cost.name'       => '품명',
        ));

        return $list;
    }

    /**
     * 현장정보 팝업창 - 일위대가
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCost(Request $request)
    {
        $list = $this->setCostDataList($request);
        
        $contract_info_no = $request->contract_info_no;

        $list->setButtonArray("일괄삭제", "managementCostAllClear('".$contract_info_no."')", "btn-danger");

        $list->setButtonArray("엑셀업로드", "managementCostExcelForm('".$contract_info_no."')", "btn-info");

        $list->setButtonArray("엑셀다운","excelDownModal('/field/managementcostexcel','form_managementCost')","btn-success");
        
        $list->setPlusButton("managementCostForm('".$contract_info_no."');");
        
        $list->setViewNum(false);
        
        $list->setHidden(Array('contract_info_no' => $contract_info_no));

        $list->setlistTitleCommon(Array
        (
            'code'                  => Array('코드', 0, '', 'center', '', 'code'),
            'name'                  => Array('품명', 1, '', 'center', '', 'name'),
            'standard1'             => Array('규격(1)', 1, '', 'center', '', 'standard1'),
            'standard2'             => Array('규격(2)', 1, '', 'center', '', 'standard2'),
            'type'                  => Array('단위', 1, '', 'center', '', 'type'),
            'count'                 => Array('수량', 1, '', 'center', '', ''),
            'price'                 => Array('단가', 1, '', 'center', '', ''),
            'balance'               => Array('금액', 0, '', 'center', '', ''),
            'etc'                   => Array('기타', 1, '', 'center', '', 'etc'),
        ));

        return view('field.managementCost')->with('result', $list->getList());
    }

    /**
     * 현장정보 팝업창 - 자재단가표 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function managementCostList(Request $request)
    {
        $param = $request->all();

        $LOAN_LIST = DB::table("cost")->join("contract_info", "contract_info.no", "=", "cost.contract_info_no")
                            ->select("cost.*")
                            ->where('contract_info.no',$param['contract_info_no'])
                            ->where('contract_info.save_status','Y')
                            ->where('cost.save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='cost.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('cost.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='cost.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('cost.name', 'like','%'.$param['searchString'].'%');
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
        $LOAN_LIST = Func::chungDec(["cost"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($LOAN_LIST as $v)
        {
            $link        = 'javascript:window.open("/field/managementcostpop?contract_info_no='.$v->contract_info_no.'&cost_no='.$v->no.'","msgpop","width=2000, height=1000, scrollbars=yes")';
            $v->code     = "<a href='".$link.";'>".$v->code."</a>";

            $material    = DB::table("cost_extra")->join("material", "material.code", "=", "cost_extra.code")
                                                ->select(DB::raw("coalesce(sum(material.price),0) as sum_price"))
                                                ->where('cost_extra.cost_no',$v->no)
                                                ->where('cost_extra.save_status','Y')
                                                ->where('material.save_status','Y')
                                                ->first();
        
            $report = DB::table("cost")->select(
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code1) as sum_volume1"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code2) as sum_volume2"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code3) as sum_volume3"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code4) as sum_volume4"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code5) as sum_volume5"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code6) as sum_volume6"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code7) as sum_volume7"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code8) as sum_volume8"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code9) as sum_volume9"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code10) as sum_volume10"),
                                        )
                                        ->where('cost.no',$v->no)
                                        ->where('cost.save_status','Y')
                                        ->first();

            $v->count    = ($report->sum_volume1 ?? 0)+($report->sum_volume2 ?? 0)+($report->sum_volume3 ?? 0)+($report->sum_volume4 ?? 0)+($report->sum_volume5 ?? 0)+($report->sum_volume6 ?? 0)+($report->sum_volume7 ?? 0)+($report->sum_volume8 ?? 0)+($report->sum_volume9 ?? 0)+($report->sum_volume10 ?? 0);
            $v->price    = $material->sum_price ?? 0;

            $v->balance  = $v->count*$v->price;

            $v->price    = number_format($v->price ?? 0);
            $v->balance  = number_format($v->balance);

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
     * 일위대가 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostExcel(Request $request)
    {
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        
        $list           = $this->setCostDataList($request);

        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;

        $LOAN_LIST = DB::table("cost")->join("contract_info", "contract_info.no", "=", "cost.contract_info_no")
                                        ->select("cost.*")
                                        ->where('contract_info.no',$param['contract_info_no'])
                                        ->where('contract_info.save_status','Y')
                                        ->where('cost.save_status','Y');
        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('cost.no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='cost.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('cost.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='cost.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('cost.name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $LOAN_LIST = $list->getListQuery('cost', 'main', $LOAN_LIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.               
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN_LIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "일위대가_".date("YmdHis").'_'.Auth::id().'.xlsx';
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

        $LOAN_LIST = $LOAN_LIST->get();
        $LOAN_LIST = Func::chungDec(["contract_info", "cost"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT
        
        // 엑셀 헤더
        $excel_header   = array('코드','품명', '규격(1)', '규격(2)','단위', '수량', '단가', '금액', '자재총소요량', '비고');
        $excel_data     = [];

        $array_config   = Func::getConfigArr();
        $arrManager     = Func::getUserList();

        foreach ($LOAN_LIST as $v)
        {
            $material = DB::table("cost_extra")->join("material", "material.code", "=", "cost_extra.code")
                                                ->select(DB::raw("coalesce(sum(material.price),0) as sum_price"))
                                                ->where('cost_extra.cost_no',$v->no)
                                                ->where('cost_extra.save_status','Y')
                                                ->where('material.save_status','Y')
                                                ->first();

            $report = DB::table("cost")->select(
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code1) as sum_volume1"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code2) as sum_volume2"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code3) as sum_volume3"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code4) as sum_volume4"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code5) as sum_volume5"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code6) as sum_volume6"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code7) as sum_volume7"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code8) as sum_volume8"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code9) as sum_volume9"),
                DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code10) as sum_volume10"),
                                        )
                                        ->where('cost.no',$v->no)
                                        ->where('cost.save_status','Y')
                                        ->first();

            $v->volume = ($report->sum_volume1 ?? 0)+($report->sum_volume2 ?? 0)+($report->sum_volume3 ?? 0)+($report->sum_volume4 ?? 0)+($report->sum_volume5 ?? 0)+($report->sum_volume6 ?? 0)+($report->sum_volume7 ?? 0)+($report->sum_volume8 ?? 0)+($report->sum_volume9 ?? 0)+($report->sum_volume10 ?? 0);

            $array_data1 = [
                $v->code,                                                       //코드
                $v->name,                                                       //품명
                $v->standard1,                                                  //규격(1)
                $v->standard2,                                                  //규격(2)
                $v->type,                                                       //단위
                number_format($v->volume ?? 0),                                 //수량 -> =SUMIF(내역서!A:A,@A:A,내역서!AH:AH)로 수정해야함
                number_format($material->sum_price ?? 0),                       //단가
                number_format(($v->volume ?? 0)*($material->sum_price ?? 0)),   //금액 -> 현재 수량이 1로되어있어서 1만 나옴
                "",                                                             //자제총소요량
                $v->etc,                                                        //기타
            ];

            $excel_data[] = $array_data1;
            
            $record_count++;

            $cost_extra = DB::table("cost_extra ce")->select(
                                                        "ce.seq",
                                                        "ce.code",
                                                        "ce.volume",
                                                        DB::raw("( select name from material where save_status='Y' and code=ce.code order by no desc fetch first 1 rows only ) as name"),
                                                        DB::raw("( select standard1 from material where save_status='Y' and code=ce.code order by no desc fetch first 1 rows only ) as standard1"),
                                                        DB::raw("( select standard2 from material where save_status='Y' and code=ce.code order by no desc fetch first 1 rows only ) as standard2"),
                                                        DB::raw("( select type from material where save_status='Y' and code=ce.code order by no desc fetch first 1 rows only ) as type"),
                                                        DB::raw("( select price from material where save_status='Y' and code=ce.code order by no desc fetch first 1 rows only ) as price"),
                                                        "ce.etc"
                                                    )
                                                    ->where('ce.cost_no',$v->no)
                                                    ->where('ce.save_status','Y')
                                                    ->get();
            
            foreach ($cost_extra as $key => $value)
            {
                $array_data2 = [
                    $value->code,                                                   //코드
                    $value->name,                                                   //품명
                    $value->standard1,                                              //규격(1)
                    $value->standard2,                                              //규격(2)
                    $value->type,                                                   //단위
                    number_format($value->volume ?? 0),                             //수량
                    number_format($value->price ?? 0),                              //단가
                    number_format(($value->volume ?? 0)*($value->price ?? 0)),      //금액
                    number_format($v->volume*($value->volume ?? 0)),                //자체총소요량(임시)--> =SUMIF(내역서!A:A,@A:A,내역서!AH:AH) * cost_extra의 수량
                    $value->etc,                                                    //기타
                ];

                $excel_data[] = $array_data2;
                
                $record_count++;
            }

            $array_data3 = [];

            $excel_data[] = $array_data3;
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
     * 일위대가 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostForm(Request $request)
    {
        $contract_info_no = $request->contract_info_no;

        return view('field.managementCostForm')->with("contract_info_no", $contract_info_no);
    }

    /*
     *  일위대가 폼 등록 액션

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function managementCostFormAction(Request $request)
    {
        $_DATA = $request->all();

        $_DATA['save_status'] = 'Y';
        $_DATA['save_id']     = Auth::id();
        $_DATA['save_time']   = date('YmdHis');

        $result = DB::dataProcess('INS', 'cost', $_DATA);

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
	 * 자재단가표 검색 모달
	 *
	 * @param  Request $request
	 * @return view
	 */
	public function managementMaterialSearch(Request $request)
	{
		$query = DB::table('contract_info')
                    ->join("material", "material.contract_info_no", "=", "contract_info.no")
					->select('material.*')
					->where('contract_info.no', $request->contract_info_no)
					->where('contract_info.save_status', 'Y')
					->where('material.save_status', 'Y');

		if(isset($request->keyword))
		{
			$keyword = $request->keyword;
			$query = $query->where(function($q) use ($keyword) {
				$q->where('material.name', 'like', '%'.$keyword.'%')
				->orWhere('material.category', 'like', '%'.$keyword.'%')
				->orWhere('material.code', 'like', '%'.$keyword.'%')
				->orWhere('material.standard1', 'like', '%'.$keyword.'%')
				->orWhere('material.standard2', 'like', '%'.$keyword.'%')
				->orWhere('material.type', 'like', '%'.$keyword.'%')
				->orWhere('material.etc', 'like', '%'.$keyword.'%');
			});
		}

		$query = $query->orderBy('no', 'desc');

		// 총건수
		$result['cnt']      = $query->count();
		
		// 한페이지.
		$result['material'] = $query->limit(10)->offset((($request->page ?? 1)-1)*10)->get();
		$result['material'] = Func::chungDec(["contract_info","material"], $result['material']);	// CHUNG DATABASE DECRYPT

        return $result;
	}

    /**
     * 일위대가 팝업
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostPop(Request $request)
    {
        $v     = [];
        $extra = [];

        $info = DB::table("contract_info")->select("*")
                                            ->where('no',$request->contract_info_no ?? 0)
                                            ->where('save_status','Y')
                                            ->first();
        if(!empty($info))
        {
            // 메인
            $v = DB::table("cost")->select("*")
                                    ->where('contract_info_no',$request->contract_info_no)
                                    ->where('no',$request->cost_no)
                                    ->where('save_status','Y')
                                    ->first();
            $v = Func::chungDec(["cost"], $v);	// CHUNG DATABASE DECRYPT

            if(!empty($v))
            {
                $report = DB::table("cost")->select(
                    DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code1) as sum_volume1"),
                    DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code2) as sum_volume2"),
                    DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code3) as sum_volume3"),
                    DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code4) as sum_volume4"),
                    DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code5) as sum_volume5"),
                    DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code6) as sum_volume6"),
                    DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code7) as sum_volume7"),
                    DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code8) as sum_volume8"),
                    DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code9) as sum_volume9"),
                    DB::raw("(select coalesce(sum(report.volume),0) from report where save_status = 'Y' and cost.code=report.code10) as sum_volume10"),
                                            )
                                            ->where('cost.no',$v->no)
                                            ->where('cost.save_status','Y')
                                            ->first();
        
                $v->volume = ($report->sum_volume1 ?? 0)+($report->sum_volume2 ?? 0)+($report->sum_volume3 ?? 0)+($report->sum_volume4 ?? 0)+($report->sum_volume5 ?? 0)+($report->sum_volume6 ?? 0)+($report->sum_volume7 ?? 0)+($report->sum_volume8 ?? 0)+($report->sum_volume9 ?? 0)+($report->sum_volume10 ?? 0);
        
                // 서브
                $cost_extra = DB::table("cost_extra")->select(
                                            "cost_extra.seq",
                                            "cost_extra.code",
                                            "cost_extra.volume",
                                            DB::raw("( select name from material where save_status='Y' and code=cost_extra.code order by no desc fetch first 1 rows only ) as name"),
                                            DB::raw("( select standard1 from material where save_status='Y' and code=cost_extra.code order by no desc fetch first 1 rows only ) as standard1"),
                                            DB::raw("( select standard2 from material where save_status='Y' and code=cost_extra.code order by no desc fetch first 1 rows only ) as standard2"),
                                            DB::raw("( select type from material where save_status='Y' and code=cost_extra.code order by no desc fetch first 1 rows only ) as type"),
                                            DB::raw("( select price from material where save_status='Y' and code=cost_extra.code order by no desc fetch first 1 rows only ) as price"),
                                            "cost_extra.etc"
                                        )
                                        ->where('cost_extra.cost_no',$request->cost_no)
                                        ->where('cost_extra.save_status','Y')
                                        ->orderBy('cost_extra.seq', 'asc')
                                        ->get();
                $cost_extra = Func::chungDec(["cost_extra","material"], $cost_extra);	// CHUNG DATABASE DECRYPT

                foreach ($cost_extra as $key => $value)
                {
                    $value->volume = rtrim($value->volume, 0);
                    $value->volume = rtrim($value->volume, '.');

                    $extra[] = $value;
                }
            }
        }

        return view('field.managementCostPop')->with("v", $v)->with("cost_extra", $extra);
    }

    /**
     * 일위대가 팝업 저장
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostPopAction(Request $request)
    {
        $_DATA = $request->all();

        if($_DATA['mode'] == 'DEL')
        {
            $_DATA['save_status'] = 'N';
            $_DATA['del_id']      = Auth::id();
            $_DATA['del_time']    = date('YmdHis');

            // 메인삭제
            $result = DB::dataProcess('UPD', 'cost', $_DATA, ['no'=>$_DATA['cost_no'], 'save_status'=>'Y']);

            // 서브삭제
            $result = DB::dataProcess('UPD', 'cost_extra', $_DATA, ['cost_no'=>$_DATA['cost_no'], 'save_status'=>'Y']);
        }
        else
        {
            $_COST = array();
            $_COST['code']      = $_DATA['code'] ?? '';
            $_COST['name']      = $_DATA['name'] ?? '';
            $_COST['standard1'] = $_DATA['standard1'] ?? '';
            $_COST['standard2'] = $_DATA['standard2'] ?? '';
            $_COST['type']      = $_DATA['type'] ?? '';
            $_COST['etc']       = $_DATA['etc'] ?? '';
            $_COST['save_id']   = Auth::id();
            $_COST['save_time'] = date('YmdHis');

            // 메인저장
            $result = DB::dataProcess('UPD', 'cost', $_COST, ['no'=>$_DATA['cost_no']]);

            $_DEL = array();
            $_DEL['save_status'] = 'N';
            $_DEL['del_id']      = Auth::id();
            $_DEL['del_time']    = date('YmdHis');

            // 서브삭제
            $result = DB::dataProcess('UPD', 'cost_extra', $_DEL, ['cost_no'=>$_DATA['cost_no'], 'save_status'=>'Y']);

            if(isset($_DATA['extra_code']))
            {
                $seq = 0;
                for($i=0;$i<sizeof($_DATA['extra_code']);$i++)
                {
                    $seq++;
                    
                    // 변수정리
                    $_COST_EXTRA = array();
                    $_COST_EXTRA['cost_no']          = $_DATA['cost_no'];
                    $_COST_EXTRA['contract_info_no'] = $_DATA['contract_info_no'];
                    $_COST_EXTRA['seq']              = $seq;
                    $_COST_EXTRA['code']             = $_DATA['extra_code'][$i];
                    $_COST_EXTRA['volume']           = sprintf('%0.3f', round(str_replace(',', '', $_DATA['extra_volume'][$i] ?? 0),3));
                    $_COST_EXTRA['etc']              = $_DATA['extra_etc'][$i];
                    $_COST_EXTRA['save_time']        = date("YmdHis");
                    $_COST_EXTRA['save_id']          = Auth::id();
                    $_COST_EXTRA['save_status']      = 'Y';

                    $result = DB::dataProcess('INS', 'cost_extra', $_COST_EXTRA);
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

    /*
     *  일위대가 일괄삭제

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function managementCostAllClear(Request $request)
    {
        $_DATA = $request->all();
        
        $_DATA['save_status'] = 'N';
        $_DATA['del_id']      = Auth::id();
        $_DATA['del_time']    = date('YmdHis');

        // 메인
        $result = DB::dataProcess('UPD', 'cost', $_DATA, ['contract_info_no'=>$_DATA['contract_info_no'], 'save_status'=>'Y']);

        // 서브
        $result = DB::dataProcess('UPD', 'cost_extra', $_DATA, ['contract_info_no'=>$_DATA['contract_info_no'], 'save_status'=>'Y']);

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
     * 일위대가 엑셀업로드 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostExcelForm(Request $request)
    {
        return view('field.managementCostExcelForm')->with("contract_info_no", $request->contract_info_no);
    }

    /**
     * 일위대가 엑셀업로드 샘플
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostExcelSample(Request $request)
    {
        if(Storage::disk('management')->exists('costExcelSample.xlsx'))
        {
            return Storage::disk('management')->download('costExcelSample.xlsx', '일위대가업로드예시파일.xlsx');
        }
        else
        {
            log::debug("샘플파일 없음");
        }
    }

    /**
     * 일위대가 엑셀업로드 액션
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostExcelAction(Request $request)
    {
        if (empty($request->contract_info_no)) 
        {
            $r['rs_code'] = "N";
            $r['rs_msg'] = "현장번호가 존재하지 않습니다.";

            return $r;
        }

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);

        if ($request->file('excel_data')) 
        {
            $file_path = $request->file('excel_data')->store("upload/" . date("YmdHis"), 'management');
            // Log::debug('[파일 경로]: ' . $file_path);

            // 파일 유무 확인
            if (Storage::disk('management')->exists($file_path)) 
            {
                // 경로 세팅
                $file = Storage::path('management/' . $file_path);

                $colHeader = [
                    "일위대가코드",
                    "일위대가품명",
                    "규격(1)",
                    "규격(2)",
                    "단위",
                    "비고",
                    "자재단가표코드",
                    "자재단가표수량",
                    "비고"
                ];
                $colNm = [
                    "code"         => "0",   // 일위대가 코드
                    "name"         => "1",   // 일위대가 품명
                    "standard1"    => "2",   // 규격(1)
                    "standard2"    => "3",   // 규격(2)
                    "type"         => "4",   // 단위
                    "etc"          => "5",   // 기타
                    "extra_code"   => "6",   // 자재단가표 코드
                    "extra_volume" => "7",  // 자재단가표 수량
                    "extra_etc"    => "8"    // 자재단가표 기타
                ];

                // 필수 값에 대한 설명을 추가한 배열
                $fieldDescriptions = [
                    "code" => "일위대가 코드",
                    "name" => "품명"
                ];

                try 
                {
                    // 엑셀 데이터 읽기
                    $excelData = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader, 0);
                    // Log::debug('EXCEL DATA: ' . json_encode($excelData));
                } 
                catch (\Exception $e) 
                {
                    Log::debug('[EXCEL ERR]: ' . $e->getMessage());
                    $r['rs_code'] = "N";
                    $r['rs_msg'] = "엑셀 읽기 중 오류가 발생하였습니다.";
                    return $r;
                }

                // 엑셀 유효성 검사
                if (!isset($excelData) || empty($excelData)) 
                {
                    // 파일경로
                    // Log::debug('[엑셀 데이터 없거나 빈값]: ' . $file_path);
                    // Log::debug('[EXCEL ERR:!=] ' . json_encode($excelData));

                    $r['rs_code'] = "N";
                    $r['rs_msg'] = "엑셀 유효성 검사를 실패하였습니다.";

                    return $r;
                }
                else 
                {
                    $this->removeEmptyRow($excelData);

                    $rowNum = 2; // 데이터 시작 행 번호 (엑셀의 2번째 행부터 데이터 시작)
                    $missingFieldsMessages = []; // 필수 값 누락 메시지를 저장할 배열

                    foreach ($excelData as $_DATA) 
                    {
                        unset($_INS);

                        // 데이터 정리
                        foreach ($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_INS[$key] = $val;
                        }

                        // 필수 값들 확인
                        $missingFields = [];
                        foreach (['code', 'name'] as $field) {
                            if (empty($_INS[$field])) {
                                $missingFields[] = $fieldDescriptions[$field];
                            }
                        }
                        
                        if (!empty($missingFields)) {
                            $missingFieldsMessages[] = "행: {$rowNum}, 열: " . implode(', ', $missingFields);
                        } else {
                            // 값이 null 이면 빈값으로 업데이트
                            foreach ($_INS as $key => $val) 
                            {
                                if ($val == "null" || $val == "NULL") 
                                {
                                    $_INS[$key] = '';
                                }
                            }

                            // cost 테이블에 데이터가 이미 존재하는지 확인 (code와 contract_info_no 모두 확인)
                            $existingCost = DB::table('cost')
                                            ->where('code', $_INS['code'])
                                            ->where('contract_info_no', $request->contract_info_no)
                                            ->where('save_status', 'Y')
                                            ->first();

                            if(!empty($existingCost))
                            {
                                $costNo = $existingCost->no;

                                // 기존 데이터가 있으면 업데이트
                                $_COST = array();
                                $_COST['name']             = $_INS['name'] ?? '';
                                $_COST['standard1']        = $_INS['standard1'] ?? '';
                                $_COST['standard2']        = $_INS['standard2'] ?? '';
                                $_COST['type']             = $_INS['type'] ?? '';
                                $_COST['etc']              = $_INS['etc'] ?? '';
                                $_COST['save_id']          = Auth::id();
                                $_COST['save_time']        = date('YmdHis');

                                $result = DB::dataProcess('UPD', 'cost', $_COST, ['no'=>$costNo]);
                            }
                            else
                            {
                                // 기존 데이터가 없으면 삽입
                                $_COST = array();
                                $_COST['contract_info_no'] = $request->contract_info_no;
                                $_COST['code']             = $_INS['code'] ?? '';
                                $_COST['name']             = $_INS['name'] ?? '';
                                $_COST['standard1']        = $_INS['standard1'] ?? '';
                                $_COST['standard2']        = $_INS['standard2'] ?? '';
                                $_COST['type']             = $_INS['type'] ?? '';
                                $_COST['etc']              = $_INS['etc'] ?? '';
                                $_COST['save_status']      = 'Y';
                                $_COST['save_id']          = Auth::id();
                                $_COST['save_time']        = date('YmdHis');

                                $result = DB::dataProcess('INS', 'cost', $_COST, '', $costNo);
                            }

                            // 시퀀스 값
                            $maxSeq = DB::table('cost_extra')->where('contract_info_no', $request->contract_info_no)->where('cost_no', $costNo)->where('save_status', 'Y')->max('seq');
                            $seq = $maxSeq ? $maxSeq + 1 : 1;

                            // cost_extra 테이블에 삽입할 데이터 준비
                            $_EXTRA = array();
                            $_EXTRA['contract_info_no'] = $request->contract_info_no;
                            $_EXTRA['cost_no']          = $costNo;
                            $_EXTRA['seq']              = $seq;

                            $_EXTRA['code']             = $_INS['extra_code'] ?? '';
                            $_EXTRA['volume']           = sprintf('%0.3f', round(str_replace(',', '', $_INS['extra_volume'] ?? 0),3));
                            $_EXTRA['etc']              = $_INS['extra_etc'] ?? '';
                            $_EXTRA['save_status']      = 'Y';
                            $_EXTRA['save_id']          = Auth::id();
                            $_EXTRA['save_time']        = date('YmdHis');

                            // cost_extra 테이블에 데이터 삽입
                            $result = DB::dataProcess('INS', 'cost_extra', $_EXTRA);
                        }

                        $rowNum++; // 다음 행으로 이동
                    }

                    // 모든 행을 처리한 후, 누락된 필수 값이 있는지 확인
                    if (!empty($missingFieldsMessages)) {
                        $r['rs_code'] = "N";
                        $r['rs_msg'] = "필수값이 존재하지 않습니다:\n\n" . implode("\n", $missingFieldsMessages);
                        return $r;
                    }
                }

                $r['rs_code'] = "Y";
                $r['rs_msg'] = "엑셀 업로드를 성공하였습니다.";

                return $r;
            }
            else 
            {
                Log::debug('[파일없음]: ' . $file_path);

                $r['rs_code'] = "N";
                $r['rs_msg'] = "파일이 존재하지 않습니다.";

                return $r;
            }
        } 
        else 
        {
            $r['rs_code'] = "N";
            $r['rs_msg'] = "엑셀파일을 등록해주세요.";

            return $r;
        }
    }




    /**
     * 현장정보 팝업창 - 자재단가표
     * 자재단가표 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setMaterialDataList(Request $request)
    {
        $list = new DataList(Array("listName"=>"managementMaterial","listAction"=>'/'.$request->path()));

        // $list->setRangeSearchDetail(Array('material.price'=>'단가'),'','','단위(원)');

        $list->setSearchDetail(Array(
            'material.category'   => '구분',
            'material.code'       => '코드',
            'material.name'       => '품명',
        ));

        return $list;
    }

    /**
     * 현장정보 팝업창 - 자재단가표
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementMaterial(Request $request)
    {
        $list = $this->setMaterialDataList($request);
        
        $contract_info_no = $request->contract_info_no;

        $list->setButtonArray("일괄삭제", "managementMaterialAllClear('".$contract_info_no."')", "btn-danger");

        $list->setButtonArray("엑셀업로드", "managementMaterialExcelForm('".$contract_info_no."')", "btn-info");

        $list->setButtonArray("엑셀다운","excelDownModal('/field/managementmaterialexcel','form_managementMaterial')","btn-success");
        
        $list->setPlusButton("managementMaterialForm('".$contract_info_no."', '');");
        
        $list->setViewNum(false);
        
        $list->setHidden(Array('contract_info_no' => $contract_info_no));

        $list->setlistTitleCommon(Array
        (
            'category'              => Array('구분', 1, '', 'center', '', 'category'),
            'code'                  => Array('코드', 0, '', 'center', '', 'code'),
            'name'                  => Array('품명', 1, '', 'center', '', 'name'),
            'standard1'             => Array('규격(1)', 1, '', 'center', '', 'standard1'),
            'standard2'             => Array('규격(2)', 1, '', 'center', '', 'standard2'),
            'type'                  => Array('단위', 1, '', 'center', '', 'type'),
            'volume'                => Array('수량', 1, '', 'center', '', ''),
            'price'                 => Array('단가', 1, '', 'center', '', 'price'),
            'balance'               => Array('금액', 0, '', 'center', '', ''),
            'etc'                   => Array('기타', 1, '', 'center', '', 'etc'),
        ));

        return view('field.managementMaterial')->with('result', $list->getList());
    }

    /**
     * 현장정보 팝업창 - 자재단가표 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function managementMaterialList(Request $request)
    {
        $param = $request->all();

        $LOAN_LIST = DB::TABLE("contract_info")
                            ->join("material", "material.contract_info_no", "=", "contract_info.no")
                            ->select("material.*")
                            ->where("contract_info.no",$param['contract_info_no'])
                            ->where('material.save_status','Y')
                            ->where('contract_info.save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='material.category' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('material.category', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='material.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('material.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='material.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('material.name', 'like','%'.$param['searchString'].'%');
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
        $LOAN_LIST = Func::chungDec(["contract_info", "material"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($LOAN_LIST as $v)
        {
            $cost_extra             = DB::table("cost_extra")->select(DB::raw("coalesce(sum(volume),0) as sum_volume"))
                                                            ->where('contract_info_no',$v->contract_info_no)
                                                            ->where('code',$v->code)
                                                            ->where('save_status','Y')
                                                            ->first();

            $v->volume              = $cost_extra->sum_volume;
            $v->price               = $v->price ?? 0;
            $v->balance             = $v->volume*$v->price;

            $v->price               = number_format($v->price ?? 0);
            $v->balance             = number_format($v->balance);

            $link_c                 = '<a class="hand" onClick="managementMaterialForm(\''.$v->contract_info_no.'\', \''.$v->no.'\')">';
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
     * 자재단가표 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementMaterialExcel(Request $request)
    {   
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        
        $list           = $this->setMaterialDataList($request);

        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;

        $LOAN_LIST = DB::TABLE("contract_info")
                            ->join("material", "material.contract_info_no", "=", "contract_info.no")
                            ->select("material.*")
                            ->where("contract_info.no",$param['contract_info_no'])
                            ->where('material.save_status','Y')
                            ->where('contract_info.save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='material.category' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('material.category', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='material.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('material.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='material.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('material.name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $LOAN_LIST = $list->getListQuery('contract_info', 'main', $LOAN_LIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.               
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN_LIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "자재단가표_".date("YmdHis").'_'.Auth::id().'.xlsx';
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
        $LOAN_LIST = Func::chungDec(["contract_info", "material"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT
        
        // 엑셀 헤더
        $excel_header   = array('구분', '코드','품명', '규격(1)', '규격(2)','단위', '수량', '단가','금액','기타');
        $excel_data     = [];

        $array_config   = Func::getConfigArr();
        $arrManager     = Func::getUserList();

        foreach ($LOAN_LIST as $v)
        {
            $cost_extra = DB::table("cost_extra")->select(DB::raw("coalesce(sum(volume),0) as sum_volume"))
                                                    ->where('contract_info_no',$v->contract_info_no)
                                                    ->where('code',$v->code)
                                                    ->where('save_status','Y')
                                                    ->first();
            $array_data = [
                $v->category,                                       //구분
                $v->code,                                           //코드
                $v->name,                                           //품명
                $v->standard1,                                      //규격(1)
                $v->standard2,                                      //규격(2)
                $v->type,                                           //단위
                number_format($cost_extra->sum_volume),             //수량
                number_format($v->price ?? 0),                      //단가
                number_format(($cost_extra->sum_volume)*($v->price ?? 0)),   //금액
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
     * 자재단가표 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementMaterialForm(Request $request)
    {
        $v = [];

        $contract_info_no = $request->contract_info_no;
        $material_no      = $request->material_no ?? 0;

        if(!empty($material_no))
        {
            $v = DB::table("material")->select("*")->where('no',$material_no)->where('save_status','Y')->first();
        }

        return view('field.managementMaterialForm')->with("contract_info_no", $contract_info_no)->with("v", $v);
    }

    /*
     *  자재단가표 폼 등록 액션

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function managementMaterialFormAction(Request $request)
    {
        $_DATA = $request->all();
        
        if($_DATA['mode'] == 'DEL')
        {
            $_DATA['save_status'] = 'N';
            $_DATA['del_id']      = Auth::id();
            $_DATA['del_time']    = date('YmdHis');

            $result = DB::dataProcess('UPD', 'material', $_DATA, ['no'=>$_DATA['material_no'], 'save_status'=>'Y']);
        }
        else if($_DATA['mode'] == 'UPD')
        {
            $code = DB::table("material")->select("no")->where('contract_info_no',$_DATA['contract_info_no'])->where('code',$_DATA['code'])->where('save_status','Y')->first();
            if(!empty($code->no))
            {
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "중복된 코드 입니다.";
            }

            $_DATA['save_id']     = Auth::id();
            $_DATA['save_time']   = date('YmdHis');

            $result = DB::dataProcess('UPD', 'material', $_DATA, ['no'=>$_DATA['material_no'], 'save_status'=>'Y']);
        }
        else
        {
            $code = DB::table("material")->select("no")->where('contract_info_no',$_DATA['contract_info_no'])->where('code',$_DATA['code'])->where('save_status','Y')->first();
            if(!empty($code->no))
            {
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "중복된 코드 입니다.";
            }

            $_DATA['save_status'] = 'Y';
            $_DATA['save_id']     = Auth::id();
            $_DATA['save_time']   = date('YmdHis');

            $result = DB::dataProcess('INS', 'material', $_DATA);
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
     *  자재단가표 일괄삭제

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function managementMaterialAllClear(Request $request)
    {
        $_DATA = $request->all();
        
        $_DATA['save_status'] = 'N';
        $_DATA['del_id']      = Auth::id();
        $_DATA['del_time']    = date('YmdHis');

        $result = DB::dataProcess('UPD', 'material', $_DATA, ['contract_info_no'=>$_DATA['contract_info_no'], 'save_status'=>'Y']);

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
     * 자재단가표 엑셀업로드 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementMaterialExcelForm(Request $request)
    {
        return view('field.managementMaterialExcelForm')->with("contract_info_no", $request->contract_info_no);
    }

    /**
     * 자재단가표 엑셀업로드 샘플
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementMaterialExcelSample(Request $request)
    {
        if(Storage::disk('management')->exists('materialExcelSample.xlsx'))
        {
            return Storage::disk('management')->download('materialExcelSample.xlsx', '자재단가표업로드예시파일.xlsx');
        }
        else
        {
            log::debug("샘플파일 없음");
        }
    }

    /**
     * 자재단가표 엑셀업로드 액션
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementMaterialExcelAction(Request $request)
    {
        if(empty($request->contract_info_no))
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "현장번호가 존재하지 않습니다.";

            return $r;
        }

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);

        if( $request->file('excel_data') )
        {
            // 엑셀 저장
            $file_path = $request->file('excel_data')->store("upload/".date("YmdHis"), 'management');
            
            // 경로세팅 
            if(Storage::disk('management')->exists($file_path))
            {
                $colHeader  = array(
                    "구분",
                    "코드",
                    "품명",
                    "규격(1)",
                    "규격(2)",
                    "단위",
                    "단가",
                    "기타"
                );
                $colNm = array(
                    "category"      => "0",	      // 구분
                    "code"	        => "1",	      // 코드
                    "name"          => "2",       // 품명
                    "standard1"     => "3",       // 규격(1)
                    "standard2"     => "4",       // 규격(2)
                    "type"          => "5",       // 단위
                    "price"         => "6",       // 단가
                    "etc"           => "7",       // 기타
                );
                                    
                $file = Storage::path('/management/'.$file_path);
                
                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    $r['rs_code'] = "N";
                    $r['rs_msg']  = "엑셀 유효성 검사를 실패하였습니다.";

                    return $r;
                }
                else
                {
                    $this->removeEmptyRow($excelData);

                    foreach($excelData as $_DATA) 
                    {
                        unset($_INS);

                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_INS[$key] = $val;
                        }

                        // 데이터 추출 및 정리
                        foreach($_INS as $key => $val)
                        {
                            // 값이 없으면 unset
                            if($val == "")
                            {
                                unset($_INS[$key]);
                                continue;
                            }

                            // 값이 null 이면 빈값으로 업데이트
                            if($val == "null" || $val == "NULL")
                            {
                                $_INS[$key] = '';
                                continue;
                            }
                        }

                        if(!isset($_INS['category']) || $_INS['category'] == '')
                        {
                            continue;
                        }
                        if(!isset($_INS['code']) || $_INS['code'] == '')
                        {
                            continue;
                        }
                        if(!isset($_INS['name']) || $_INS['name'] == '')
                        {
                            continue;
                        }

                        if(!empty($_INS['price']))
                        {
                            $_INS['price'] = round(str_replace(',', '', $_INS['price']));
                        }

                        $_INS['contract_info_no'] = $request->contract_info_no;
                        $_INS['file_path']        = $file_path;

                        $_INS['save_status']      = 'Y';
                        $_INS['save_id']          = Auth::id();
                        $_INS['save_time']        = date('YmdHis');

                        // 중복코드
                        $code = DB::table("material")->select("no")->where('contract_info_no',$request->contract_info_no)->where('code',$_INS['code'])->where('save_status','Y')->first();
                        if(!empty($code->no))
                        {
                            $rslt = DB::dataProcess('UPD', 'material', $_INS, ['no'=>$code->no]);
                        }
                        else
                        {
                            $rslt = DB::dataProcess('INS', 'material', $_INS);
                        }

                    }
                }

                $r['rs_code'] = "Y";
                $r['rs_msg']  = "엑셀 업로드를 성공하였습니다.";

                return $r;
            }
            else 
            {
                log::debug($file_path ?? '파일경로 없음');

                $r['rs_code'] = "N";
                $r['rs_msg']  = "엑셀 업로드를 실패했습니다.";

                return $r;
            }
        }
        else
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "엑셀을 등록해주세요.";

            return $r;
        }
    }
}