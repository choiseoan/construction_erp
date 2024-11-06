<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Log;
use Func;
use Auth;

class CalendarController extends Controller
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
     * 영업일관리 메인화면
     *
     * @param  Void
     * @return view
     */
	public function calendar()
    {
        return view('config.calendar');
    }

    /**
     * 영업일관리 휴일 리스트 (ajax 부분)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function calendarHoliday(Request $request)
    {  
        $param = $request->input();

        $year_month = $param['y'].'-'.$param['m'];

        $result = DB::TABLE("DAY_CONF")->SELECT("DAY")->WHERERAW("(day like '".$year_month."%')")->GET();
        
        return $result;
    }

    /**
     * 영업일관리 휴일 추가 (ajax 부분)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function calendarInsert(Request $request)
    {  
        $param['holiday'] = $request->input('holiday');
        $result  = DB::TABLE("DAY_CONF")->SELECT("DAY")->WHERE("DAY", $param['holiday'])->FIRST();

        if($result)
        {
            $param['type'] = 'N';
            $rslt = DB::table('DAY_CONF')->WHERE('day', $param['holiday'])->DELETE();
        }
        else
        {
            $param['type'] = 'Y';   
            $day_ymd = explode('-', $param['holiday']);
            $year = $day_ymd[0];
            $month = $day_ymd[1];
            $day = $day_ymd[2];

            
            // DAY_CONF TYPE 추출
            $target_yoil = date('w', strtotime($year.'-'.$month.'-'.$day));
            // 일요일
            if($target_yoil == 0)
            {
                $_DATA['type'] = '03';
            }
            // 토요일
            elseif($target_yoil == 6)
            {
                $_DATA['type'] = '02';    
            }
            // 공휴일(평일)
            else
            {
                $_DATA['type'] = '04';
            }

            $_DATA['day'] = $param['holiday'];
            $_DATA['save_status'] = 'Y';
            $_DATA['save_id'] = Auth::user()->id;
            $_DATA['save_time'] = date('YmdHis');

            $rslt = DB::dataProcess('INS', 'DAY_CONF', $_DATA);
        }

        if($rslt)
        {
            return $param;
        }
        else
        {
            return '통신오류가 발생하였습니다.';
        }
       
    }

   
}
