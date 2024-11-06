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
                            부서
                        </h3>
                    </div>
                    <div class="card-body" id="branchList" style="height: 620px;">
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

@section('lump')
일괄처리할거 입력
@endsection


@section('javascript')
<script>

// 직원관리 modal show 동작
function setUserForm(id) {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#userModal").modal('show');
	$("#userModalContent").html(loadingString);
	$.post("/config/userform", { id: id }, function (data) {
		$("#userModalContent").html(data);
	});
}

// 조직도 세팅
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
    // 직원 목록 정렬옵션 및 행 색상 초기화
    $('#userListHeader').removeClass('bg-click');
    $('#listOrderuser').val('');
    $('#listOrderAscuser').val('');
    $('.orderIcon').removeClass('fas fa-arrow-down');
    $('.orderIcon').removeClass('fas fa-arrow-up');

    $('#customSearchuser').val(code);
    listRefresh();
}

// 부서 클릭시 색 변경
$(document).on("click","#branchList div table tbody tr", function() {
    $(this).closest('table').find('tr').removeClass('bg-click');
    $(this).addClass('bg-click');
});

// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
        if (event.keyCode === 13)
        {
            event.preventDefault();
            listRefresh();
        };
    });

    $("input[data-bootstrap-switch]").each(function() {
        $(this).bootstrapSwitch('state', $(this).prop('checked'));
    });
}

setBranchList();
enterClear();
</script>
@endsection