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
                    <form name="form_change" id="form_change" method="post" onsubmit="setUserList('',''); return false;">
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
            <div class="card card-lightblue card-outline">
                <form class="form-horizontal" role="form" name="search_form" id="search_form" method="post">
                <input type='hidden' id='id' name='id' value='' >
                <div class="card-header">

                    <h3 class="card-title">
                    <i class="fas fa-search mr-1"></i> 
                    검색
                    </h3>
                    <div class="card-tools form-inline">
                        <select class="form-control select2 form-control-sm mr-1" id="search_date" name="search_date">
                            <option value=''>날짜</option>
                            <option value='save_time' selected>변경일시</option>
                        </select>
                        <div class="input-group date mr-1" id="sDateDiv" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#sDateDiv" name="sDate" id="sDate" maxlength="10" size="6"/>
                            <div class="input-group-append" data-target="#sDateDiv" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <span class="ml-1 mr-1">~</span>
                        <div class="input-group date mr-1" id="eDateDiv" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#eDateDiv" name="eDate" id="eDate" maxlength="10" size="6"/>
                            <div class="input-group-append" data-target="#eDateDiv" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <div class="btn-group mr-1 mb-1 mt-1">
                            <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("today", "sDate", "eDate")'>금일</button>
                            <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("week", "sDate", "eDate")'>금주</button>
                            <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("month", "sDate", "eDate")'>금월</button>
                        </div>

                        <div class="input-group input-group-sm float-right">
                            <!-- <input type="text" name="search_string" class="form-control " placeholder="Search"> -->
                            <div class="input-group-append">
                            <button type="button" class="btn btn-default" onclick="setPermitList('')"><i class="fas fa-search"></i></button>
                            <button type="button" class="btn btn-default" onclick="location.href='/config/changepermitinfo';"><i class="fa fa-sync-alt"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
                <div class="card-body table-responsive p-0" id="permitList">
                </div>
            </div>
        </div>
    </div>
</div>
</section>
<!-- /.content -->


@endsection


@section('javascript')
<script>

setPermitList('');
enterClear();

$('#sDateDiv').datetimepicker({
    format: 'YYYY-MM-DD',
    locale: 'ko',
	useCurrent: false,
});
$('#eDateDiv').datetimepicker({
    format: 'YYYY-MM-DD',
    locale: 'ko',
	useCurrent: false,
});

// 선택직원 변경사항내역 출력
function setChangeUserInfo(id)
{
    // 직원 선택시 금월변경내역 defalut 값으로 출력
    $('#id').val(id);
    //setDateUser("month", "sDate", "eDate");
    //$('#search_date').val('save_time').trigger('change');
    setPermitList(id);
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



// 에이전트 리스트 ajax
function setPermitList(id)
{

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#permitList").html(loadingString);

    var postdata = $('#search_form').serialize();

    $.ajax({
        url  : "/config/changepermitinfolist",
        type : "post",
        data : postdata,
        success : function(result)
        {
            $("#permitList").html(result);
        },
        error : function(xhr)
        {
            $("#permitList").html("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}

// 날짜 버튼 지정
function setDateUser(mode, startDt, endDt)
{
    // 종료일이 있으면 오늘.
    if(endDt!='')
        $('#' + endDt).val('{{ date("Y-m-d") }}');

    switch(mode)
    {
        case 'today' :
            $('#' + startDt).val('{{ date("Y-m-d") }}');
            break;

        case 'yesterday' :
                $('#' + startDt).val('{{ date("Y-m-d", time()-86400) }}');
                $('#' + endDt).val('');
                break;

        case 'week' :
            $('#' + startDt).val('{{ date("Y-m-d", strtotime("last Monday", time()+86400)) }}');
            $('#' + endDt).val('{{ date("Y-m-d", strtotime("next Sunday", time()+86400)) }}');
            break;

        case 'month' :
            $('#' + startDt).val('{{ date("Y-m") }}-01');
            $('#' + endDt).val('{{ date("Y-m-t") }}');
            break;
    }
    return false;
}
setDateUser("month", "sDate", "eDate");

// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
        if (event.keyCode === 13)
        {
            event.preventDefault();
        }
    });

    $("input[data-bootstrap-switch]").each(function() {
        $(this).bootstrapSwitch('state', $(this).prop('checked'));
    });
}

$(document).on("click","#permitUserList div table tbody tr", function() {
    $(this).closest('table').find('tr').removeClass('bg-click');
    $(this).addClass('bg-click');
});


</script>
@endsection