@extends('layouts.master')

@section('content')  

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card card-lightblue">
                    <div class="card-header">
                        <h3 class="card-title" style="width:100%">
                        직업코드 리스트
                        </h3>
                    </div>
                    <div class="card-body table-responsive" id="jobList" style="height: 490px;">
                        [[ CONTENT ]]
                    </div>
                </div>
            </div>

            <!-- 직업코드 등록 form -->
            <div class="col-md-4">
                <div class="card card-lightblue">
                    <div class="card-header">
                        <h3 class="card-title">직업코드 등록</h3>
                        <div class='float-right' onclick="setJobForm('','');">신규등록</div>
                    </div>
                    <form class="form-horizontal" name="jobForm" id="jobForm">
                    <input type="hidden" name="oldJobCode" id="oldJobCode" value="">
                    <input type="hidden" name="mode" id="mode" value="">
                    <div class="card-body" id="codeForm">
                        <div class="form-group ">
                            <label for="jobCode" class=" col-form-label">직업코드</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control form-control-sm" maxlength=5 id="jobCode" name="jobCode" placeholder="공백없는 영문 또는 숫자"  value="">
                            </div>
                        </div>
                        <div class="form-group ">
                            <label for="jobName" class=" col-form-label">직업코드명</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control form-control-sm" id="jobName" name="jobName" placeholder="한글등록" value="">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-sm btn-info float-right ml-2" id="code_btn" onclick="jobAction('UPD');">등록</button>
                        <button type="button" class="btn btn-sm btn-danger float-right" id="code_btn_del" onclick="jobAction('DEL');">삭제</button>
                    </div>
                    </form>
                    <!--테스트 삭제 예정-->
                        <button type="button" class="btn btn-sm btn-info float-right ml-2"  onclick="getJobCode('job_code');">팝업테스트</button>
                        <input type="text" name="job_codename1" id="job_codename1" value="">
                        <input type="text" name="job_code1" id="job_code1" value="">
                        <input type="text" name="job_codename2" id="job_codename2" value="">
                        <input type="text" name="job_code2" id="job_code2" value="">
                        <input type="text" name="job_codename3" id="job_codename3" value="">
                        <input type="text" name="job_code" id="job_code" value="">
                    <!--테스트 삭제 예정-->
                </div>
            </div>    
        </div>
    </div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
<script>

/**
 * 직업코드 리스트 가져오기
 *
**/
function setJobList(jobCode)
{
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $("#jobList"+jobCode).html(loadingString);
  $.post("/config/jobcodelist", {jobcode:jobCode}, function(data) {
      $("#jobList"+jobCode).html(data);
  });
}
setJobList('');

/**
 * 직업 코드 등록 폼 세팅
 *
**/
function setJobForm(jobCode,jobName){
    $("#oldJobCode").val(jobCode);
    $("#jobCode").val(jobCode);
    $("#jobName").val(jobName);
}


/**
 * 직업코드 등록/수정/삭제 action 
 *
**/
function jobAction(mode)
{
    //유효성 체크
    var jobCode = $("#jobForm #jobCode").val();
    if( jobCode.length<4 )
    {
        alert("코드를 입력해주세요.(4자리)");
        return false;
    }
    if( $("#jobForm #jobName").val()=="" )
    {
        alert("직업코드명을 입력해주세요.")
        return false;
    }

    $('#mode').val(mode);

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#jobForm').serialize();
    $.ajax({
        url  : "/config/jobaction",
        type : "post",
        data : postdata,
    success : function(result)
    {
        alert(result);
        setJobList('');
    },
    error : function(xhr)
    {
        alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}


/**
*   (공통) 직업코드 검색 팝업
*   jobId : 최종코드저장 ID 
*   전달된 파라미터 기준 ID+1~4 있으면 세팅
*   전달된 파라미터 기준 ID+name 1~4 있으면 세팅
*   전달된 파라미터 기준 ID+str 전체 name text 세팅 
*/
function getJobCode(jobId)
{
    window.open("/config/jobcodepop?jobId="+jobId, "msgInfo", "width=800, height=350, scrollbars=no");
}

</script>
@endsection