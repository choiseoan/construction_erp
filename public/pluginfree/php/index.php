<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title>nProtect Online Security v1.0.0</title>


<script type="text/javascript"> var nua=navigator.userAgent; </script>
<script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="../js/nppfs-1.13.0.js"></script>

<script type="text/javascript">
jQuery(document).ready(function(){

    npPfsStartup(null, false, true, false, false, "npkencrypt", "on");

});

</script>
</head>

<body>
<table>
	<tr>
 		<th style="text-align:left;font-size:14pt;">접속정보</th>
 	</tr>
	<tr>
 		<td>
			<span id="userAgent"></span>
		</td>
	</tr>
</table>

<script>
function GetReplaceKeyData(form, field){
	var table = npPfsCtrl.GetReplaceField(form, field);
	alert(table);
}
function GetReplaceTable(form, field){
	var data = npPfsCtrl.GetResultField(form, field);
	alert(data);
}

</script>

<div style="margin-bottom:20px; padding:10px; border:1px solid #000;">
<form name="form1" action="#" method="post" target="resultTarget">
	<input type="hidden" name="mode" value="KEYCRYPT" />
	<table style="width:100%;">
		<colgroup>
			<col width="20%"></col>
			<col width="80%"></col>
		</colgroup>
		<tr>
			<th colspan="2" style="text-align:left;font-size:14pt;">키보드보안 테스트</th>
		</tr>
		<tr>
			<td> 일반필드(미보호) </td>
			<td> <input type="text"	name="NONE_TEXT_1" id="t4" value="" ></td>
		</tr>
		<tr>
			<td> 키보드보안 ID </td>
			<td> <input type="text"	name="NONE_TEXT_4" id="t4" value="" npkencrypt="key" maxlength="10"></td>
		</tr>
		<tr>
			<td> 키보드보안 PW </td>
			<td> <input type="password" name="NONE_PASS_4" id="p4" npkencrypt="key" value=""></td>
		</tr>
	</table>
</form>
</div>
</body>
</html>

