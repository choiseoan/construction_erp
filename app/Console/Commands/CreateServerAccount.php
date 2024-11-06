<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;
use App\Http\Controllers\Config\BatchController;

class CreateServerAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work:CreateServerAccount {strLen? : 문자열 길이} {type? : 문자형태}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '서버 계정 및 비밀번호 랜덤 생성';

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
        //echo self::randFunc(10, "NaAS");            // PASSWD 생성용
        //echo self::randFunc(10, "NaA")."\n";        // ID 생성용
        //echo self::randFunc(16, "NaAS");            // PASSWD 생성용
        echo "난수 생성 결과 : [".self::randFunc($this->argument('strLen'), $this->argument('type'))."]\n";            // PASSWD 생성용
    }

    /**
     * 난수 생성함수
     * Argv1 : 난수길이
     * Argv2 : 난수Char 복수type으로 설정 가능
     *         N -> 숫자
     *         a -> 영문 소문자
     *         A -> 영문 대문자
     *         S -> 특수문자
     */
    private static function randFunc($randLength, $type)
    {
        $chars = $randStr = "";

        // 난수 생성대상 문자열 생성
        for($i=0;$i<strlen($type);$i++)
        {
            $subType = substr($type, $i, 1);

            // 숫자 배열 추가
            if($subType=="N")
            {
                for($j=48; $j<=57; $j++)
                {
                    $chars.=chr($j);
                }
            }
            // 영문 소문자
            else if($subType=="a")
            {
                for($j=97; $j<=122; $j++)
                {
                    $chars.=chr($j);
                }
            }
            // 영문 대문자
            else if($subType=="A")
            {
                for($j=65; $j<=90; $j++)
                {
                    $chars.=chr($j);
                }
            }
            // 특수문자
            else if($subType=="S")
            {
                for($j=33; $j<=126; $j++)
                {
                    // 숫자 배열 제외
                    if($j>=48 && $j>=57) continue;
                    // 영문 소문자 배열 제외
                    if($j>=97 && $j>=122) continue;
                    // 영문 대문자 배열 제외
                    if($j>=65 && $j>=90) continue;

                    $chars.=chr($j);
                }
            }
        }

        for($i=0; $i<$randLength; $i++) $randStr .= substr($chars, rand(0, strlen($chars)-1), 1);
        return $randStr;
    }
}