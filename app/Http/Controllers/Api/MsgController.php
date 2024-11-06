<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;

class MsgController extends Controller
{
	/**
    * 메시지 전달을 위한 임시 api
    *
    */
    public function msg(Request $request)
    {
        $table = 'TMP_MSG';
        // seq
        $ss = DB::table($table)
                ->select("seq")
                ->orderBy('seq', 'desc')
                ->first();
        $seq = 1;
        if(isset($ss))
        {
            $seq = $ss->seq + 1;
        }


        $_DATA['seq']       = $seq;
        $_DATA['status']    = 'N';
        $_DATA['message']  = $request->contents;
        $_DATA['cid']       = $request->channer_id;
        $_DATA['save_time'] = date("YmdHis");

        $result = DB::dataProcess('INS', $table, $_DATA);

        if( $result == "Y" )
        {
            return ["code" => "Y", "message" => "정상 처리"];
        }
        else
        {
            return ["code" => "N", "message" => "데이터 저장 실패"];
        }
    }



}
?>