@extends('layouts.master')
@section('content')



<div class="modal fade" id="subCodeModal">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header">
              <h4 class="modal-title">하위코드관리</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body" id="subCodeForm">
              [[[CONTENTS]]]
          </div>
          <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
              <div class="p-0">
                  <button type="button" class="btn btn-sm btn-info" onclick="subCodeAction('');">저장</button>              
              </div>
          </div>
      </div>
      <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- Main content -->
<section class="content">
<div class="container-fluid">




    <div class="row">
    <div class="col-md-4">
            <div class="card card-lightblue">
                <div class="card-header ">
                  <div class="row"> 
                  <h3 class="card-title col-md-4" style="width:100%">
                    카테고리 리스트
                  </h3>
                  <div class="col-md-5">
                    <form class="" onsubmit="setCateList();return false;">
                    <div class="input-group  " >
                      <input class="form-control form-control-navbar form-control-sm" type="search" placeholder="Enter" aria-label="Search" name="search_detail" id="search_detail">
                      <div class="input-group-append">
                        <button class="btn btn-sm" type="submit">
                          <i class="fas fa-search"></i>
                        </button>
                      </div>
                    </div>
                    </form>
                  </div>
                  <div class="col-md-3 text-right"><h3 style="cursor: pointer;" onclick="setCateForm('');">신규등록</h3></div>
                </div>
                </div>
                <div class="card-body" id="cateList" style="height: 490px;">
                </div>
            </div>
          </div>
    <div class="col-md-8">
    <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title" style="width:100%">
                코드 리스트
                <div class='float-right' onclick="setCodeForm('');">신규등록</div>                  
                </h3>
                </div>
                <div class="card-body" id="codeList" style="height: 490px;">
                </div>
            </div>
          </div>
    </div>    


    <div class="row">

    <div class="col-md-4">
            <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title">카테고리 등록</h3>
                </div>

                <form class="form-horizontal" name="cate_form" id="cate_form">
                <div class="card-body" id="cateForm">

                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-sm btn-info float-right ml-2" id="cate_btn" onclick="cateAction();">저장</button>

                    <button type="button" class="btn btn-sm btn-danger float-right" id="cate_btn_del" onclick="cateActionDel();">삭제</button>
                </div>
                </form>

                
            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title">코드 등록</h3> 
                </div>

                <form class="form-horizontal" name="code_form" id="code_form">
                <div class="card-body" id="codeForm">

                </div>
                <div class="card-footer">
                  
                  <button type="button" class="btn btn-sm btn-info float-right ml-2" id="code_btn" onclick="codeAction();">저장</button>
                  
                  <button type="button" class="btn btn-sm btn-danger float-right" id="code_btn_del" onclick="codeActionDel();">삭제</button>
                  
                  
                </div>
                </form>
            </div>
        </div>
        
    </div>

    <button type="button" class="btn btn-sm btn-default" id="code_btn" onclick="cacheClear();">Cache Clear</button>
    



</div>
</section>
<!-- /.content -->



@endsection






@section('javascript')
<script>



function setCateList()
{
  var search = $('#search_detail').val();
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $.post("/config/codelist", {gubun:'CATE',search: search}, function(data) {
      $("#cateList").html(data);
      setCateForm('');
  });
}
function setCateForm(cat_code)
{
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $("#cateForm").html(loadingString);
  $.post("/config/codeform", {gubun:'CATE', cat_code:cat_code}, function(data) {
      $("#cateForm").html(data);
      setCodeList(cat_code);
      $("#cate_btn_del").attr("disabled", ( $("#cate_form #mode").val()=="INS" ));
});
  //$("#topMenuList tbody tr").css("background", "#FFFFFF");
}



function setCodeList(cat_code)
{
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $("#codeList").html(loadingString);
  $.post("/config/codelist", {gubun:'CODE', cat_code:cat_code}, function(data) {
      $("#codeList").html(data);
      if(cat_code) setCodeForm('');
  });
}

function setCodeForm(code)
{
  var cat_code = $("#cate_form #cat_code").val();
  if( cat_code=="" )
  {
    alert("카테고리가 선택되지 않았습니다.")
    return false;
  }

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $.post("/config/codeform", {gubun:'CODE', cat_code:cat_code, code:code}, function(data) {
      $("#codeForm").html(data);
      $("#code_btn_del").attr("disabled", ( $("#code_form #mode").val()=="INS" ));
  });
}





setCateList();





//최상위메뉴 등록,수정
function cateAction()
{
  var cat_code = $("#cate_form #cat_code").val();

  if( cat_code.length<6 )
  {
    alert("카테고리코드는 6자 이상 입력가능합니다.")
    return false;
  }
  if( $("#cate_form #cat_name").val()=="" )
  {
    alert("카테고리명을 입력해주세요.")
    return false;
  }

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  var postdata = $('#cate_form').serialize();
  $.ajax({
		url  : "/config/codeaction",
		type : "post",
		data : postdata,
    success : function(result)
    {
      alert(result);
      setCateList();
		},
    error : function(xhr)
    {
      alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});
}

//서브메뉴 등록,수정
function codeAction()
{

  var cat_code = $("#code_form #cat_code").val();
  if( cat_code.length<6 )
  {
    alert("카테고리가 선택되지 않았습니다.");
    return false;
  }
  if( $("#code_form #code").val()=="" )
  {
    alert("코드를 입력해주세요.")
    return false;
  }
  if( $("#code_form #name").val()=="" )
  {
    alert("카테고리명을 입력해주세요.")
    return false;
  }
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  var postdata = $('#code_form').serialize();
  $.ajax({
		url  : "/config/codeaction",
		type : "post",
		data : postdata,
    success : function(result)
    {
      alert(result);
      setCodeList(cat_code);
		},
    error : function(xhr)
    {
      alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});
}

function cateActionDel()
{
  var cat_code = $("#cate_form #cat_code").val();
  if( !confirm("카테고리를 삭제하시겠습니까?") ) return false;

  var gubun   = $("#cate_form #gubun").val();
  var mode    = "DEL";

  $.post("/config/codeaction", {gubun:gubun, mode:mode, cat_code:cat_code}, function(data) {
    setCateList();
    alert(data);
  });
}

function codeActionDel()
{
  var cat_code = $("#code_form #cat_code").val();
  if( !confirm("코드를 삭제하시겠습니까?") ) return false;

  var gubun   = $("#code_form #gubun").val();
  var mode    = "DEL";
  var code = $("#code_form #code").val();

  $.post("/config/codeaction", {gubun:gubun, mode:mode, cat_code:cat_code, code:code}, function(data) {
    setCodeList(cat_code);
    setCodeForm('');
    alert(data);
  });
}

function subCodeForm()
{
  var cat_code = $('#cat_code').val();
  var conf_code = $('#code').val();
  
  // return;
  if(!conf_code || conf_code=='')
  {
    alert('코드를 선택 후 이용해 주세요.');
    return;
  }
  
  $("#subCodeModal").modal('show');

  // CORS 에러방지
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  
  // 로딩 스피너
  $("#subCodeForm").html(loadingString);

  // 데이터 가져와서 리스트로 만들기
  $.post("/config/subcodeform", {cat_code:cat_code, conf_code:conf_code}, function(data) {
      $("#subCodeForm").html(data);
      
      afterAjax();
      
  });

}



function subCodeAction()
{
  
  // 입력값 체크  
  var chk = true;
  var checkName = ['input[name="sub_code[]"]', 'input[name="sub_code_name[]"]', 'input[name="conf_order[]"]'];
  
  checkName.forEach(function(name){
      if(chk==false)
      {
        return false;
      }
      
      var chkCodes = '';
      $(name).each(function(index, item){

          if(!item.value)
          {
            alert('필수 입력값을 확인해 주세요.');
            chk = false;
            item.focus();
            return false;
          }

          // 중복체크
          if(chkCodes.indexOf('|'+item.value.toLowerCase()+'|')>=0)
          {
            alert('중복된 하위코드가 있습니다. 다시 확인해 주세요.');
            chk = false;
            item.focus();
            return false;
          }

          // 중복값이 있는지 체크하기 문자열
          if(name=='input[name="sub_code[]"]')
          {
            chkCodes += '|'+ item.value.toLowerCase() + '|';
          }
      });
  });

  if(chk==false)
  {
    return;
  }

  var postdata = $('#subCodeInputForm').serialize();
    
  $.ajax({
      url  : "/config/subcodeaction",
      type : "post",
      data : postdata,
      success : function(result)
      {
          alert(result.rs_msg);

          setCodeForm($('#code').val());
          subCodeForm();
      },
      error : function(xhr)
      {
          alert("이전 입력 내역이 있는 코드 입니다. 다른 코드를 사용하거나 관리자에게 문의해 주세요");
      }
  });

}


$(document).on("click","#cateList div table tbody tr", function() {
  $(this).closest('table').find('tr').removeClass('bg-click');
  $(this).addClass('bg-click');
});
$(document).on("click","#codeList div table tbody tr", function() {
  $(this).closest('table').find('tr').removeClass('bg-click');
  $(this).addClass('bg-click');
});

function cacheClear()
{   
  var url = "/config/cacheclear";
  $.post(url, {}, function(data) {
    alert(data);
  });
}

</script>
@endsection