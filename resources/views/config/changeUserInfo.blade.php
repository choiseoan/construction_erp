@extends('layouts.master')
@section('content')


<!-- Main content -->
<section class="content">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="card card-lightblue">
                <div class="card-header">
                    <h3 class="card-title" style="width:100%">
                    직원정보 리스트
                    </h3>
                </div>
                <div class="card-body" style="height: 720px;">

                    <form name="form_change" id="form_change" method="post">
                    <input type="hidden" id="order_colm" name="order_colm" value="">
                    <input type="hidden" id="order_type" name="order_type" value="">

                    <div class="card-tools row">
                        <select class="form-control select2 form-control-sm col" id="branch_code" name="branch_code">
                        <option value=''>전체부서</option>
                        {{ Func::printOptionArray($array_branch, 'branch_name', '') }}
                        </select>

                        <div class="input-group input-group-sm col">
                        <input type="text" name="search_string" class="form-control float-right" placeholder="Search">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default" onclick="setUserList('','');"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    </form>

                    <div class="mt-2" id="permitUserList">
                        <div class="text-center pt-5">직원을 검색해주세요.</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            @include('inc/list')
        </div>
    </div>
</div>
</section>
<!-- /.content -->



@endsection





@section('javascript')
<script>

enterClear();

// 선택직원 변경사항내역 출력
function setChangeUserInfo(id)
{
  $("input[type='hidden'][name='id']").val(id);
  listRefresh();
}

function listAdd()
{
  $("#tabsChangeuser").val('Y');
  listRefresh();
}


// 직원출력
function setUserList(oc, ot)
{
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $("#order_colm").val(oc);
  $("#order_type").val(ot);
  $("#permitUserList").html(loadingString);

  var postdata = $('#form_change').serialize();
  
  $.ajax({
		url  : "/config/changeusertarget",
		type : "post",
		data : postdata,
    success : function(result)
    {
      $("#permitUserList").html(result);
		},
    error : function(xhr)
    {
      $("#permitUserList").html("통신오류입니다. 관리자에게 문의해주세요.");
		}
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


$(document).on("click","#permitUserList div table tbody tr", function() {
  $(this).closest('table').find('tr').removeClass('bg-click');
  $(this).addClass('bg-click');
});


$("input:checkbox[id^='lists']").prop("checked",  true);
listAdd();

</script>
@endsection
