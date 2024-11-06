<?php
 
namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;  
use Auth;
use Log; 
use Storage;

class DataShareController extends Controller
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
     * 데이터 공유 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function dataShare(Request $request)
    {        
        $branchInfo = Func::getBranchById(Auth::id());
        //$branchInfo->code = '607';// 영업기획팀
        $branch = $this->getBranchShareInfo($branchInfo->code);
        
        $arrayFilesMon = null;
        $arrayFilesDay = null;
        if($branch!=null)
        {
            foreach($branch as $key=>$v)
            {
                if($v[1]=='M')
                {
                    $arrayFilesMon[$key] = $v[0];
                }
                else if($v[1]=='D')
                {
                    $arrayFilesDay[$key] = $v[0];
                }
            }
        }
        Log::debug($arrayFilesMon);
        Log::debug($arrayFilesDay);
        return view('intranet.dataShare')
                ->with('branch', $branchInfo)
                ->with('arrayFilesMon', $arrayFilesMon)
                ->with('arrayFilesDay', $arrayFilesDay)
                ;
    }

    private function getBranchShareInfo($branchCd)
    {
        // 부서별 다운로드 파일 세팅
        $arrayBrnachFiles = [
        ];

        // 전산팀은 모두 나오게 변경한다.
        if($branchCd=='P99')
        {
            $arrayTemp = null;
            foreach($arrayBrnachFiles as $bcd=>$arrayData)
            {
                if($arrayTemp==null)
                {
                    $arrayTemp = $arrayData;
                }
                else 
                {
                    $arrayTemp = array_merge($arrayTemp, $arrayData);
                }
            }
            return $arrayTemp;
        }
        else 
        {
            if(isset($arrayBrnachFiles[$branchCd]))
            {
                return $arrayBrnachFiles[$branchCd];
            }
            else 
            {
                return null;
            }
        }
    }

    /**
     * 파일 다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function dataShareDownload(Request $request)
    {
        $dir = env('DATA_SHARE_DIR');

        $param    = $request->input();
        Log::debug($param);

        $branchInfo = Func::getBranchById(Auth::id());
        //$branchInfo->code = '607';// 영업기획팀
        $branch = $this->getBranchShareInfo($branchInfo->code);

        if(!isset($branch[$param['sel_file']]))
        {
            return "<script>alert('다운받을 수 있는 파일이 아닙니다'); history.back();</script>";
        }

        $f = $branch[$param['sel_file']];
        $dt = str_replace("-", "", $param['sel_date']);

        // 파일명 세팅
        $origin = $dir."/".$f[2]."/".$dt."_".$param['sel_file'].".".$f[3];        
        $target = $dt."_".$f[0].".".$f[3];
        Log::debug($origin);
        if(!file_exists($origin))
        {
            return "<script>alert('선택한 파일을 찾을 수 없습니다. 관리자에게 문의해 주세요.'); history.back();</script>";
        }       
        
        return response()->download($origin, $target);
    }
}