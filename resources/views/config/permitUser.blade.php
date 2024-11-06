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

                  <form name="search_form" id="search_form" method="post" onsubmit="setUserList('',''); return false;">
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
    <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title" style="width:100%">
                직원별 권한 관리
                </h3>
                </div>
                <div class="card-body" id="permitBranchForm">

                <form class="form-horizontal" name="permit_form" id="permit_form">
                <input type="hidden" name="user_id" id="user_id" value="">

                <div class="row"  style="overflow:scroll;  height:650px;">
                <div class="col-md-11 pt-1 pb-2">
                    <span class="btn btn-xs btn-secondary mt-1" onclick="setPermit('01');">
                        <i class="nav-icon fas fa-users"></i>
                        심사
                    </span>
                    <span class="btn btn-xs btn-secondary mt-1" onclick="setPermit('02');">
                        <i class="nav-icon fas fa-users"></i>
                        회수
                    </span>
                    <span class="btn btn-xs btn-secondary mt-1" onclick="setPermit('03');">
                        <i class="nav-icon fas fa-users"></i>
                        연체
                    </span>
                    <span class="btn btn-xs btn-secondary mt-1" onclick="setPermit('04');">
                        <i class="nav-icon fas fa-users"></i>
                        결재
                    </span>
                    <span class="btn btn-xs btn-secondary mt-1" onclick="setPermit('05');">
                        <i class="nav-icon fas fa-users"></i>
                        마스터
                    </span>
                </div>
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

                  @foreach( $pvalue['sub'] as $cd => $value )

                    <div class="form-check ml-2">
                      <input type="checkbox" class="form-check-input pcd-{{ $pcd }}" name="menus[]" id="menu_id_{{ $value['code'] }}" value="{{ $value['code'] }}">
                      <label class="form-check-label" for="menu_id_{{ $value['code'] }}"> {{ $value['name'] }}</label>
                    </div>

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
                <button type="button" class="btn btn-sm btn-info float-right ml-2" id="user_btn" onclick="permitAction();">저장</button>
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
    if( $(this).prop("disabled")==false )
    {
      $(this).prop("checked", ops)
    }
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







function setPermitUserForm(id, cd)
{
  $("#permit_form #user_id").val(id);

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $("#user_btn").html(loadingStringtxt);
  $.post("/config/permitusermenus", {id:id,cd:cd}, function(data) {
    $("#user_btn").html('저장');
    setMenus(data);
  });
}

function setMenus(menus)
{
  $("input[type=checkbox]").prop("checked", false);
  $("input[type=checkbox]").prop("disabled",false);

  if( menus=="ALL" )
  {
    $("input[type=checkbox]").prop("checked",true); 
  }
  else
  {

    var menu2 = menus.split("|");
    var b_menu = menu2[0];
    var u_menu = menu2[1];

    b_menu.split(",").map(function(n) { $("input:checkbox[id='menu_id_"+n+"']").prop("disabled", true); } );
    b_menu.split(",").map(function(n) { $("input:checkbox[id='menu_id_"+n+"']").prop("checked",  true); } );
    u_menu.split(",").map(function(n) { $("input:checkbox[id='menu_id_"+n+"']").prop("checked",  true); } );

  }
}







//최상위메뉴 등록,수정
function permitAction()
{

  if( $("#permit_form #user_id").val()=="" )
  {
    alert("직원을 선택해주세요.");
    return false;
  }

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  var postdata = $('#permit_form').serialize();
  
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });  

  $.ajax({
		url  : "/config/permituseraction",
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

function setPermit(code)
{
    $('input[type=checkbox]').prop("checked", false);
        
    if(code == '01')
    {
        checkPcd('001',true);
        checkPcd('002',true);
    }
    if(code == '02')
    {
        checkPcd('001',true);
        checkPcd('003',true);
    }
    // 결재
    if(code == '03')
    {

    } 
    if(code == '04')
    {
        checkPcd('001',true);
        checkPcd('002',true);
        checkPcd('003',true);
        checkPcd('004',true);
    }
    if(code == '05')
    {
        $('input[type=checkbox]').prop("checked", true)
    }
}



$(document).on("click","#permitUserList div table tbody tr", function() {
  $(this).closest('table').find('tr').removeClass('bg-click');
  $(this).addClass('bg-click');
});

</script>
@endsection