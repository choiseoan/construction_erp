<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;

class MemberController extends Controller
{
	/**
    * 기업 검색 후 return url 처리
    *
    */
    public function member(Request $request)
    {
        $result = DB::table("users")->select("*")->get();

        $arrayConf = [];
        $result = Func::chungDec(["users"], $result);
        foreach($result as $value)
        {
            $arrayConf[$value->id] = $value->name;
        }
        return $arrayConf;
    }
}
?>