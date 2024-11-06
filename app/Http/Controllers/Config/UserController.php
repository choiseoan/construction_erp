<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use DB;
use Log;
use Auth;
use Func;
use Hash;
use Vars;
use DataList;
use App\Chung\Paging;
use ExcelFunc;
use FastExcel;
use Illuminate\Support\Facades\Storage;
class UserController extends Controller
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
        
        $list->setTabs([], $request->Tabs); 
        
        $list->setCheckBox("id");

        $list->setSearchDate('날짜검색',Array('ipsa' => '입사일', 'toesa' => '퇴사일'),'searchDt','Y','Y');

        $list->setSearchType('branch_code',Func::getBranch(),'부서선택',"onchange=\"listRefresh();\"");
        
        $list->setSearchDetail(Array('id' => '아이디', 'name' => '이름', 'email' => '이메일'));    
        // $list->setSearchDetailLikeOption("%%");

        if( Func::funcCheckPermit("P022") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/config/userexcel', 'form_user')", "btn-success");
        }

        return $list;
    }
    

    /**
     * 직원관리 메인화면
     *
     * @param  Void
     * @return view
     */
	public function user(Request $request)
    {
        $list = $this->setDataList($request);
        
        $list->setPlusButton("setUserForm('')");

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleCommon( Array(
            'branch_code'       =>     Array('부서', 99, '', 'center', '', 'branch_code'),
            'id'                =>     Array('사번', 1, '', 'center', '', 'id'),
            'name'              =>     Array('이름', 1, '', 'center', '', 'name'),
            'user_rank_cd'      =>     Array('직급', 0, '', 'center', '', 'user_rank_cd'),
            'user_position_cd'  =>     Array('직책', 0, '', 'center', '', 'user_position_cd'),
            'ph34'              =>     Array('내선', 0, '', 'center', '', 'ph34'),
            'email'   	        =>     Array('이메일', 0, '', 'center', '', 'email'),
            'birthday'          =>     Array('생년월일', 0, '', 'center', '', 'birthday'),
            'ipsa'              =>     Array('입사일', 0, '', 'center', '', 'ipsa'),
            'toesa'             =>     Array('퇴사일', 0, '', 'center', '', 'toesa'),
            'save_time'         =>     Array('저장일', 0, '', 'center', '', 'save_time'),
        ));
        return view('config.user')->with("result", $list->getList());
    }

    /**
     * 부서관리 부서정보조직도 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function userList(Request $request)
    {
        // $request->isDebug = true;

        $list   = $this->setDataList($request);
        $param  = $request->all();

        // 기본쿼리
        $users = DB::TABLE("users")->LEFTJOIN("branch", function($join) {
            $join->ON("users.branch_code", "=", "branch.code")->WHERE("branch.save_status", "Y");
        })->SELECT("users.*, branch.branch_name")->WHERE("users.save_status","Y");

        // 부서명 클릭시, 부서 하위 직원 검색
        if(isset($request->customSearch) && $request->customSearch!='0000')
        {
            // 클릭한 부서가 상위부서인 부서를 모두 가져온다.
            $arrBranch = DB::TABLE('branch')->select('code')->where("save_status", "Y")->where("parent_code", $request->customSearch)->get();
            $arrBranch = Func::chungDec(["BRANCH"], $arrBranch);	// CHUNG DATABASE DECRYPT

            $arrayBranch[] = $request->customSearch;
            foreach($arrBranch as $tmp)
            {
                $arrayBranch[] = $tmp->code;
            }

            $users = $users->whereIn("users.branch_code", $arrayBranch);
        }
        

        if(!isset($request->listOrder))
        if(!isset($param['listOrder']))
        {
            $param['listOrder'] = 'branch_code,name';
            $param['listOrderAsc'] = 'asc';            
        }

        $users = $list->getListQuery("users", 'main', $users, $param);
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($users, $request->page, $request->listLimit, 10);
        
		$result = $users->get();
        $result = Func::chungDec(["USERS","BRANCH"], $result);	// CHUNG DATABASE DECRYPT

		// 뷰단 데이터 정리.
        $cnt = 0;
        $configArr = Func::getConfigArr();
        $branchArr = Func::getBranch();
		foreach ($result as $v)
		{
            $v->onclick             = 'setUserForm(\''.$v->id.'\');';
            $v->line_style          = 'cursor: pointer;';

            if (isset($v->login_lock_time) && !empty($v->login_lock_time))
            {
                $v->name = $v->name.' <i class="fas fa-lock"></i>';
            }

            $v->birthday            = Func::dateFormat($v->birthday);
            $v->ipsa                = Func::dateFormat($v->ipsa);
            $v->toesa               = Func::dateFormat($v->toesa);
            $v->save_time           = Func::dateFormat($v->save_time);
            $v->branch_code         = Func::getArrayName($branchArr, $v->branch_code);
            $v->user_rank_cd        = Func::getArrayName($configArr['user_rank_cd'], $v->user_rank_cd);
            $v->user_position_cd    = Func::getArrayName($configArr['user_position_cd'], $v->user_position_cd);

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
     * 부서관리 엑셀 다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function userExcel(Request $request)
    {
        if( !Func::funcCheckPermit("P022") && !isset($request->excel_flag) )
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

        // 기본쿼리
        $users = DB::TABLE("users")->LEFTJOIN("branch", function($join) {
            $join->ON("users.branch_code", "=", "branch.code")->WHERE("branch.save_status", "Y");
        })->SELECT("users.*, branch.branch_name")->WHERE("users.save_status","Y");

        // 부서명 클릭시, 부서 하위 직원 검색
        if(isset($request->customSearch) && $request->customSearch!='0000')
        {
            // 클릭한 부서가 상위부서인 부서를 모두 가져온다.
            $arrBranch = DB::TABLE('branch')->select('code')->where("save_status", "Y")->where("parent_code", $request->customSearch)->get();
            $arrBranch = Func::chungDec(["BRANCH"], $arrBranch);	// CHUNG DATABASE DECRYPT

            $arrayBranch[] = $request->customSearch;
            foreach($arrBranch as $tmp)
            {
                $arrayBranch[] = $tmp->code;
            }

            $users = $users->whereIn("users.branch_code", $arrayBranch);
        }
        

        if(!isset($request->listOrder))
        if(!isset($param['listOrder']))
        {
            $param['listOrder'] = 'branch_code,name';
            $param['listOrderAsc'] = 'asc';            
        }

        $users = $list->getListQuery("users", 'main', $users, $param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($users, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($users);
        
        $file_name    = "직원관리_".date("YmdHis").'_'.Auth::id().'.xlsx';
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
        
		$result = $users->get();
        $result = Func::chungDec(["USERS","BRANCH"], $result);	// CHUNG DATABASE DECRYPT

        // 엑셀헤더
        $excel_header = array('부서', '사번', '이름', '직급', '직책', '내선', '이메일', '생년월일', '입사일', '퇴사일', '저장일');
        $excel_data   = [];

		// 뷰단 데이터 정리.
        $configArr    = Func::getConfigArr();
        $branchArr    = Func::getBranch();
		foreach ($result as $v)
		{
            $array_data = Array(
                !empty($branchArr[$v->branch_code]) ? $branchArr[$v->branch_code] : '',
                $v->id,
                $v->name,
                Func::getArrayName($configArr['user_rank_cd'], $v->user_rank_cd),
                Func::getArrayName($configArr['user_position_cd'], $v->user_position_cd),
                $v->ph34,
                $v->email,
                Func::dateFormat($v->birthday),
                Func::dateFormat($v->ipsa),
                Func::dateFormat($v->toesa),
                Func::dateFormat($v->save_time),
            );
            
            $record_count ++;
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


    /**
     * 직원관리 입력폼 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function userForm(Request $request)
    {
        $id   = $request->input('id');

        $rslt = null;
        if(isset($id))
        {
            $rslt = DB::TABLE("users")->SELECT("*")->WHERE('id', $id)->FIRST();
            $rslt = Func::chungDec(["USERS"], $rslt);	// CHUNG DATABASE DECRYPT

            $rslt->login_lock_time = Func::dateFormat($rslt->login_lock_time);

            $rslt->birthday = Func::dateFormat($rslt->birthday);
            $rslt->ipsa     = Func::dateFormat($rslt->ipsa);
            $rslt->toesa    = Func::dateFormat($rslt->toesa);

            // 현재 프로필 사진이 있는 경우
            if(isset($rslt->profile_img_src))
            {
                // 해당 경로에 파일이 존재하는 경우
                if(Storage::disk('public')->exists($rslt->profile_img_src))
                {
                    // base64 encoding
                    $rslt->profile_img_src = base64_encode(Storage::disk('public')->get($rslt->profile_img_src));
                }
                else
                {
                    $rslt->profile_img_src = null;
                }
            }
        }

        $mode = ( $rslt ) ? 'UPD' : 'INS' ;

        $readonly = '';

        $array_branch = Func::getBranchList();
        $configArr = Func::getConfigArr();

        return view('config.userForm')->with(['mode'=>$mode,'readonly'=>$readonly,'v'=>$rslt, 'array_branch' => $array_branch, 'configArr' => $configArr]);
    }


    /**
     * 직원관리 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function userAction(Request $request)
    {
        $param = $request->input();
        $id    = $param['id'];
        $mode  = $param['mode'];

        // DEFAULT VALUE
        if(!isset($param['passwd']) || strlen($param['passwd'])==0 )
        {
            unset($param['passwd']);
        }
        else
        {
            $param['passwd'] = Bcrypt($param['passwd']);
            $param['passwd_ch_dt'] = date('Ymd');
        }

        // CTI pw
        if(!isset($param['cti_pw']) || strlen($param['cti_pw'])==0 )
        {
            unset($param['cti_pw']);
        }
        else
        {
            $param['cti_pw'] = Func::encrypt($param['cti_pw'], 'CTI_PW_KEY');
        }

        if(!isset($param['cti_auto']))
        {
            $param['cti_auto'] = 'N';
        }

        //$param['passwd'] = "1234";
        //return print_r($param,true);
        if( $mode=="DEL" )
        {
            // 업로드 이미지 처리
            $originImgSrc = DB::TABLE("users")->SELECT("profile_img_src")->WHERE("id", $param['id'])->first();
            $originImgSrc = Func::chungDec(["USERS"], $originImgSrc);	// CHUNG DATABASE DECRYPT

            // 기존 이미지가 존재하는 경우 - 파일 제거
            if (isset($originImgSrc->profile_img_src))
            {
                Storage::disk('public')->delete($originImgSrc->profile_img_src);
            }
            
            $param['profile_img_src'] = null;
            $param['save_status']     = "N";
            $param['del_id']          = Auth::user()->id;
            $param['del_time']        = date('YmdHis');
            $rslt = DB::dataProcess('UPD', 'users', $param, ['id'=>$id]);
        }
        else if( $mode == "LOCK" )
        {
            $UPD['id']                = $id;
            $UPD['passwd']            = $param['passwd'];
            $UPD['passwd_ch_dt']      = $param['passwd_ch_dt'];
            $UPD['login_cnt']         = 0;
            $UPD['login_lock_time']   = null;
            $UPD['worker_id']         = Auth::user()->id;
            $UPD['save_time']         = date('YmdHis');
            $rslt = DB::dataProcess('UPD', 'users', $UPD);
        }
        else
        {
            // 사번(PK) 검사
            if ($mode == 'INS')
            {
                $search = DB::TABLE("users")->WHERE("id", $param['id'])->exists();
                if(!empty($search))
                {
                    return ['result' => 'T', 'msg' => '이미 존재하는 사번입니다.'];
                }
            }
            else
            {
                $search = DB::TABLE("users")->WHERE("id", $param['id'])->WHERERAW("coalesce(login_lock_time,'')!=''")->exists();
                if(!empty($search))
                {
                    return ['result' => 'T', 'msg' => '로그인 차단 해제후 수정 가능합니다.'];
                }
            }

            $param['save_time']  = date('YmdHis');
            $param['workder_id'] = Auth::user()->id;


            // 날짜 형식 수정
            if(isset($param['birthday']))
            {
                $param['birthday'] = str_replace('-', '', $param['birthday']);
            }
            if(isset($param['toesa']))
            {
                $param['toesa'] = str_replace('-', '', $param['toesa']);
            }
            if(isset($param['ipsa']))
            {
                $param['ipsa'] = str_replace('-', '', $param['ipsa']);
            }

            // 업로드 이미지 처리
            $originImgSrc = DB::TABLE("users")->SELECT("profile_img_src")->WHERE("id", $param['id'])->first();
            $originImgSrc = Func::chungDec(["USERS"], $originImgSrc);	// CHUNG DATABASE DECRYPT

            // 업로드할 파일이 존재하는 경우
            if($request->file('profile_img_origin')) 
            {
                // 기존 파일이 존재한다면 기존 파일 제거
                if(isset($originImgSrc) && $originImgSrc->profile_img_src != null)
                {
                    Storage::disk('public')->delete($originImgSrc->profile_img_src);
                }
                
                // 업로드 파일 저장 후 경로 저장
                $param['profile_img_src'] = $request->file('profile_img')->store('users', 'public');
            } 
            else
            {
                // 기존 이미지로 설정 했을 때 기존 이미지가 존재하는 경우, 파일 제거
                if($param['imgDeleteFlag'] == 'Y' && isset($originImgSrc)) 
                {
                    Storage::disk('public')->delete($originImgSrc->profile_img_src);

                    $param['profile_img_src'] = null;
                }

            }

            $rslt = DB::dataProcess($mode, 'users', $param);
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

        $resp = ['result' => $rslt, 'msg' => $msg];
        return $resp;
    }

    /**
     * 내정보관리
     *
     * @return view
     */
    public function myInfo(Request $request)
    {
        $tab = 'myinfo';
        if (isset($request->tab)) {
            $tab = $request->tab;
        }

        $configArr = Func::getConfigArr();
        $branchArr = Func::getBranch();
        
        $rslt = DB::TABLE("users")->SELECT("*")->WHERE('id', Auth::id())->FIRST();
        $rslt = Func::chungDec(["USERS"], $rslt);	// CHUNG DATABASE DECRYPT

        $rslt->login_lock_time = Func::dateFormat($rslt->login_lock_time);

        $rslt->birthday = Func::dateFormat($rslt->birthday);
        $rslt->ipsa     = Func::dateFormat($rslt->ipsa);
        $rslt->toesa    = Func::dateFormat($rslt->toesa);
        
        $rslt->branch_code         = isset($branchArr[$rslt->branch_code]) ? $branchArr[$rslt->branch_code] : '';
        $rslt->user_rank_cd        = isset($configArr['user_rank_cd'][$rslt->user_rank_cd]) ? $configArr['user_rank_cd'][$rslt->user_rank_cd] : '';
        $rslt->user_position_cd    = isset($configArr['user_position_cd'][$rslt->user_position_cd]) ? $configArr['user_position_cd'][$rslt->user_position_cd] : '';

        // 현재 프로필 사진이 있는 경우
        if(isset($rslt->profile_img_src))
        {
            // 해당 경로에 파일이 존재하는 경우
            if(Storage::disk('public')->exists($rslt->profile_img_src))
            {
                // base64 encoding
                $rslt->profile_img_src = base64_encode(Storage::disk('public')->get($rslt->profile_img_src));
            }
            else
            {
                $rslt->profile_img_src = null;
            }
        }

        return view('intranet.myInfo')->with('v', $rslt)->with('tab', $tab);
    }

    /**
     * 내정보관리 Action
     *
     * @return Array Action 결과
     */
    public function myInfoAction(Request $request)
    {
        $param = $request->input();
        $param['id'] = Auth::id();
        $param['worker_id'] = Auth::id();
        $param['save_time'] = date('YmdHis');

        // CTI pw
        if(!isset($param['cti_pw']) || strlen($param['cti_pw'])==0 )
        {
            unset($param['cti_pw']);
        }
        else
        {
            $param['cti_pw'] = Func::encrypt($param['cti_pw'], 'CTI_PW_KEY');
        }

        if(!isset($param['cti_auto']))
        {
            $param['cti_auto'] = 'N';
        }

        // 날짜 형식 수정
        if(isset($param['birthday']))
        {
            $param['birthday'] = str_replace('-', '', $param['birthday']);
        }

        // 업로드 이미지 처리
        $originImgSrc = DB::TABLE("users")->SELECT("profile_img_src")->WHERE("id", $param['id'])->first();
        $originImgSrc = Func::chungDec(["USERS"], $originImgSrc);	// CHUNG DATABASE DECRYPT

        // 업로드할 파일이 존재하는 경우
        if($request->file('profile_img_origin')) 
        {
            // 기존 파일이 존재한다면 기존 파일 제거
            if(isset($originImgSrc->profile_img_src))
            {
                Storage::disk('public')->delete($originImgSrc->profile_img_src);
            }
            
            // 업로드 파일 저장 후 경로 저장
            $param['profile_img_src'] = $request->file('profile_img')->store('users', 'public');
        } 
        else
        {
            // 기존 이미지로 설정 했을 때 기존 이미지가 존재하는 경우, 파일 제거
            if($param['imgDeleteFlag'] == 'Y' && isset($originImgSrc)) 
            {
                Storage::disk('public')->delete($originImgSrc->profile_img_src);

                $param['profile_img_src'] = null;
            }

        }
        
        $rslt = DB::dataProcess('UPD', 'users', $param);

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

        $resp = ['result' => $rslt, 'msg' => $msg];
        return $resp;
    }

    /**
     * 내정보관리 비밀번호 변경 Action
     *
     * @return Array Action 결과
     */
    public function myInfoPwdAction(Request $request)
    {
        $checkPwdMonth = 1; // 최근 사용한 비밀번호 확인할 개월
        $param = $request->input();
        $user = Auth::user();

        // DEFAULT VALUE
        if(!isset($param['changePwd']) || strlen($param['changePwd']) == 0 )
        {
            $rslt = 'N';
        }
        else
        {
            // 현재 비밀번호 체크
            if(Hash::check($param['currentPwd'], $user->passwd))
            {
                // 최근 변경한 비밀번호 검사
                $check = DB::table('users_log')->select('distinct passwd')->where('id', $user->id)->whereRaw("left(save_time,8) >= to_char(current_date + '-".$checkPwdMonth." month'::interval, 'YYYYmmdd')")->get();
                $check = Func::chungDec(["USERS_LOG"], $check);	// CHUNG DATABASE DECRYPT

                foreach ($check as $value) {
                    if(Hash::check($param['changePwd'], $value->passwd))
                    {
                        $rslt = 'R';
                        break;
                    }
                }

                if(!isset($rslt))
                {
                    $user->passwd = Bcrypt($param['changePwd']);
                    $user->passwd_ch_dt = date('Ymd');
                    if ($user->save())
                    {
                        $rslt = 'Y';
                    }
                    else
                    {
                        $rslt = 'N';
                    }
                }
            }
            else
            {
                $rslt = 'F';
            }
        }
        
        if( $rslt=="Y" )
        {
            $msg = "정상처리되었습니다.";
        }
        else if( $rslt=="N" )
        {
            $msg = "처리에 실패하였습니다.";
        }
        else if( $rslt=="F" )
        {
            $msg = "현재 비밀번호를 확인해 주세요.";
        }
        else if( $rslt=='R')
        {
            $msg = "최근 ".$checkPwdMonth."개월 이내에 사용한 비밀번호입니다.";
        }
        else
        {
            $msg = "기타오류";
            if( $rslt )
            {
                $msg.= "(".$rslt.")";
            }
        }

        $resp = ['result' => $rslt, 'msg' => $msg];
        return $resp;
    }

    /**
     * 로그인 기록 공용 리스트 함수
     *
     * @param Request $request
     * @return DataList
     */
    private function setLogDataList($request) {
        $list = new DataList(Array("listName"=>"loginLog","listAction"=>'/'.$request->path()));

        $list->setTabs(Array(), 'All');

        $list->setSearchDate('날짜검색', Array('access_time' => '로그인 날짜'), 'searchDt', 'Y', '', '');
        
        $list->setSearchType('login_success', Vars::$arrayLoginSuccess, '로그인 결과', "onchange=\"listRefresh();\"");

        return $list;
    }

    /**
     * 로그인 기록
     *
     * @param Request $request
     * @return View myInfoLoginLog
     */
    public function myLoginLog(Request $request) {
        $list = $this->setLogDataList($request);
        $list->setlistTitleCommon(Array
        (
            'access_time'       =>     Array('로그인 시간', 0, '', 'center', '', 'access_time'),
            'access_ip'         =>     Array('로그인 IP', 0, '', 'center', '', 'access_ip'),
            'access_agent'      =>     Array('접속 브라우저', 0, '', 'center', '', 'access_agent'),
            'login_success'     =>     Array('로그인 성공 여부', 0, '', 'center', '', 'login_success'),
        ));

        $tempArr = $list->getList();
        $tempArr['isPopup'] = 'Y';
        $tempArr['popupListAction'] = "location.href='/intranet/myinfo';";
        $list = new DataList($tempArr);
        $result = $list->getList();

        return view('intranet.myInfoLoginLog')->with("result", $result);
    }

    /**
     * 로그인 기록 데이터
     *
     * @param Request $request
     * @return Json 로그인 기록 데이터
     */
    public function myLoginLogList(Request $request) {
        
        $list = $this->setLogDataList($request);
        $param = $request->all();

        // 기본쿼리
        $loginLogs = DB::TABLE("users_login_history")->SELECT("*")->WHERE('id', Auth::id());

        if (!isset($param['listOrder'])) {
            $param['listOrder']     = 'seq';
            $param['listOrderAsc']  = 'desc';
        }

        $loginLogs = $list->getListQuery("users_login_history",'main',$loginLogs,$param);
        
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($loginLogs, $request->page, $request->listLimit, 10);
		$result = $loginLogs->get();
        $result = Func::chungDec(["USERS_LOGIN_HISTORY"], $result);	// CHUNG DATABASE DECRYPT

		// 뷰단 데이터 정리.
        $cnt = 0;
		foreach ($result as $v)
		{
            $v->access_time   = Func::dateFormat($v->access_time);
            $v->login_success = isset(Vars::$arrayLoginSuccess[$v->login_success]) ? Vars::$arrayLoginSuccess[$v->login_success] : $v->login_success;

            $browser = $this->getBrowser($v->access_agent);
            $v->access_agent = isset($browser['name']) ? $browser['name'].'['.$browser['version'].']' : '알 수 없는 브라우저';

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
    * 직원접속기록 부서별 직원
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function userBranchDiv(Request $request)
    {
        $member = DB::TABLE("users")->SELECT("id, name")->WHERE("save_status", "Y")->WHERE("branch_code", $request->branch)->GET();
        $member = Func::chungDec(["USERS"], $member);	// CHUNG DATABASE DECRYPT
        $str = "";
        $str.= "<option value='' >직원선택</option>";

        foreach($member as $v)
        {
            $str.= "<option value='".$v->id."' >".$v->name."</option>";
        }
        return $str;
    }

     /**
     * 직원접속 기록 공용 리스트 함수
     *
     * @param Request $request
     * @return DataList
     */
    private function setuserLogDataList(Request $request, $mode='') {
       
        $list = new DataList(Array("listName"=>"loginLog","listAction"=>'/'.$request->path()));
        
        $list->setTabs(Array(), 'All');

        $list->setSearchDate('날짜검색', Array('access_time' => '로그인 날짜'), 'searchDt', 'Y', '', '');
        
        $list->setSearchType('login_success', Vars::$arrayLoginSuccess, '로그인 결과');

        $list->setSearchType('code',Func::getBranch(),'부서선택',"onchange=\"getBranchUser(this.value, 'users-id', '직원선택');\"");
        
        $list->setSearchType('users-id',[],'직원선택');

        $list->setSearchDetail(Array(
                                        'users_login_history.id' => '사번',
                                        'name' => '직원명'));   
                                        
        if( Func::funcCheckPermit("P022") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/config/userlogexcel', 'form_loginLog')", "btn-success");
        }

        return $list;
    }

    /**
     * 직원접속 기록
     *
     * @param Request $request
     * @return View mbInfoLoginLog
     */
    public function userLoginLog(Request $request) {
        $list = $this->setuserLogDataList($request);
        
        $list->setlistTitleCommon(Array
        (
            
            'branch_code'       =>     Array('부서',99, '', 'center', '', 'branch_code'),
            'id'                =>     Array('사번', 1, '', 'center', '', 'id'),
            'name'              =>     Array('직원명', 1, '', 'center', '', 'name'),
            'access_time'       =>     Array('로그인 시간', 0, '', 'center', '', 'access_time'),
            'access_ip'         =>     Array('로그인 IP', 0, '', 'center', '', 'access_ip'),
            'access_agent'      =>     Array('접속 브라우저', 0, '', 'center', '', 'access_agent'),
            'login_success'     =>     Array('로그인 성공 여부', 0, '', 'center', '', 'login_success'),
        ));

        $tempArr = $list->getList();
        $tempArr['isPopup'] = 'Y';
        $list = new DataList($tempArr);
        $array_branch = Func::getBranchList();

        return view('config.userLoginLog')->with("result", $tempArr);                                            
    }

    /**
     * 로그인 기록 데이터
     *
     * @param Request $request
     * @return Json 로그인 기록 데이터
     */
    public function userLoginLogList(Request $request) {
        $request->isDebug = true;
       
        $list = $this->setuserLogDataList($request);
        $param = $request->all();

        // 기본쿼리
        $loginLogs = DB::TABLE("users_login_history")->LEFTJOIN("branch", function($join){$join
                                                        ->ON("users_login_history.branch_code", "=", "branch.code")
                                                        ->WHERE("branch.save_status", "Y");
                                                    })
                                                        ->LEFTJOIN("users", function($join){$join
                                                        ->ON("users_login_history.id", "=", "users.id")
                                                        ->WHERE("users.save_status", "Y");
                                                    })
                                                    ->SELECT("users_login_history.*, users.name, branch.branch_name");                                         

        if (!isset($param['listOrder'])) {
            $param['listOrder']     = 'access_time';
            $param['listOrderAsc']  = 'desc';
        }

        $loginLogs = $list->getListQuery("users_login_history",'main',$loginLogs,$param);
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($loginLogs, $request->page, $request->listLimit, 10, $request->listName);
		$result = $loginLogs->get();
        $result = Func::chungDec(["USERS_LOGIN_HISTORY","BRANCH","USERS"], $result);	// CHUNG DATABASE DECRYPT

		// 뷰단 데이터 정리.
        $cnt = 0;
        $branchArr = Func::getBranch();
		foreach ($result as $v)
		{
            $v->access_time   = Func::dateFormat($v->access_time);
            $v->login_success = isset(Vars::$arrayLoginSuccess[$v->login_success]) ? Vars::$arrayLoginSuccess[$v->login_success] : $v->login_success;

            $browser = $this->getBrowser($v->access_agent);
            $v->access_agent  = isset($browser['name']) ? $browser['name'].'['.$browser['version'].']' : $v->access_agent;//'알 수 없는 브라우저';
            $v->branch_code   = isset($branchArr[$v->branch_code]) ? $branchArr[$v->branch_code] : '';

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
     * 로그인 기록 데이터
     *
     * @param Request $request
     * @return Json 로그인 기록 데이터
     */
    public function userLogExcel(Request $request) {
        if( !Func::funcCheckPermit("P022") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }

        $request->isDebug = true;
      
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list           = $this->setuserLogDataList($request);
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        // 기본쿼리
        $loginLogs = DB::TABLE("users_login_history")->LEFTJOIN("branch", function($join){$join
                                                        ->ON("users_login_history.branch_code", "=", "branch.code")
                                                        ->WHERE("branch.save_status", "Y");
                                                    })
                                                        ->LEFTJOIN("users", function($join){$join
                                                        ->ON("users_login_history.id", "=", "users.id")
                                                        ->WHERE("users.save_status", "Y");
                                                    })                                            
                                                    ->SELECT("users_login_history.*, users.name, branch.branch_name");                                         

        if (!isset($param['listOrder'])) {
            $param['listOrder']     = 'access_time';
            $param['listOrderAsc']  = 'desc';
        }

        $loginLogs = $list->getListQuery("users_login_history",'main',$loginLogs,$param);
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($loginLogs, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($loginLogs);

        $file_name    = "시스템접속로그_".date("YmdHis").'_'.Auth::id().'.xlsx';
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

		$result = $loginLogs->get();
        $result = Func::chungDec(["USERS_LOGIN_HISTORY","BRANCH","USERS"], $result);	// CHUNG DATABASE DECRYPT

        // 엑셀헤더
        $excel_header = array('No', '부서', '사번', '직원명', '로그인 시간', '로그인IP', '접속 브라우저', '로그인 성공 여부');
        $excel_data   = [];
        

		// 뷰단 데이터 정리.
        $cnt          = 1;
        $record_count = 0;
        $branchArr = Func::getBranch();

		foreach ($result as $v)
		{
            $browser = $this->getBrowser($v->access_agent);
            $array_data = Array(
                $cnt,
                !empty($branchArr[$v->branch_code]) ? $branchArr[$v->branch_code] : '',
                $v->id,
                $v->name,
                Func::dateFormat($v->access_time),
                $v->access_ip,
                !empty($browser['name']) ? $browser['name'].'['.$browser['version'].']' : $v->access_agent,
                !empty(Vars::$arrayLoginSuccess[$v->login_success]) ? Vars::$arrayLoginSuccess[$v->login_success] : '알 수 없는 브라우저',
            );

			$cnt ++;
            $record_count ++;
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

    /**
     * 브라우저 정보를 반환하는 함수
     *
     * @param String $userAgent
     * @return Arr 브라우저 정보
     */
    function getBrowser($userAgent)
    { 
        $u_agent = $userAgent; 
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";
    
        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) { $platform = 'linux'; }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent)) { $platform = 'mac'; }
        elseif (preg_match('/windows|win32/i', $u_agent)) { $platform = 'windows'; }

        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) { $bname = 'Internet Explorer'; $ub = "MSIE"; } 
        elseif(preg_match('/Firefox/i',$u_agent)) { $bname = 'Mozilla Firefox'; $ub = "Firefox"; } 
        elseif(preg_match('/Whale/i',$u_agent)) { $bname = 'Naver Whale'; $ub = "Whale"; } 
        elseif(preg_match('/Edg/i',$u_agent)) { $bname = 'Microsoft Edge'; $ub = "Edg"; } 
        elseif(preg_match('/Chrome/i',$u_agent)) { $bname = 'Google Chrome'; $ub = "Chrome"; } 
        elseif(preg_match('/Safari/i',$u_agent)) { $bname = 'Apple Safari'; $ub = "Safari"; } 
        elseif(preg_match('/Opera/i',$u_agent)) { $bname = 'Opera'; $ub = "Opera"; } 
        elseif(preg_match('/Netscape/i',$u_agent)) { $bname = 'Netscape'; $ub = "Netscape"; }
        else { return ''; } 

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){ $version= $matches['version'][0]; }
            else { $version= $matches['version'][1]; }
        }
        else { $version= $matches['version'][0]; }

        // check if we have a number
        if ($version==null || $version=="") {$version="?";}
        return array('userAgent'=>$u_agent, 'name'=>$bname, 'version'=>$version, 'platform'=>$platform, 'pattern'=>$pattern);
    }
}