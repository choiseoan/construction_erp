<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Vars;
use Cache;
use Log;
use Auth;

class MenuController extends Controller
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
     * 메뉴관리 메인화면
     *
     * @param  Void
     * @return view
     */
	public function menu()
    {
        return view('config.menu');
    }

    /**
     * 메뉴관리 최상위리스트, 서브메뉴리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function menuList(Request $request)
    {
        $gubun   = $request->input('gubun');
        $menu_cd = $request->input('menu_cd');
        
        $rslt = null;
        if( $gubun=="TOP" )
        {
            $rslt = DB::TABLE("CONF_MENU")->SELECT("*")->WHERE('LENGTH(menu_cd)',3)->ORDERBY("menu_cd")->GET();
            $rslt = Func::chungDec(["CONF_MENU"], $rslt);	// CHUNG DATABASE DECRYPT
        }
        else if( $gubun=="SUB" && strlen($menu_cd)==3 )
        {
            $rslt = DB::TABLE("CONF_MENU")->SELECT("*")->WHERE('menu_cd','<>',$menu_cd)->WHERE('menu_cd','like',$menu_cd.'%')->ORDERBY('menu_order')->GET();
            $rslt = Func::chungDec(["CONF_MENU"], $rslt);	// CHUNG DATABASE DECRYPT
        }

        return view('config.menuList')->with(['gubun'=>$gubun,'result'=>$rslt]);
    }

    /**
     * 메뉴관리 최상위폼, 서브폼 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function menuForm(Request $request)
    {
        $gubun   = $request->input('gubun');
        $menu_cd = $request->input('menu_cd');
        $pcode   = $request->input('pcode');

        $rslt = null;
        if( $gubun=="TOP" && strlen($menu_cd)==3 )
        {
            $rslt = DB::TABLE("CONF_MENU")->SELECT("*")->WHERE('menu_cd', $menu_cd)->FIRST();
        }
        else if( $gubun=="SUB" && strlen($menu_cd)==6 )
        {
            $rslt = DB::TABLE("CONF_MENU")->SELECT("*")->WHERE('menu_cd', $menu_cd)->FIRST();
        }
        $mode = ( $rslt ) ? 'UPD' : 'INS' ;
        $read = ( $mode=='UPD' ) ? 'readonly' : '' ;

        return view('config.menuForm')->with(['gubun'=>$gubun,'mode'=>$mode,'pcode'=>$pcode,'readonly'=>$read,'result'=>$rslt]);
    }



    /**
     * 메뉴관리 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function menuAction(Request $request)
    {
        $gubun = $request->input('gubun');
        $mode  = $request->input('mode');

        Log::info(print_r($request->all(), true));

        if( $mode=="DEL" || $mode=="TOPDEL")
        {
            if($mode=="DEL")
            {
                $save_id = Auth::id();
                $save_time = date("YmdHis");
                $menu_cd  = $request->input('menu_cd');

                // $rslt = DB::dataProcess($mode, 'CONF_MENU_USER',   $request, ["menu_cd"=>$request->input("menu_cd")]);
                // $rslt = DB::dataProcess($mode, 'CONF_MENU_BRANCH', $request, ["menu_cd"=>$request->input("menu_cd")]);

                // 삭제로 업데이트
                $rslt = DB::dataProcess("UPD", "CONF_MENU_USER", [
                    'user_use_yn'=>'N',
                    'del_id'=>$save_id,
                    'del_time'=>$save_time,
                ], ['menu_cd'=>$menu_cd, 'user_use_yn'=>'Y']);

                $rslt = DB::dataProcess("UPD", "CONF_MENU_BRANCH", [
                    'branch_use_yn'=>'N',
                    'del_id'=>$save_id,
                    'del_time'=>$save_time,
                ], ['menu_cd'=>$menu_cd, 'branch_use_yn'=>'Y']);

                $rslt = DB::dataProcess($mode, 'CONF_MENU_HEAD',   $request, ["menu_cd"=>$request->input("menu_cd")]);
                
                $rslt = DB::dataProcess($mode, 'CONF_MENU',        $request, ["menu_cd"=>$request->input("menu_cd")]);
            }
            // 최상위 메뉴 삭제
            else
            {
                // 하위메뉴 존재하는지 확인.
                $subCnt = DB::TABLE("CONF_MENU")->WHERE('LENGTH(menu_cd)', '>',3)->WHERE('LEFT(menu_cd, 3)',$request->input("menu_cd"))->count();
                if($subCnt>0)
                {
                    return "최상위메뉴 삭제시 하위메뉴가 없어야 합니다.\n확인 후 다시 시도해주세요.(하위메뉴수:".$subCnt.")";
                }
                else
                {
                    $rslt = DB::dataProcess('DEL', 'CONF_MENU',        $request, ["menu_cd"=>$request->input("menu_cd")]);
                }
            }
        }
        else
        {
            $rslt = DB::dataProcess($mode, 'CONF_MENU', $request);
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
        }

        Cache::flush();
        return $msg;
    }

}
