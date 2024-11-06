<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Func;
use Log;
use DB;
use Vars;
use Auth;
use Redirect;
use DataList;
use Validator;
use Carbon;
use Artisan;
use Storage;
use Excel;
use DateTime;
use ExcelFunc;
use FastExcel;
use App\Chung\Sms;
use App\Chung\Paging;

class QuickAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Quick:action {--flag=}{--opt=}{--opt2=}';
    //                      php artisan Quick:action --flag=devTest --opt=e --opt2=value
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '1회성 처리';

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
     * @return mixed
     */
	public function handle()
	{
		$flag = $this->option('flag');
		$opt = $this->option('opt');
		$opt2 = $this->option('opt2');

		if($flag == 'encryptDecrypt') {
			self::encryptDecrypt($opt, $opt2);
		} else if($flag == 'unixtime'){
			self::unixtime($opt);
		} else if($flag == 'timediff'){
			self::timediff($opt, $opt2);
		} else if($flag == 'printQuery'){
			self::printQuery();
		} else if($flag == 'devTest'){
			self::devTest($opt);
		} else if($flag == 'devTest2'){
			self::devTest2($opt);
		} else if($flag == 'devTest3'){
			self::devTest3();
		} else if($flag == 'devTest4'){
			self::devTest4();
		}
	}

    // 암복호화 변환: php artisan Quick:action --flag=encryptDecrypt --opt='e' --opt2='1255964400'
	public function encryptDecrypt($mode, $value)
	{
		if($mode == 'e')
		{
			echo Func::encrypt($value, 'ENC_KEY_SOL');
		}
		else
		{
			echo Func::decrypt($value, 'ENC_KEY_SOL');
		}
	}

    // unix 시간 변환: php artisan Quick:action --flag=unixtime --opt='1255964400'
    public function unixtime($value)
    {
        $time = Carbon::createFromTimestamp($value);
        echo $time;
    }

    // 시간계산: php artisan Quick:action --flag=timediff --opt=20180725 --opt2=20200101
    public function timediff($value, $value2)
	{
        $from = new DateTime($value);
        $to = new DateTime($value2);

        echo $from->diff($to)->days;

        //echo date_diff($from, $to)->days;
	}
        
    // 쿼리찍어보기 get 이나 first 전에 써야됨
    // php artisan Quick:action --flag=printQuery
    public function printQuery()
	{
        $_query = DB::TABLE("");
        echo Func::printQuery($_query);
	}
        
    // 테스트용1
    // php artisan Quick:action --flag=devTest1
    public function devTest1()
	{
	}
        
    // 테스트용2
    // php artisan Quick:action --flag=devTest2
    public function devTest2()
	{
	}
}