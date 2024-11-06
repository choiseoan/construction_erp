<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Auth;
use Log;
use Cache;

class IntranetController extends Controller
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

	public function mainContent(Request $request)
    {
        // 공지사항 게시판
        $notice  = DB::table("board")->select(["NO","TITLE","SAVE_TIME","SAVE_ID","CLICK"])->where("save_status","Y")->where("div",'notice')->orderBy("save_time","desc")->LIMIT(5)->GET();
        $notice  = Func::chungDec(["board"], $notice);	// CHUNG DATABASE DECRYPT

        // 읽지않은 메세지
        $message = DB::table("messages")->select("*")->where("RECV_ID",Auth::user()->id)->where('recv_status',"Y")->whereNull('RECV_TIME')->where('COALESCE(RESERVE_TIME,SEND_TIME)',"<=",date("YmdHis"))->orderBy("NO","DESC")->limit(5)->get();
        $message = Func::chungDec(["messages"], $message);	// CHUNG DATABASE DECRYPT

        return view('intranet.mainContent')->with('notice',$notice)->with('message',$message);
    }


	public function setHeadMenu(Request $request)
    {
        $id = Auth::user()->id;
        $code  = $request->input('code');
        $mode  = $request->input('mode');

        // 권한 검사

        if( $mode=="ADD" )
        {
            $mymn = DB::TABLE('CONF_MENU_HEAD')->SELECT(DB::raw('min(seq) as min_seq, max(seq) as max_seq, count(*) as cnt'))->WHERE('user_id',$id)->first();
            if( $mymn->cnt>=8 )
            {
                $rslt = DB::dataProcess('DEL', 'CONF_MENU_HEAD', [], ['user_id'=>$id,'seq'=>$mymn->min_seq]);
            }
            $seq = $mymn->max_seq + 1;
           
            $rslt = DB::dataProcess('UST', 'CONF_MENU_HEAD', ['user_id'=>$id,'menu_cd'=>$code,'seq'=>$seq]);
        }
        else if( $mode=="DEL" )
        {
            $rslt = DB::dataProcess('DEL', 'CONF_MENU_HEAD', [], ['user_id'=>$id,'menu_cd'=>$code]);
        }
        return $rslt;
    }

	public function getHeadMenu(Request $request)
    {
        $tmp = Func::getMyMenu();
        $array_head_menu = $tmp['HEAD'];
        
        $rslt = "";
        if( sizeof($array_head_menu)>0 )
        {
            foreach( $array_head_menu as $val )
            {
                $rslt.= '<li class="nav-item d-none d-sm-inline-block">';
                $rslt.= '<a href="'.$val['link'].'" class="nav-link">'.$val['name'].'</a>';
                $rslt.= '</li>';
            }
        }
        return $rslt;
    }
}