
// 로딩 문자열.
var loadingStringtxt = "<i class='fas fa-asterisk fa-spin text-orange mr-1'></i> Loading...";
var loadingString = "<div style='text-align:center;padding:40px;'>" + loadingStringtxt + "</div>";

// 주소 자동 채워넣기
function setAddr(zip,addr1,addr2,getZip,getAddr1,getAddr2,getOldAddr)
{
	$('#'+zip).val(getZip);
	$('#'+addr1).val(getAddr1);
	$('#'+addr2).val(getAddr2);

	// 지번
	$('#old_'+addr1).val(getOldAddr);
}

// 다음 주소 API
function DaumPost(zip1, addr11, addr12, addr_value, callback) {
	new daum.Postcode({
		oncomplete: function (data) {
			
			// 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

			// 각 주소의 노출 규칙에 따라 주소를 조합한다.
			// 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
			var fullAddr = ''; // 최종 주소 변수
			var extraAddr = ''; // 조합형 주소 변수

			// 사용자가 선택한 주소 타입에 따라 해당 주소 값을 가져온다.
			//	if (data.userSelectedType === 'R') { // 사용자가 도로명 주소를 선택했을 경우
			//		fullAddr = data.roadAddress;
			//
			//	} else { // 사용자가 지번 주소를 선택했을 경우(J)
			//		fullAddr = data.jibunAddress;
			//	}
			// 모두 도로명으로
			fullAddr = data.roadAddress;

			// 도로명이 안오는 경우가 있음.
			if(fullAddr=='')
			{
				fullAddr = data.autoRoadAddress;
			}

			// 사용자가 선택한 주소가 도로명 타입일때 조합한다.
			//	if(data.userSelectedType === 'R'){
			//법정동명이 있을 경우 추가한다.
			if (data.bname !== '') {
				extraAddr += data.bname;
			}
			// 건물명이 있을 경우 추가한다.
			if (data.buildingName !== '') {
				extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
			}
			// 조합형주소의 유무에 따라 양쪽에 괄호를 추가하여 최종 주소를 만든다.
			fullAddr += (extraAddr !== '' ? ' (' + extraAddr + ')' : '');
			//	}
			// 우편번호와 주소 정보를 해당 필드에 넣는다.
			document.getElementById(zip1).value = data.zonecode; //5자리 새우편번호 사용
			document.getElementById(addr11).value = fullAddr;

			if(document.getElementById('old_'+addr11))
			{
				document.getElementById('old_'+addr11).value = data.jibunAddress;
			}

			// 커서를 상세주소 필드로 이동한다.
			document.getElementById(addr12).focus();

			if(callback)
			{
				callback(data.zonecode, fullAddr);
			}
		}
	}).open({
		q: addr_value
	});
}

// 쿼리스트링을 배열로 바꿔서 리턴함.
$.extend({
	getQueryParameters: function (str) {
		str = str || document.location.search;
		return (!str && {}) || str.replace(/(^\?)/, '').split("&").map(function (n) { return n = n.split("="), this[n[0]] = n[1], this }.bind({}))[0];
	}
});

// 리스트에서 서류함(탭) 이동시 공통으로 사용함.
function goTab(url, tabs, listName) {
	var oldTab = $("#tabsSelect" + listName).val();
	$("#tabsChange" + listName).val('Y');

	// 일괄처리 열려있으면 닫기
	closeLump();	

	// 정렬 버튼 초기화
	$('.order-text').html('△');

	// 탭색 적용 - 기존꺼는 빼고 클릭은 넣는다
	$("#tab" + oldTab).removeClass("active");
	$("#tab" + tabs).addClass("active");

	$('.listHeader-'+oldTab).css('display','none');
	$('.listHeader-'+tabs).css('display','');
	// 탭선택시 일괄처리에 레이어 등으로 만든걸 안보이게 한다. customerbatch.blade.php 참고.
	if (typeof hideDiv == 'function') {
		hideDiv();
	}

	if(url == '/account/account' && tabs == 'S')
    {
		if($('#LUMP_BTN_UPD'))
		{
			$('#LUMP_BTN_UPD').css('display', '');
		}
		if($('#LUMP_BTN_DEL'))
		{
			$('#LUMP_BTN_DEL').css('display', '');
		}
    }
	else if(url == '/account/account' && tabs == 'W')
    {
		if($('#LUMP_BTN_UPD'))
		{
			$('#LUMP_BTN_UPD').css('display', 'none');
		}
		if($('#LUMP_BTN_DEL'))
		{
			$('#LUMP_BTN_DEL').css('display', '');
		}
    }
    else
    {
		if(url == '/account/account')
		{
			if($('#LUMP_BTN_UPD'))
			{
				$('#LUMP_BTN_UPD').css('display', 'none');
			}
			if($('#LUMP_BTN_DEL'))
			{
				$('#LUMP_BTN_DEL').css('display', 'none');
			}
		}
    }

	// 첫페이지 처럼 해서 보냄.
	$("#isFirst" + listName).val('1');
	$("#tabsSelect" + listName).val(tabs);

	$('#' + listName + 'ListHeader').removeClass('bg-click');
	$("#listOrder" + listName).val('');
	$("#listOrderAsc" + listName).val('');
	$('.orderIcon').removeClass('fas fa-arrow-down');
    $('.orderIcon').removeClass('fas fa-arrow-up');
	getDataList(listName, 1, url + 'list', $('#form_' + listName).serialize(), tabs);
}

// 공통으로 리스트 가져오기(page번호, url)
function getDataList(nm, page, action, postdata, oldTab) {
	postdata = postdata + '&page=' + page;
	var queryParam = $.getQueryParameters(postdata);
	console.log(queryParam);
	//파라미터로 받는다.	
	var nm = queryParam['listName'];
	setLoading('start', nm);

	// 정렬 버튼 초기화
	$('.order-text').html('△');

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$.ajax({
		url: action,
		type: "POST",
		data: postdata,
		dataType: "json",
		success: function (data) {
			if (data.result == "1") {
				$("#listData_" + nm).empty();
				$("#listError_" + nm).empty();
				 
				if ($("#tabsChange" + nm).val() == 'Y') {
					if (data.listTitle) {
						$("#" + nm + "ListHeader").html(data.listTitle.header);
					}
					
					$("#tabsChange" + nm).val('N');
				}

				if (data.listTitle) {
					var markup = data.listTitle.markup;
				} else {
					var markup = eval('getMarkup' + nm + '()');
				}

				// 데이터 없음.
				if (data.txt == "0") 
				{
					setLoading('stop', nm);
				} else {

					$.template("listTemplate" + nm, markup);

					for(listNum=0; listNum<data.txt; listNum++)
					{
						data.v[listNum]['listNum'] = listNum + 1;
					}

					var list_data = data.v;
					$.tmpl("listTemplate" + nm, list_data).appendTo("#listData_" + nm);

					// 맨위로 이동.
					location.href = "#";
				}

				// 페이징 처리
				$("#pageList_" + nm).html(data.pageList);
				$("#searchCnt" + nm).val(data.totalCnt);
				// $("#searchCnt" + nm).val(data.txt);

				// tab
				if (data.tabCount) {
					$.each(data.tabCount, function (key, val) {
						if( val==null ) val = 0;
						var tmp = $("#tab" + key).text().split('(');
						$("#tab" + key).text(tmp[0] + '(' + val + ')');
					});
				}

				// 배치 항목 처리 - 처음 진입이나 탭이 변경됐을 경우만 변경한다.
				// 현재탭과 선택한 탭이 같은지 확인한다.
				if (typeof oldTab == 'string' && $('#tabsSelect' + nm).val() != oldTab) {
					if (data.batchArray) {
						$('#batchSta').find("option").remove();
						$('#batchSta').append('<option value="">선택</option>');

						$.each(data.batchArray, function (key, val) {
							$('#batchSta').append('<option value="' + key + '">' + val + '</option>');
						});
						$('#batchDiv').show();
					}
					else {
						$('#batchDiv').hide();
					}
				}

				// 상단 카운트 불러오는 설정값 초기화
				$("#isFirst" + nm).val('0');

				// 현재 페이지번호
				$("#nowPage" + nm).val(page);

				// 전체선택 없앰
				if (data.listTitle) {
					$('.check-all').iCheck({
						checkboxClass: 'icheckbox_square-blue',
					});
				}
				$('.check-all').iCheck('uncheck');

				// 체크박스 icheck
				$('.list-check').iCheck({
					checkboxClass: 'icheckbox_square-blue',
					radioClass: 'iradio_square-blue',
					handle: 'checkbox'
				});

				// 리스트 하단 합계 form
				if (data.incCheck && data.incSum) 
				{
					$('#incCheck').css("display" , "");
					$.each(data.incSum, function (key, val) {
						$('#' + key).html(val);
					});
					
					if(data.incCheck == "Y")
					{
						searchSumTotal();
					}
				}

				// 쿼리 저장
				if(data.targetSql)
				{
					$('#form_'+nm).find("#target_sql"+nm).val(data.targetSql);
				}
				afterAjax();
				console.log('===========================================');
				return false;
			}
			else if (data.result == "0" && data.msg != "") {
				alert(data.msg);
				setLoading('stop', nm);
			} else {
				alert('데이터를 불러오지 못했습니다.');
				setLoading('stop', nm);
			}
		},
		error: function (xhr) {
			console.log(xhr.responseText);
			alert("통신오류입니다. 관리자에게 문의해주세요.");
			setLoading('stop', nm);
		}
	});

}

// 로딩 페이지 구현
function setLoading(opt, nm) {
	$("#listData_" + nm).empty();

	if (opt == 'start')
		$("#listError_" + nm).html(loadingString);
	else
		$("#listError_" + nm).html("<div style='text-align:center;padding:40px;'>표시 할 데이터가 없습니다.</div>");
}

/**
 * INPUT MASK 세팅 함수
 * @param md - 설정 항목타입 (id, class)
 * @param nm - 설정항목 이름
 * @param type - 정규식 타입
 * 
 * 사용법
 * $(function () {
 *  setInputMask('class', 'mf', 'money');
 * });
 */
function setInputMask(md, nm, type) {
	var div = "";
	if (md == 'class') {
		div = "." + nm;
	}
	else if (md == 'id') {
		div = "#" + nm;
	}

	if (type == 'ratio') {
		$(div).inputmask('99.99', { rightAlign: true });
	}
	else if (type == 'money') {
		$(div).inputmask({ alias: "currency", rightAlign: true, digits: "0", });
	}
	else if (type == 'phone') {
		$(div).inputmask('999-9999-9999', {});
	}
	else if (type == 'date') {
		$(div).inputmask('9999-99-99', {});
	}
	else if (type == 'datetime') {
		$(div).inputmask('9999-99-99 99:99', {});
	}
}

// 영어, 숫자입력
function specialCharRemove(obj) {
	var val = obj.value;
	var pattern = /[^(a-zA-Z0-9)]/gi;
	if (pattern.test(val)) {
		obj.value = val.replace(pattern, "");
	}
}

// 한글입력
function onlyKorean(obj) {
	var val = obj.value;
	var pattern = /[^(가-힣ㄱ-ㅎㅏ)]/gi;
	if (pattern.test(val)) {
		obj.value = val.replace(pattern, "");
	}
}

// 숫자입력
function onlyNumber(obj) {
	var val = obj.value;
	var pattern = /[^0-9]/g;
	if (pattern.test(val)) {
		obj.value = val.replace(pattern, "");
	}
}

// 숫자만남겨
function clearNumber(str)
{
    var pattern = /[^0-9]/g;
    str = str.replace(pattern, "");
	return str;
}

// 0일 경우 10으로 변경
function NotZero(obj)
{
    var val = obj.value;
    var position = val.search('0');
    if(position==0) obj.value = val.replace('0', "10");
}

//popup 창 호출
function getPopUp(url,name,option)
{
	if(option=='') option = "width=500, height=800 ";
	window.open(url,name,option);
}

//popup 전체창 호출
function popUpFull(url,name)
{
	var options = 'right=0, top=0, height=' + screen.height + ', width=' + screen.width + ', fullscreen=yes';
	window.open(url, name, options);
}

  //메세지 작성 popUp
  function popupMsg(no)
  {
	getPopUp('/intranet/msgpop?mdiv=recv&msgNo='+no,'msgpopup'+no, "width=600, height=800, scrollbars=no");
  }
  
// 엑셀 모달
function excelDownModal(url,formId) {
    $("#excelDownModal").modal({ backdrop:'static', keyboard:false});
	if (document.getElementById('check_all')) {
		document.getElementById("check_all").click();
	}
	$("#"+formId).find("input[name='excelUrl'").text(url);
}
// 엑셀 다운로드
function excelDown(formId)
{
	if(formId=='')
	{
		formId = $('#excelSelectId').val();
	}

	// 필수값 선택
	 if(!$('#excel_down_cd option:selected').val())
     {
         alert('조회사유를 선택해주세요');
         $('#excel_down_cd').focus();
         return false;
     }

	 var excel_div_flag = $('input[name="excel_down_div"]:checked').val();
	if(excel_div_flag==undefined)
	{
		alert('실행구분을 선택해주세요');
         $('#excel_down_div').focus();
         return false;
	}

    var excelDownCd = $("#excel_down_cd option:selected").val();
    $("input[name='excelDownCd']").val(excelDownCd);
	var down_div = $('input[name="radio_div"]:checked').val();
	$("input[name='down_div']").val(down_div);
	var excel_down_div = $('input[name="excel_down_div"]:checked').val();
	$("input[name='excel_down_div']").val(excel_down_div);
	if($('#down_filename').val() != ''){
		var down_filename = $('#down_filename').val();
		$("input[name='down_filename']").val(down_filename+'.xlsx');
	}

	// appendTo 전 초기화
	$("input[name=filename]").remove();
	$("input[name=origin_filename]").remove();
	$("input[name=excel_no]").remove();
	$("input[name=record_count]").remove();

	var excelUrl = $("#"+formId).find("input[name='excelUrl'").text();
	var postdata = $("#"+formId).serialize();
	 
	var excelForm = $("#excelForm").html();
	
	// 중복 클릭 방지
	if(ccCheck()) return;  

	$("#excelForm").html(loadingString);
    $("#excelMsg").css("display","block");

	if(excel_down_div != "E")
	{
		alert('엑셀 다운이 예약됩니다.\n진행상황은 예약내역에서 확인하시기 바랍니다.');
		$(".modal-backdrop").remove();
		$("#excelForm").html(excelForm);
		$("#excelDownModal").modal('hide');
		$("#excelMsg").css("display","none");
		$('#down_filename').css('display','block');
		$('#down_filename').val('');
		$('#reservation').val('S');
		$('#realtime').val('E');
	}

	$.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

	$.ajax({
		url: excelUrl,
		type: "post",
		data: postdata,
		success : function(data) {
			if(data.result == "Y")
			{
				if(data.excel_down_div == "E")
                {
					var f = document.getElementById(formId);
					$( "<input>",{type:'hidden',name:'excel_no',value:data.excel_no}).appendTo(f);
					$( "<input>",{type:'hidden',name:'filename',value:data.filename}).appendTo(f);
					$( "<input>",{type:'hidden',name:'record_count',value:data.record_count}).appendTo(f);
					$( "<input>",{type:'hidden',name:'down_filename',value:data.down_filename}).appendTo(f);
					$( "<input>",{type:'hidden',name:'excel_down_div',value:data.excel_down_div}).appendTo(f);
					$( "<input>",{type:'hidden',name:'origin_filename',value:data.origin_filename}).appendTo(f);
					f.action = "/config/exceldown";
					f.method = 'POST';
					f.submit();

					// 초기화
					f.filename.value = '';
					f.origin_filename.value = '';
					f.excel_no.value = '';
					f.record_count.value = '';
				}
			}
			else
			{
				alert(data.error_msg);
			}
			if(excel_down_div == "E")
			{
				$(".modal-backdrop").remove();
				$("#excelForm").html(excelForm);
				$("#excelDownModal").modal('hide');
				$("#excelMsg").css("display","none");
				$('#down_filename').css('display','block');
				$('#down_filename').val('');
				$('#reservation').val('S');
				$('#realtime').val('E');
			}
			globalCheck = false;
		},
		error : function(xhr) {
			console.log(xhr.responseText);
			$(".modal-backdrop").remove();
			$("#excelDownModal").modal('hide');
			$("#excelForm").html(excelForm);
			$("#excelMsg").css("display","none");
			$('#down_filename').css('display','block');
			$('#down_filename').val('');
			$('#reservation').val('S');
            $('#realtime').val('E');
			globalCheck = false;
		}
	});
}

// 엑셀 다운로드 실행구분 체크
function input_filename()
{
    var div = $('input[name="excel_down_div"]:checked').val();

    if(div == "S")
    {
        $('#down_filename').css('display', 'block');
    }
    else
    {
        $('#down_filename').css('display', 'none');
        $('#down_filename').val('');
    }
}

// 엑셀 항목 체크 활성화/비활성화
function input_select()
{
	var flg = $('input[name="excel_down_sell"]:checked').val();

	if(flg=="ALL")
	{
		$('#selectHeaders').css('display', 'none');
		$('#allChecks').css('display', 'none');		
	}
	else
	{
		$('#selectHeaders').css('display', '');
		$('#allChecks').css('display', '');
	}
}

// ajax 요청 공통함수(formdata 형식 : aa=bb&cc=dd )
function jsonAction(url, method, formdata, callback)
{
	$.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

	$.ajax({
		url: url,
		type: method,
		data: formdata,
		dataType: "json",
		success : function(data) {
			callback(data);
		},
		error : function(xhr) {
            console.log(xhr.responseText);
			alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});
}

// AJAX로 페이지 호출 하거나 페이지 로딩후 실행 할것.
function afterAjax()
{
	// selectpicker
	$('.selectpicker').selectpicker({
		width: 'auto',
		style: 'btn-default form-control-sm bg-white',
	});

	// 달력
	$(".datetimepicker").datetimepicker({
		format: 'YYYY-MM-DD',
		locale: 'ko',
		useCurrent: false,
	});

	// 숫자만 입력, 콤마 자동 생성
	$(".comma").number(true);

	// 소숫점 2자리 자동 입력
    $(".floatnum").number(true, 2);

	// On Off 버튼
	$("input[data-bootstrap-switch]").each(function() {
		$(this).bootstrapSwitch('state', $(this).prop('checked'));
	});
	//
	$(".datetimepicker-wol").datetimepicker({
		format: 'yyyy-MM',
		locale: 'ko',
		useCurrent: false,
	});
}

// popover 자동 닫기
$("html").on("mouseup", function (e) {
    var l = $(e.target);
    if (l[0].className.indexOf("popover") == -1) {
        $(".popover").each(function () {
            $(this).popover("hide");
        });
    }
});

// 팝오버 호출.
function viewPopover(item, title, memo)
{   
    var options = {
        html : true,
        container: 'body',
        title : title + '&nbsp; <a href="#" class="close" data-dismiss="alert" style="position: relative; bottom: 3px;">&times;</a>',
        content : memo
    };

    $(item).popover(options);
    $(item).popover('show');
}

// 명세 팝업창
function investor_info_pop( cin, lin, cnd, cnt, total )
{
	if( cnd==undefined )
	{
		cnd = "";
	}
	if( cnt==undefined )
	{
		cnt = "";
	}
	if( total==undefined )
	{
		total = "";
	}
	var wnd = window.open("/erp/custpop?cust_info_no="+cin+"&no="+lin+"&condition="+cnd+"&cnt="+cnt+"&total="+total, "", "width=2000, height=1000, scrollbars=yes");
	wnd.focus();
}

/**
 * 중복클릭방지함수
 *
 * 1. 시작시
 * if(ccCheck()) return;
 *
 * 2. 실행후 또는 취소후(submit으로 보낼때는 아래를 안넣어도됨)
 * globalCheck = false;
 */
 var globalCheck = false;
 function ccCheck()
 {
	 if(!globalCheck)
	 {
		 globalCheck = true;
		 return false;
	 }
	 else
	 {
		 alert('요청 사항을 실행중입니다. 잠시 기다려 주세요');
		 return true;
	 }
 }

/**
 * 문자열이 빈 문자열인지 체크하여 결과값을 리턴한다.
 * @param str       : 체크할 문자열
 */
	function isEmpty(str){
		
	if(typeof str == "undefined" || str == null || str == "")
		return true;
	else
		return false ;
}
	
/**
 * 문자열이 빈 문자열인지 체크하여 기본 문자열로 리턴한다.
 * @param str           : 체크할 문자열
 * @param defaultStr    : 문자열이 비어있을경우 리턴할 기본 문자열
 */
function nvl(str, defaultStr){
		
	if(typeof str == "undefined" || str == null || str == "")
		str = defaultStr ;
		
	return str ;
}

// 유효성검사 후 alert 표시
function alertErrorMsg(msg) 
{
    $('html, body').scrollTop(0);

    // 에러메세지 표시
    for(var i=0;i<Object.keys(msg).length;i++)
    {
        arr = (msg[i].toString()).split("|");
        alert(arr[1]);
        $('#'+arr[0]).focus();
        return;
    }
}

// 체크박스 체크여부
function isCheckboxChecked(nm)
{
	return $("input:checkbox[name='"+ nm + "']").is(":checked");
}

// 체크박스 배열값만 serialize(listChk[]=123&listChk[]=456&listChk[]=789)
function getArrayCheckbox(nm)
{
	var result = '';
	$("input:checkbox[name='"+ nm + "']:checked").each(function(i){  
		if(result!='')
			result += '&';
		result += nm + '=' + $(this).val();
	});

	return result;
}

// 유효성검사 후 메세지 표시하기
function printErrorMsg(msg) 
{
    $('html, body').scrollTop(0);

    // 기존 에러메세지 리셋
    $(".was-validated").removeClass("was-validated");
    $('.error-msg').empty();
    
    // 에러메세지 표시
    for(var i=0;i<Object.keys(msg).length;i++)
    {
        if(msg[i]=='validation.required')
            continue;
		console.log(msg[i]);
        arr = (msg[i].toString()).split("|");
        //$('#'+arr[0]+'_error').append('<li>'+arr[1]+'</li>'); 
        // $('#'+arr[0]).parent().addClass("was-validated");
        alert(arr[1]);
        $('#'+arr[0]).focus();
        return;
    }
}

// 일괄처리 창 열려있으면 닫기
function closeLump()
{
	if(( $('.control-sidebar').css("right")=="0px" ))
	{
		$('#lump_btn').trigger('click');
	}
}


function searchLink(phone)
{
	var wnd = window.open("/ups/search/?searchStr="+phone, "custsearch", "left=0, top=0, width=950, height=800, scrollbars=yes");
	wnd.focus();
}

/*
* 인쇄
*
* headerTitle 인자가 없으면 페이지의 타이틀 머릿말로 인쇄 / 인자가 있으면 받은 인자값 머릿말로 인쇄
*
* 머릿말 들어갈 div 영역 id = headerArea / 글 입력될 h태그 id = headerTitle
*
* 검색, 버튼 등 인쇄하지 않을 div 영역 id = searchArea 
*
* 인쇄할 div 영역 id = printArea
*/
function doPrint(headerTitle)
{
    var header =  document.getElementById('headerTitle');
    
    if(headerTitle === undefined)
    {
        header.innerText = $('#masterTitle').text();
    }
    else
    {
        header.innerText = headerTitle;
    }
    window.print();
}

// 부서 선택시 직원 컬럼 자동 채움
function getBranchUser(branchCd, userCol, userColName)
{   
	if(!userColName)
		userColName = '직원선택';

	if(branchCd=='')
	{
		$("#"+userCol).selectpicker({
			noneSelectedText : userColName
		});
		$("#"+userCol).html('<option value="">' + userColName +'</option>');
		$('#'+userCol).selectpicker('refresh'); 
	}
	else
	{
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
	
		$.ajax({
			url  : "/config/userbranchdiv",
			type : "post",
			data : {branch:branchCd},
				success : function(result)
				{   
					$("#"+userCol).selectpicker({
						noneSelectedText : userColName
					});
					$("#"+userCol).html(result);
					$('#'+userCol).selectpicker('refresh');    
				},
				error : function(xhr)
				{
					alert("통신오류입니다. 관리자에게 문의해주세요.");
				}
		});
	}	
}

// 쿠키 저장 함수
function setCookie(name, value, exp) 
{
	var date = new Date();
	date.setTime(date.getTime() + exp*24*60*60*1000);
	document.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';path=/';
}

// 메뉴자동숨김 체크 이벤트
function chkSidebar(chk)
{
	if(chk==true)
	{
		setCookie('hide_sidebar', 'Y', 365);
		$('#sidebar').addClass("sidebar-collapse");
	}
	else
	{
		setCookie('hide_sidebar', 'N', 365);
		$('#sidebar').removeClass("sidebar-collapse");
		$('#sidebar').removeClass("sidebar-expanded-on-hover");
	}
}	

// datestring = 14자리 날짜스트링
function getTimestamp(dateString)
{ 
	if(dateString)
	{
		var date = new Date(dateString.replace(/^(\d{4})(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)$/,'$4:$5:$6 $2/$3/$1'));
		date.setHours(date.getHours() + 9); 
		return  date.toISOString().replace('T', ' ').substring(0, 19); 
	}
}

// ajax 요청 동기 공통함수(formdata 형식 : aa=bb&cc=dd )
function jsonActionNosync(url, method, formdata, callback)
{
	$.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

	$.ajax({
		url: url,
		type: method,
		data: formdata,
		async: false,
		dataType: "json",
		success : function(data) {
			callback(data);
		},
		error : function(xhr) {
            console.log(xhr.responseText);
			alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});
}


// 결과내 정렬
function orderTable(tableId, column)
{

	var orderId = 'order-' + column;
	var idxCnt = 0;
	var nowIdx = 0; 

	console.log("선택컬럼 : " + orderId);

	$(".result-order").each(function(idx){	
		if(orderId==this.id)
		{
			nowIdx = idxCnt;
			console.log(nowIdx + orderId + '=' + this.id);
			return false;
		}

		if($('#'+this.id).css("display")!='none')
		{
			idxCnt ++;
		}
	});

	column = nowIdx;

	// 정렬 버튼 초기화
	$('.order-text').html('△');

	var click = $('#'+orderId);

	if(click.is('.order-asc'))
	{
		click.removeClass('order-asc');
		click.addClass('order-desc');
		click.html('▼');
		sorting = -1;
	}
	else 
	{
		click.removeClass('order-desc');
		click.addClass('order-asc');
		click.html('▲');
		sorting = 1;
	}

	var rec = $('#'+tableId).find('tbody>tr').get();

	// 컬럼의 타입 검사
	var types = 'string';
	rec.sort(function(a, b){
		var aa = $(a).children('td').eq(column).text();
		var bb = $(b).children('td').eq(column).text();

		aa = aa.replace( /[,\s\xA0]+/g , "" ); 
		bb = bb.replace( /[,\s\xA0]+/g , "" ); 

		var numA = parseFloat( aa ) + ""; 
		var numB = parseFloat( bb ) + ""; 

		if ( numA == "NaN" || numB == "NaN" || aa != numA || bb != numB )
		{
			return false; 
		}

		types = 'number';
	});
	
	rec.sort(function (a, b) {
		var val1 = $(a).children('td').eq(column).text();
		var val2 = $(b).children('td').eq(column).text();
		if(types=='string')
		{
			val1 = val1.toUpperCase();
			val2 = val2.toUpperCase();
		}
		else 
		{
			val1 = val1.replace( /[,\s\xA0]+/g , "" )*1;
			val2 = val2.replace( /[,\s\xA0]+/g , "" )*1;
		}
		return (val1 < val2)?-sorting:(val1>val2)?sorting:0;
	});

	$.each(rec, function(index, row) {
		$('#'+tableId+' tbody').append(row);
	});
}



// 1차 셀렉트 변경시 2차 셀렉트 채움.
// (code:select 첫번째 선택값, mainCode:select 첫번째 코드명, subCode:select 두번째 코드명, subCodeName:select 두번째 타이틀, subCodeVal:select 두번째 선택값 )
function getSubSelect(code, mainCode, subCode, subCodeName, subCodeVal, isSelectPicker)
{   
	if(!subCodeName)
	{
		subCodeName = '구분선택';
	}

	if(!isSelectPicker)
	{
		isSelectPicker = 'Y';
	}

	subCode = 'select_id_' + subCode;
	
	// 선택없을때 없앰.
	var json_data = '';
	if(code!='')
	{
		json_data = $('#select_json_'+ mainCode + '_' +code).html();
	}

	if(isSelectPicker=='Y')
	{
		$("#"+subCode).selectpicker({
			noneSelectedText : subCodeName
		});
	}
	var result = '<option value="">' + subCodeName +'</option>';
	
	if(code!='' && json_data!='' && json_data!='undefined')
	{
		var data = JSON.parse(json_data);
		for(var key in data)
		{
			result += '<option value="' + key + '">' + data[key] + '</option>';
		}
	}	
	
	$("#"+subCode).html(result);
	$("#"+subCode).val(subCodeVal);

	if(isSelectPicker=='Y')
	{
		$('#'+subCode).selectpicker('refresh'); 
	}
}


// 1차 셀렉트 변경시 2차 셀렉트 채움.
// (code:select 첫번째 선택값, mainCode:select 첫번째 코드명, subCode:select 두번째 코드명, subCodeName:select 두번째 타이틀, subCodeVal:select 두번째 선택값 )
function getSubSelectMulti(codeId, mainCode, subCode, subCodeName, subCodeVal)
{   
	if(!subCodeName)
	{
		subCodeName = '구분선택';
	}

	subCode = 'select_id_' + subCode;
	
	$("#"+subCode).selectpicker({
		noneSelectedText : subCodeName
	});
	
  var result = '';
  var arraySelected = $('#'+codeId).val();

  $.each(arraySelected, function (key, selectCode) {
    json_data = $('#select_json_'+ mainCode + '_' +selectCode).html();

    if(json_data!='' && json_data!='undefined')
	  {
      var data = JSON.parse(json_data);
      for(var key in data)
      {
        result += '<option value="' + key + '">' + data[key] + '</option>';
      }
    }
  });
	
	$("#"+subCode).html(result);
	$("#"+subCode).val(subCodeVal);
	$('#'+subCode).selectpicker('refresh'); 
}

// 숫자만 입력했는지 검사
function checkOnlyNumber()
{
	var ob=event.srcElement;
	ob.value = filterNum(ob.value);
	ob.value = commaSplitAndNumberOnly(ob);
	return false;
}

// $ 와 콤마 제거 함수
function filterNum(str)
{
	re = /^\$|,/g;
	return str.replace(re, "");
}

function commaSplitAndNumberOnly(ob)
{
	var txtNumber = '' + ob.value;
	if (isNaN(txtNumber) || txtNumber.indexOf('.') != -1 )
	{
		ob.value = ob.value.substring(0, ob.value.length-1 );
		ob.value = commaSplitAndNumberOnly(ob);
		ob.focus();
		return ob.value;
	}
	else
	{
		var rxSplit = new RegExp('([0-9])([0-9][0-9][0-9][,.])');
		var arrNumber = txtNumber.split('.');
		arrNumber[0] += '.';
		do
		{
			arrNumber[0] = arrNumber[0].replace(rxSplit, '$1,$2');
		}
		while (rxSplit.test(arrNumber[0]));
		if (arrNumber.length > 1)
		{
			return arrNumber.join('');
		}
		else
		{
			return arrNumber[0].split('.')[0];
		}
	}
}