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
                부서정보 조직도
                </h3>
                </div>
                <div class="card-body" id="permitBranchList" style="height: 720px;">

                  <div class="card-body table-responsive p-0" style="height: 680px;">
                  <table class="table table-sm table-hover table-head-fixed text-nowrap">
                  <thead>
                  <tr>
                  <th>부서코드</th>
                  <th>부서명</th>
                  </tr>
                  </thead>
                  <tbody>

                  @forelse( $branch as $value )

                  <tr onclick="setPermitBranchForm('{{ $value['code'] }}');">
                  <td>{{ $value['code'] }}</td>
                  <td style="padding-left: {{ $value['branch_depth']*40 }}px;">{{ $value['branch_name'] }}</td>
                  </tr>

                  @empty

                  <tr>
                  <td colspan=2 class='text-center p-4'>등록된 부서정보가 없습니다.</td>
                  </tr>

                  @endforelse

                  </table>
                  </div>

                </div>
            </div>
          </div>
    <div class="col-md-9">
    <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title" style="width:100%">
                부서별 권한 관리
                </h3>
                </div>
                <div class="card-body" id="permitBranchForm" >

                <form class="form-horizontal" name="permit_form" id="permit_form">
                <input type="hidden" name="branch_cd" id="branch_cd" value="">

                <div class="row">
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
                <button type="button" class="btn btn-sm btn-info float-right ml-2" id="branch_btn" onclick="permitAction();">저장</button>
                <button type="button" class="btn btn-sm btn-secondary float-right ml-2" id="branch_btn_all" onclick="setMenus('ALL');">전체선택</button>
                <button type="button" class="btn btn-sm btn-secondary float-right ml-2" id="branch_btn_non" onclick="setMenus('');">전체해제</button>
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


function setPermitBranchForm(code)
{
  $("#permit_form #branch_cd").val(code);

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $("#branch_btn").html(loadingStringtxt);
  $.post("/config/permitbranchmenus", {code:code}, function(data) {
    $("#branch_btn").html('저장');
    setMenus(data);
  });

}

function setMenus(menus)
{
  $("input[type=checkbox]").prop("checked",false);

  if( menus=="ALL" )
  {
    $("input[type=checkbox]").prop("checked",true);
  }
  else
  {
    menus.split(",").map(function(n) { $("input:checkbox[id='menu_id_"+n+"']").prop("checked", true); } );
  }
}







//최상위메뉴 등록,수정
function permitAction()
{

  if( $("#permit_form #branch_cd").val()=="" )
  {
    alert("부서를 선택해주세요.");
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
		url  : "/config/permitbranchaction",
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



$(document).on("click","#permitBranchList div table tbody tr", function() {
  $(this).closest('table').find('tr').removeClass('bg-click');
  $(this).addClass('bg-click');
});

</script>
@endsection