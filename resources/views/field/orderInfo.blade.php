<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card shadow-sm" id="orderinfoInput">
                <form class="mb-0" name="order_form" id="order_form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="excelUrl" value="/field/orderinfoexcel">
                    <input type="hidden" name="excelDownCd" value="">
                    <input type="hidden" name="down_div" value="">
                    <input type="hidden" name="excel_down_div" value="">
                    <input type="hidden" name="down_filename" value="">
                    <input type="hidden" name="excelHeaders" value="">
                    <input type="hidden" id="order_info_no" name="order_info_no" value="{{ $v->no ?? '' }}">
                    @csrf

                    <!-- 발주서 헤더 -->
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="card-title pt-3 pl-3"><i class="fas fa-file-alt p-1"></i>발주서</h3>
                        </div>
                    </div>

                    <!-- 발주서 정보 입력 -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card-body pt-3 pl-3">
                                <table class="table table-sm table-input text-xs">
                                    <tbody>
                                        <tr height="34">
                                            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>현장명</th>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-center" id="field_name" name="field_name" placeholder="" data-target="#field_name" value="{{ $v->field_name ?? '' }}" />
                                            </td>
                                        </tr>
                                        <tr height="34">
                                            <th><span class="text-danger font-weight-bold h6 mr-1"></span>현장주소</th>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-center" id="field_addr" name="field_addr" placeholder="" data-target="#field_addr" value="{{ $v->field_addr ?? '' }}" />
                                            </td>
                                        </tr>
                                        <tr height="34">
                                            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>협력사명</th>
                                            <td>
                                                <div class="col-sm-12 ml-lg-n2">
                                                    <div class="row">
                                                        <select class="form-control form-control-sm col-md-12 mt-1 ml-2 text-center" name="partner_name" id="partner_name">
                                                            <option value=''></option>
                                                                {{ Func::printOption(Func::getArrayPartner(), $v->partner_name ?? '') }}
                                                        </select>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr height="34">
                                            <th><span class="text-danger font-weight-bold h6 mr-1"></span>담당부서</th>
                                            <td>
                                                <div class="col-sm-12 ml-lg-n2">
                                                    <div class="row">
                                                        <select class="form-control form-control-sm col-md-12 mt-1 ml-2 text-center" name="branch_code" id="branch_code">
                                                            <option value=''>미지정</option>
                                                                {{ Func::printOption(Func::getBranch(), $v->branch_code ?? '') }}
                                                        </select>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card-body pt-3 pl-3">
                                <table class="table table-sm table-input text-xs">
                                    <tbody>
                                        <tr height="34">
                                            <th><span class="text-danger font-weight-bold h6 mr-1"></span>발주일자</th>
                                            <td>
                                                <div class="input-group date datetimepicker" id="order_date_div" data-target-input="nearest">
                                                    <input type="text" class="form-control form-control-sm dateformat text-center" name="order_date" id="order_date" value="{{ Func::dateFormat($v->order_date ?? '') }}">
                                                    <div class="input-group-append" data-target="#order_date_div" data-toggle="datetimepicker">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr height="34">
                                            <th><span class="text-danger font-weight-bold h6 mr-1"></span>반입일자</th>
                                            <td>
                                                <div class="input-group date datetimepicker" id="import_date_div" data-target-input="nearest">
                                                    <input type="text" class="form-control form-control-sm dateformat text-center" name="import_date" id="import_date" value="{{ Func::dateFormat($v->import_date ?? '') }}">
                                                    <div class="input-group-append" data-target="#import_date_div" data-toggle="datetimepicker">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr height="34">
                                            <th><span class="text-danger font-weight-bold h6 mr-1"></span>인수자</th>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-center" id="receiver_name" name="receiver_name" placeholder="" data-target="#receiver_name" value="{{ $v->receiver_name ?? '' }}" />
                                            </td>
                                        </tr>
                                        <tr height="34">
                                            <th><span class="text-danger font-weight-bold h6 mr-1"></span>인수자 연락처</th>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-center" id="receiver_ph" name="receiver_ph" placeholder="" data-target="#receiver_ph" value="{{ $v->receiver_ph ?? '' }}" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-tools float-right" style="padding: 10px;">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addRow(this);">
                                    항목 추가 <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm ml-1" onclick="delRow(this);">
                                    항목 삭제 <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-info ml-1 mr-2" onclick="allDelRow(this);">
                                    항목 일괄삭제 <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>

                            <div class="card-body pl-3">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-xs" id="trCheck">
                                        <colgroup>
                                            <col width="10%"/>
                                            <col width="15%"/>
                                            <col width="10%"/>
                                            <col width="10%"/>
                                            <col width="5%"/>
                                            <col width="10%"/>
                                            <col width="10%"/>
                                            <col width="10%"/>
                                            <col width="10%"/>
                                        </colgroup>
                                        <thead class="thead-light">
                                            <tr align='center'>
                                                <th>코드</th>
                                                <th>품명</th>
                                                <th>규격(1)</th>
                                                <th>규격(2)</th>
                                                <th>단위</th>
                                                <th>수량</th>
                                                <th>단가</th>
                                                <th>금액</th>
                                                <th>비고</th>
                                            </tr>
                                        </thead>
                                        <tbody id="inputTbody">
                                            @php ( $scheduleCnt = $sum_volume = $sum_price = $sum_balance= 0 )
                                            @if(isset($order_extra))
                                            @foreach($order_extra as $key => $val)
                                                @php ( $sum_volume += ($val->volume ?? 0) )
                                                @php ( $sum_price += ($val->price ?? 0) )
                                                @php ( $sum_balance += ($val->volume * $val->price) )
                                                
                                                @php ( $scheduleCnt++ )
                                                <tr>
                                                    <td class="text-center">
                                                        <input type="text" class="form-control form-control-sm text-center" id="code{{$scheduleCnt}}" name="code[]" value="{{ $val->code ?? '' }}" onclick="codeSearch({{$scheduleCnt}});" readonly>
                                                    </td>
                                                    
                                                    <td class="text-center" id="td_name{{ $scheduleCnt }}">{{ $val->name ?? '' }}</td>
                                                    <td class="text-center" id="td_standard1{{ $scheduleCnt }}">{{ $val->standard1 ?? '' }}</td>
                                                    <td class="text-center" id="td_standard2{{ $scheduleCnt }}">{{ $val->standard2 ?? '' }}</td>
                                                    <td class="text-center" id="td_type{{ $scheduleCnt }}">{{ $val->type ?? '' }}</td>
                                                    
                                                    <td class="text-right">
                                                        <input type="text" class="form-control form-control-sm text-right" id="volume{{$scheduleCnt}}" name="volume[]" value="{{ $val->volume ?? 0 }}" onkeyup="setInput({{$scheduleCnt}}, 'volume');">
                                                    </td>
                                                    <td class="text-right">
                                                        <input type="text" class="form-control form-control-sm text-right" id="price{{$scheduleCnt}}" name="price[]" value="{{ $val->real_price ?? 0 }}" onkeyup="setInput({{$scheduleCnt}}, 'price');">
                                                    </td>
                                                    <input type="hidden" id="balance{{ $scheduleCnt }}" name="balance[]" value="{{ ($val->volume ?? 0) * ($val->price ?? 0) }}">
                                                    <td class="text-right" id="td_balance{{ $scheduleCnt }}">{{ number_format(($val->volume??0) * ($val->price?? 0)) }}</td>
                                                    <td class="text-center">
                                                        <input type="text" class="form-control form-control-sm" id="etc{{$scheduleCnt}}" name="etc[]" value="{{ $val->etc ?? '' }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @endif
                                        </tbody>
                                        
                                        <tbody id="inputTbody2">
                                            <tr class="bg-secondary">
                                                <td class="text-center" id="td_sum"></td>
                                                <td class="text-center" colspan="4">합계 [ 최종갱신 : {{ Func::dateFormat($v->save_time ?? '') }} ]</td>
                                                <td class="text-right" id="td_tot_volume">{{ number_format($sum_volume,3 ?? 0) }}</td>
                                                <td class="text-right" id="td_tot_price">{{ number_format($sum_price ?? 0) }}</td>
                                                <td class="text-right" id="td_tot_balance">{{ number_format($sum_balance ?? 0) }}</td>
                                                <td class="text-center" colspan="1"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 메모 -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-body pl-3 pt-0 pb-0 pr-3">
                                <table class="table table-sm table-bordered table-input text-xs" id='memo_title'>
                                    <colgroup>
                                    <col width="17%"/>
                                    <col width="83%"/>
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <th class="text-center bold">특이사항</th>
                                            <td>
                                                <textarea class="form-control form-control-xs" name="order_memo" id="order_memo" placeholder=" 특이사항입력..." rows="4" style="resize:none;">{{ $v->order_memo ?? '' }}</textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="pl-0 pt-2 pb-5 pr-3" id="input_footer">
                                <button type="button" class="btn btn-info btn-sm ml-1" onclick="excelDownModal('/field/orderinfoexcel', 'order_form');">
                                    엑셀 다운 <i class="fas fa-download"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-info mr-1 float-left" style="margin-left:2%;" id="down_confirm" onclick="savePdf();">PDF 다운 <i class="fas fa-download"></i></button>
                                <button type="button" class="btn btn-sm btn-danger float-right" id="del_confirm" onclick="confirmSave('DEL');">삭제</button>
                                <button type="button" class="btn btn-sm btn-info mr-1 float-right" id="save_confirm" onclick="confirmSave('UPD');">저장</button>
                            </div>
                        </div>
                    </div>

                    <!-- 검색 모달 -->
                    <div class="modal fade" id="modalS" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myModalLabel">검색</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="searchForm">
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <div class="input-group">
                                                    <input type="hidden" name="currentPage" value="1"/>
                                                    <input type="hidden" name="search_no" id="search_no"/>
                                                    <input type="text" class="form-control" id="contract_search_string" placeholder="코드를 입력하세요">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="search();">검색</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group" style="padding-top:50px;">
                                            <div class="col-sm-12">
                                                <table class="table table-hover table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>구분</th>
                                                            <th>CODE</th>
                                                            <th>품명</th>
                                                            <th>규격(1)</th>
                                                            <th>규격(2)</th>
                                                            <th>단위</th>
                                                            <th>단가</th>
                                                            <th>비고</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="list">
                                                        @if(isset($contract))
                                                            @foreach($contract as $key => $val)
                                                            <tr onclick="receiverListCheck();" style="cursor:pointer;">
                                                                <td>{{ $val->category ?? '' }}</td>
                                                                <td>{{ $val->code ?? '' }}</td>
                                                                <td>{{ $val->name ?? '' }}</td>
                                                                <td>{{ $val->standard1 ?? '' }}</td>
                                                                <td>{{ $val->standard2 ?? '' }}</td>
                                                                <td>{{ $val->type ?? '' }}</td>
                                                                <td>{{ isset($val->price) ? rtrim(rtrim(number_format($val->price,1),0),'.') : '' }}</td>
                                                                <td>{{ $val->etc ?? '' }}</td>
                                                            </tr>
                                                            @endforeach
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </form>

                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <div id="list" style=""></div>
                                            <div id="pageApi" style="text-align:center"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 엑셀 다운 모달 -->
                    <div class="modal fade" id="excelDownModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">엑셀 다운로드</h5>
                                    <button type="button" class="close" id="excelClose"data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="excelForm" name="excelForm" method="post" enctype="multipart/form-data" action="" onSubmit="return false;">
                                        @csrf
                                        <div class="row mt-1">
                                            <span class="form-control-sm col-3" for="reason" style="font-weight:700; padding-top:3px; width:30px;">다운로드 사유 : </span> 
                                            <select class="form-control form-control-sm text-xs col-md-6" name="excel_down_cd" id="excel_down_cd">
                                                <option value=''>선택</option>
                                                    {{ Func::printOption(Func::getConfigArr("excel_down_cd"), '001') }} 
                                            </select>
                                        </div>
                                        <div class="row mt-1" style="display:none">
                                            <div class="icheck-success d-inline">
                                                <span class="form-control-sm col-3" for="reason" style="font-weight:700; margin-top:10px;">다운로드 구분 : </span> 
                                                <label class="radio-block">
                                                <input type="radio" name="radio_div" value="now" checked > 현재 페이지 &nbsp;
                                                </label>
                                            </div>
                                            <div class="icheck-success d-inline">
                                                <label class="radio-block">
                                                <input type="radio" name="radio_div" value="all" > 전체 페이지 &nbsp;
                                                </label>
                                            </div>
                                        </div>
                                        <div class="row mt-1" style="display:none">>
                                            <div class="icheck-success d-inline">
                                                <span class="form-control-sm col-3" for="execution" style="font-weight:700; margin-top:10px;">다운로드 실행구분 : </span> 
                                                <label class="radio-block" style="padding-left: 5px!important;">
                                                    <input type="radio" name="excel_down_div" id="realtime" value="E" checked onchange="input_filename()"> 바로실행 &nbsp;
                                                </label>
                                            </div>
                                            <div class="icheck-success d-inline">
                                                <label class="radio-block" style="width:110px; padding-left: 5px!important;">
                                                    <input type="radio" name="excel_down_div" id="reservation" value="S" onchange="input_filename()"> 예약실행 &nbsp;
                                                </label>
                                            </div>
                                            <input class="form-control form-control-sm text-xs col-md-6"type="text" id="down_filename" style="margin-left:120px;"placeholder="다운받을 파일명을 입력해주세요">
                                        </div>
                                        @if( ExcelFunc::getExcelHeader($_SERVER['REQUEST_URI']) !== null )
                                        <div class="row mt-1">
                                            <div class="icheck-success d-inline">
                                                <span class="form-control-sm col-3" for="execution" style="font-weight:700; margin-top:10px;">다운로드 항목 : </span> 
                                                <label class="radio-block">
                                                    <input type="radio" name="excel_down_sell" id="excel_down_sell" value="ALL" checked onclick="input_select()"> 전체&nbsp;
                                                </label>
                                            </div>
                                            <div class="icheck-success d-inline">
                                                <label class="radio-block">
                                                    <input type="radio" name="excel_down_sell" id="excel_down_sell" value="SELECT" onclick="input_select()"> 선택 &nbsp;
                                                </label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row" id="selectHeaders" style="display:none;">
                                                    @foreach( ExcelFunc::getExcelHeader($_SERVER['REQUEST_URI']) as $seq => $header )
                                                    <div class="col-md-6">
                                                        <input type="checkbox" class="form-check-input" name="excelHeader[]" id="excelHeader[]" value="{{ $seq }}" checked>{{ $header }}
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <span class="form-control-sm col-8 text-red" id='excelMsg' style="display:none;">* 다운로드 중 입니다. </span> 
                                    <button type="button" class="btn btn-sm btn-secondary" id="closeBtn" data-dismiss="modal" aria-hidden="true">닫기</button>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="excelDown('order_form');">다운로드</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>     
            </div>
        </div>
    </div>
</div>

<script>

    $(".datetimepicker").datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
        useCurrent: false,
    });

    // 발주 PDF 다운
    function savePdf()
    {
        var pdf_data = document.getElementById('order_form');
        pdf_data.action = "/field/orderinfopdf";
        pdf_data.method = 'POST';
        pdf_data.submit();
    }

    // 저장
    function confirmSave(div)
    {
        if(div == 'UPD')
        {
            // 입력값 확인
            var fieldName = $('#field_name').val();
            var partnerName = $('#partner_name').val();

            if(fieldName == '')
            {
                alert('현장명을 입력해주세요.');
                return false;
            }
            if(partnerName == '')
            {
                alert('협력사명을 입력해주세요.');
                return false;
            }
        }
        else
        {
            if(!confirm('정말 삭제하시겠습니까?'))
            {
                return false;
            }
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        var postdata = $('#order_form').serialize();
        postdata = postdata + '&mode=' + div;

        // 중복클릭 방지
        if(ccCheck()) return;

        $.ajax({
            url  : "/field/orderinfoaction",
            type : "post",
            data : postdata,
            success : function(data)
            {
                if(data['rs_code'] == "Y") 
                {
                    globalCheck = false;
                    alert(data['result_msg']);

                    if(div == 'UPD')
                    {
                        document.location.href = "/field/orderpop?no="+$('#order_info_no').val();
                    }
                    else
                    {
                        window.opener.listRefresh();
                        self.close();
                    }
                }
                // 실패알림
                else 
                {
                    globalCheck = false;
                    alert(data['result_msg']);
                }
            },
            error: function(xhr, status, error)
            {
                console.error("Status: " + status);
                console.error("Error: " + error);
                console.error("Response: " + xhr.responseText);
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }

    //////////////////////////////////// 계약수량부분
    var scheduleCnt = {{ $scheduleCnt ?? 0 }};

    pageMake(0);

    setInput(0);

    function setInput(cnt, type='')
    {
        if(cnt > 0 && type)
        {
            var inputBox = document.getElementById(type+cnt);
            var cursorPosition = inputBox.selectionStart;
        }

        var get_targetExtraVolume = $("#inputTbody input[name^='volume[]']");

        $.each(get_targetExtraVolume, function (index, value)
        {
            var volume_value = ($('#volume' + (index + 1)).val() || 0).replace(/,/gi, "");
            $('#volume' + (index + 1)).val(parseInt(volume_value).toLocaleString());

            var price_value = ($('#price' + (index + 1)).val() || 0).replace(/,/gi, "");
            var price_data  = price_value.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            $('#price' + (index + 1)).val(price_data);

            var balance_value = (parseFloat(volume_value) || 0) * (parseFloat(price_value) || 0);
            $('#balance' + (index + 1)).val(parseInt(balance_value).toLocaleString());
            $('#td_balance' + (index + 1)).html(balance_value.toLocaleString());
        });

        if(cnt > 0 && type)
        {
            inputBox.setSelectionRange(cursorPosition, cursorPosition);
        }

        // 변수 초기화
        var cal_volume = 0, cal_price = 0, cal_balance = 0;

        // 수량
        var get_targetMoney = $("#inputTbody input[name^='volume[]']");
        $.each(get_targetMoney, function (index, value)
        {
            cal_volume += parseFloat(($(value).val() || 0).replace(/,/gi, "")) || 0;
        });

        // 단가
        get_targetMoney = $("#inputTbody input[name^='price[]']");
        $.each(get_targetMoney, function (index, value)
        {
            cal_price += parseFloat(($(value).val() || 0).replace(/,/gi, "")) || 0;
        });

        // 금액
        get_targetMoney = $("#inputTbody input[name^='balance[]']");
        $.each(get_targetMoney, function (index, value)
        {
            cal_balance += parseFloat(($(value).val() || 0).replace(/,/gi, "")) || 0;
        });

        // 합계 변경
        $('#td_tot_volume').html(cal_volume.toLocaleString());
        $('#td_tot_price').html(cal_price.toLocaleString());
        $('#td_tot_balance').html(cal_balance.toLocaleString());
    }

    // 행추가
    function addRow()
    {
        scheduleCnt++;

        let tr = '<tr>';
            tr+= '<td class="text-center">';
            tr+= '<input type="text" class="form-control form-control-sm text-center" id="code'+scheduleCnt+'" name="code[]" onclick="codeSearch('+scheduleCnt+');" readonly>';
            tr+= '</td>';
            tr+= '<td class="text-center" id="td_name'+scheduleCnt+'"></td>';
            tr+= '<td class="text-center" id="td_standard1'+scheduleCnt+'"></td>';
            tr+= '<td class="text-center" id="td_standard2'+scheduleCnt+'"></td>';
            tr+= '<td class="text-center" id="td_type'+scheduleCnt+'"></td>';
            tr+= '<td class="text-right">';
            tr+= '<input type="text" class="form-control form-control-sm text-right" id="volume'+scheduleCnt+'" name="volume[]" value="0" onkeyup="setInput('+scheduleCnt+', \'volume\');">';
            tr+= '</td>';
            tr+= '<td class="text-right">';
            tr+= '<input type="text" class="form-control form-control-sm text-right" id="price'+scheduleCnt+'" name="price[]" value="0" onkeyup="setInput('+scheduleCnt+', \'price\');">';
            tr+= '</td>';
            tr+= '<input type="hidden" id="balance'+scheduleCnt+'" name="balance[]" value="">0';
            tr+= '<td class="text-right" id="td_balance'+scheduleCnt+'">0';
            tr+= '</td>';
            tr+= '<td class="text-center">';
            tr+= '<input type="text" class="form-control form-control-sm text-center" id="etc'+scheduleCnt+'" name="etc[]" value="">';
            tr+= '</td>';
            tr+= '</td>';
            tr+= '</tr>';
        $('#inputTbody').append(tr);
        
        setInput(0);
    }

    // 행삭제
    function delRow()
    {
        scheduleCnt--;

        $('#inputTbody > tr:last').remove();
        
        setInput(0);
    }

    // 내역서 일괄삭제
    function allDelRow()
    {
        if(!confirm('정말 삭제하시겠습니까?'))
        {
            return false;
        }

        var order_info_no = document.getElementById('order_info_no').value;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.post("/field/orderextraallclear", { order_info_no: order_info_no }, function (data)
        {
            alert('삭제 완료하였습니다.');
            getOrderData('orderinfo');
        });
    }
    
    // 계약수량 코드 검색
    function codeSearch(index)
    {
        document.getElementById('search_no').value = index;
        document.getElementById('contract_search_string').value = '';
        $('#list').html('');
        $('#modalS').modal('show');
    }

    // 검색
    function search()
    {
        var searchdata = $('#contract_search_string').val();
        searchdata = searchdata.replace(/\s/gi, ""); //공백제거

        if(searchdata != '')
        {
            var order_info_no = document.getElementById('order_info_no').value;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.post('/field/ordercontractsearch', { type:'search', keyword:searchdata, order_info_no:order_info_no, page : $('input[name=currentPage]').val() }, function(data) {

                var htmlStr = makeList(data.contract);
                $('#list').html(htmlStr);
                $('#pageApi').html('');

                pageMake(data.cnt);
            });
        }
    }

    function pageMake(cnt)
    {
        var total = cnt; // 총건수
        var pageNum = $('input[name=currentPage]').val();// 현재페이지
        var pageStr = "";

        if(!(total) || typeof total == "undefined" || total == '' || total == 0)
        {
            $("#pageApi").html("");
        }
        else
        {
            // $("#list").html(htmlStr);
            $("#pageApi").html("");
            if(total > 1000)
            {
                total = 1000; //100페이지 까지만 가져오기
            }
            var pageBlock=10;
            var pageSize=10;
            var totalPages = Math.floor((total-1)/pageSize) + 1; // 총페이지
            var firstPage = Math.floor((pageNum-1)/pageBlock) * pageBlock + 1; // 리스트의 처음 ( (2-1)/10 ) * 10 + 1 // 1 11 21 31
            if(firstPage <= 0) firstPage = 1;	// 무조건 1
            var lastPage = firstPage-1 + pageBlock; // 리스트의 마지막 10 20 30 40 50
            if(lastPage > totalPages) lastPage = totalPages;	// 마지막페이지가 전체페이지보다 크면 전체페이지
            var nextPage = lastPage+1 ; // 11 21
            var prePage = firstPage-pageBlock ;

            if(firstPage > pageBlock)
            {
                pageStr +=  "<a class=\"btn btn-default\" href='javascript:goPage("+prePage+");'>◁</a>  " ; // 처음 페이지가 아니면 <를 넣어줌
            }

            for(var i=firstPage; i<=lastPage; i++ )
            {
                if(pageNum == i)
                    pageStr += "<a class=\"btn btn-info\" href='javascript:goPage("+i+");'>" + i + "</a>  "; // 현재페이지 색넣어주기
                else
                    pageStr += "<a class=\"btn btn-default\" href='javascript:goPage("+i+");'>" + i + "</a>  ";
            }

            if(lastPage < totalPages)
            {
                pageStr +=  "<a class=\"btn btn-default\" href='javascript:goPage("+nextPage+");'>▷</a>"; // 마지막페이지가 아니면 >를 넣어줌
            }

            $("#pageApi").html(pageStr);
        }
    }

    // 검색 결과 리스트 생성
    function makeList(data)
    {
        if(data[0])
        {
            var tr = '';
            for(var i=0; i<data.length; i++)
            {
                tr += '<tr onclick="receiverListCheck();" style="cursor:pointer;">';
        
                var category = '';
                var code = '';
                var name = '';
                var standard1 = '';
                var standard2 = '';
                var type = '';
                var price = '';
                var etc = '';
                if(data[i].category != null) category = data[i].category;
                if(data[i].code != null) code = data[i].code;
                if(data[i].name != null) name = data[i].name;
                if(data[i].standard1 != null) standard1 = data[i].standard1;
                if(data[i].standard2 != null) standard2 = data[i].standard2;
                if(data[i].type != null) type = data[i].type;
                if(data[i].price != null) price = data[i].price;
                if(data[i].etc != null) etc = data[i].etc;
        
                var td = '<td>' + category + '</td>';
                td += '<td>' + code + '</td>';
                td += '<td>' + name + '</td>';
                td += '<td>' + standard1 + '</td>';
                td += '<td>' + standard2 + '</td>';
                td += '<td>' + type + '</td>';
                td += '<td>' + commaInput(price) + '</td>';
                td += '<td>' + etc + '</td>';
                tr += td + '</tr>';
            }
        }
        else
        {
            var tr = '<tr><th colspan="8" style="text-align:center;"> 정보가 없습니다. </th></tr>';
        }

        return tr;
    }

    function commaInput(num)
    {
        num = String(num);
        var parts = num.toString().split("."); 
        parts[0] = parts[0].replace(/,/g, "");
        parts[0] = parts[0].replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
        var number = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + (parts[1] ? "." + parts[1] : "");
        return number;
    }

    //페이지 이동
    function goPage(pageNum)
    {
        $('input[name=currentPage]').val(pageNum);
        search();
    }

    // 항목 선택
    function receiverListCheck()
    {
        var obj = event.srcElement;
        var tr = getTrValues(obj.parentNode.children);
        var no = $("input[name=search_no]").val();

        $('#code'+no).val(tr[1] ?? 0);
        $('#td_name'+no).html(tr[2] ?? '');
        $('#td_standard1'+no).html(tr[3] ?? '');
        $('#td_standard2'+no).html(tr[4] ?? '');
        $('#td_type'+no).html(tr[5] ?? '');
        $('#price'+no).val(tr[6] ?? 0);
        
        setInput(0);

        $('#modalS').modal('hide');
    }

    function getTrValues(tr)
    {
        var array = new Array();
        for(var i = 0; i<tr.length; i++)
        {
            if(tr[i].firstChild)
            {
                array.push(tr[i].firstChild.nodeValue);
            }
            else
            {
                array.push('');
            }
        }
        return array;
    }

    enterClear();

    // 엔터막기
    function enterClear()
    {
        $('#contract_search_string').keydown(function() {
            if (event.keyCode === 13)
            {
                event.preventDefault();
                search();
            };
        });
    }

</script> 