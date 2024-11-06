<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use App\Chung\Paging;
use App\Chung\Vars;
use DataList;
class ChangeController extends Controller
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
    private function setDataList(Request $request){

        $list   = new DataList(Array("listName"=>"user","listAction"=>'/'.$request->path()));
 
        $list->setTabs(Array(),$request->tabs);
        
        $list->setHidden(Array('id' => $request->id));

        $list->setSearchDate('날짜검색',Array('save_time' => '변경일'),'searchDt','Y', '',date("Ym").'01', date("Ymd"),'save_time');
        
        //$list->setCheckboxListAdd(Array('ph34' => '내선번호', 'user_rank_cd' => '직급', 'user_position_cd' => '직책', 'ph2' => '핸드폰', 'email' => '이메일', 'addr' => '집주소',  'save_time' => '변경일시'));

        return $list;
    }


     /**
     * 직원정보변경내역 리스트 메인화면
     *
     * @param  Void
     * @return view
     */
	public function changeUserInfo(Request $request)
    {
        $array_branch = Func::getBranchList();
        $list         = $this->setDataList($request);

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleCommon(Array
        (
            'seq'              =>  Array('번호', 0, '', 'center', '', 'seq'),
            'id'               =>  Array('사번', 0, '', 'center', '', 'id'),
            'name'             =>  Array('이름', 0, '', 'center', '', 'name'),
            'branch_code'      =>  Array('부서', 0, '', 'center', '', 'branch_code'),
            'access_ip'        =>  Array('IP', 0, '', 'center', '', 'access_ip'),
            'toesa'            =>  Array('퇴사일', 0, '', 'center', '', 'toesa'),
            'save_time'        =>  Array('변경일시', 0, '', 'center', '', 'save_time'),

        ));

        return view('config.changeUserInfo')->with('result', $list->getList())->with("array_branch", $array_branch);
        
    }

     /**
     * 직원정보변경내역 리스트 메인화면(데이터 출력)
     *
     * @param  Void
     * @return Json $r
     */
	public function changeUserInfoList(Request $request)
    {
        $list   = $this->setDataList($request);

        $param  = $request->all();

        // // 기본헤더 리스트값 세팅
        // $tempArr = Array(
        //     'seq'               =>     Array('번호', 1, '', 'center', '', 'seq'),
        //     'id'                =>     Array('사번', 1, '', 'center', '', 'id'),
        //     'name'              =>     Array('이름', 1, '', 'center', '', 'name'),
        //     'branch_code'   	=>     Array('부서', 0, '', 'center', '', 'branch_code'),
        //     'access_ip'                =>     Array('IP', 0, '', 'center', '', 'access_ip'),
        //     'toesa'             =>     Array('퇴사일', 0, '', 'center', '', 'toesa'),
        //     'save_time'         =>     Array('변경일시', 0, '', 'center', '', 'save_time'),
        // );

        // 리스트 추가 체크박스 선택 시
        // if(isset($request->lists))
        // {
        //     foreach ($request->lists as $key=>$val)
        //     {   
        //         // 핸드폰 및 집주소 정렬
        //         $order_key = $key;
        //         if($key == 'ph2')
        //         {
        //             $order_key = 'ph21||ph22||ph23';
        //         }
        //         if($key == 'addr')
        //         {
        //             $order_key = 'addr11||addr12';
        //         }

        //         $tempArr[$key] = Array($val, 0, '', 'center', '', $order_key);
                
        //     }
        // }
        
        // 리스트 헤더 생성
        //$r['listTitle'] = Func::changeListCols($tempArr);
        
        // 쿼리 볼 수 있도록 활성화
        DB::enableQueryLog();
        
         // 기본쿼리
        $USER_LOG = DB::TABLE("USERS_LOG")->SELECT("ID", "NAME", "SEQ", "EMAIL", "BRANCH_CODE", "USER_RANK_CD", "USER_POSITION_CD", "PH21", "PH22", "PH23", "PH31", "PH32", "PH33", "PH34", "ADDR11", "ADDR12", "ACCESS_IP", "TOESA", "SAVE_TIME")->WHERE('id', $param['id'])->WHERE('LOG_DIV', '1');

        if(!isset($param['listOrder']))
        {
            $param['listOrder'] = 'save_time';
            $param['listOrderAsc'] = 'desc';
        } 
    
        $USER_LOG = $list->getListQuery("USERS_LOG",'main',$USER_LOG,$param);
       
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($USER_LOG, $request->page, $request->listLimit, 10, $request->listName);

        $rslt = $USER_LOG->GET();
        $rslt = Func::chungDec(["USERS_LOG"], $rslt);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $configArr = Func::getConfigArr();
        $array_branch = Func::getBranch();
        $cnt = 0;
        foreach ($rslt as $v)
        {
            $v->branch_code         = Func::getArrayName($array_branch, $v->branch_code);;
            // $v->user_rank_cd        = Func::getArrayName($configArr['user_rank_cd'], $v->user_rank_cd);
            // $v->user_position_cd    = Func::getArrayName($configArr['user_position_cd'], $v->user_position_cd);;
            // $v->ph3                 = $v->ph31.'-'.$v->ph32.'-'.$v->ph33;
            // $v->ph2                 = $v->ph21.'-'.$v->ph22.'-'.$v->ph23;
            // $v->addr                = $v->addr11.' '.$v->addr12;
            $v->toesa               = Func::dateFormat($v->toesa);

            $v->save_time           = Func::dateFormat($v->save_time);
            
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
     * 직원,기능 변경내역(직원정보) 메인화면
     *
     * @param  Void
     * @return view
     */
	public function changeUserTarget(Request $request)
    {
        // 검색어 정리
        $search_string = $request->input('search_string');
        $branch_code   = $request->input('branch_code');

        // 기본쿼리
        $USER = DB::TABLE("USERS")->LEFTJOIN("BRANCH", function($join) {
            $join->ON("USERS.BRANCH_CODE", "=", "BRANCH.CODE")->WHERE("BRANCH.SAVE_STATUS", "Y");
        })->SELECT("USERS.ID, USERS.NAME, BRANCH.BRANCH_NAME")->WHERE("USERS.SAVE_STATUS","Y");

        // 검색
        if( $branch_code )
        {
            $USER->WHERE("USERS.BRANCH_CODE", $branch_code);
        }
        if( $search_string )
        {
            $USER->WHERERAW("( USERS.ID = ? OR USERS.NAME = ? )", [$search_string, Func::encrypt($search_string, 'ENC_KEY_SOL') ]);
            /*
            if( is_numeric($search_string) )
            {
                $USER->WHERE("USERS.ID", $search_string);
            }
            else
            {
                $USER->WHERE("USERS.NAME", "like", $search_string."%");
            }
            */
        }

        $order_colm = ( $request->order_colm=="" )     ? "NAME" : $request->order_colm ;
        $order_type = ( $request->order_type=="DESC" ) ? "DESC" : "ASC" ;
        if( $order_colm && $order_type )
        {
            $USER->ORDERBY($order_colm, $order_type);
        }

        $rslt = $USER->GET();
        $rslt = Func::chungDec(["USERS","BRANCH"], $rslt);	// CHUNG DATABASE DECRYPT

        return view('config.changeUserTarget')->with(['users'=>$rslt, 'order_colm'=>$order_colm, 'order_type'=>$order_type]);        
    }


    /**
     * 기능권한변경내역 메인화면
     *
     * @param  Void
     * @return view
     */
	public function changePermitInfo(Request $request)
    {
        $array_branch = Func::getBranchList();
        return view('config.changePermitInfo')->with('array_branch', $array_branch);   
    }


    /**
     * 기능권한변경내역 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function changePermitInfoList(Request $request)
    {

        $id             = $request->input('id');
        $search_date    = $request->input('search_date');
        $sDate          = $request->input('sDate');
        $eDate          = $request->input('eDate');

        $func_array     = Vars::$arrayFuncPermit;
        $func_name      = Func::getMyMenu();
        $arrayUserId    = Func::getUserId();

        // 주메뉴 기본쿼리
        $MENUS = DB::TABLE("CONF_MENU")->SELECT("MENU_CD")->WHERE('USE_YN','Y')->WHERE('LENGTH(menu_cd)',3);
        $m_rslt = $MENUS->GET();
        $m_rslt = Func::chungDec(["CONF_MENU"], $m_rslt);	// CHUNG DATABASE DECRYPT

        // 유저로그 기본쿼리
        $USERS = DB::TABLE("USERS_LOG")->SELECT("SAVE_TIME, PERMIT, NAME, WORKER_ID")->WHERE('LOG_DIV', '2')->WHERE('ID', $id)->ORDERBY('SAVE_TIME', 'ASC');

        // 날짜 검색
        if( $search_date && ($sDate || $eDate) )
        {
            $sDate = str_replace('-', '', $sDate);
            $eDate = str_replace('-', '', $eDate);

            if( $sDate )
            {
                $USERS->WHERE($search_date, '>=', $sDate."000000");
            }
            if( $eDate ) 
            {
                $USERS->WHERE($search_date, '<=', $eDate."235959");
            }
        }

        // 해당 직원의 로그데이터가 있을 경우
        $list_chk = $USERS->exists();

        // 리스트 출력
        if( $list_chk )
        {   
            $u_rslt = $USERS->GET();
            $u_rslt = Func::chungDec(["USERS_LOG"], $u_rslt);	// CHUNG DATABASE DECRYPT
            
            $same_permit = null;
            $cnt = 0;

            // 뷰 데이터 정리
            foreach( $u_rslt as $v )
            {
                $v->save_time = Func::dateFormat($v->save_time);
                
                // 기능권한 정보 없어도 최초에 데이터 세팅
                if($cnt != 0)
                {
                    // 권한변경이 없을 경우 출력생략
                    if($same_permit === $v->permit)
                    {
                        continue;
                    }
                }
                $v->worker_id = Func::getArrayName($arrayUserId, $v->worker_id);
                Log::debug($v->worker_id);
                $func_permit[$v->save_time] = explode(',', $v->permit);
                $same_permit = $v->permit;    

                $r['v'][] = $v;
                $cnt++;
            }

            return view('config.changePermitInfoList')->with(['users'=>$r['v'], 'menus'=>$m_rslt, 'func_array'=>$func_array, 'func_name'=>$func_name, 'func_permit'=>$func_permit]);
        }
        else
        {
            return view('config.changePermitInfoList')->with(['users'=>[], 'menus'=>[], 'func_array'=>$func_array, 'func_name'=>$func_name]);
        }
        
    }

}
