<?
require(__DIR__."/../../vendor/autoload.php");
$target = $_GET['target'];
if($target=='agent')
{
    $route = 'agent';
    $file = 'apiAgent';
}
// else if($target=='edms')
// {
//     $route = 'edms';
//     $file = 'apiEdms';
// }
else if($target=='homepage')
{
    $route = 'homepage';
    $file = 'apiHomepage';
}
else if($target=='ipcc')
{
    $route = 'ipcc';
    $file = 'apiIpcc';
}
else
{
    exit;
}

$openapi = \OpenApi\scan(__DIR__."/../../routes/api/".$route);
header('Content-Type: application/json');
$txt = $openapi->toJson();
$handle = fopen($file.'.json', 'w');
$rs = fwrite($handle, $txt);
fclose($handle);

if(!$rs)
	echo "JSON 생성 실패";
else
	echo $txt;
?>
