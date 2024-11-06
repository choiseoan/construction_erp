<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
use Func;
use Cache;
use Auth;

class CodeController extends Controller
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
     * 코드관리 메인화면
     *
     * @param  Void
     * @return view
     */
	public function code()
    {
        return view('config.code');
    }

    /**
     * 코드관리 카테고리리스트, 코드리스트
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function codeList(Request $request)
    {
        $gubun    = $request->input('gubun');
        $cat_code = $request->input('cat_code');
        
        $rslt = null;
        if( $gubun=="CATE" )
        {
            $rslt = DB::table("conf_cate")->select("*")->where('save_status','Y');

            //검색 추가
            if(isset($request->search)&&!empty($request->search)){
                $search = $request->input('search');
                $rslt   = $rslt->where(function($query) use ($search) {
                    $query->where('cat_code','like','%'.$search.'%')
                          ->orWhere('cat_name', 'like','%'.$search.'%');
                });
            } 
            
            $rslt = $rslt->orderBy("cat_name")->GET();
            $rslt = Func::chungDec(["CONF_CATE"], $rslt);	// CHUNG DATABASE DECRYPT
        }
        else if( $gubun=="CODE" && $cat_code )
        {
            $rslt = DB::table("conf_code")->select("*")->where('save_status','Y')->where('cat_code',$cat_code)->orderBy("code_order")->orderby("name")->GET();
            $rslt = Func::chungDec(["CONF_CODE"], $rslt);	// CHUNG DATABASE DECRYPT
        }

        return view('config.codeList')->with(['gubun'=>$gubun,'result'=>$rslt]);
    }

    /**
     * 메뉴관리 최상위폼, 서브폼 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function codeForm(Request $request)
    {
        $gubun    = $request->input('gubun');
        $cat_code = $request->input('cat_code');
        $code     = $request->input('code');

        $rslt = null;
        $subCnt = 0;
        if( $gubun=="CATE" && $cat_code )
        {
            $rslt = DB::table("conf_cate")->select("*")->where('cat_code',$cat_code)->first();
        }
        else if( $gubun=="CODE" && $cat_code )
        {
            $rslt = DB::table("conf_code")->select("*")->where('cat_code', $cat_code)->where('code', $code)->first();
            $subCnt = DB::table("conf_sub_code")->where('cat_code', $cat_code)->where('conf_code', $code)->where('save_status', 'Y')->count();
        }
        $mode = ( $rslt ) ? 'UPD' : 'INS' ;
        $read = ( $mode=='UPD' ) ? 'readonly' : '' ;

        // LOG::debug("gubun=>".$gubun.", mode => ".$mode.", cat_code => ".$cat_code.", readonly => ".$read);
        return view('config.codeForm')->with(['gubun'=>$gubun,'mode'=>$mode,'cat_code'=>$cat_code,'readonly'=>$read,'result'=>$rslt, 'sub_cnt'=>$subCnt]);
    }



    /**
     * 메뉴관리 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function codeAction(Request $request)
    {
        $request->isDebug = true;
        $param = $request->input();
        $gubun = $param['gubun'];
        $mode  = $param['mode'];

        if( $gubun=="CATE" && $param['cat_code'] )
        {
            if( $mode=="DEL" )
            {
                $v = DB::TABLE("CONF_CODE")->SELECT("count(*) as cnt")->WHERE('cat_code',$param['cat_code'])->WHERE('save_status','Y')->FIRST();
                if( $v->cnt>0 )
                {
                    return "사용중인 코드가 존재하여 삭제할 수 없습니다.";
                }
                $mode = "UPD";
                $param['save_status'] = "N";
                $rslt = DB::dataProcess($mode, 'CONF_CATE', $param, ["cat_code"=>$param['cat_code']]);
            }
            else
            {
                if( $mode=="INS" )
                {
                    $param['save_status'] = "Y";
                }
                $rslt = DB::dataProcess($mode, 'CONF_CATE', $param);
            }
        }
        else if( $gubun=="CODE" && $param['cat_code'] && strlen($param['code'])>0 )
        {

            $v = DB::TABLE("CONF_CATE")->SELECT("*")->WHERE('cat_code',$param['cat_code'])->WHERE('save_status','Y')->FIRST();
            if( $v->readonly=='Y' )
            {
                return "코드 수정이 불가능한 카테고리입니다.";
            }

            
            if( $mode=="DEL" )
            {
                $cc = DB::table("conf_sub_code")->where('cat_code',$param['cat_code'])->where('conf_code',$param['code'])->where('save_status','Y')->count();
                if( $cc>0 )
                {
                    Log::debug($param['code'].':'.$cc);
                    return "사용중인 하위코드가 존재하여 삭제할 수 없습니다.";
                }

                $mode = "UPD";
                $param['save_status'] = "N";
                $rslt = DB::dataProcess($mode, 'CONF_CODE', $param, ["cat_code"=>$param['cat_code'],"code"=>$param['code']]);
            }
            else
            {
                if( $mode=="INS" )
                {
                    $param['save_status'] = "Y";
                }
                $rslt = DB::dataProcess($mode, 'CONF_CODE', $param);
            }
        }
        else
        {
            $msg = "등록정보가 올바르지 않습니다.";
        }

        
        if( $rslt=="Y" )
        {
            $msg = "정상처리되었습니다.";
            Cache::flush();
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
        }
        return $msg;
    }

    /**
     * Cache Clear 캐시 초기화
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function cacheClear(Request $request)
    {
        $result = Cache::clear();
        if($result==true)
        {
            $msg = '캐시가 초기화 되었습니다.';
        }
        else
        {
            $msg = '캐시 초기화 중 오류가 발생했습니다.';
        }

        return $msg;
    }


    /**
     * 하위코드 관리 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function subCodeForm(Request $request)
    {


        $sub_v = DB::table("conf_sub_code")->select("*")
                    ->where('save_status','Y')
                    ->where('cat_code', $request->cat_code)
                    ->where('conf_code', $request->conf_code)
                    ->orderBy('code_order')->orderBy('sub_code')->get();
        
        $array_return['sub_v'] = $sub_v;
        $array_return['cat_code'] = $request->cat_code;
        $array_return['conf_code'] = $request->conf_code;
        
        return view('config.subCodeForm')->with($array_return);
    }

    
    /**
     * 하위 메뉴 관리 저장
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function subCodeAction(Request $request)
    {
        $request->isDebug = true;
        $param = $request->input();
        
        $catCode = $param['cat_code'];
        $confCode = $param['conf_code'];

        $cnt = ( !isset($param['sub_code']) ) ? 0:count($param['sub_code']);

        // 업데이트 할 내용
        $UPDATA['del_time']     = date("YmdHis");
        $UPDATA['del_id']       = Auth::id();
        $UPDATA['save_status']  = 'N';

        // 코드가 입력되지 않은 모든 건 삭제처리
        if($cnt==0)
        {
            $param['sub_code'][] = '9999999999';
        }

        DB::table('conf_sub_code')
            ->where('cat_code', $catCode)
            ->where('conf_code', $confCode)
            ->where('save_status', 'Y')
            ->whereNotIn('sub_code', $param['sub_code'])
            ->update($UPDATA);

        // 입력 또는 업데이트
        if($cnt>0)
        {
            $UPDATA['del_time']       = '';
            $UPDATA['del_id']         = '';
            $UPDATA['save_time']      = date("YmdHis");
            $UPDATA['save_id']        = Auth::id();
            $UPDATA['save_status']    = 'Y';
            $UPDATA['cat_code']       = $catCode;
            $UPDATA['conf_code']      = $confCode;
            
            
            for($i=0; $i<$cnt; $i++)
            {
                $UPDATA['sub_code']         = $param['sub_code'][$i];
                $UPDATA['sub_code_name']    = $param['sub_code_name'][$i];
                $UPDATA['code_order']       = ($param['code_order'][$i]=='') ? '0':$param['code_order'][$i];
                
                // 같은 코드로 있는지 확인.
                $cc = DB::table("conf_sub_code")
                            ->where('cat_code', $catCode)
                            ->where('conf_code', $confCode)
                            ->where('sub_code', $UPDATA['sub_code'])
                            ->where('save_status', 'Y')
                            ->count();
                
                if($cc>0)
                {
                    $rslt = DB::dataProcess('UPD', 'conf_sub_code', $UPDATA, ['cat_code' => $catCode, 'conf_code' => $confCode, 'sub_code' => $UPDATA['sub_code'], 'save_status' => 'Y']);    
                }
                else 
                {
                    $rslt = DB::dataProcess('INS', 'conf_sub_code', $UPDATA, null);    
                }
                log::debug($rslt);
            }
        }
        
        $msg = "정상적으로 입력되었습니다.";
        
        return ['rs_msg'=>$msg];
    }


}
