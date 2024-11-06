@extends('layouts.master')
@section('content')

<!-- Main content -->
<section class="content ml-3 mr-3">
    <div class="row justify-content-center">
        <div class="col-xl-2 col-md-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">내정보관리</h3>
                    <div class="card-tools">
                        {{-- <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button> --}}
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="nav nav-pills flex-column" id="nav-tab" role="tablist">
                        <a class="nav-link {{ $tab == 'myinfo' ? 'active' : '' }}" id="nav-home-tab" data-toggle="tab" href="#nav-info" role="tab" aria-controls="nav-info" aria-selected="true">
                            <i class="fas fa-user"></i> 
                            <span>내 정보</span>
                        </a>
                        <a class="nav-link {{ $tab == 'pwd' ? 'active' : '' }}" id="nav-profile-tab" data-toggle="tab" href="#nav-changePwd" role="tab" aria-controls="nav-changePwd" aria-selected="false">
                            <i class="fas fa-lock"></i>
                            <span>비밀번호 변경</span>
                        </a>
                        <a class="nav-link" id="nav-loginLog-tab" data-toggle="tab" href="#nav-loginLog" role="tab" aria-controls="nav-loginLog" aria-selected="false" onclick="loginLogTab();">
                            <i class="fas fa-id-card"></i> 
                            <span>로그인 기록</span>
                        </a>
                    </div>
                </div>
            </div>
                <!-- /.card-body -->
        </div>
        <!-- /.col -->

        <div class="col-xl-7 col-md-12 col-sm-12">
            
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade {{ $tab == 'myinfo' ? 'show active' : '' }}" id="nav-info" role="tabpanel" aria-labelledby="nav-info-tab">
                    <div class="card">
                        <div class="card-body">
                            <form class="form-horizontal" role="form" name="myInfoForm" id="myInfoForm" method="post" enctype="multipart/form-data">
                                <input type="hidden" id="imgDeleteFlag" name="imgDeleteFlag" value="N">
                                @if (isset($v->profile_img_src))
                                <input type="hidden" id="img_src" id="default_img" value="data:image;base64,{{ $v->profile_img_src ?? '' }}" />
                                @else
                                <input type="hidden" id="img_src" id="default_img" value="/img/blank_profile.png" />
                                @endif
                                <div class="row">
                                    <div class="col-12">

                                        <div class="form-group row mb-0">
                                            <div class="col-8 pr-0">
                                                <!-- row -->
                                                <div class="form-group row mb-2">
                                                    <label for="id" class="col-sm-3 col-form-label text-center">사번</label>
                                                    <div class="col-sm-9">
                                                    <input type="text" class="form-control form-control-sm" id="id" placeholder="" value="{{ $v->id ?? '' }}" maxlength="10" readonly>
                                                    </div>
                                                </div>
                                                
                                                <!-- row -->
                                                <div class="form-group row mb-2">
                                                    <label for="name" class="col-sm-3 col-form-label text-center">이름</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control form-control-sm" id="name" placeholder="한글등록" value="{{ $v->name ?? '' }}" maxlength="50" readonly>
                                                    </div>
                                                </div>
                                            
                                                <!-- row -->
                                                <div class="form-group row mb-2">
                                                    <label for="branch_code" class="col-sm-3 col-form-label text-center">부서</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control form-control-sm" type="text" value="{{$v->branch_code ?? ''}}" readonly/>
                                                    </div>
                                                </div>

                                                <!-- row -->
                                                <div class="form-group row mb-2">
                                                    <label for="user_rank_cd" class="col-sm-3 col-form-label text-center">직급</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control form-control-sm" type="text" value="{{$v->user_rank_cd ?? ''}}" readonly/>
                                                    </div>
                                                </div>


                                                <!-- row -->
                                                <div class="form-group row mb-2">
                                                    <label for="user_position_cd" class="col-sm-3 col-form-label text-center">직책</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control form-control-sm" type="text" value="{{$v->user_position_cd ?? ''}}" readonly/>
                                                    </div>
                                                </div>


                                            </div>
                                            <div class="col">
                                                <canvas id="img_canvas_show" class="profile_img_canvas"></canvas>
                                                <canvas id="img_canvas_save" width="132" height="170" hidden></canvas>
                                                <div class="w-100 text-center pt-1">
                                                <input type="file" name="profile_img_origin" id="fileUploadBtn" onchange="setThumbnail(event);" hidden/>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="$('#fileUploadBtn').click();">업로드</button>
                                                <button type="button" class="btn btn-sm btn-info" onclick="basicImg();">기본 이미지</button>
                                                </div>
                                            </div>
                                        
                                        </div>

                                        <!-- row -->
                                        <div class="form-group row">
                                        <label for="birthday" class="col-sm-2 col-form-label text-center">생년월일</label>
                                        <div class="col-sm-4 col-lg-3">
                                            <div class="input-group date" id="birthdayDiv" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#birthdayDiv" name="birthday" id="birthday" value='{{ $v->birthday ?? '' }}' maxlength="10" size="6" disabled/>
                                            <div class="input-group-append" data-target="#birthdayDiv" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="icheck-primary d-inline align-sub mr-1">
                                            <input type="radio" id="radioPrimary1" name="birth_type" value="Y" {{ $v->birth_type == 'Y' ? 'checked' : '' }} checked disabled>
                                            <label for="radioPrimary1">양력</label>
                                            </div>
                                            <div class="icheck-primary d-inline align-sub">
                                            <input type="radio" id="radioPrimary2" name="birth_type" value="N" {{ $v->birth_type == 'N' ? 'checked' : '' }} disabled>
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
                                        <div class="col-sm-9">

                                            <div class="input-group col-2 pl-0 pb-1">
                                                <input type="text" class="form-control form-control-sm" id="zip" name="zip" numberOnly="true" value="{{ $v->zip ?? '' }}" size="6" readOnly>
                                            </div>
                                            <input type="text" class="form-control form-control-sm mb-1" id="addr11" name="addr11" value="{{ $v->addr11 ?? '' }}" readOnly>
                                            <input type="text" class="form-control form-control-sm" id="addr12" name="addr12" value="{{ $v->addr12 ?? '' }}" maxlength="100" readonly>

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
                                            <input type="text" class="form-control form-control-sm" id="ph34" name="ph34" placeholder="" value="{{ $v->ph34 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);" size="4" disabled>
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
                                        <div class="col-sm-4 col-lg-3">
                                            <div class="input-group date" id="ipsaDiv" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#ipsaDiv" id="ipsa" value='{{ $v->ipsa ?? '' }}' maxlength="10" size="6" disabled/>
                                            <div class="input-group-append" data-target="#ipsaDiv" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                            </div>
                                        </div>
                                        </div>

                                        <!-- row -->
                                        <div class="form-group row">
                                        <label for="toesa" class="col-sm-2 col-form-label text-center">퇴사일</label>
                                        <div class="col-sm-4 col-lg-3">
                                            <div class="input-group date" id="toesaDiv" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#toesaDiv" id="toesa" value='{{ $v->toesa ?? '' }}' maxlength="10" size="6" disabled/>
                                            <div class="input-group-append" data-target="#toesaDiv" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                            </div>
                                        </div>
                                        </div>
                                        <!-- row -->
                                        <div class="form-group row mb-0">
                                        <label for="ip" class="col-sm-2 col-form-label text-center">IP</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control form-control-sm " id="access_ip" placeholder="000.000.000.000, 000.000.000.000, 000.000.000.000" value="{{ $v->access_ip ?? '' }}" maxlength="50" readonly>
                                        </div>
                                        </div>



                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-right">
                            <button type="button" class="btn btn-sm btn-info" onclick="myInfoAction();">저장</button>
                        </div>
                    </div>
                </div>

                <!-- 비밀번호 변경 탭 -->
                <div class="tab-pane fade {{ $tab == 'pwd' ? 'show active' : '' }}" id="nav-changePwd" role="tabpanel" aria-labelledby="nav-changePwd-tab">
                    <div class="card">
                        <form id="pwdChangeForm">
                            <div class="card-body row justify-content-center">
                                <div class="form-group col-10 mb-2">
                                    <label for="exampleInputEmail1">현재 비밀번호 <span class="text-danger" id="currentPwdMsg"></span></label>
                                    <input type="password" class="form-control" id="currentPwd" name="currentPwd" onkeyup="currentPwdFunc();">
                                </div>
                                <div class="form-group col-10 mb-2">
                                    <label for="exampleInputEmail1">변경할 비밀번호 <span class="text-danger" id="changePwdMsg"></span></label>
                                    <input type="password" class="form-control" id="changePwd" name="changePwd" onkeyup="changePwdFunc();">
                                </div>
                                <div class="form-group col-10 mb-2">
                                    <label for="exampleInputEmail1">비밀번호 확인 <span class="text-danger" id="checkPwdMsg"></span></label>
                                    <input type="password" class="form-control" id="checkPwd" name="checkPwd" onkeyup="checkPwdFunc();">
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button type="button" class="btn btn-sm btn-info" id="pwdChangeBtn" onclick="changePwdAction();">변경</button>
                            </div>
                        </form>
                    </div> <!-- /.card -->
                </div>


                <!-- 로그인 기록 -->
                <div class="tab-pane fade" id="nav-loginLog" role="tabpanel" aria-labelledby="nav-loginLog-tab">
                    <div class="p-0" id="loginLogDiv">
                    
                    </div>
                </div>
            </div>
        </div> <!-- /.col -->
    </div> <!-- /.row -->
</section> <!-- /.content -->
@endsection

@section('javascript')
<script>
function loginLogTab() {
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$.ajax({
		url: "/intranet/myloginlog",
		type: "get",
        dataType: 'html',
		success: function (result) {
            $("#loginLogDiv").html(result);
		},
		error: function (xhr) {
			alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});
}

// 회원정보창 Action
function myInfoAction() {
	if (!myInfoDataValidation()) {
		return false;
	}

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	var formData = new FormData($('#myInfoForm')[0]);

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
		url: "/intranet/myinfoaction",
		type: "post",
		data: formData,
		enctype: 'multipart/form-data',
		contentType: false,
		processData: false,
		success: function (result) {
			alert(result.msg);
			if(result.result == 'Y') {
                location.reload();
			}
		},
		error: function (xhr) {
			alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});
}

// 직원정보 form 데이터 유효성 검사
function myInfoDataValidation() {
	// 19|20 으로 시작
	var dayRegExp = /^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1])$/;

	if ($("#birthday").val()) {
		if (!dayRegExp.test($("#birthday").val())) {
			alert("생년월일을 확인해주세요.");
			$("#birthday").focus();
			return false;
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

// 비밀번호 변경 탭
function changePwdFunc() {
    var pw = $("#changePwd").val();
    var id = $("#id").val();
    var birth = $("#ssn11").val().substring(2,6);
    var ssn11 = $("#ssn11").val();
    var ssn12 = $("#ssn12").val();
    var ph22 = $("#ph22").val();
    var ph23 = $("#ph23").val();
    var checkNumber  = pw.search(/[0-9]/g);
    var checkEnglish = pw.search(/[a-zA-Z]/ig);
    var checkSpecial = pw.search(/[~!@#$%^=&*()_+-]/ig);

    {{-- console.log(birth);
    console.log(ssn11);
    console.log(ssn12);
    console.log(ph22);
    console.log(ph23); --}}
    checkPwdFunc();

    if(pw != "") {
        if(pw.length > 20 || pw.length < 8) {
            $("#changePwd").removeClass('is-valid');
            $("#changePwd").addClass('is-invalid');
            $("#changePwdMsg").html('패스워드 길이를 확인해주세요. (8자 ~ 20자)');
        } else {
            if(checkNumber < 0 || checkEnglish < 0 || checkSpecial < 0){
                $("#changePwd").removeClass('is-valid');
                $("#changePwd").addClass('is-invalid');
                $("#changePwdMsg").html('패스워드에 영문, 숫자, 특수문자를 혼용해주세요.');
            } else if(pw.search(id) > -1) {
                $("#changePwd").removeClass('is-valid');
                $("#changePwd").addClass('is-invalid');
                $("#changePwdMsg").html('비밀번호에 아이디가 포함되어있습니다.');
            } else if(birth!= "" && pw.search(birth) > -1) {
                $("#changePwd").removeClass('is-valid');
                $("#changePwd").addClass('is-invalid');
                $("#changePwdMsg").html('비밀번호에 생일이 포함되어있습니다.');
            } else if( (ssn11!= "" && pw.search(ssn11) > -1) || (ssn12!= "" && pw.search(ssn12) > -1) )  {
                $("#changePwd").removeClass('is-valid');
                $("#changePwd").addClass('is-invalid');
                $("#changePwdMsg").html('비밀번호에 주민번호가 포함되어있습니다.');
            } else if((ph22!= "" && pw.search(ph22) > -1 )|| (ph23!= "" && pw.search(ph23) > -1)) {
                $("#changePwd").removeClass('is-valid');
                $("#changePwd").addClass('is-invalid');
                $("#changePwdMsg").html('비밀번호에 전화번호가 포함되어있습니다.');
            } else {
                $("#changePwd").removeClass('is-invalid');
                $("#changePwd").addClass('is-valid');
                $("#changePwdMsg").html('');
                return true;
            }
        }
    } else {
        $("#changePwd").removeClass('is-valid');
        $("#changePwd").addClass('is-invalid');
        $("#changePwdMsg").html('변경할 패스워드를 입력해주세요.');
    }

    return false;
}

function checkPwdFunc() {

    var pw = $("#checkPwd").val();
var id = $("#id").val();
    if(pw != "") {
        if (pw == $('#changePwd').val()) {
            $("#checkPwd").removeClass('is-invalid');
            $("#checkPwd").addClass('is-valid');
            $("#checkPwdMsg").html('');
            return true;
        } else {
            $("#checkPwd").removeClass('is-valid');
            $("#checkPwd").addClass('is-invalid');
            $("#checkPwdMsg").html('변경할 패스워드와 일치하지 않습니다.');
        }
    } else {
        $("#checkPwd").removeClass('is-valid');
        $("#checkPwd").addClass('is-invalid');
        $("#checkPwdMsg").html('변경할 패스워드를 다시 입력해주세요.');
    }

    return false;
}

function currentPwdFunc() {
    if ($("#currentPwd").val() == '') {
        $("#currentPwd").removeClass('is-valid');
        $("#currentPwd").addClass('is-invalid');
        $("#currentPwdMsg").html('현재 비밀번호를 입력해주세요.');
        return false;
    } else {
        $("#currentPwd").addClass('is-valid');
        $("#currentPwd").removeClass('is-invalid');
        $("#currentPwdMsg").html('');
        return true;
    }
}

function changePwdAction() {
    if (!currentPwdFunc() || !changePwdFunc() || !checkPwdFunc()) {
        return false;
    }

    $("#pwdChangeBtn").attr('disabled', true);
    var formData = $("#pwdChangeForm").serialize();

    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

    $.ajax({
		url: "/intranet/myinfopwdaction",
		type: "post",
		data: formData,
		success: function (data) {
            if(data.result == 'F') {
                $("#currentPwd").removeClass('is-valid');
                $("#currentPwd").addClass('is-invalid');
                $("#currentPwdMsg").html(data.msg);
            } else if (data.result == 'R') {
                $("#changePwd").removeClass('is-valid');
                $("#changePwd").addClass('is-invalid');
                $("#changePwd").val('');
                $("#changePwdMsg").html(data.msg);
                $("#checkPwd").val('');
                checkPwdFunc();
            }else {
                alert(data.msg);
            }

			if(data.result == 'Y') {
                location.reload();
			}
		},
		error: function (xhr) {
			alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});
    
    $("#pwdChangeBtn").attr('disabled', false);
}

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
</script>
@endsection