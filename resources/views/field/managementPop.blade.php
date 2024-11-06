@extends('layouts.masterPop')
@section('content')

<!-- 화면 분할 (split.css) -->
<link rel="stylesheet" href="/css/split.css">

<style>

/* 상단고정 */
#fixed_top {    
  position: fixed;
  width: calc(75% - 10px);
  height: 38px;
  z-index:101;
  margin-left:0px;
  margin-bottom:0px;
  padding-left:0px;
}

/* 컨텐츠 높이 조정 */
#splitParent {
    top: 42.5px;
    z-index:100;
}

.pagination {
    margin-bottom:0px;
}
    .content::-webkit-scrollbar{
        width: 8px;
        height: 10px;
    }
    .content::-webkit-scrollbar-button {
        width: 8px;
    }
    .content::-webkit-scrollbar-thumb {
        background: #999;
        border: thin solid gray;
        border-radius: 10px;
    }
    .content::-webkit-scrollbar-track {
        background: #eee;
        border: thin solid lightgray;
        box-shadow: 0px 0px 3px #dfdfdf inset;
        border-radius: 10px;
    }


.user-nav-link {
  display: block;
  padding: 0.3rem 0.2rem;
  border: 1px solid transparent;
  border-top-left-radius: 0.25rem;
  border-bottom-left-radius: 0.25rem;
}
</style>

<input type="hidden" name="status_color" id="status_color" value="{{ $status_color }}">
<input type="hidden" name="cust_select" id="cust_select" value="">
<input type="hidden" id="out_loan_no" name="out_loan_no" value="{{ isset($rs->out_loan_no) ? $rs->out_loan_no : '' }}">
<div class="content-wrapper m-0" >
    <div class="row m-0">
        <!-- 왼쪽패널 -->
        <div class="col-md-12" >
            <!-- 현장자요약정보 -->
            <div class="col-md-12 card mt-1 mb-2 p-1" style="border:2px solid {{ $status_color }};">
                <table class="table table-sm table-borderless m-0">
                    <colgroup>
                        <col width="6%"/>
                        <col width="6%"/>
                        <col width="6%"/>
                        <col width="6%"/>
                        <col width="6%"/>
                        <col width="6%"/>
                        <col width="6%"/>
                        <col width="6%"/>
                        <col width="6%"/>
                        <col width="6%"/>
                        <col width="6%"/>
                        <col width="12%"/>
                    </colgroup>
                    <tbody>
                        <tr>
                            <th class="text-center">코드</th>
                            <td>{{ $info->code }}</td>
                            <th class="text-center">현장명</th>
                            <td>{{ $info->name ?? '' }}</td>
                            <th class="text-center">구분</th>
                            <td>{{ Func::getConfigArr('management_div')[$info->div] ?? '' }}</td>
                            <th class="text-center">발주처</th>
                            <td>{{ $info->orderer ?? '' }}</td>
                            <th class="text-center">공사금액</th>
                            <td>{{ Func::numberFormat($info->balance ?? 0) }}</td>
                            <th class="text-center">현장기간</th>
                            <td>{{ Func::dateFormat($info->contract_date, 'kor') }} ~ {{ Func::dateFormat($info->contract_end_date, 'kor') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

                    
            <!-- 현장정보 -->
            <div class="row pr-2">
                {{-- 왼쪽메뉴 --}}
                <div class="col-md-1 pr-0"  style="border-right:2px solid {{ $status_color }}; ">
                    <div class="nav flex-column nav-tabs" id="cust-tab" role="tablist" aria-orientation="vertical" >
                        <a class="nav-link text-bold mb-1 hand" id="cust-nav-managementinfo-tab" data-toggle="pill" role="tab" aria-selected="true" onclick="getManagementData('managementinfo');">현장관리표</a>
                        <a class="nav-link text-bold mb-1 hand" id="cust-nav-managementhistory-tab" data-toggle="pill" role="tab" aria-selected="false" onclick="getManagementData('managementhistory');">실행내역서</a>
                        <a class="nav-link text-bold mb-1 hand" id="cust-nav-managementcost-tab" data-toggle="pill" role="tab" aria-selected="false" onclick="getManagementData('managementcost');">일위대가</a>
                        <a class="nav-link text-bold mb-1 hand" id="cust-nav-managementmaterial-tab" data-toggle="pill" role="tab" aria-selected="false" onclick="getManagementData('managementmaterial');">자재단가표</a>
                    </div>
                </div>
                {{-- 메뉴클릭 출력화면 --}}
                <div class="col-md-11 pb-3 status-border-left-none bg-white " style='border-top-right-radius:4px; border-bottom-right-radius:4px;min-heignt:1000px;' style="min-heignt:1000px">
                    <div class="tab-content" id="customer-tabContent ">
                        <!--  상단 현장내역 출력 영역 -->
                        <div class="fade show active p-2" id="customer-contents"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection


@section('javascript')
<div class="modal fade" id="scheduleModal">
    <div class="modal-dialog modal-sm">
      <div class="modal-content" id="scheduleModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<script>

getManagementData('managementinfo');
function getManagementData(view, div, no, nn, tab='', ino=0, page=1)
{
    // CORS 방지
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 현재 선택된 메뉴가 무엇인지 저장
    $("#cust_select").val(view);
    var nowV = $("#cust_select").val(view);
    
    // 전체 메뉴 색 입히고, 선택항목만 흰 배경으로 변경
    resetMenuColor();
    selectMenuColor('cust', view);

    var li_no = "{{ $info->no }}";

    // 현장정보 받아오기
    var url = "/field/"+view;
    $("#customer-contents").html(loadingString);
    $.post(url, { mode:view, contract_info_no:li_no, div:div, no:no, page:page }, function(data) {
        $("#customer-contents").html(data);
        afterAjax();
    });
}

// 전체 메뉴 색상 변경
function resetMenuColor(color)
{
    if(!color)
    {
        color = $("#status_color").val();
    }
    
    // 고객상세정보 border 수정
    $(".status-border-right").css('border-right', '2px solid '+color);
    $(".status-border-left-none").css('border', '2px solid '+color);
    $(".status-border-left-none").css('border-left', 'none');
    $(".status-border-right-none").css('border', '2px solid '+color);
    $(".status-border-right-none").css('border-right', 'none');
    $(".status-border-right-none").css('background-color', color);
    $(".status-border").css('border', '2px solid '+color);

    // 고객상세정보 메뉴 배경 수정
    $("[id^='cust-nav-']").css('background-color', color);
    $("[id^='cust-nav-']").css('color', '#FFFFFF');

    // 고객메모정보 선택색 수정
    $(".card-custnav.card-outline-tabs > .card-header a.active ").css('border-top', '3px solid '+color);
    $(".card-custnav.card-outline-tabs > .card-header a.active ").css('background-color', color);
    $(".card-custnav.card-outline-tabs > .card-header a.active ").css('color', '#FFFFFF');
    $(".card-custnav.card-outline-tabs > .card-header a").css('border-top', '3px solid '+color);
    // $(".card-custnav.card-outline-tabs > .card-header a").css('color', color);

}


// 메뉴 선택 색상변경
function selectMenuColor(md, id)
{
    if(md=='right')
    {
        var color = $("#status_color").val();
        $(".card-custnav.card-outline-tabs > .card-header a").css('background-color', '#FFFFFF');
        $(".card-custnav.card-outline-tabs > .card-header a").css('color', 'black');
        $(".card-custnav.card-outline-tabs > .card-header a").css('border-top', '3px solid '+color);
        
        $("#right-menu-"+id+"-tab").css('background-color', color);
        $("#right-menu-"+id+"-tab").css('color', '#FFFFFF');
    }
    else
    {
        $("#"+md+"-nav-"+id+"-tab").css('background-color', '#FFFFFF');
        $("#"+md+"-nav-"+id+"-tab").css('color', 'black');
        $("#"+md+"-nav-"+id+"-tab").css('margin-right', '-3px');
        $("#"+md+"-nav-"+id+"-tab").css('border', '2px solid {{ $status_color }}');
        $("#"+md+"-nav-"+id+"-tab").css('border-right', 'none');
    }
}
function creditForm(no, list_no, code) {
        var width = 1000;
        var height = 1000;
        if (list_no == undefined) {
            list_no = '';
        }

        // 조기경보 팝업
        if (code == 'ews') {
            width = 1200;
        }

        window.open("/field/cust" + code + "pop?no=" + no + "&list_no=" + list_no, "erp_" + code + "pop_" + no, "left=0, top=0, width=" + width + ", height=" + height + ", scrollbars=yes");
    }


    function custcredit(no,div)
    {
        var width = 1000;
        var height = 1000;
        console.log(no);
        console.log(div);
        if(div=="K")
        {
            window.open("/field/custcreditget?no="+no,"credit","left=0,top=0,width=1000,height=1000, scrollbars=yes");
        }
        else if(div=="N")
        {
            window.open("/field/custcreditgetnice?no="+no,"credit","left=0,top=0,width=1000,height=1000, scrollbars=yes");
        }
    }

function clickManagementInfo(no)
{
    location.href='/field/managementpop?no='+no;
}

</script>
@endsection