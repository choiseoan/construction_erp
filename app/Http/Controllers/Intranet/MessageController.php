<?php
 
namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;  
use Auth;
use Log; 
  
class MessageController extends Controller
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
     * 쪽지함
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function msg(Request $request)
    {
        $result = array();  // return array

        //쪽지함 구분
        if(isset($request->mdiv)) $mdiv=$request->mdiv;
        else $mdiv='recv';
        
        $result['mdiv'] = $mdiv;

        if(isset($request->mtype))  $result['mtype'] = $request->mtype;   

        return view('intranet.msg')->with($result);
    }

    
    /** 
     * 쪽지 list
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function msgList(Request $request)
    { 
        // 사용자 id
        $id         = Auth::user()->id;
        
        //직원정보
        $userList   = Func::getUserList('');

        // 쪽지함 구분 받은(recv) or 보낸(send)
        $div        = $request->mdiv;
        $id_col     = ( $div=="send" ) ? "send_id" : "recv_id" ;
        
        // 페이징 처리 
        $cnt_q = DB::TABLE("MESSAGES")->SELECT("count(*) as cnt")->WHERE($id_col, $id);
        if( isset($request->mtype) )
        {
            $cnt_q = $cnt_q->WHERE('MSG_TYPE',$request->mtype);
        }
        if( $div=="send" )
        {
            $cnt_q = $cnt_q->WHERE('SEND_STATUS',"Y");
        }
        else
        {
            $cnt_q = $cnt_q->WHERE('RECV_STATUS',"Y");
            $cnt_q = $cnt_q->WHERE('RESERVE_TIME',"<=",date("YmdHis"));
        }
        $v = $cnt_q->first();

        $page['total']  = $v->cnt;  //전체페이지수
        $page['num']    = 30;       //페이지당 글 개수
        $page['start']  = (!isset($request->page)||$request->page<0)? 0:$request->page; //현재페이지 시작 글번호
        $page['next']   = ($page['start']+$page['num'])>=$page['total']?null:($page['start']+$page['num']); // 다음페이지 시작 글번호
        $page['before'] = $page['start']-$page['num'];  //이전페이지 시작 글번호
        if($page['total']>0)
        {
            $pageTerm = ($page['start']+1).'-';
            $pageTerm.= $page['next']?$page['next']:$page['total'];
        }
        else
        {
            $pageTerm = '0';
        }
        // --- 페이징처리 
            
        //리스트 쿼리 
        $query = DB::TABLE("MESSAGES")->SELECT("*")->WHERE($id_col,$id);
        if(isset($request->mtype))
        {
            $query = $query->WHERE('MSG_TYPE',$request->mtype);
        }
        if( $div=="send" )
        {
            $query = $query->WHERE('SEND_STATUS',"Y");
        }
        else
        {
            $query = $query->WHERE('RECV_STATUS',"Y");
            $query = $query->WHERE('COALESCE(RESERVE_TIME,SEND_TIME)',"<=",date("YmdHis"));
        }
        $rslt = $query->ORDERBY("NO","DESC")->limit($page['num'])->offset($page['start'])->get();
        $rslt = Func::chungDec(["MESSAGES"], $rslt);	// CHUNG DATABASE DECRYPT

        return view('intranet.msgList')->with(['result'=>$rslt,'page'=>$page,'pageTerm'=>$pageTerm,'mdiv'=>$div,'infoUser'=>$userList]);
    }

    /**
     * 상단 navbar 쪽지 알림 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function msgNav(Request $request)
    { 
        // 사용자 id
        $id = Auth::user()->id;
        
        $cnt['total'] = 0;
        $cnt['M'] = 0;
        $cnt['N'] = 0;
        $cnt['S'] = 0;
        
        $cnt['gray']    = 0;
        $cnt['success'] = 0;
        $cnt['info']    = 0;
        $cnt['warning'] = 0;
        $cnt['error']   = 0;

        $msg_cnt = DB::table("messages")->select(DB::raw('count(*) as cnt'),'msg_type','msg_level')->where('recv_id', $id)
                    ->whereRaw("coalesce(recv_time,'')=''")
                    ->where('send_status',"Y")
                    ->where('recv_status',"Y")
                    ->whereRaw("coalesce(reserve_time,send_time)<='".date("YmdHis")."'")
                    ->groupBy('msg_type','msg_level')->get();

        foreach($msg_cnt as $v)
        {
            if( $v->msg_level=="danger" )
            {
                $v->msg_level = "error";
            }
            $cnt[$v->msg_type]  += $v->cnt;
            $cnt[$v->msg_level] += $v->cnt;
            $cnt['total']       += $v->cnt;
        }
        return json_encode($cnt);    
    }


    /**
     * 쪽지 Form, view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function msgPop(Request $request)
    { 
        // 쪽지 no
        $no = $request->msgNo;
        // 사용자 id
        $id = Auth::user()->id;
       

        if(is_numeric($no))
        {

            $rslt['result'] = DB::TABLE("MESSAGES")->SELECT("*")->WHERE("no",'=',$no)->FIRST();     
            $rslt['result'] = Func::chungDec(["MESSAGES"], $rslt['result']);	// CHUNG DATABASE DECRYPT
            
            // 메세지 읽음 처리
            if( $rslt['result']->recv_time=="" && $request->mdiv=="recv" )
            {
                $recv_time = date("YmdHis");
                $v = DB::dataProcess('UPD', 'MESSAGES', ['RECV_TIME'=>$recv_time], ["NO"=>$no]);
                $rslt['result']->recv_time = $recv_time;
            }
            $send = Func::getUserList($rslt['result']->send_id);
            if(isset($send)) $rslt['result']->send_name = $send->name;
            $recv = Func::getUserList($rslt['result']->recv_id);
            if(isset($recv)) $rslt['result']->recv_name = $recv->name;
            
            //메세지 타입에 따른 style
            switch ($rslt['result']->msg_type)
            {
                case "N":
                    $rslt['msg_card']       = 'warning';
                    $rslt['msg_fas']        = 'bullhorn';
                    $rslt['msg_type_str']   = '공지사항';
                    break;
                case "S":
                    $rslt['msg_card']       = 'danger';
                    $rslt['msg_fas']        = 'bell';
                    $rslt['msg_type_str']   = '시스템알림';
                    break;
                default:
                    $rslt['msg_card']       = 'info';
                    $rslt['msg_fas']        = 'envelope';
                    $rslt['msg_type_str']   = '메세지';
            }

            // 다중링크
            if(!empty($rslt['result']->json_link))
                $rslt['result']->json_link = json_decode($rslt['result']->json_link);

            if( $rslt['result']->msg_level=="error" )
            {
                $rslt['result']->msg_level = "danger";
            }
            $rslt['msg_card'] = $rslt['result']->msg_level;
            if($rslt['result']->send_id == $id && $request->mdiv == 'send') {
                $rslt['msgDiv']         = "SEND_MSG";    
            }
            else {
                $rslt['msgDiv']         = "RECV";
            }

            // 함께받은사람
            $with = DB::TABLE("MESSAGES")
                        ->WHERE("MSG_TYPE", "M")
                        ->WHERE("TITLE", $rslt['result']->title)
                        ->WHERE("SEND_ID", $rslt['result']->send_id)
                        ->WHERE("SEND_TIME", $rslt['result']->send_time);
            $with_cnt = $with->COUNT();
            if($with_cnt>1)
            {
                $with = $with->GET();
                $with = Func::chungDec(["MESSAGES"], $with);	// CHUNG DATABASE DECRYPT

                $with_str = "";
                foreach($with as $with_seq => $with_data)
                {
                    if($with_data->recv_id!=$id)
                    {
                        $with_str.= Func::getUserList($with_data->recv_id)->name." (".$with_data->recv_id.") / ";
                    }
                }

                $rslt['with'] = substr($with_str, 0, -2);
            }
        }
        else
        {
            //직원정보
            $rslt['userList']   = Func::getUserList('');
            //부서정보
            $rslt['branchList'] = Func::getBranchList();
            $rslt['msgDiv'] = "SEND";

            $rslt['to'] = isset($request->to) ? $request->to : "" ;

            $rslt['re_title'] = (isset($request->re_title) && $request->re_title!='') ? 'RE : '.$request->re_title : "" ;
        }
        
        return view('intranet.msgPop')->with($rslt);
    }

    /**
     * 쪽지 action
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function msgAction(Request $request)
    {
        $val    = $request->input();
        // 쪽지 구분 - 메세지
        $request->merge(['MSG_TYPE' =>$val['msg_type']]);
        $recv_branch=array();
        // 전송 ID 일괄 발송 체크 
        if(isset($val['recv_id']))
        {
            if(is_array($val['recv_id']))
            {
                $recv_id = $val['recv_id'];
            }
            else
            {
                $recv_id[]  = $val['recv_id'];   
            }
            foreach($recv_id as $i => $id)
            {
                if(strpos($id, 'bch_') !== false)
                {
                    array_push($recv_branch,str_replace("bch_","",$id));  
                    unset($recv_id[$i]);
                }
            }
        }
        //부서별 전송 데이터 체크
        if(is_array($recv_branch) && count($recv_branch)>0)
        {
            // 해당 부서 직원 검색  (하위부서포함X)
            $users = DB::TABLE("USERS")->SELECT("*")->WHERE('SAVE_STATUS','Y')->WHEREIN('BRANCH_CODE',$recv_branch)->GET();
            $users = Func::chungDec(["USERS"], $users);	// CHUNG DATABASE DECRYPT

            foreach($users as $u)
            {
                if(!in_array($u->id,$recv_id)) $recv_id[]  = $u->id;
            }
        }

        // 최종 전송 ID array  
        foreach($recv_id as $recv)
        {
            $request->merge(['recv_id' =>$recv,'recv_time'=>null]);
            //메세지 발송
            //$rs = event(new \App\Events\SendMessage($request));
            $rs = Func::sendMessage($request);
        }

        return $rs;
    }
    
    /**
    * 쪽지삭제
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function lumpMsgAction(Request $request)
    {
        // nos말고 no만 들어올 수 있다.
        if( !isset($request->nos) && isset($request->no) )
        {
            $request->nos = Array();
            $request->nos[0] = $request->no;
        }
        if( !isset($request->nos) || sizeof($request->nos)==0 )
        {
            $array_result['rst'] = "E";
            $array_result['msg'] = "선택된 메세지가 없습니다.";
            return $array_result;
        }
        $id = Auth::user()->id;

        DB::beginTransaction();
        $error_msg = "";

        // 읽음표시
        if( $request->mode=='RECV' )
        {
            $NOS = DB::TABLE("MESSAGES")->SELECT("NO")->whereIn('NO',$request->nos)->WHERE('RECV_ID',$id)->WHERENULL('RECV_TIME')->GET()->TOARRAY();
            if( $NOS )
            {
                $recv_time = date("YmdHis");
                foreach( $NOS as $v )
                {
                    $rslt = DB::dataProcess('UPD', 'MESSAGES', ['RECV_TIME'=>$recv_time], ['NO'=>$v->no]);
                    if($rslt != 'Y')
                    {
                        break;
                    }
                }
            }
            else
            {
                $error_msg = "읽음처리 할 쪽지가 없습니다.\n";
            }
        }
        // 삭제 - 받은쪽지
        else if( $request->mode=='RDEL' )
        {
            $NOS = DB::TABLE("MESSAGES")->SELECT("NO")->whereIn('NO',$request->nos)->WHERE('RECV_ID',$id)->GET()->TOARRAY();
            if( $NOS )
            {
                $recv_time = date("YmdHis");
                foreach( $NOS as $v )
                {
                    $rslt = DB::dataProcess('UPD', 'MESSAGES', ['RECV_STATUS'=>'N'], ['NO'=>$v->no]);
                    if($rslt != 'Y')
                    {
                        break;
                    }
                }
            }
            else
            {
                $error_msg = "삭제 할 받은쪽지가 없습니다.\n";
            }
        }
        // 삭제 - 보낸쪽지
        else if( $request->mode=='SDEL' )
        {
            $NOS = DB::TABLE("MESSAGES")->SELECT("NO")->whereIn('NO',$request->nos)->WHERE('SEND_ID',$id)->GET()->TOARRAY();
            if( $NOS )
            {
                $recv_time = date("YmdHis");
                foreach( $NOS as $v )
                {
                    $rslt = DB::dataProcess('UPD', 'MESSAGES', ['SEND_STATUS'=>'N'], ['NO'=>$v->no]);
                    if($rslt != 'Y')
                    {
                        break;
                    }
                }
            }
            else
            {
                $error_msg = "삭제 할 보낸쪽지가 없습니다.\n";
            }
        }


        if( isset($rslt) && $rslt=='Y' )
        {
            DB::commit();

            $array_result['rst'] = "Y";
            $array_result['msg'] = "처리에 성공하였습니다.";
        }   
        else
        {
            DB::rollback();

            $array_result['rst'] = "E";
            $array_result['msg'] = isset($error_msg) ? $error_msg : "처리에 실패하였습니다." ;
        }

        return $array_result;
    }
}