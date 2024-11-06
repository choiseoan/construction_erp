<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Vars;
use Cache;

class BranchController extends Controller
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
     * 부서관리 메인화면
     *
     * @param  Void
     * @return view
     */
	public function branch()
    {
        return view('config.branch');
    }

    /**
     * 부서관리 부서정보조직도 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function branchList($pcode="000")
    {
        $array_branch = Func::getBranchList();
        return view('config.branchList')->with(['result'=>$array_branch]);
    }


    /**
     * 부서관리 입력폼 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function branchForm(Request $request)
    {
        $code = $request->input('code');
        $array_branch = Func::getBranchList();
        $array_center = Vars::$arrayBranchCenterCode;
        $arrayBranchDiv  = Func::getConfigArr('branch_div');
        $arrayBranchArea  = Func::getConfigArr('area_name_cd');
        $users = DB::TABLE("USERS")->SELECT("ID","NAME","BRANCH_CODE")->WHERE('save_status','Y')->WHERE('branch_code','!=','0000')->ORDERBY('ID')->GET();
        $users = Func::chungDec(["USERS"], $users);	// CHUNG DATABASE DECRYPT
        
        foreach( $users as $u )
        {
            $array_user[$u->id]   = $u->name."  (".$u->id.")";
        }

        $rslt = null;
        if( $code )
        {
            $rslt = DB::TABLE("BRANCH")->SELECT("*")->WHERE('code',$code)->WHERE('save_status','Y')->FIRST();
            $rslt = Func::chungDec(["BRANCH"], $rslt);	// CHUNG DATABASE DECRYPT
        }

        $mode = ( $rslt ) ? 'UPD' : 'INS' ;
        $read = ( $mode=='UPD' ) ? 'readonly' : '' ;

        return view('config.branchForm')
                ->with(['mode'=>$mode,
                        'readonly'=>$read,
                        'v'=>$rslt, 
                        'array_branch'=>$array_branch, 
                        'arrayBranchDiv'=>$arrayBranchDiv,
                        'arrayCenter'=>$array_center, 
                        'arrayUsers'=>$array_user,
                        'arrayBranchArea'=>$arrayBranchArea,
                    ]);
    }


    /**
     * 부서관리 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function branchAction(Request $request)
    {
        $code = $request->input('code');
        $mode = $request->input('mode');

        // 데이터 세팅
        unset($DATA);
        $DATA                = $request->all();
        
        // 변수정리
        if(isset($DATA['open_date']))
        {
            $DATA['open_date']   = str_replace("-","", $DATA['open_date']);
        }

        if(isset($DATA['close_date']))
        {
            $DATA['close_date']  = str_replace("-","", $DATA['close_date']);
        }

        if( $mode=="DEL" )
        {
            $v = DB::TABLE("BRANCH")->SELECT("count(*) as cnt")->WHERE('parent_code',$code)->WHERE('save_status','Y')->FIRST();
            $v = Func::chungDec(["BRANCH"], $v);	// CHUNG DATABASE DECRYPT
            
            if( $v->cnt>0 )
            {
                return "하부 부서가 존재하여 삭제할 수 없습니다.";
            }

            $u = DB::TABLE("USERS")->SELECT("count(*) as cnt")->WHERE('branch_code',$code)->WHERE('save_status','Y')->FIRST();
            $u = Func::chungDec(["USERS"], $u);	// CHUNG DATABASE DECRYPT
            if( $u->cnt > 0) {
                return "소속 직원이 존재하여 삭제할 수 없습니다.";
            }

            $rslt = DB::dataProcess('UPD', 'BRANCH', $DATA, ["code"=>$code]);
        }
        else
        {
            $rslt = DB::dataProcess($mode, 'BRANCH', $DATA);
        }

        // DEPTH, ORDER 컬럼 정리
        $arr = Func::getBranchTree();
        $i = 1;
        foreach( $arr as $b )
        {
            if(!empty($b['code']) && $b['code'] == 'P99') continue;
            DB::dataProcess('UPD', 'BRANCH', ['branch_order'=>$i,'branch_depth'=>$b['branch_depth']], ["code"=>$b['code'],'save_status'=>'Y']);
            $i++;
        }

        if( $rslt=="Y" )
        {
            $msg = "정상처리되었습니다.";
        }
        else if( $rslt=="N" )
        {
            $msg = "처리에 실패하였습니다.";
        }
        else if( $rslt=="E" )
        {
            $msg = "등록정보가 올바르지 않습니다.";
        }
        else
        {
            $msg = "기타오류";
            if( $rslt )
            {
                $msg.= "(".$rslt.")";
            }
        }

        Cache::flush();

        return $msg;
    }
}
