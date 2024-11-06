<div class="modal-header">
  <h4 class="modal-title">직원관리</h4>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">

  <form class="form-horizontal" role="form" name="user_form" id="user_form" method="post" enctype="multipart/form-data">
    <input type="hidden" id="mode" name="mode" value="{{ $mode }}">
    <input type="hidden" id="save_status" name="save_status" value="{{ $v->save_status ?? 'Y' }}">
    <input type="hidden" id="imgDeleteFlag" name="imgDeleteFlag" value="N">
    @if (isset($v->profile_img_src))
    <input type="hidden" id="img_src" id="default_img" value="data:image;base64,{{ $v->profile_img_src ?? '' }}" />
    @else
    <input type="hidden" id="img_src" id="default_img" value="/img/blank_profile.png" />
    @endif
    

    <div class="form-group row">
      <div class="col-sm-8">
        <!-- row -->
        <div class="form-group row">
          <label for="id" class="col-sm-3 col-form-label text-center">사번<span class="text-danger align-middle">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control form-control-sm" id="id" name="id" placeholder="" @if($mode != 'INS') readonly @endif value="{{ $v->id ?? '' }}" maxlength="50">
          </div>
          
        </div>
        <!-- row -->
        <div class="form-group row">
          <label for="name" class="col-sm-3 col-form-label text-center">이름<span class="text-danger align-middle">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="한글등록" value="{{ $v->name ?? '' }}" maxlength="50" {{ $readonly }}>
          </div>
        </div>
      
        <!-- row -->
        <div class="form-group row">
          <label for="branch_code" class="col-sm-3 col-form-label text-center">부서</label>
          <div class="col-sm-8">
            <select class="form-control select2 form-control-sm w-100" name="branch_code" @if(!empty($readonly)) onFocus='this.initialSelect = this.selectedIndex;' onChange='this.selectedIndex = this.initialSelect;' @endif {{ $readonly }}>
              {{ Func::printOptionArray($array_branch, 'branch_name', $v->branch_code ?? '') }}
            </select>
          </div>
        </div>


        <!-- row -->
        <div class="form-group row">
          <label for="passwd" class="col-sm-3 col-form-label text-center">비밀번호@if($mode == 'INS')<span class="text-danger align-middle">*</span>@endif</label>
          <div class="col-sm-8">
            <input type="password" class="form-control form-control-sm" id="passwd" name="passwd"@if($mode != 'INS') placeholder="변경 시에만 입력해주세요"@endif value="" onkeyup="passwdFunc();">
            <span class="text-danger align-middle" id="passwdMsg"></span>

          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="passwd" class="col-sm-3 col-form-label text-center">비밀번호 확인@if($mode == 'INS')<span class="text-danger align-middle">*</span>@endif</label>
          <div class="col-sm-8">
            <input type="password" class="form-control form-control-sm" id="checkpasswd" name="checkpasswd"@if($mode != 'INS') placeholder="변경 시에만 입력해주세요"@endif onkeyup="checkPwdFunc();" value="">
            <span class="text-danger align-middle" id="checkpasswdMsg"></span>
          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="user_rank_cd" class="col-sm-3 col-form-label text-center">직급</label>
          <div class="col-sm-8">
            <select class="form-control select2 form-control-sm" id="user_rank_cd" name="user_rank_cd" @if(!empty($readonly)) onFocus='this.initialSelect = this.selectedIndex;' onChange='this.selectedIndex = this.initialSelect;' @endif {{ $readonly }}>
              <option class="bg-gray" value=''>직급</option>
              {{ Func::printOption($configArr['user_rank_cd'], $v->user_rank_cd ?? '') }}
            </select>
          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="user_position_cd" class="col-sm-3 col-form-label text-center">직책</label>
          <div class="col-sm-8">
            <select class="form-control select2 form-control-sm" id="user_position_cd" name="user_position_cd" @if(!empty($readonly)) onFocus='this.initialSelect = this.selectedIndex;' onChange='this.selectedIndex = this.initialSelect;' @endif {{ $readonly }}>
              <option class="bg-gray" value=''>직책</option>
              {{ Func::printOption($configArr['user_position_cd'], $v->user_position_cd ?? '') }}
            </select>
          </div>
        </div>


      </div>
      <div class="col-sm-4 pl-0">
        <canvas id="img_canvas_show" class="profile_img_canvas"></canvas>
        <canvas id="img_canvas_save" width="132" height="170" hidden></canvas>
        <div class="text-center pt-1">
          <input type="file" name="profile_img_origin" id="fileUploadBtn" onchange="setThumbnail(event);" hidden/>
          <button type="button" class="btn btn-sm btn-primary" onclick="$('#fileUploadBtn').click();">업로드</button>
          <button type="button" class="btn btn-sm btn-info" onclick="basicImg();">기본 이미지</button>
        </div>
      </div>
      
    </div>



    <!-- row -->
    <div class="form-group row">
      <label for="birthday" class="col-sm-2 col-form-label text-center">생년월일</label>
      <div class="col-sm-5 col-lg-3">
        <div class="input-group date" id="birthdayDiv" data-target-input="nearest">
          <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#birthdayDiv" name="birthday" id="birthday" value='{{ $v->birthday ?? '' }}' maxlength="10" size="6"/>
          <div class="input-group-append" data-target="#birthdayDiv" data-toggle="datetimepicker">
            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="icheck-primary d-inline align-sub mr-1">
          <input type="radio" id="radioPrimary1" name="birth_type" value="Y" {{ $mode == 'UPD' && $v->birth_type == 'Y' ? 'checked' : '' }} checked>
          <label for="radioPrimary1">양력</label>
        </div>
        <div class="icheck-primary d-inline align-sub">
          <input type="radio" id="radioPrimary2" name="birth_type" value="N" {{ $mode == 'UPD' && $v->birth_type == 'N' ? 'checked' : '' }}>
          <label for="radioPrimary2">음력</label>
        </div>
      </div>
    </div>

    <!-- row -->
    <div class="form-group row">
      <label for="ssn11" class="col-sm-2 col-form-label text-center">주민번호</label>
      <div class="col-sm-2">
        <input type="text" class="form-control form-control-sm" id="ssn11" name="ssn11" placeholder="" value="{{ $v->ssn11 ?? '' }}" maxlength="6" onkeyup="onlyNumber(this);">
      </div>
      <div class="col-sm-2">
        <input type="text" class="form-control form-control-sm" id="ssn12" name="ssn12" placeholder="" value="{{ $v->ssn12 ?? '' }}" maxlength="7" onkeyup="onlyNumber(this);">
      </div>
    </div>


    <!-- row -->
    <div class="form-group row">
      <label for="zip" class="col-sm-2 col-form-label text-center">집주소</label>
      <div class="col-sm-8">

        <div class="input-group col-sm-3 pl-0 pb-1">
          <input type="text" class="form-control form-control-sm" id="zip" name="zip" numberOnly="true" value="{{ $v->zip ?? '' }}" readOnly>
          <span class="input-group-append">
            <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('zip', 'addr11', 'addr12', '')">검색</button>
          </span>
        </div>
        <input type="text" class="form-control form-control-sm mb-1" id="addr11" name="addr11" value="{{ $v->addr11 ?? '' }}" readOnly>
        <input type="text" class="form-control form-control-sm" id="addr12" name="addr12" value="{{ $v->addr12 ?? '' }}" maxlength="100">

      </div>
    </div>
    
    <!-- row -->
    <div class="form-group row">
      <label for="ph31" class="col-sm-2 col-form-label text-center">회사전화</label>
      <div class="col-auto">
        <input type="text" class="form-control form-control-sm" id="ph31" name="ph31" placeholder="" value="{{ $v->ph31 ?? '' }}" maxlength="3" onkeyup="onlyNumber(this);" size="3">
      </div>
      <div class="col-auto">
        <input type="text" class="form-control form-control-sm" id="ph32" name="ph32" placeholder="" value="{{ $v->ph32 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);" size="4">
      </div>
      <div class="col-auto">
        <input type="text" class="form-control form-control-sm" id="ph33" name="ph33" placeholder="" value="{{ $v->ph33 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);" size="4">
      </div>
      <label for="ph34" class="col-auto col-form-label text-center">내선번호</label>
      <div class="col-auto">
        <input type="text" class="form-control form-control-sm" id="ph34" name="ph34" placeholder="" value="{{ $v->ph34 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);" size="4">
      </div>
    </div>

    <!-- row -->
    <div class="form-group row">
      <label for="ph21" class="col-sm-2 col-form-label text-center">핸드폰</label>
      <div class="col-auto">
        <input type="text" class="form-control form-control-sm" id="ph21" name="ph21" placeholder="" value="{{ $v->ph21 ?? '' }}" maxlength="3" onkeyup="onlyNumber(this);" size="3">
      </div>
      <div class="col-auto">
        <input type="text" class="form-control form-control-sm" id="ph22" name="ph22" placeholder="" value="{{ $v->ph22 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);" size="4">
      </div>
      <div class="col-auto">
        <input type="text" class="form-control form-control-sm" id="ph23" name="ph23" placeholder="" value="{{ $v->ph23 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);" size="4">
      </div>
    </div>

    <!-- row -->
    <div class="form-group row">
      <label for="ph11" class="col-sm-2 col-form-label text-center">집전화</label>
      <div class="col-auto">
        <input type="text" class="form-control form-control-sm" id="ph11" name="ph11" placeholder="" value="{{ $v->ph11 ?? '' }}" maxlength="3" onkeyup="onlyNumber(this);" size="3">
      </div>
      <div class="col-auto">
        <input type="text" class="form-control form-control-sm" id="ph12" name="ph12" placeholder="" value="{{ $v->ph12 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);" size="4">
      </div>
      <div class="col-auto">
        <input type="text" class="form-control form-control-sm" id="ph13" name="ph13" placeholder="" value="{{ $v->ph13 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);" size="4">
      </div>
    </div>

    <!-- row -->
    <div class="form-group row">
      <label for="email" class="col-sm-2 col-form-label text-center">이메일</label>
      <div class="col-auto">
        <input type="text" class="form-control form-control-sm" id="email" name="email" placeholder="" value="{{ $v->email ?? '' }}" maxlength="50">
      </div>
    </div>


    <!-- row -->
    <div class="form-group row">
      <label for="ipsa" class="col-sm-2 col-form-label text-center">입사일</label>
      <div class="col-sm-5 col-lg-3">
        <div class="input-group date" id="ipsaDiv" data-target-input="nearest">
          <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#ipsaDiv" name="ipsa" id="ipsa" value='{{ $v->ipsa ?? '' }}' maxlength="10" size="6" {{ $readonly ? 'disabled' : '' }}/>
          <div class="input-group-append" data-target="#ipsaDiv" data-toggle="datetimepicker">
            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
          </div>
        </div>
      </div>
    </div>

    <!-- row -->
    <div class="form-group row">
      <label for="toesa" class="col-sm-2 col-form-label text-center">퇴사일</label>
      <div class="col-sm-5 col-lg-3">
        <div class="input-group date" id="toesaDiv" data-target-input="nearest">
          <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#toesaDiv" name="toesa" id="toesa" value='{{ $v->toesa ?? '' }}' maxlength="10" size="6" {{ $readonly ? 'disabled' : '' }}/>
          <div class="input-group-append" data-target="#toesaDiv" data-toggle="datetimepicker">
            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
          </div>
        </div>
      </div>
    </div>
    <!-- row -->
    <div class="form-group row">
      <label for="ip" class="col-sm-2 col-form-label text-center">IP</label>
      <div class="col-sm-10">
        <input type="text" class="form-control form-control-sm " name="access_ip" id="access_ip" placeholder="000.000.000.000, 000.000.000.000, 000.000.000.000" value="{{ $v->access_ip ?? '' }}" maxlength="50" {{ $readonly }}>
      </div>
    </div>
  </form>
</div>
<div class="modal-footer justify-content-between">
  <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
  <div class="p-0">
  @if ($mode == 'UPD')
  @if (empty($readonly) && isset($v->login_lock_time) && !empty($v->login_lock_time))
  <span class="mr-2 text-danger text-bold">차단일시: {{ $v->login_lock_time }}</span>
  <button type="button" class="btn btn-sm btn-warning" id="login_lock_del" onclick="$('#mode').val('LOCK'); userAction();">로그인 차단 해제</button>
  @endif
  <button type="button" class="btn btn-sm btn-danger" id="user_btn_del" onclick="userActionDel();">삭제</button>
  @endif
  <button type="button" class="btn btn-sm btn-info" onclick="userAction();">저장</button>
  </div>
</div>

<script> 
// 회원정보창 Action
function userAction() {
	if (!userDataValidation($("#mode").val())) {
		return false;
	}
    if(($("#mode").val()=='LOCK' || $("#passwd").val()) && (!passwdFunc() || !checkPwdFunc())) {
      $('#mode').val('{{$mode}}');
      alert("변경할 비밀번호를 확인해주세요");
      return false;
  }

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	var formData = new FormData($('#user_form')[0]);

	//canvas의 dataurl를 blob(file)화 하는 과정
	var canvas = document.getElementById('img_canvas_save');
	var dataURL = canvas.toDataURL("image/png"); //png => jpg 등으로 변환 가능
	var byteString = atob(dataURL.split(',')[1]);
	var mimeString = dataURL.split(',')[0].split(':')[1].split(';')[0];
	var ab = new ArrayBuffer(byteString.length);
	var ia = new Uint8Array(ab);
	for (var i = 0; i < byteString.length; i++) {
		ia[i] = byteString.charCodeAt(i);
	}
	
	//리사이징된 file 객체
	var tmpThumbFile = new Blob([ab], {type: mimeString});

	formData.append('profile_img', tmpThumbFile);

	$.ajax({
		url: "/config/useraction",
		type: "post",
		data: formData,
		enctype: 'multipart/form-data',
		contentType: false,
		processData: false,
		success: function (result) {
			alert(result.msg);
      if (typeof listRefresh != 'undefined'){
        listRefresh();
      }
			if(result.result == 'Y') {
				$("#userModal").modal('hide');
			}
		},
		error: function (xhr) {
			alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});
}

// 직원정보창 제거 버튼
function userActionDel() {
	if (!confirm("직원정보를 삭제하시겠습니까?")) return false;

	var mode = "DEL";
	var id = $("#id").val();

	$.post("/config/useraction", { mode: mode, id: id }, function (data) {
		alert(data.msg);
    if (typeof listRefresh != 'undefined'){
      listRefresh();
    }
		$("#userModal").modal('hide');
	}).fail(function (jqXHR) {
		alert(jqXHR);
	});
}

// 직원정보 form 데이터 유효성 검사
function userDataValidation(mode) {
	var idReg 	= /^[A-Za-z0-9+]{2,15}$/g;
	var korReg 	= /^[가-힣_0-9]{2,50}$/g;
	// 19|20 으로 시작
	var dayRegExp = /^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1])$/;

	if (mode == 'INS') {
		if (!$("#id").val()) {
			alert("사번을 확인해주세요.");
			$("#id").focus();
			return false;
		}
		// if (!korReg.test($("#name").val())) {
		// 	alert("이름을 확인해주세요.");
		// 	$("#name").focus();
		// 	return false;
		// }
    if ($("#name").val()=='') {
			alert("이름을 입력해주세요.");
			$("#name").focus();
			return false;
		}
		if (!$("#passwd").val()) {
			alert("비밀번호를 입력해주세요.");
			$("#passwd").focus();
			return false;
		}
	} else if(mode == 'UPD') {
		// if (!korReg.test($("#name").val())) {
		// 	alert("이름을 확인해주세요.");
		// 	$("#name").focus();
		// 	return false;
		// }
    if ($("#name").val()=='') {
			alert("이름을 입력해주세요.");
			$("#name").focus();
			return false;
		}
	}

	if ($("#birthday").val()) {
		if (!dayRegExp.test($("#birthday").val())) {
			alert("생년월일을 확인해주세요.");
			$("#birthday").focus();
			return false;
		}
	}
	if ($("#ipsa").val()) {
		if (!dayRegExp.test($("#ipsa").val())) {
			alert("입사일을 확인해주세요.");
			$("#ipsa").focus();
			return false;
		}
	}
	if ($("#toesa").val()) {
		if (!dayRegExp.test($("#toesa").val())) {
			alert("퇴사일을 확인해주세요.");
			$("#toesa").focus();
			return false;
		}
	}

	if ($("#toesa").val() && $("#toesa").val() < $("#ipsa").val()) {
		
			alert("퇴사일을 입사일보다 빠르게 저장할 수 없습니다.");
			$("#toesa").focus();
			return false;
		
	}


	if ($("#access_ip").val()) {
    var accessIp = $("#access_ip").val();
    accessIpArr = accessIp.replaceAll(' ', '').split(',');

    for(var i = 0; i < accessIpArr.length; i++) {
      let ipReg = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/g;
      if (!ipReg.test(accessIpArr[i])) {
        alert("IP형식을 확인해주세요.");
        $("#access_ip").focus();
        return false;
      }
    }
	}

	return true;
}

function basicImg() {
  $("#imgDeleteFlag").val('Y');

  drawImg('/img/blank_profile.png');
}

function drawImg(src) {
    var canvas_show = document.getElementById('img_canvas_show');
    var canvas_save = document.getElementById('img_canvas_save');
    var ctx_show = canvas_show.getContext('2d');
    var ctx_save = canvas_save.getContext('2d');
    ctx_show.clearRect(0, 0, canvas_show.width, canvas_show.height)
    ctx_save.clearRect(0, 0, canvas_save.width, canvas_save.height)
    var img = new Image();
    img.src = src;

    img.onload = function(){
        ctx_show.drawImage(this, 0, 0, canvas_show.width, canvas_show.height);
        ctx_save.drawImage(this, 0, 0, canvas_save.width, canvas_save.height);
    }
}

function setThumbnail(event) {

  $("#imgDeleteFlag").val('N');

  var reader = new FileReader(); 
  reader.onload = function(event) { 
    drawImg(event.target.result);
  }; 

  reader.readAsDataURL(event.target.files[0]); 
} 


drawImg($("#img_src").val());

$('#birthdayDiv').datetimepicker({
  format: 'YYYY-MM-DD',
  locale: 'ko',
	useCurrent: false,
});
$('#ipsaDiv').datetimepicker({
  format: 'YYYY-MM-DD',
  locale: 'ko',
	useCurrent: false,
});
$('#toesaDiv').datetimepicker({
  format: 'YYYY-MM-DD',
  locale: 'ko',
	useCurrent: false,
});

{{-- passwd checkpasswd --}}


// 비밀번호 변경 탭
function passwdFunc() {
    var pw = $("#passwd").val();
    var id = $("#id").val();
    var birth = $("#ssn11").val().substring(2,6);
    var ssn11 = $("#ssn11").val();
    var ssn12 = $("#ssn12").val();
    var ph22 = $("#ph22").val();
    var ph23 = $("#ph23").val();
    var checkNumber  = pw.search(/[0-9]/g);
    var checkEnglish = pw.search(/[a-zA-Z]/ig);
    var checkSpecial = pw.search(/[~!@#$%^=&*()_+-]/ig);

    checkPwdFunc();

    if(pw != "") {
        if(pw.length > 20 || pw.length < 8) {
            $("#passwd").removeClass('is-valid');
            $("#passwd").addClass('is-invalid');
            $("#passwdMsg").html('패스워드 길이를 확인해주세요. (8자 ~ 20자)');
        } else {
            if(checkNumber < 0 || checkEnglish < 0 || checkSpecial < 0){
                $("#passwd").removeClass('is-valid');
                $("#passwd").addClass('is-invalid');
                $("#passwdMsg").html('패스워드에 영문, 숫자, 특수문자를 혼용해주세요.');
            } else if(pw.search(id) > -1) {
                $("#passwd").removeClass('is-valid');
                $("#passwd").addClass('is-invalid');
                $("#passwdMsg").html('비밀번호에 아이디가 포함되어있습니다.');
            } else if(birth!= "" && pw.search(birth) > -1) {
                $("#passwd").removeClass('is-valid');
                $("#passwd").addClass('is-invalid');
                $("#passwdMsg").html('비밀번호에 생일이 포함되어있습니다.');
            } else if( (ssn11!= "" && pw.search(ssn11) > -1) || (ssn12!= "" && pw.search(ssn12) > -1) )  {
                $("#passwd").removeClass('is-valid');
                $("#passwd").addClass('is-invalid');
                $("#passwdMsg").html('비밀번호에 주민번호가 포함되어있습니다.');
            } else if((ph22!= "" && pw.search(ph22) > -1 )|| (ph23!= "" && pw.search(ph23) > -1)) {
                $("#passwd").removeClass('is-valid');
                $("#passwd").addClass('is-invalid');
                $("#passwdMsg").html('비밀번호에 전화번호가 포함되어있습니다.');
            } else {
                $("#passwd").removeClass('is-invalid');
                $("#passwd").addClass('is-valid');
                $("#passwdMsg").html('');
                return true;
            }
        }
    } else {
        $("#passwd").removeClass('is-valid');
        $("#passwd").addClass('is-invalid');
        $("#passwdMsg").html('변경할 패스워드를 입력해주세요.');
    }

    return false;
}

function checkPwdFunc() {

    var pw = $("#checkpasswd").val();

    if(pw != "") {
        if (pw == $('#passwd').val()) {
            $("#checkpasswd").removeClass('is-invalid');
            $("#checkpasswd").addClass('is-valid');
            $("#checkpasswdMsg").html('');
            return true;
        } else {
            $("#checkpasswd").removeClass('is-valid');
            $("#checkpasswd").addClass('is-invalid');
            $("#checkpasswdMsg").html('변경할 패스워드와 일치하지 않습니다.');
        }
    } else {
        $("#checkpasswd").removeClass('is-valid');
        $("#checkpasswd").addClass('is-invalid');
        $("#checkpasswdMsg").html('변경할 패스워드를 다시 입력해주세요.');
    }

    return false;
}



</script>