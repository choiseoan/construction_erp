<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use Hash;

class LoginController extends Controller
{
	/**
    * 유저검색 후 return 처리
    *
    */
    public function permit(Request $request)
    {
        log::debug(__METHOD__);

        $chung = ['chung', ];

        $v = DB::table("users")->select("*")->where('id', $request->id)->where("save_status","Y")->first();

        if(isset($v) && Hash::check($request->password, $v->passwd) && (in_array($request->id, $chung) || (isset($v->separate_ok_date) && $v->separate_ok_date == date('Y-m-d')))){
            $_DATA['result'] = true;
            $_DATA['id'] = $request->id;
            $_DATA['passwd'] = $request->password;
        } else {
            $_DATA['result'] = false;
        }

        return $_DATA;
    }



}
?>