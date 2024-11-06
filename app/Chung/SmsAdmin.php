<?php
namespace App\Chung;

use DB;
use Log;
use Func;

class SmsAdmin
{

    /**
 	* 관리자에게 문자발송(주의 : 새벽에도 발송됨)
 	* @param $message 메세지
 	* @param $confCode 종합코드에 등록된 코드
 	* @return 발송결과 YN
 	*/
 	public static function sendSmsConf($message, $confCode='sms_alarm_ph_cd')
 	{		
        $sender = '15442525';

        if(!$confCode)
            return 'N';

        $configs = DB::TABLE("CONF_CODE")->SELECT("NAME, CODE, CAT_CODE")->WHERE('save_status', 'Y')->WHERE('CAT_CODE', $confCode)->ORDERBY('CAT_CODE')->ORDERBY('CODE_ORDER')->GET();
        foreach($configs as $config)
        {
            $receiver = $config->name;
            if( substr($receiver,0,3)!="010" )
            {
                continue;
            }
            echo $receiver."\t";
    
            //문자발송
            $smsReturn = SmsAdmin::sendSms($sender, $receiver, $message);
            if( $smsReturn=='Y' )
            {
                echo "SUCCESS\n";
            }
            else
            {
                Log::debug("관리자문자발송 에러 : ".$sender."|".$receiver."|".$message);
                echo "ERROR\n";
            }
        }

        return 'Y';
    }
    
    /**
 	* 관리자에게 실제 문자발송(주의:새벽에도 발송됨)
    * @param $sender 보내는 번호
    * @param $receiver 받는 번호
 	* @param $message 메세지
    * @param $reserve_time 예약시간('YYYYMMDDHHIISS')
 	* @return 발송결과 true, false
 	*/
 	public static function sendSms($sender, $receiver, $message, $reserve_time='')
 	{	

        // 받는번호
        $receiver = str_replace("-", "", $receiver);
        $receiver = str_replace(" ", "", $receiver);

        $msgByte = 0;
        if( mb_detect_encoding($message, ['UTF-8','EUC-KR'], true)=="UTF-8" )
        {
            $msgByte = mb_strwidth(iconv("UTF-8","EUC-KR", $message), "EUC-KR");
        }
        else 
        {
            $msgByte = mb_strwidth($message, "EUC-KR");
            Log::debug('EUC-KR : SMS_CHECK '.$msgByte.':'.$message);
        }

        // LMS 발송
        if( $msgByte > 90)
        {
            if($reserve_time == '')
            {
                $reserve_time = date('YmdHis');
            }

            $seq_table = 'MMS_ADMIN_MSG_SEQ';
            $sms_lms_div = 'L';
            $lms = DB::table("SYSIBM.SYSDUMMY1")->SELECT("(NEXTVAL FOR MMS_ADMIN_MSG_SEQ) AS no")->first();

            // 문자 컨텐츠 세팅
            $_DATA = [
                "MSGKEY"        =>  $lms->no,
                "SUBJECT"       =>  "제목없음",
                "PHONE"	        =>	$receiver,
                "CALLBACK"	    =>	$sender,
                "STATUS"        =>  '0',
                "REQDATE"   	=>	$reserve_time,
                "MSG"           =>  $message,
                "FILE_CNT"		=>	'0',
                "TYPE"		    =>	'0',
                "ETC1"          =>  '',  
                "ETC2"          =>  NULL,
            ];

            $msg_no = $lms->no;
            $rslt = DB::dataProcess("INS", "MMS_ADMIN_MSG", $_DATA);
        }
        // SMS 발송
        else
        {   
            // 예약시간 형태 변경
            if($reserve_time == '')
            {
                $reserve_time = date('Y-m-d-H.i.s');
                //$reserve_time = date('YmdHis');
            }
            else
            {
                $reserve_time = date('Y-m-d-H.i.s', strtotime($reserve_time));
            }

            $seq_table = 'SC_ADMIN_TRAN_SEQ';
            $sms_lms_div = 'S';
            $sms = DB::table("SYSIBM.SYSDUMMY1")->SELECT("(NEXTVAL FOR SC_ADMIN_TRAN_SEQ) AS no")->first();
            

            // SMS 데이터 세팅
            $_DATA = [
                "TR_NUM"        =>	$sms->no,
                "TR_SENDDATE"   =>	$reserve_time,
                "TR_SENDSTAT"	=>	'0',
                "TR_MSGTYPE"	=>	'0',
                "TR_PHONE"	    =>	$receiver,
                "TR_CALLBACK"	=>	$sender,
                "TR_MSG"        =>  $message,
                "TR_ETC1"       =>  '',     
                "TR_ETC2"	    =>	NULL,
            ];
            
            $msg_no = $sms->no;
            $rslt = DB::dataProcess("INS", "SC_ADMIN_TRAN", $_DATA);
        }

        // 발송결과 리턴
        if( $rslt=="Y")
        {
            return 'Y';
        }
        else
        {
            return 'N';
        }         
    }
}
?>