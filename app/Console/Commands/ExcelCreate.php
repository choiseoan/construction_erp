<?php

namespace App\Console\Commands;

use App\Models\ExcelImport;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use DB;
use Func;
use Vars;
use Auth;
use Log;
use DataList;
use App\Chung\Paging;
use ExcelFunc;
use Cache;
use Illuminate\Support\Facades\Storage;
use Excel;
use App\Chung\ExcelCustomExport;
use App\Http\Controllers\Config\BatchController;

class ExcelCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excel:create {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '예약된 EXCEL 생성';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        $cnt = 0;

        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);
        
        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);

        $table = "excel_down_log"; 

        $excel_check = DB::table($table)
                    ->wherenotnull("rsn_cd")
                    ->where('status', "S")
                    ->count();

        if($excel_check > 0)
        {
            $excel_data = DB::table($table)
                    ->wherenotnull("rsn_cd")
                    ->where('status', "S")
                    ->orderBy('no', 'asc')
                    ->get();

            foreach ($excel_data as $key => $val)
            {
                unset($excel_check2, $excel_count, $request, $controller, $excel_controller);
                
                $excel_check2 = DB::table($table)
                        ->wherenotnull("rsn_cd")
                        ->where('status', "A")
                        ->where('no', $val->no)
                        ->count();

                if($excel_check2 > 0)
                {
                    continue;
                }

                $excel_count = DB::table($table)
                            ->wherenotnull("rsn_cd")
                            ->where('status', "A")
                            ->count();

                if($excel_count < 5)
                {
                    $request               = json_decode($val->request, 1);
                    $request['file_name']  = $val->filename;
                    $request['excel_no']   = $val->no;
                    $request['func_name']  = $request['listName'].'Excel';
                    $request['excel_flag'] = 'ok';
                    $excel_controller      = $request['class'];
                    
                    Func::$excelBatchId = $val->id;
                    $controller = new $excel_controller();
                    $controller->{$request['func_name']}(new Request($request));

                    $cnt++;
                }
                else
                {
                    break;
                }   
            }
        }
        
        //배치 종료 기록
        if($batchLogNo>0)
        {
            $note = $cnt > 0 ? '성공: '.$cnt.'건': '';
            BatchController::setBatchLog($this->argument('batchNo'), $batchLogNo, $note, $stime);
        }
    }

    // 배치로그 시작
    public function startBatchLog($stime)
    {
        $batchNo = $this->argument('batchNo');
        $batchLogNo =  0;
        if(!empty($batchNo))
        {
            $batchLogNo = BatchController::setBatchLog($batchNo, 0, '', $stime);
        }

        return $batchLogNo;
    }
}