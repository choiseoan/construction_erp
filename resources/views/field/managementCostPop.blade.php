@extends('layouts.masterPop')
@section('content')

<div class="col-md-12 p-0 m-0 " >
    <form class="mb-0" name="form_cost" id="form_cost">
        <input type="hidden" id="cost_no" name="cost_no" value="{{ $v->no ?? '' }}" />
        <input type="hidden" id="contract_info_no" name="contract_info_no" value="{{ $v->contract_info_no ?? '' }}" />
        @csrf

        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h3 class="card-title">일위대가</h3>
                <div class="card-tools float-right">
                    <button type="button" class="btn btn-default btn-sm float-center mr-2 addbtn" onclick="addRow(this);">항목 추가</button>
                    <button type="button" class="btn btn-default btn-sm float-center mr-2 delbtn" onclick="delRow(this);">항목 삭제</button>            
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-xs" id="trCheck">
                        <colgroup>
                            <col width="7%"/>
                            <col width="9%"/>
                            <col width="9%"/>
                            <col width="9%"/>
                            <col width="5%"/>
                            <col width="9%"/>
                            <col width="9%"/>
                            <col width="9%"/>
                            <col width="9%"/>
                            <col width="7%"/>
                        </colgroup>
                        <thead class="thead-light">
                            <tr align='center'>
                                <th>일위대가 코드</th>
                                <th>품명</th>
                                <th>규격(1)</th>
                                <th>규격(2)</th>
                                <th>단위</th>
                                <th>수량</th>
                                <th>단가</th>
                                <th>금액</th>
                                <th colspan ='2'>비고</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">
                                    <input type="text" class="form-control form-control-sm text-center" id="code" name="code" value="{{ $v->code ?? '' }}">
                                </td>
                                <td class="text-center">
                                    <input type="text" class="form-control form-control-sm text-center" id="name" name="name" value="{{ $v->name ?? '' }}">
                                </td>
                                <td class="text-center">
                                    <input type="text" class="form-control form-control-sm text-center" id="standard1" name="standard1" value="{{ $v->standard1 ?? '' }}">
                                </td>
                                <td class="text-center">
                                    <input type="text" class="form-control form-control-sm text-center" id="standard2" name="standard2" value="{{ $v->standard2 ?? '' }}">
                                </td>
                                <td class="text-center">
                                    <input type="text" class="form-control form-control-sm text-center" id="type" name="type" value="{{ $v->type ?? '' }}">
                                </td>
                                <input type="hidden" id="volume" value="{{ $v->volume ?? 1 }}">
                                <td class="text-right" id="td_volume">
                                    {{ number_format($v->volume ?? 0) }}
                                </td>
                                <input type="hidden" id="price" value="{{ $v->price ?? 0 }}">
                                <td class="text-right" id="td_price">
                                    {{ number_format($v->price ?? 0) }}
                                </td>
                                <input type="hidden" id="balance" value="{{ $v->balance ?? 0 }}">
                                <td class="text-right" id="td_balance">
                                    {{ number_format($v->balance ?? 0) }}
                                </td>
                                <td class="text-center" colspan ='2'>
                                    <input type="text" class="form-control form-control-sm text-center" id="etc" name="etc" value="{{ $v->etc ?? '' }}">
                                </td>
                            </tr>

                        <tbody>
                        <thead class="thead-light">
                            <tr align='center'>
                                <th>자재단가표 코드</th>
                                <th>품명</th>
                                <th>규격(1)</th>
                                <th>규격(2)</th>
                                <th>단위</th>
                                <th>수량</th>
                                <th>단가</th>
                                <th>금액</th>
                                <th>자재총소요량</th>
                                <th>비고</th>
                            </tr>
                        </thead>

                        <tbody id="inputTbody">
                            @php ( $scheduleCnt = $sum_volume = $sum_price = $sum_balance= 0 )
                            @foreach($cost_extra as $key => $val)
                                @php ( $sum_volume += ($val->volume ?? 0) )
                                @php ( $sum_price += ($val->price ?? 0) )
                                @php ( $sum_balance += ($val->balance ?? 0) )
                                
                                @php ( $scheduleCnt++ )
                                <tr>
                                    <td class="text-center">
                                        <input type="text" class="form-control form-control-sm text-center" id="extra_code{{$scheduleCnt}}" name="extra_code[]" value="{{ $val->code ?? '' }}" onclick="codeSearch({{$scheduleCnt}});" readonly>
                                    </td>
                                    <td class="text-center" id="td_extra_name{{ $scheduleCnt }}">{{ $val->name ?? '' }}</td>
                                    <td class="text-center" id="td_extra_standard1{{ $scheduleCnt }}">{{ $val->standard1 ?? '' }}</td>
                                    <td class="text-center" id="td_extra_standard2{{ $scheduleCnt }}">{{ $val->standard2 ?? '' }}</td>
                                    <td class="text-center" id="td_extra_type{{ $scheduleCnt }}">{{ $val->type ?? '' }}</td>
                                    <td class="text-right">
                                        <input type="text" class="form-control form-control-sm text-right" id="extra_volume{{$scheduleCnt}}" name="extra_volume[]" value="{{ $val->volume ?? 0 }}" onkeyup="setInput({{$scheduleCnt}});">
                                    </td>
                                    <input type="hidden" id="extra_price{{ $scheduleCnt }}" name="extra_price[]" value="{{ $val->price ?? 0 }}">
                                    <td class="text-right" id="td_extra_price{{ $scheduleCnt }}">
                                        {{ number_format($val->price ?? 0) }}
                                    </td>
                                    <input type="hidden" id="extra_balance{{ $scheduleCnt }}" name="extra_balance[]" value="{{ $val->balance ?? 0 }}">
                                    <td class="text-right" id="td_extra_balance{{ $scheduleCnt }}">
                                        {{ number_format($val->balance ?? 0) }}
                                    </td>
                                    <input type="hidden" id="extra_material{{ $scheduleCnt }}" name="extra_material[]" value="{{ $v->material ?? 0 }}">
                                    <td class="text-right" id="td_extra_material{{ $scheduleCnt }}">
                                        {{ number_format($v->extra_material ?? 0) }}
                                    </td>
                                    <td class="text-center">
                                        <input type="text" class="form-control form-control-sm" id="extra_etc{{$scheduleCnt}}" name="extra_etc[]" value="{{ $val->etc ?? '' }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        
                        <tbody id="inputTbody2">
                            <tr class="bg-secondary">
                                <td class="text-center" id="td_sum"></td>
                                <td class="text-center" colspan="4">합계 [ 최종갱신 : {{ Func::dateFormat($v->save_time ?? '') }} ]</td>
                                <td class="text-right" id="td_tot_volume">{{ number_format($sum_volume ?? 0) }}</td>
                                <td class="text-right" id="td_tot_price">{{ number_format($sum_price ?? 0) }}</td>
                                <td class="text-right" id="td_tot_balance">{{ number_format($sum_balance ?? 0) }}</td>
                                <td class="text-center" colspan="3"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-danger float-right" onclick="costSave('DEL');">삭제</button>
                <button type="button" class="btn btn-sm btn-info float-right mr-1" onclick="costSave('UPD');">저장</button>
            </div>
        </div>

        <!-- 검색 모달 -->
        <div class="modal fade" id="modalS" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">자재단가표 코드</h4>
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
                                        <input type="text" class="form-control" id="material_search_string" placeholder="코드를 입력하세요">
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
                                            @if(isset($material))
                                                @foreach($material as $key => $val)
                                                <tr onclick="receiverListCheck();" style="cursor:pointer;">
                                                    <td>{{ $val->material ?? '' }}</td>                                      <!-- 0 -->
                                                    <td>{{ $val->code ?? '' }}</td>                                          <!-- 1 -->
                                                    <td>{{ $val->name ?? '' }}</td>                                          <!-- 2 -->
                                                    <td>{{ $val->standard1 ?? '' }}</td>                                     <!-- 3 -->
                                                    <td>{{ $val->standard2 ?? '' }}</td>                                     <!-- 4 -->
                                                    <td>{{ $val->type ?? '' }}</td>                                          <!-- 5 -->
                                                    <td>{{ $val->price ? @number_format($val->price,3) : '' }}</td>          <!-- 6 -->
                                                    <td>{{ $val->etc ?? '' }}</td>                                           <!-- 7 -->
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
    </form>
</div>

@endsection

@section('javascript')

<script>

var scheduleCnt = 0;

pageMake(0);

setInput(0);

$(document).ready(function()
{
    scheduleCnt = {{ $scheduleCnt ?? 0 }};
});

function setInput(cnt)
{
    if(cnt > 0)
    {
        var inputBox = document.getElementById('extra_volume'+cnt);
        var cursorPosition = inputBox.selectionStart;
    }

    var volume_value = $('#volume').val().replace(/,/gi, "");
    var get_targetExtraVolume = $("#inputTbody input[name^='extra_volume[]']");

    $.each(get_targetExtraVolume, function (index, value)
    {
        var extra_volume_value = $('#extra_volume' + (index + 1)).val().replace(/,/gi, "");
        var extra_volume_data = extra_volume_value.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        $('#extra_volume' + (index + 1)).val(extra_volume_data);

        var extra_price_value = $('#extra_price' + (index + 1)).val().replace(/,/gi, "");
        $('#extra_price' + (index + 1)).val(extra_price_value);

        var extra_balance_value = (parseFloat(extra_volume_value) || 0) * (parseFloat(extra_price_value) || 0);
        $('#extra_balance' + (index + 1)).val(extra_balance_value);
        $('#td_extra_balance' + (index + 1)).html(extra_balance_value.toLocaleString());

        // 자재단가표합
        var extra_material_value = (parseFloat(volume_value) || 0) * (parseFloat(extra_volume_value) || 0);
        $('#extra_material' + (index + 1)).val(extra_material_value);
        $('#td_extra_material' + (index + 1)).html(extra_material_value.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 3 }));
    });

    if(cnt > 0)
    {
        inputBox.setSelectionRange(cursorPosition, cursorPosition);
    }

    // 변수 초기화
    var cal_volume = 0, cal_price = 0, cal_balance = 0;

    // 수량
    var get_targetMoney = $("#inputTbody input[name^='extra_volume[]']");
    $.each(get_targetMoney, function (index, value)
    {
        cal_volume += parseFloat($(value).val().replace(/,/gi, "")) || 0;
    });

    // 단가
    get_targetMoney = $("#inputTbody input[name^='extra_price[]']");
    $.each(get_targetMoney, function (index, value)
    {
        cal_price += parseFloat($(value).val().replace(/,/gi, "")) || 0;
    });

    // 금액
    get_targetMoney = $("#inputTbody input[name^='extra_balance[]']");
    $.each(get_targetMoney, function (index, value)
    {
        cal_balance += parseFloat($(value).val().replace(/,/gi, "")) || 0;
    });

    // 합계 변경
    $('#td_tot_volume').html(cal_volume.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 3 }));
    $('#td_tot_price').html(cal_price.toLocaleString());
    $('#td_tot_balance').html(cal_balance.toLocaleString());

    // 메인
    $('#td_price').html(cal_price.toLocaleString());
    $('#price').val(cal_price);
    var balance_value = (parseFloat(volume_value) || 0) * cal_price;
    $('#td_balance').html(balance_value.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 3 }));
    $('#balance').val(balance_value);
}

// 행추가
function addRow()
{
    scheduleCnt++;

    let tr = '<tr>';
        tr+= '<td class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="extra_code'+scheduleCnt+'" name="extra_code[]" onclick="codeSearch('+scheduleCnt+');" readonly>';
        tr+= '</td>';
        tr+= '<td class="text-center" id="td_extra_name'+scheduleCnt+'"></td>';
        tr+= '<td class="text-center" id="td_extra_standard1'+scheduleCnt+'"></td>';
        tr+= '<td class="text-center" id="td_extra_standard2'+scheduleCnt+'"></td>';
        tr+= '<td class="text-center" id="td_extra_type'+scheduleCnt+'"></td>';
        tr+= '<td class="text-right">';
        tr+= '<input type="text" class="form-control form-control-sm text-right" id="extra_volume'+scheduleCnt+'" name="extra_volume[]" value="0" onkeyup="setInput('+scheduleCnt+');">';
        tr+= '</td>';
        tr+= '<input type="hidden" id="extra_price'+scheduleCnt+'" name="extra_price[]" value="">0';
        tr+= '<td class="text-right" id="td_extra_price'+scheduleCnt+'">0';
        tr+= '</td>';
        tr+= '<input type="hidden" id="extra_balance'+scheduleCnt+'" name="extra_balance[]" value="">0';
        tr+= '<td class="text-right" id="td_extra_balance'+scheduleCnt+'">0';
        tr+= '</td>';
        tr+= '<input type="hidden" id="extra_material'+scheduleCnt+'" name="extra_material[]" value="{{ $v->material ?? 0 }}">';
        tr+= '<td class="text-right" id="td_extra_material'+scheduleCnt+'">0';
        tr+= '</td>';
        tr+= '<td class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="extra_etc'+scheduleCnt+'" name="extra_etc[]" value="">';
        tr+= '</td>';
        tr+= '</td>';
        tr+= '</tr>';
    $('#inputTbody').append(tr);

    $("#inputTbody tr").each(function(index)
    {
        let newIndex = index + 1;

        if ($(this).find("td[id^='td_sum']").length > 0)
        {
            return true; 
        }
        
        $(this).find("input[name='extra_code[]']").attr("id", "extra_code" + newIndex);
        $(this).find("input[name='extra_volume[]']").attr("id", "extra_volume" + newIndex);
        $(this).find("input[name='extra_volume[]']").attr("onkeyup", "setInput(" + newIndex+")");
        $(this).find("td[id^='td_extra_price']").attr("id", "td_extra_price" + newIndex);
        $(this).find("input[name='extra_price[]']").attr("id", "extra_price" + newIndex);
        $(this).find("td[id^='td_extra_balance']").attr("id", "td_extra_balance" + newIndex);
        $(this).find("input[name='extra_balance[]']").attr("id", "extra_balance" + newIndex);
        $(this).find("td[id^='td_extra_material']").attr("id", "td_extra_material" + newIndex);
        $(this).find("input[name='extra_material[]']").attr("id", "extra_material" + newIndex);

        $(".addbtn").index();
    });
    
    setInput(0);
}

// 행삭제
function delRow()
{
    scheduleCnt--;

    $('#inputTbody > tr:last').remove();
    
    $("#inputTbody tr").each(function(index)
    {
        let newIndex = index + 1;

        if ($(this).find("td[id^='td_sum']").length > 0) {
            return true;
        }
        
        $(this).find("input[name='extra_code[]']").attr("id", "extra_code" + newIndex);
        $(this).find("input[name='extra_volume[]']").attr("id", "extra_volume" + newIndex);
        $(this).find("input[name='extra_volume[]']").attr("onkeyup", "setInput(" + newIndex+")");
        $(this).find("td[id^='td_extra_price']").attr("id", "td_extra_price" + newIndex);
        $(this).find("input[name='extra_price[]']").attr("id", "extra_price" + newIndex);
        $(this).find("td[id^='td_extra_balance']").attr("id", "td_extra_balance" + newIndex);
        $(this).find("input[name='extra_balance[]']").attr("id", "extra_balance" + newIndex);
        $(this).find("td[id^='td_extra_material']").attr("id", "td_extra_material" + newIndex);
        $(this).find("input[name='extra_material[]']").attr("id", "extra_material" + newIndex);
    });
    
    setInput(0);
}

// 자재단가표 코드 검색
function codeSearch(index)
{
    document.getElementById('search_no').value = index;
    document.getElementById('material_search_string').value = '';
    $('#list').html('');
    $('#modalS').modal('show');
}

// 검색
function search()
{
    var searchdata = $('#material_search_string').val();
    searchdata = searchdata.replace(/\s/gi, ""); //공백제거

    if(searchdata != '')
    {
        var contract_info_no = document.getElementById('contract_info_no').value;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.post('/field/managementmaterialsearch', { type:'search', keyword:searchdata, contract_info_no:contract_info_no, page : $('input[name=currentPage]').val() }, function(data) {

            var htmlStr = makeList(data.material);
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
            td += '<td>';
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

    $('#extra_code'+no).val(tr[1] ?? 0);
    $('#td_extra_name'+no).html(tr[2] ?? '');
    $('#td_extra_standard1'+no).html(tr[3] ?? '');
    $('#td_extra_standard2'+no).html(tr[4] ?? '');
    $('#td_extra_type'+no).html(tr[5] ?? '');
    $('#td_extra_price'+no).html(tr[6] ?? 0).number(true);
    $('#extra_price'+no).val(tr[6] ?? 0);
    
    setInput(no);

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

// 일위대가 상세 저장
function costSave(div)
{
    if(div == 'DEL')
    {
        if(!confirm("정말 삭제 하시겠습니까?")) return false;
    }
    else
    {
        var code = $('#code').val();
        var name = $('#name').val();
        
        if(code == '')
        {
            alert('코드를 입력해주세요.');
            return false;
        }
        if(name == '')
        {
            alert('품명을 입력해주세요.');
            return false;
        }
    }

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

    var postdata = $('#form_cost').serialize();
    postdata = postdata + '&mode=' + div;

    $.ajax({
        url  : "/field/managementcostpopaction",
        type : "post",
        data : postdata,
        success : function(data)
        {
            if(data.rs_code=="Y") 
            {
                alert(data.result_msg);

                if(div == 'DEL')
                {
                    window.opener.listRefresh();
                    self.close();
                }
                else
                {
                    document.location.href = "/field/managementcostpop?contract_info_no="+$('#contract_info_no').val()+"&cost_no="+$('#cost_no').val();
                }
            }
            // 실패알림
            else 
            {
                alert(data.result_msg);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}

enterClear();

// 엔터막기
function enterClear()
{
    $('#material_search_string').keydown(function() {
        if (event.keyCode === 13)
        {
            event.preventDefault();
            search();
        };
    });
}

</script>

@endsection
