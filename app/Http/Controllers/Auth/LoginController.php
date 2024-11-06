<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use DateTime;

class LoginController extends Controller
{
    //use Notifiable;
    //use AuthenticatesUsers;

    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
    }
    public function username()
    {
        return 'id';
    }



    public function login(Request $request)
    {
        $setLoginCnt = 5;
        $setPwChAlertDate1 = 75;
        $setPwChAlertDate2 = 85;
        $setPwChLockDate = 90;

        $v = $request->only('id', 'password');
        if( !$v['id'] || !$v['password']  )
        {
            return redirect("/")->with("error", '아이디, 패스워드를 정확히 입력해 주세요.');
        }

        $v['save_status'] = "Y";
        $user = User::where(['id' => $v['id'], 'save_status' => 'Y'])->first();
        $user = Func::chungDec(["USERS"], $user);	// CHUNG DATABASE DECRYPT

        if(isset($user))
        {
            // 로그인 시도 기록
            $DATA['id']             = $user->id;
            $DATA['branch_code']    = $user->branch_code;
            $DATA['access_ip']      = $request->ip();
            $DATA['access_agent']   = $request->userAgent();
            $DATA['access_time']    = date("YmdHis");
            $DATA['seq']            = $this->getSeq($user->id);
        }

        // 로그인 차단 확인
        if(isset($user) && $user->login_cnt >= $setLoginCnt)
        {
            $DATA['login_success'] = 'N';
            DB::dataProcess('INS', 'USERS_LOGIN_HISTORY', $DATA);
            return redirect("/")->with("error", '로그인에 5회 이상 패스워드 오류로 인해 로그인이 불가능합니다.\\n관리자에게 문의해 주세요.');
        }

        // 로그인 차단 확인
        if(isset($user) && !empty($user->login_lock_time))
        {
            $DATA['login_success'] = 'N';
            DB::dataProcess('INS', 'USERS_LOGIN_HISTORY', $DATA);
            return redirect("/")->with("error", '계정 잠금으로인해 로그인이 불가능합니다.\\n관리자에게 문의해 주세요.');
        }

        // 퇴사일 확인
        if( !empty($user->toesa)  )
        {
            $DATA['login_success'] = 'N';
            DB::dataProcess('INS', 'USERS_LOGIN_HISTORY', $DATA);
            return redirect("/")->with("error", '존재하지 않는 계정입니다.\\n관리자에게 문의해 주세요.');
        }

        if( Auth::attempt($v) )
        {
            // 아이피 확인
            if(isset($user->access_ip) && !empty($user->access_ip))
            {
                $checkIp = explode(',', str_replace(' ', '', $user->access_ip));
                if(!in_array($request->ip(), $checkIp))
                {
                    $DATA['login_success'] = 'I';
                    DB::dataProcess('INS', 'USERS_LOGIN_HISTORY', $DATA);

                    Auth::logout();
                    $msg = '허가된 ip가 아닙니다.\\n관리자에게 문의바랍니다.';
                    return redirect('/')->with('error', $msg);
                }
            }

            // 패스워드 변경일 확인
            $pwdChDt= new DateTime(isset($user->passwd_ch_dt) ? $user->passwd_ch_dt : $user->save_time);
            $today  = new DateTime();
            $diff   = $pwdChDt->diff($today)->days; // 날짜차이
            $DATA['login_success'] = 'Y';

            if($diff)
            {
                switch ($diff) 
                {
                    // 패스워드 변경일과 변경일 일수차이가 90일 이상인 경우
                    case $diff >= $setPwChLockDate:
                        // 계정잠금추가
                        $user->login_lock_time = date('YmdHis');
                        $user->worker_id = "SYSTEM";
                        $user->save();

                        $DATA['login_success'] = 'P';
                        DB::dataProcess('INS', 'USERS_LOGIN_HISTORY', $DATA);
                        $msg = "비밀번호를 변경한지 ".$setPwChLockDate."일이 지났습니다.\\nIT보안/지원파트로 잠금 해제 요청해 주세요.";
                        Auth::logout();
                        return redirect("/")->with("error", $msg);
                        break;
                    // alertdate
                    case $diff >= $setPwChAlertDate2:
                        DB::dataProcess('INS', 'USERS_LOGIN_HISTORY', $DATA);
                        // 패스워드 변경일과 변경일 일수차이가 85일 이상인 경우
                        // main으로 넘기고 변경 form 불러오기
                        $msg = "비밀번호를 변경한지 ".$diff."일이 지났습니다.\\n비밀번호를 변경하여 주시기 바랍니다.\\n(미변경 시, ".($setPwChLockDate-$diff)."일 이후 계정은 잠금 상태로 변경되며 이후에는 IT보안/지원파트로 잠금 해제 요청이 필요합니다..)";
                        return redirect('/intranet/main')->with("warning", $msg);
                        break;
                    // alertdate
                    case $diff >= $setPwChAlertDate1:
                        DB::dataProcess('INS', 'USERS_LOGIN_HISTORY', $DATA);
                        // 패스워드 변경일과 변경일 일수차이가 75일 이상인 경우
                        // main으로 넘기고 변경 form 불러오기
                        $msg = "비밀번호를 변경한지 ".$diff."일이 지났습니다.\\n비밀번호를 변경하여 주시기 바랍니다.\\n(미변경 시, ".($setPwChLockDate-$diff)."일 이후 계정은 잠금 상태로 변경됩니다.)";
                        return redirect('/intranet/main')->with("warning", $msg);
                        break;
                }
            }
            
            if( $v['password']=="chung123!" )
            {
                $msg = "초기 비밀번호로 로그인하셨습니다.\\n비밀번호를 변경해 주세요.";
                return redirect('/intranet/main')->with("warning", $msg);
            }

            
            $user->login_cnt    = null;
            $user->last_login   = date('YmdHis');
            $user->worker_id = "SYSTEM";
            $user->save();

            DB::dataProcess('INS', 'USERS_LOGIN_HISTORY', $DATA);
            return redirect('/intranet/main');
        }
        else
        {
            $msg = '아이디와 패스워드를 확인해주세요.';
            if(isset($user))
            {
                $DATA['login_success'] = 'N';
                
                $user->login_cnt = $user->login_cnt ? $user->login_cnt + 1 : 1;
                if($user->login_cnt >= $setLoginCnt) {
                    $DATA['login_success'] = 'L';
                    $user->login_lock_time = date('YmdHis');
                }
                
                DB::dataProcess('INS', 'USERS_LOGIN_HISTORY', $DATA);
                $user->worker_id = "SYSTEM";
                $user->save();

                $msg .= '\\n('.$user->login_cnt.'번 로그인 실패, 총 '.$setLoginCnt.'번 실패시 로그인 불가)';
            } 
            return redirect("/")->with("error", $msg);
        }
    }

    public function logout(Request $request)
    {
        $userInfo = Auth::user();

        if(isset($userInfo))
        {
            // 로그아웃 시도 기록
            $DATA['id']             = $userInfo->id;
            $DATA['branch_code']    = $userInfo->branch_code;
            $DATA['access_ip']      = $request->ip();
            $DATA['access_agent']   = $request->userAgent();
            $DATA['login_success']  = 'O';
            $DATA['access_time']    = date("YmdHis");
            $DATA['seq']            = $this->getSeq($userInfo->id);


            DB::dataProcess('INS', 'USERS_LOGIN_HISTORY', $DATA);
        }

        Auth::logout();
        return redirect("/");
    }

    private function getSeq($id)
    {
        // seq No 가져오기
        $maxSeq = DB::TABLE("users_login_history")->WHERE("id", $id)->max('seq');
        if(empty($maxSeq)) 
        {
            $maxSeq = 1;
        }
        else
        {
            $maxSeq+= 1;
        }
        return $maxSeq;
    }
}
