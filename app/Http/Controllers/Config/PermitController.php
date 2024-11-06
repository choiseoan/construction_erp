<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Chung\Vars;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;

class PermitController extends Controller
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
     * 권한관리(부서별) 메인화면
     *
     * @param  Void
     * @return view
     */
	public function permitBranch()
    {

        $rslt = DB::TABLE("CONF_MENU")->SELECT("*")->WHERE('use_yn','Y')->WHERENOTIN("menu_cd", ['DEV', '002', '006'])->ORWHERE('LENGTH(menu_cd)',3)->ORDERBY("substr(menu_cd,1,3)")->ORDERBY("COALESCE(menu_order,-1)")->GET();
        $rslt = Func::chungDec(["CONF_MENU"], $rslt);	// CHUNG DATABASE DECRYPT

		$array_side_menu = [];
		foreach( $rslt as $menu )
		{
			$menu_info = [];
			$menu_info['code']  = $menu->menu_cd;
			$menu_info['name']  = $menu->menu_nm;
			$menu_info['icon']  = $menu->menu_icon;

			if( strlen($menu->menu_cd)==3 )
			{
				$array_side_menu[$menu->menu_cd] = $menu_info;
				$array_side_menu[$menu->menu_cd]['sub'] = [];
			}
			else
			{
				$pcode = substr($menu->menu_cd,0,3);
				$array_side_menu[$pcode]['sub'][$menu->menu_cd] = $menu_info;
			}
		}
        
        $array_branch = Func::getBranchList();
        return view('config.permitBranch')->with(['branch'=>$array_branch, 'menus'=>$array_side_menu]);
    }

    /**
     * 부서의 메뉴권한 가져오기
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function permitBranchMenus(Request $request)
    {
        $branch_cd = $request->input('code');
        $rslt = DB::TABLE("CONF_MENU_BRANCH")->SELECT("*")->WHERE('branch_use_yn','Y')->WHERE('branch_cd',$branch_cd)->ORDERBY("menu_cd")->GET();
        $rslt = Func::chungDec(["CONF_MENU_BRANCH"], $rslt);	// CHUNG DATABASE DECRYPT

        $menus = [];

        if( $rslt )
        {
            foreach( $rslt as $val )
            {
                $menus[] = $val->menu_cd;
            }
        }
        return sizeof($menus)>0 ? implode(",",$menus) : "";
    }

    /**
     * 부서메뉴권한 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function permitBranchAction(Request $request)
    {
        $value = $request->input();
        $branch_cd = $value['branch_cd'];

        // 기존 저장정보 배열에 저장.
        $arrayMenus = null;
        $rslt = DB::table("CONF_MENU_BRANCH")->select('menu_cd')->where('branch_cd', $branch_cd)->get();
        $rslt = Func::chungDec(["CONF_MENU_BRANCH"], $rslt);	// CHUNG DATABASE DECRYPT

        if(isset($rslt))
        {
            foreach($rslt as $b)
            {
                $arrayMenus[$b->menu_cd] = true;
            }
        }

        DB::beginTransaction();
        
        //$rslt = DB::dataProcess("DEL", "CONF_MENU_BRANCH", [], ['branch_cd'=>$branch_cd]);
        $save_id = Auth::id();
        $save_time = date("YmdHis");

        $msg = "";
        if( isset($value['menus']) && sizeof($value['menus'])>0 )
        {
            for( $i=0; $i<sizeof($value['menus']); $i++ )
            {
                $menu_cd = $value['menus'][$i];
                if(isset($arrayMenus[$menu_cd]))
                {
                    $rslt = DB::dataProcess("UPD", "CONF_MENU_BRANCH", [
                        'branch_use_yn'=>'Y',
                        'save_id'=>$save_id,
                        'save_time'=>$save_time,
                        'del_id'=>'',
                        'del_time'=>'',
                    ], ['menu_cd'=>$menu_cd, 'branch_cd'=>$branch_cd]);

                    if( $rslt!="Y" )
                    {
                        DB::rollBack();
                        $msg = "처리에 실패하였습니다.";
                        break;
                    }
                }
                else
                {
                    $rslt = DB::dataProcess("INS", "CONF_MENU_BRANCH", [
                            'menu_cd'=>$menu_cd,
                            'branch_cd'=>$branch_cd,
                            'branch_use_yn'=>'Y',
                            'save_id'=>$save_id,
                            'save_time'=>$save_time,
                    ]);

                    if( $rslt!="Y" )
                    {
                        DB::rollBack();
                        $msg = "처리에 실패하였습니다.";
                        break;
                    }
                }
                unset($arrayMenus[$menu_cd]);
            }
        }

        // 없는건 삭제
        if(isset($arrayMenus))
        {
            foreach($arrayMenus as $menu_cd=>$isTrue)
            {
                $rslt = DB::dataProcess("UPD", "CONF_MENU_BRANCH", [
                    'branch_use_yn'=>'N',
                    'del_id'=>$save_id,
                    'del_time'=>$save_time,
                ], ['menu_cd'=>$menu_cd, 'branch_cd'=>$branch_cd, 'branch_use_yn'=>'Y']);

                if( $rslt!="Y" )
                {
                    DB::rollBack();
                    $msg = "처리에 실패하였습니다.";
                    break;
                }
            }
        }

        if( $msg=="" )
        {
            DB::commit();
            $msg.= "정상처리되었습니다.";
        }
        return $msg;
    }



    /**
     * 권한관리(직원별) 메인화면
     *
     * @param  Void
     * @return view
     */
	public function permitUser()
    {
        $rslt = DB::TABLE("CONF_MENU")->SELECT("*")->WHERE('use_yn','Y')->WHERENOTIN("menu_cd", ['DEV', '002', '006'])->ORWHERE('LENGTH(menu_cd)',3)->ORDERBY("substr(menu_cd,1,3)")->ORDERBY("COALESCE(menu_order,-1)")->GET();
        $rslt = Func::chungDec(["CONF_MENU"], $rslt);	// CHUNG DATABASE DECRYPT

        $array_branch = Func::getBranchList();

		$array_side_menu = [];
		foreach( $rslt as $menu )
		{
			$menu_info = [];
			$menu_info['code']  = $menu->menu_cd;
			$menu_info['name']  = $menu->menu_nm;
			$menu_info['icon']  = $menu->menu_icon;

			if( strlen($menu->menu_cd)==3 )
			{
				$array_side_menu[$menu->menu_cd] = $menu_info;
				$array_side_menu[$menu->menu_cd]['sub'] = [];
			}
			else
			{
				$pcode = substr($menu->menu_cd,0,3);
				$array_side_menu[$pcode]['sub'][$menu->menu_cd] = $menu_info;
			}
        }
        
        return view('config.permitUser')->with(['menus'=>$array_side_menu, 'array_branch'=>$array_branch]);
    }

    
    /**
     * 권한관리(직원별) 메인화면
     *
     * @param  Void
     * @return view
     */
	public function permitUserList(Request $request)
    {
        // 검색어 정리
        $search_string = $request->input('search_string');
        $branch_code   = $request->input('branch_code');

        // 기본쿼리
        $USER = DB::TABLE("USERS")->LEFTJOIN("BRANCH", function($join) {
            $join->ON("USERS.BRANCH_CODE", "=", "BRANCH.CODE")->WHERE("BRANCH.SAVE_STATUS", "Y");
        })->SELECT("USERS.*, BRANCH.BRANCH_NAME")->WHERE("USERS.SAVE_STATUS","Y")/*->WHERE("USERS.BRANCH_CODE", "!=", "99")*/;

        
        // 검색
        if( $branch_code )
        {
            $USER->WHERE("USERS.BRANCH_CODE", $branch_code);
        }
        if( $search_string )
        {
            $USER->WHERERAW("( USERS.ID = ? OR USERS.NAME LIKE ? )", [$search_string, Func::encrypt($search_string, 'ENC_KEY_SOL')]);
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
        $permit_div = ( isset($request->permit_div) && $request->permit_div=="A" ) ? "A" : "" ;
        if( $order_colm && $order_type )
        {
            $USER->ORDERBY($order_colm, $order_type);
        }

        $rslt = $USER->GET();
        $rslt = Func::chungDec(["USERS","BRANCH"], $rslt);	// CHUNG DATABASE DECRYPT

        return view('config.permitUserList')->with(['users'=>$rslt, 'order_colm'=>$order_colm, 'order_type'=>$order_type ,'permit_div'=>$permit_div]);
    }

    /**
     * 직원의 메뉴권한 가져오기
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function permitUserMenus(Request $request)
    {
        $user_id = $request->input('id');
        $branch_cd = $request->input('cd');
        $menus_b = [];
        $menus_u = [];

        $rslt = DB::TABLE("CONF_MENU_BRANCH")->SELECT("*")->WHERE('branch_use_yn','Y')->WHERE('branch_cd',$branch_cd)->ORDERBY("menu_cd")->GET();
        $rslt = Func::chungDec(["CONF_MENU_BRANCH"], $rslt);	// CHUNG DATABASE DECRYPT

        if( $rslt )
        {
            foreach( $rslt as $val )
            {
                $menus_b[] = $val->menu_cd;
            }
        }
        $rslt = DB::TABLE("CONF_MENU_USER")->SELECT("*")->WHERE('user_use_yn','Y')->WHERE('user_id',$user_id)->ORDERBY("menu_cd")->GET();
        $rslt = Func::chungDec(["CONF_MENU_USER"], $rslt);	// CHUNG DATABASE DECRYPT
        
        if( $rslt )
        {
            foreach( $rslt as $val )
            {
                if( !in_array( $val->menu_cd, $menus_b ) )
                {
                    $menus_u[] = $val->menu_cd;
                }
            }
        }

        $rslt = sizeof($menus_b)>0 ? implode(",",$menus_b) : "";
        $rslt.= "|";
        $rslt.= sizeof($menus_u)>0 ? implode(",",$menus_u) : "";
        return $rslt;
    }


    /**
     * 직원메뉴권한 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function permitUserAction(Request $request)
    {
        $value = $request->input();
        $user_id = $value['user_id'];

        // 기존 저장정보 배열에 저장.
        $arrayMenus = null;
        $rslt = DB::table("CONF_MENU_USER")->select('menu_cd')/*->where('USER_USE_YN','Y')*/->where('user_id', $user_id)->get();
        $rslt = Func::chungDec(["CONF_MENU_USER"], $rslt);	// CHUNG DATABASE DECRYPT
        if(isset($rslt))
        {
            foreach($rslt as $b)
            {
                $arrayMenus[$b->menu_cd] = true;
            }
        }
        

        DB::beginTransaction();
        //$rslt = DB::dataProcess("DEL", "CONF_MENU_USER", [], ['user_id'=>$user_id]);
        $save_id = Auth::id();
        $save_time = date("YmdHis");
        
        $msg = "";
        if(isset($value['menus']) && sizeof($value['menus'])>0 )
        {
            for( $i=0; $i<sizeof($value['menus']); $i++ )
            {
                $menu_cd = $value['menus'][$i];
                if(isset($arrayMenus[$menu_cd]))
                {
                    $rslt = DB::dataProcess("UPD", "CONF_MENU_USER", [
                            'menu_cd'=>$menu_cd,
                            'user_id'=>$user_id,
                            'user_use_yn'=>'Y',
                            'save_id'=>$save_id,
                            'save_time'=>$save_time,
                        ], ['menu_cd'=>$menu_cd, 'user_id'=>$user_id]);
                    if( $rslt!="Y" )
                    {
                        DB::rollBack();
                        $msg = "처리에 실패하였습니다.";
                        break;
                    }
                }
                else
                {
                    $rslt = DB::dataProcess("INS", "CONF_MENU_USER", [
                            'menu_cd'=>$menu_cd,
                            'user_id'=>$user_id,
                            'user_use_yn'=>'Y',
                            'save_id'=>$save_id,
                            'save_time'=>$save_time,
                    ]);

                    if( $rslt!="Y" )
                    {
                        DB::rollBack();
                        $msg = "처리에 실패하였습니다.";
                        break;
                    }
                }
                unset($arrayMenus[$menu_cd]);
            }
        }

        // 없는건 삭제
        if(isset($arrayMenus))
        {
            foreach($arrayMenus as $menu_cd=>$isTrue)
            {
                $rslt = DB::dataProcess("UPD", "CONF_MENU_USER", [
                    'user_use_yn'=>'N',
                    'del_id'=>$save_id,
                    'del_time'=>$save_time,
                ], ['menu_cd'=>$menu_cd, 'user_id'=>$user_id, 'user_use_yn'=>'Y']);

                if( $rslt!="Y" )
                {
                    DB::rollBack();
                    $msg = "처리에 실패하였습니다.";
                    break;
                }
            }
        }
        
        if( $msg=="" )
        {
            DB::commit();
            $msg.= "정상처리되었습니다.";
        }

        return $msg;
    }


     /**
     * 기능관리(직원별) 메인화면
     *
     * @param  Void
     * @return view
     */
	public function funcPermitUser()
    {
        $array_side_menu = [];
        
        // 기능권환관리
        $func_array = Vars::$arrayFuncPermit;

        $rslt = DB::TABLE("CONF_MENU")->SELECT("*")->WHERE('use_yn','Y')->ORWHERE('LENGTH(menu_cd)',3)->ORDERBY("substr(menu_cd,1,3)")->ORDERBY("COALESCE(menu_order,-1)")->GET();
        
        $rslt = Func::chungDec(["CONF_MENU"], $rslt);	// CHUNG DATABASE DECRYPT

        
		foreach( $rslt as $menu )
		{
			$menu_info = [];
			$menu_info['code']  = $menu->menu_cd;
			$menu_info['name']  = $menu->menu_nm;
            $menu_info['icon']  = $menu->menu_icon;
            
            // 최상위 메뉴일 경우
			if( strlen($menu->menu_cd)==3)
			{
                // 최상위 메뉴 배열 추가
                $array_side_menu[$menu->menu_cd] = $menu_info;
                
                // 기능권한 배열이 존재할경우 최상위 메뉴에 기능권한 추가
                if(isset($func_array[$menu->menu_cd]))
                {
                    $array_side_menu[$menu->menu_cd]['func'][$menu->menu_cd] = $func_array[$menu->menu_cd];
                }
                // 기능권한 배열이 존재하지 않을 경우 메뉴테이블만 출력
                else
                {
                    $array_side_menu[$menu->menu_cd]['func'][$menu->menu_cd] = Array();
                }
            }
        }

        $array_branch = Func::getBranchList();

        return view('config.funcPermitUser')->with(['array_branch'=>$array_branch, 'menus'=>$array_side_menu]);
    }

    /**
     * 직원의 기능권한 가져오기
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function funcPermitUserMenus(Request $request)
    {
        $user_id = $request->input('id');
 
        $rslt = DB::TABLE("USERS")->SELECT("PERMIT")->WHERE('id', $user_id)->WHERE('save_status', 'Y')->FIRST();
        $rslt = Func::chungDec(["USERS"], $rslt);	// CHUNG DATABASE DECRYPT

        if( $rslt )
        {
            $func_menu = $rslt->permit;
        }

        return $func_menu;
    }

    /**
     * 직원별 기능권한 처리 ( 등록, 수정 )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function funcPermitUserAction(Request $request)
    {
        $request->isDebug = true;
        $value = $request->input();
        $user_id = $value['user_id'];
        $func_cd = '';

        // 체크된 메뉴 확인
        if( isset($value['menus']))
        {
            for( $i=0; $i<sizeof($value['menus']); $i++ )
            {
                $menu_cd = $value['menus'][$i];

                $func_cd.= $menu_cd.",";
            }
        }

        $rslt = DB::dataProcess("UPD", "USERS", ['permit'=>$func_cd, 'worker_id'=>Auth::id(), 'save_time'=>date("YmdHis")], ['id'=>$user_id]);

        if($rslt)
        {
            $msg = "정상처리되었습니다.";
        }
        else
        {
            $msg = "처리에 실패하였습니다.";
        }

        return $msg;
    }

    /**
     * 승인권한관리 메인화면
     *
     * @param  Void
     * @return view
     */
	public function confirmPermit()
    {
        $array_branch = Func::getBranchList();
        return view('config.confirmPermit')->with([ 'array_branch'=>$array_branch, 'array_confirm'=>Vars::$arrConfirmPermit]);
    }

    /**
     * 직원의 기능권한 가져오기
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function getConfirmPermit(Request $request)
    {
        $user_id = $request->input('id');

        $rslt = DB::TABLE("USERS")->SELECT("CONFIRM_PERMIT")->WHERE('id', $user_id)->WHERE('save_status', 'Y')->FIRST();
        $rslt = Func::chungDec(["USERS"], $rslt);	// CHUNG DATABASE DECRYPT
        
        if( $rslt )
        {
            $func_menu = $rslt->confirm_permit;
        }

        return $func_menu;
    }

    /**
     * 승인권한 저장
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function confirmPermitAction(Request $request)
    {
        $user_id           = $request->user_id;
        $new_permit_string = !empty($request->permits)?implode(",",$request->permits).",":"";

        $rslt = DB::dataProcess("UPD", "USERS", ['confirm_permit'=>$new_permit_string], ['id'=>$user_id]);

        if(isset($rslt) && $rslt == 'Y')
        {
            $arr_result['msg'] = "정상처리되었습니다.";
            $arr_result['permit_string'] = $new_permit_string;
        }
        else
        {
            $arr_result['msg'] = "처리에 실패하였습니다.";
        }

        return $arr_result;
    }
}
