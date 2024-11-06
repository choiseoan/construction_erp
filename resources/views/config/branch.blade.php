@extends('layouts.master')
@section('content')

  <!-- Main content -->
  <section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-5">
        <div class="card card-lightblue">
          <div class="card-header">
            <h3 class="card-title" style="width:100%">
              부서정보 조직도
            </h3>
          </div>
          <div class="card-body" id="branchList" style="height: 620px;">
          </div>
        </div>
      </div>
      <div class="col-md-7">
        <div class="card card-lightblue">
          <div class="card-header">
            <h3 class="card-title" style="width:100%">
              부서정보 등록
              <div class='float-right' onclick="setBranchForm('');">신규등록</div>                  
            </h3>
          </div>
          <div class="card-body" id="branchForm" style="">
          </div>
          <form class="form-horizontal">
            <div class="card-footer">
              <button type="button" class="btn btn-sm btn-info float-right ml-2" id="branch_btn" onclick="branchAction();">저장</button>
              <button type="button" class="btn btn-sm btn-danger float-right" id="branch_btn_del" onclick="branchActionDel();">삭제</button>
            </div>
          </form>
        </div>
      </div>
    </div>    
  </div>
</section>
<!-- /.content -->

@endsection










@section('javascript')
<script>

  // 리스트 세팅
  function setBranchList()
  {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $.post("/config/branchlist", {}, function(data) {
      $("#branchList").html(data);
    });
  }

  // 폼 세팅
  function setBranchForm(code)
  {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    // 로딩페이지 띄우기
    $("#branchForm").html(loadingString);
    $.post("/config/branchform", {code:code}, function(data) {
      $("#branchForm").html(data);
      $("#branch_btn_del").attr("disabled", ( $("#branch_mode").val()=="INS" ));
      enterClear();

      // Input Mask
      setInputMask('class', 'dateformat', 'date');

      //Date range picker
      $('#div_open_date').datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
		    useCurrent: false,
      });

      $('#div_close_date').datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
		    useCurrent: false,
      });
    });
  }

  //최상위메뉴 등록,수정
  function branchAction()
  {

    if( $("#parent_code").val()=="" )
    {
      alert("상위부서를 선택해주세요.")
      return false;
    }

    if( $("#code").val()=="" )
    {
      alert("부서코드를 입력해주세요.")
      return false;
    }

    if( $("#parent_code").val()==$("#code").val() )
    {
      alert("상위부서코드와 부서코드가 같습니다.");
      return false;
    }

    if( $("#branch_name").val()=="" )
    {
      alert("부서명을 입력해주세요.")
      return false;
    }

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var postdata = $('#br_form').serialize();

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });  

    $.ajax({
      url  : "/config/branchaction",
      type : "post",
      data : postdata,
      success : function(result)
      {
        setBranchList();
        alert(result);
      },
      error : function(xhr)
      {
        alert("통신오류입니다. 관리자에게 문의해주세요.");
      }
    });

  }

  // 부서 삭제
  function branchActionDel()
  {
    var pcode = $("#top_menu_cd").val();  
    if( !confirm("부서를 삭제하시겠습니까?") ) return false;

    var mode = "DEL";
    var code = $("#code").val();

    $.post("/config/branchaction", {mode:mode, save_status:'N', code:code}, function(data) {
      setBranchList();
      setBranchForm('');
      alert(data);
    });
  }

  // 엔터막기
  function enterClear()
  {
    $('input[type="text"]').keydown(function() {
      if (event.keyCode === 13)
      {
        event.preventDefault();
      };
    });

    $("input[data-bootstrap-switch]").each(function() {
      $(this).bootstrapSwitch('state', $(this).prop('checked'));
    });
  }

  // 리스트, 폼 세팅
  setBranchList('');
  setBranchForm('');

  // 부서 클릭시 색 변경
  $(document).on("click","#branchList div table tbody tr", function() {
    $(this).closest('table').find('tr').removeClass('bg-click');
    $(this).addClass('bg-click');
  });

</script>
@endsection