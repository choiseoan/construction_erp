<?
header("Content-Type: application/json");
$request_data = file_get_contents('php://input');
$request_data = str_replace("\n", "<br>", $request_data);

$logPath = "/home/logs/";

if(!isset($_GET['sys']) || !isset($_GET['commit_id']))
{
    $fp = fopen($logPath."deploy_error_".date("Ymd").".log", "a");
    fwrite($fp, "[".date("Y-m-d H:i:s")."] 구분값 또는 COMMIT ID가 없음 ".$_SERVER['REMOTE_ADDR']."\n");
    fclose($fp);
    exit;
}

$mode = $_GET['sys'];
$commit_id = $_GET['commit_id'];
$GIT_PATH = "/var/jenkins_home/workspace/laravel/";
$SRC_PATH = "/home/laravel/";

$fp = fopen($logPath."deploy_".$mode."_".date("Ymd").".log", "a");

fwrite($fp, "[".date("Y-m-d H:i:s")."] START ".$_GET['commit_id']."\n");

// 검증예외 파일
$array_except_files = Array
(
	"app/Console/Kernel.php",
	".env",
	"laravel-echo-server.json",
	"laravel-echo-server.lock"
);

// 소스 내 사용불가 단어
$array_ban_words = Array
(
    "laravel",
);

// 알림.
$DEL_FILES = Array();
$BAN_FILES = Array();
$ERR_FILES = Array();


if($request_data)
{
    $data = json_decode($request_data,  true);
    if($data)
    {
        $files = $data['changeFiles'];

        for ( $i=0; $i<sizeof($files); $i++ )
        {
            $file = trim($files[$i]);

            $SOURCE_FILES = $SRC_PATH.$file;
            fwrite($fp, "FILE#".($i+1)." CHECK = ".$SOURCE_FILES."\n");

            $tmp = explode(".", $SOURCE_FILES);
            $ext = $tmp[sizeof($tmp)-1];


            if( !file_exists($GIT_PATH.$file) )
            {
                fwrite($fp, "- REMOVE = File Delete [".$SOURCE_FILES."]\n");
                $DEL_FILES[] = $SOURCE_FILES;
                continue;
            }

            if( $ext=="php" && !in_array($file, $array_except_files) )
            {
                // 문법체크
                $rslt = @exec("php -l ".$SOURCE_FILES);
                if( substr($rslt,0,16)=="No syntax errors" )
                {
                    fwrite($fp, " - Syntax_Check = OK\n");
                }
                else
                {
                    fwrite($fp, " - Syntax_Check = ERROR\n");
                    $ERR_FILES[] = $SOURCE_FILES;
                }

                // 사용불가 단어 검출
                $contents = file_get_contents($SOURCE_FILES);
                $contents = strtoupper($contents);
                for ( $j=0; $j<sizeof($array_ban_words); $j++ )
                {
                    if( substr_count($contents, $array_ban_words[$j])>0 )
                    {
                        fwrite($fp, " - Banned_Words = ".$array_ban_words[$j]."\n");
                        $BAN_FILES[] = $array_ban_words[$j]." = ".$file;
                    }
                }
            }
            else if( in_array($file, $array_except_files) )
            {
                fwrite($fp, " - SKIP = Exception File\n");
            }
            else
            {
                fwrite($fp, " - SKIP = Not a PHP File (".$ext.")\n");
            }
        }
    }
}

fwrite($fp, "[".date("Y-m-d H:i:s")."] 개발시스템 소스배포 시작 \n\n");

for ( $i=0; $i<sizeof($files); $i++ )
{
	$file = trim($files[$i]);

	$SOURCE_FILES = $GIT_PATH.$file;
	$TARGET_FILES = $SRC_PATH.$file;


	fwrite($fp, "FILE#".($i+1)." = ".$TARGET_FILES."\n");

	if( substr($SOURCE_FILES,-4)==".env" )
	{
		fwrite($fp, "- Config File\n");
		continue;
	}
	if( !file_exists($SOURCE_FILES) && file_exists($TARGET_FILES) )
	{
        fwrite($fp, "- REMOVE = File Delete\n");
		$DEL_FILES[] = $TARGET_FILES;
		continue;
	}


	if( file_exists($TARGET_FILES) )
	{
        fwrite($fp, "- FILE EXISTS >> OVERWRITTEN\n");
	}
	else
	{
        fwrite($fp, "- NEW FILE >> CREATION\n");
		
		$tmp = explode("/",$TARGET_FILES);
		unset($tmp[sizeof($tmp)-1]);
		$TARGET_DIR = implode("/",$tmp);

		if( !is_dir($TARGET_DIR) )
		{
			mkdir($TARGET_DIR, 0777, true);
            fwrite($fp, " - MAKE DIRECTORY >> ".$TARGET_DIR."\n");
		}
	}
	copy($SOURCE_FILES, $TARGET_FILES);
}

fwrite($fp, "[".date("Y-m-d H:i:s")."] END ".$_GET['commit_id']."\n");

$message = "\n정상처리 성공";


if( sizeof($DEL_FILES)>0 )
{
        $message.= "\n\n";
        $message.= "※ 파일삭제 내역.\n";
        $message.= implode("\n",$DEL_FILES);
}
if( sizeof($BAN_FILES)>0 )
{
        $message.= "\n\n";
        $message.= "※ 사용불가 단어가 검출되었습니다.\n";
        $message.= implode("\n",$BAN_FILES);
}
if( sizeof($ERR_FILES)>0 )
{
        $message.= "\n\n";
        $message.= "※ Syntax 에러 파일이 있습니다.\n";
        $message.= implode("\n",$ERR_FILES);
}

?>
