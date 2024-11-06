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

                  <form name="search_form" id="search_form" method="post" onSubmit="setUserList('',''); return false;">
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

                  <div class="mt-2" id="permitUserList" style="height: 680px;">
                  <div class="text-center pt-5">직원을 검색해주세요.</div>
                  </div>

                </div>
            </div>
          </div>
    <div class="col-md-9">
    <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title" style="width:100%">
                  직원별 권한 관리
                </h3>
                </div>
                <div class="card-body" id="permitBranchForm">

                <form class="form-horizontal" name="func_permit_form" id="func_permit_form">
                <input type="hidden" name="user_id" id="user_id" value="">

                <div class="row"  style="overflow:scroll;  height:650px;">
                @foreach( $menus as $pcd => $pvalue )
                  <div class="col-md-3">
                  <div class="card card-secondary">
                  <div class="card-header pt-1 pb-2">
                    <h3 class="card-title">
                      <i class="nav-icon fas fa-<?=($pvalue['icon']) ? $pvalue['icon'] : 'plus-square'; ?> mr-1"></i>
                      {{ $pvalue['name'] }}
                    </h3>
                  </div>
                  <div class="card-body">
                  @foreach( $pvalue['func'] as $cd => $value )

                    @foreach( $value as $key => $val )
                    <div class="form-check ml-2">
                      <input type="checkbox" class="form-check-input pcd-{{ $pcd }}" name="menus[]" id="menu_id_{{ $key }}" value="{{ $key }}">
                      <label class="form-check-label" for="menu_id_{{ $key }}"> {{ $val.' ('.$key.')' }}</label>
                    </div>
                    @endforeach

                  @endforeach

                  <button type="button" class="btn btn-xs btn-secondary float-right mt-1 ml-2" onclick="checkPcd('{{ $pcd }}', true );">일괄선택</button>
                  <button type="button" class="btn btn-xs btn-secondary float-right mt-1 ml-2" onclick="checkPcd('{{ $pcd }}', false);">일괄해제</button>
                  </div>
                  </div>
                  </div>

                @endforeach
                </div>
                </form>

                </div>
                <form class="form-horizontal">
                <div class="card-footer">
                <button type="button" class="btn btn-sm btn-info float-right ml-2" id="user_btn" onclick="funcPermitUserAction();">저장</button>
                <button type="button" class="btn btn-sm btn-secondary float-right ml-2" id="user_btn_all" onclick="setMenus('ALL');">전체선택</button>
                <button type="button" class="btn btn-sm btn-secondary float-right ml-2" id="user_btn_non" onclick="setMenus('');">전체해제</button>
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



function checkPcd( pcd, ops )
{
  $(".pcd-"+pcd).each(function() {
    $(this).prop("checked", ops)
  });
}





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

  var postdata = $('#search_form').serialize();
  
  $.ajax({
		url  : "/config/permituserlist",
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

// 기능권한 체크 박스
function setPermitUserForm(id, cd)
{
  $("#func_permit_form #user_id").val(id);

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $("#user_btn").html(loadingStringtxt);
  $.post("/config/funcpermitusermenus", {id:id,cd:cd}, function(data) {
    $("#user_btn").html('저장');
    setMenus(data);
  });
}

// 선택된 직원의 기능권한 체크박스 세팅
function setMenus(menus)
{
  // 초기값 빈 체크박스
  $("input[type=checkbox]").prop("checked", false);

  // 전체선택
  if( menus=="ALL" )
  {
    $("input[type=checkbox]").prop("checked",true); 
  }
  // 해당 직원의 기능권한
  else
  {
    menus.split(",").map(function(n) { $("input:checkbox[id='menu_id_"+n+"']").prop("checked",  true); } );
  }
}



//직원 기능권한 등록,수정
function funcPermitUserAction()
{

  if( $("#func_permit_form #user_id").val()=="" )
  {
    alert("직원을 선택해주세요.");
    return false;
  }

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  var postdata = $('#func_permit_form').serialize();
  
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });  

  $.ajax({
		url  : "/config/funcpermituseraction",
		type : "post",
		data : postdata,
      success : function(result)
      {
        alert(result);
      },
      error : function(xhr)
      {
        alert("통신오류입니다. 관리자에게 문의해주세요.");
      }
  }); 
}





$(document).on("click","#permitUserList div table tbody tr", function() {
  $(this).closest('table').find('tr').removeClass('bg-click');
  $(this).addClass('bg-click');
});

</script>
@endsection