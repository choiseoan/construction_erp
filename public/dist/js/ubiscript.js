
/*
	data : ajax 에 보낼 데이터 (Array형태)
	erp_ups : erp / ups 구분 (소문자)
	mode : A : 일괄출력(多건 출력)  // B : 한 건 출력
	directPrint : 바로 출력해야 함.
*/

var ubi_lnos = null;
var ubi_erp_ups = null;
var ubi_post_cd = null;
var ubi_addr = null;

var pJrfDir = null;
	
function after()
{
}

function RetrieveEnd() {

	// printFlag = false;
}

function PrintEnd(status) {

	if( ubi_lnos != null )
	{
		afterPrint(ubi_lnos, ubi_erp_ups, ubi_post_cd, ubi_addr);
	}
}

function ExportEnd(filePath) {

	if( ubi_lnos != null )
	{
		afterPrint(ubi_lnos, ubi_erp_ups, ubi_post_cd, ubi_addr);
	}
}

function Ubi_Version() {

	wsViewer.aboutBox();
}

/*
	erp_ups : erp / ups 구분
	post_cd : 양식지 구분
*/
function afterPrint(lnos, erp_ups, post_cd, addr=null)
{
    $.ajax({
        url  : "/"+erp_ups+"/lumpafterprint",
        type : "post",
        data : {
            "lno" : lnos,
            "post_cd" : post_cd,
			"addr" : addr
        },
        success : function(result) {

			console.log("확인");
        },
        error : function(xhr) {
            // alert("통신오류입니다. 관리자에게 문의해주세요.");
			console.log(xhr.responseText);
        }
    });

	ubi_lnos = null;
	ubi_erp_ups = null;
	ubi_post_cd = null;
	ubi_addr = null;
}

