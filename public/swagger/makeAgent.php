<?
require(__DIR__."/../../vendor/autoload.php");
$openapi = \OpenApi\scan(__DIR__."/../../routes/api/agent.php");
header('Content-Type: application/json');
$txt = $openapi->toJson();
$handle = fopen('apiAgent.json', 'w');
$rs = fwrite($handle, $txt);
fclose($handle);

if(!$rs)
	echo "JSON 생성 실패";
else
	echo $txt;
?>
