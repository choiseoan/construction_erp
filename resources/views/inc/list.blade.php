<?
    if(!isset($_COOKIE[$result['listName']]))
    {
        $_COOKIE[$result['listName']] = 10;
    }

    if(!isset($result['tabsSelect']))
    {
        $result['tabsSelect'] = '';
    }

    // if(!isset($_COOKIE['search_div_select']))
    // {
    //     $_COOKIE['search_div_select'] = '';
    // }
    // else
    // {
    //     print_r($_COOKIE);
    // }

    $arrayAllCol = array();
    $obj = new Decrypter();
	$arrayAllCol_list = $obj->arrayEncCol;
    foreach($arrayAllCol_list as $key => $val)
    {
        foreach($val as $value)
        {
            array_push($arrayAllCol, $value);
        }
    }
?>

<script language='javascript'>
// 리스트 표현 태그 
function getMarkup{{ $result['listName'] ?? '' }}() 
{
    var tabs = $("#tabsSelect{{ $result['listName'] ?? '' }}").val();
    var markup = "";
    markup = "<tr id='no_${no}' style='${line_style}' onclick='${onclick}'>";
    
    @if($result['viewNum']==true)
        markup+= "<td class='text-center' style='width:45px'>${listNum}</td>";
    @endif

    // checkbox 
    @if(isset($result['checkbox']))
        markup+= "<td class='text-center'><input type='checkbox' name='listChk[]' id='listChk${"+"{{ $result['listName'] ?? ''}}"+"}' class='list-check' value='${"+"{{ $result['checkbox'] ?? ''}}"+"}'></td>";
    @endif
    @foreach($result['listTitle'] as $tabs=>$listTitle)
        @if($tabs!='common' && $tabs!='commonEnd') if(tabs=="{{$tabs}}"){ @endif
            @foreach($listTitle as $key => $val)
                @if(isset($val[6]) && is_array($val[6]))
                    markup+= "<td class='text-{{ $val[3] }} @if(isset($val[4]) && $val[4]!='') rightline @endif one'>";
                        markup+= "<?=($key=='cust_info_no') ? Func::addCi():''?>{{html <?=$key?>}}";                        
                        @foreach ($val[6] as $k => $arr)
                            markup+= "<?=$arr[2]?><?=($k=='cust_info_no') ? Func::addCi():''?>{{html <?=$k?>}}";
                        @endforeach
                markup+= "</td>";
                @else
                    markup+= "<td class='text-{{ $val[3] }} @if(isset($val[4]) && $val[4]!='') rightline @endif one'><?=($key=='cust_info_no') ? Func::addCi():''?>{{html <?=$key?>}}</td>";
                @endif
            @endforeach
        @if($tabs!='common' && $tabs!='commonEnd') } @endif
    @endforeach
    markup+= "</tr>";

    return markup;
}

@if(isset($result['isModal']))
    function modalAction(
        @for ($i=0; $i<count($result['isModal']['modalParams']); $i++) @if ($i>0) ,@endif {{ $result['isModal']['modalParams'][$i] }} @endfor
    )
    {
        $("#modalTitle").text('{{ $result['isModal']['modalTitle'] }}');
        $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        $.post("{{ $result['isModal']['modalAction'] }}", {
            @for($i=0; $i<count($result['isModal']['modalParams']); $i++) 
                @if ($i>0) , @endif 
                {{ $result['isModal']['modalParams'][$i].' : ' }} {{ $result['isModal']['modalParams'][$i] }} 
            @endfor
            
        }, function(data) {
            setTimeout(function(e){
                $("#modalBody").html(data);
            }, 500);
        }).
        done( function() {
                $("#modal01").modal();
        }).
        fail( function() {
                alert('데이터를 불러오지 못했습니다. 관리자에게 문의해주세요');
        });
    }
@endif
// 팝업창에서 호출해서 사용(submit)
function listRefresh()
{
    $('#isFirst{{ $result['listName'] }}').val('1');
    getDataList('{{ $result['listName'] }}', $("#nowPage{{ $result['listName'] }}").val(), '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());
}
// 날짜 버튼 지정
function setDateUser(mode, startDt, endDt, id)
{
    // 종료일이 있으면 오늘.
    if(endDt!='')
        $('#'+id + endDt).val('{{ date("Y-m-d") }}');
    switch(mode)
    {
        case 'today' :
            $('#'+id + startDt).val('{{ date("Y-m-d") }}');
            break;
        case 'yesterday' :
            $('#'+id + startDt).val('{{ date("Y-m-d", time()-86400) }}');
            $('#'+id + endDt).val('{{ date("Y-m-d", time()-86400) }}');
            break;
        case 'week' :
            $('#'+id + startDt).val('{{ date("Y-m-d", strtotime("last Monday", time()+86400)) }}');
            $('#'+id + endDt).val('{{ date("Y-m-d", strtotime("next Sunday", time()+86400)) }}');
            break;
        case 'month' :
            $('#'+id + startDt).val('{{ date("Y-m") }}-01');
            $('#'+id + endDt).val('{{ date("Y-m-t") }}');
            break;
        case 'year' :
            $('#'+id + startDt).val('{{ date("Y") }}-01-01');
            $('#'+id + endDt).val('{{ date("Y") }}-12-31');
            break;
    }
    return false;
}
function buttonOnOff(id, div)
{
	if (id != 'pwd')
	{
		if (div == "Y")
		{
			document.getElementById(id).style.display = "";
			document.getElementById(id+'Y').style.background = "#d0d0d0";
			document.getElementById(id+'N').style.background = "";
		}
		else
		{
			document.getElementById(id).style.display = "none";
			document.getElementById(id+'Y').style.background = "";
			document.getElementById(id+'N').style.background = "#d0d0d0";
		}
	}
	else
	{
		if (div == "N")
		{
			document.getElementById(id).style.display = "";
			document.getElementById(id+'Y').style.background = "";
			document.getElementById(id+'N').style.background = "#d0d0d0";
		}
		else
		{
			document.getElementById(id).style.display = "none";
			document.getElementById(id+'Y').style.background = "#d0d0d0";
			document.getElementById(id+'N').style.background = "";
		}
	}
	document.getElementById(id+'Input').value = div;
}
function nameorder(order, element)
{
   
    $('.orderIcon').removeClass('fas fa-arrow-down');
    $('.orderIcon').removeClass('fas fa-arrow-up');
    if($('#listOrder{{ $result['listName'] }}').val() != order) {
            $('#listOrderAsc{{ $result['listName'] }}').val('asc');
            $(element).children('i').addClass('fas fa-arrow-up');
    } else {
        if($('#listOrderAsc{{ $result['listName'] }}').val() == 'asc')
        {
            $('#listOrderAsc{{ $result['listName'] }}').val('desc');
            $(element).children('i').addClass('fas fa-arrow-down');
        }
        else
        {
            $('#listOrderAsc{{ $result['listName'] }}').val('asc');
            $(element).children('i').addClass('fas fa-arrow-up');
        }
    }
    $('#listOrder{{ $result['listName'] }}').val(order);
    getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());

}
function select_cal_type(ob)
{
	var type = ob.value;
}

function getNos()
{

}
// 멀티검색
function multi_view()
{
    var view_tr = document.getElementById("row_select_multi").style.display;

    if(view_tr=="none")
    {
        document.getElementById("row_select_multi").style.display = "";
    }
    else
    {
        document.getElementById("row_select_multi").style.display = "none";
    }
}

</script>


{{-- Content Wrapper. Contains page content --}}
<div class="col-12" @if(isset($result['isPopup']) && $result['isPopup']==='Y') style="padding-left:0px;" @endif >
    @if(isset($result['title']))
        <section class="content-header">
        <h1>{{ $result['title'] }}<small> {{ $result['subTitle'] }} </small></h1>
        </section>
    @endif
    {{-- Main content--}} 
    <section class="content">		
		
		{{-- 서류함 --}} 
        @if(isset($result['Tabs']))
            <nav id="tabsBox" style="display: inline-block;">
                <div class="nav nav-pills border-bottom-0" role="tablist">
                @foreach($result['Tabs']['tabsArray'] as $key=>$val)
                    <a class="nav-link @if ($result['Tabs']['tabsSelect']===$key) active @endif" id="tab{{ $key }}" style='margin-right:0px; cursor: pointer;' onClick='goTab("{{ $result['listAction'] }}", "{{ $key }}", "{{ $result['listName'] }}");'><span id='tab{{ $key }}'>{{ $val }}</span></a>
                @endforeach
				</div>
            </nav>
			<div style="display: inline-block; float: right; margin: 10px 10px 0 0">{!! $scrapHTML ?? '' !!}</div>
        @endif

        {{-- 서류함 끝 --}} 
        <div class="col-md-12 pl-0">
            <div class="card card-lightblue card-outline">
                <div class="box box-{{ config('view.box') }}">
                    <form class="form-horizontal" onsubmit="getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize()); return false;" method="post" name="form_{{ $result['listName'] }}" id="form_{{ $result['listName'] }}">
                    <input type="hidden" name="listName" id="listName{{ $result['listName'] }}" value="{{ $result['listName'] }}">
                    <input type="hidden" name="listOrder" id="listOrder{{ $result['listName'] }}">
                    <input type="hidden" name="listOrderAsc" id="listOrderAsc{{ $result['listName'] }}">
                    <input type="hidden" name="isFirst" id="isFirst{{ $result['listName'] }}" value="1">
                    @if(isset($result['Tabs']))
                    <input type="hidden" name="tabsSelect" id="tabsSelect{{ $result['listName'] }}" value="{{ $result['Tabs']['tabsSelect'] }}">
                    <input type="hidden" name="tabsChange" id="tabsChange{{ $result['listName'] }}">
                    @endif
                    <input type="hidden" name="mode" id="GET_LIST{{ $result['listName'] }}">
                    <input type="hidden" name="nowPage" id="nowPage{{ $result['listName'] }}">
                    <input type="hidden" name="searchCnt" id="searchCnt{{ $result['listName'] }}">
                    <input type="hidden" name="customSearch" id="customSearch{{ $result['listName'] }}">
                    <input type="hidden" name="excelDownCd" id="excelDownCd{{ $result['listName'] }}">
                    <input type="hidden" name="excelUrl" id="excelUrl{{ $result['listName'] }}">
                    <input type="hidden" name="down_div" id="down_div{{ $result['listName'] }}">
                    <input type="hidden" name="excel_down_div" id="excel_down_div{{ $result['listName'] }}">
                    <input type="hidden" name="down_filename" id="down_filename{{ $result['listName'] }}">
                    <input type="hidden" name="excelHeaders" id="excelHeaders{{ $result['listName'] }}">

                    {{ csrf_field() }}
                    @if(isset($result['hidden']))
                        @foreach($result['hidden'] as $key=>$val)
                            <input type="hidden" name='{{ $key }}' id='{{ $key }}{{ $result['listName'] }}' value='{{ $val }}'>
                        @endforeach
                    @endif
                    @if($result['tabsSelect']=='calculation')
                        @include('inc.list_cal')
                    @else
                    
                    {{--  box-header searchBox   --}} 
                    <div class="card-header pt-2">
                        {{-- 체크박스 클릭 시 리스트 출력 컬럼 추가 -----------------}}
                        @if(isset($result['checkboxListAdd']))
                            <div class="pt-2 ml-2" style="float:left;">
                                @foreach($result['checkboxListAdd'] as $key=>$val)
                                    <input type="checkbox" class="form-check-input" name="lists[{{$key}}]" id="lists[{{$key}}]" onclick="listAdd();" value="{{$val}}">
                                    <label class="form-check-label mr-4" for="lists[{{$key}}]">{{$val}} </label>
                                @endforeach
                            </div>
                        @endif
                        <div class="card-tools form-inline" id="searchBox" style="justify-content: flex-end; padding-right:4px;">
                            {{-- 버튼 Array 추가 -------------------------------}}
                            @if(isset($result['buttonArray']))
                                <div class="mr-1 mb-1 mt-1">
                                @foreach($result['buttonArray'] as $i =>$btn)
                                    <button type="button" class="btn btn-sm {{ $btn['buttonArrayClass'] ?? 'btn-primary' }}" onclick="{!! $btn['buttonArrayAction'] !!}" id="{{ $btn['buttonArrayId'] ?? '' }}">{{ $btn['buttonArrayNm'] }}</button>
                                @endforeach
                                </div>
                            @endif
                            {{-- 버튼 Array 추가 끝 -------------------------------}}
                            {{-- 월 검색 ------------------------------}}
                            @if(isset($result['searchWol']))
                                @foreach($result['searchWol'] as $i => $search)
                                    <select class="form-control form-control-sm selectpicker mr-1 mb-1 mt-1" 
                                    name="{{ $search['searchWolNm'] }}" id="{{ $search['searchWolNm'] }}" onChange="{{ $search['searchWolFunc'] }}">
                                        <option value="">{{ $search['searchWolTitle'] }}</option>
                                        {{ Func::printOption($search['searchWolArray'],Func::getArrayName($result,'searchWolSelect'))  }}
                                    </select>
                                        
                                    <div class="input-group date mt-0 mb-0 datetimepicker-wol mr-1 mb-1 mt-1" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm datetimepicker-wol" data-target="#searchWolString" name="{{ $search['searchWolNm'] }}String" id="{{ $search['searchWolNm'] }}String" DateOnly="true" @if ( isset($search['searchWolTxt']) ) value="{{ $search['searchWolTxt'] }}" @endif size="6">
                                        <div class="input-group-append" data-target="#searchWolString" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            {{-- 월 검색 끝 ---------------------------}}
                            {{-- 일자 검색 ------------------------------}}
                            @if(isset($result['searchDate']))
                                @foreach($result['searchDate'] as $i => $search)
                                    <select class="form-control form-control-sm selectpicker mr-1 mb-1 mt-1" 
                                    name="{{ $search['searchDateNm'] }}" id="{{ $search['searchDateNm'] }}" onChange="{{ $search['searchDateFunc'] }}">
                                        <option value="">{{ $search['searchDateTitle'] }}</option>
                                        {{ Func::printOption($search['searchDateArray'],Func::getArrayName($search,'searchDateSelect'))  }}
                                    </select>
                                        
                                    <div class="input-group date mr-1 mt-0 mb-0 datetimepicker" id="{{ $result['listName'] }}{{ $search['searchDateNm'] }}searchDateStringStart" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#{{ $result['listName'] }}{{ $search['searchDateNm'] }}searchDateStringStart" name="{{ $search['searchDateNm'] }}String" id="{{  $result['listName']  }}{{ $search['searchDateNm'] }}String" DateOnly="true" @if ( isset($search['searchDateTxt']) ) value='{{ $search['searchDateTxt'] }}' @endif size="6" autocomplete="off">
                                        <div class="input-group-append" data-target="#{{ $result['listName'] }}{{ $search['searchDateNm'] }}searchDateStringStart" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                    
                                    @if ($search['searchDatePair'] === 'Y')
                                    <span class="mr-1">-</span>
                                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1 mt-1" id="{{ $result['listName'] }}{{ $search['searchDateNm'] }}searchDateStringEnd" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#{{ $result['listName'] }}{{ $search['searchDateNm'] }}searchDateStringEnd" name="{{ $search['searchDateNm'] }}StringEnd" id="{{ $result['listName'] }}{{ $search['searchDateNm'] }}StringEnd" DateOnly="true" @if ( isset($search['searchDateTxtEnd']) ) value='{{ $search['searchDateTxtEnd'] }}' @endif size="6" autocomplete="off">
                                        <div class="input-group-append" data-target="#{{ $result['listName'] }}{{ $search['searchDateNm'] }}searchDateStringEnd" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                        @if ($search['searchDateNoBtn'] === 'YEAR')
                                        <div class="btn-group mr-1 mb-1 mt-1">
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("yesterday", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>전일</button>                                            
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("today", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>금일</button>
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("month", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>금월</button>
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("year", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>금해</button>
                                        </div>
                                        @elseif ($search['searchDateNoBtn'] === 'NONE')
                                        <div class="btn-group mr-1 mb-1 mt-1">
                                        </div>
                                        @elseif ($search['searchDateNoBtn'] != 'Y')
                                        <div class="btn-group mr-1 mb-1 mt-1">
                                            <!--
                                            다 넣어달램
                                            @if(isset($search['searchDateNoBtn']) && $search['searchDateNoBtn']=='YESTERDAY')
                                                <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("yesterday", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd")'>전일</button>
                                            @endif
                                            -->
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("yesterday", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>전일</button>                                            
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("today", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>금일</button>
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("week", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>금주</button>
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("month", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>금월</button>
                                        </div>
                                        @endif
                                    @else
                                        @if($search['searchDateNoBtn'] != 'Y')
                                            @if(isset($search['searchDateNoBtn']) && $search['searchDateNoBtn']=='YESTERDAY')
                                            <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("yesterday", "{{ $search['searchDateNm'] }}String")'>{{ $search['searchDateNoBtnNm'] }}</button>
                                            @else
                                            {{-- <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("today", "{{ $search['searchDateNm'] }}String")'>금일</button> --}}
                                            @endif
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                            {{-- 일자 검색 끝 ---------------------------}}
                            {{-- select 검색상세기준 ------------------------------}}
                            @if(isset($result['searchTypeDetail']))
                                @foreach ($result['searchTypeDetail'] as $select)
                                {{ $select['searchTypeSubject'] ?? '' }}
                                <select class=" form-control form-control-sm selectpicker mr-1 mb-1 mt-1 " 
                                @if($select['searchMultiple']=='Y')
                                    name="{{ $select['searchTypeNm'] }}[]" multiple data-actions-box="true"
                                @else
                                    name="{{ $select['searchTypeNm'] }}" 
                                @endif
                                @if($select['searchLive']=='Y')
                                    data-live-search="true"
                                @endif
                                id="{{ $select['searchTypeNm'] }}" {!! $select['searchTypeAction'] ?? '' !!}

                                @if($select['searchMultiple']=='Y' && isset($select['searchTypeTitle'])) data-none-selected-text="{{ $select['searchTypeTitle'] }}" @endif>
                                
                                    @if($select['searchMultiple']!='Y' && isset($select['searchTypeTitle']))
                                        <option value="">{{ $select['searchTypeTitle'] }}</option>
                                    @endif
                                    @if(isset($select['searchTypeArray']))
                                        {{ Func::printOption($select['searchTypeArray'],Func::getArrayName($select,'searchTypeVal'))  }}
                                    @endif
                                </select>
                                @endforeach
                            @endif
                            {{-- select 검색 끝 ---------------------------}}
                            {{-- select 검색 ------------------------------}}
                            @if(isset($result['searchType']))
                                @foreach ($result['searchType'] as $select)
                                {{ $select['searchTypeSubject'] ?? '' }}
                                <select class=" form-control form-control-sm selectpicker mr-1 mb-1 mt-1 " 
                                @if($select['searchMultiple']=='Y')
                                    name="{{ $select['searchTypeNm'] }}[]" multiple data-actions-box="true"
                                @else
                                    name="{{ $select['searchTypeNm'] }}" 
                                @endif
                                @if($select['searchLive']=='Y')
                                    data-live-search="true"
                                @endif
                                id="{{ $select['searchTypeNm'] }}" {!! $select['searchTypeAction'] ?? '' !!}

                                @if($select['searchMultiple']=='Y' && isset($select['searchTypeTitle'])) data-none-selected-text="{{ $select['searchTypeTitle'] }}" @endif>
                                
                                    @if($select['searchMultiple']!='Y' && isset($select['searchTypeTitle']))
                                        <option value="">{{ $select['searchTypeTitle'] }}</option>
                                    @endif
                                    @if(isset($select['searchTypeArray']))
                                        {{ Func::printOption($select['searchTypeArray'],Func::getArrayName($select,'searchTypeVal'))  }}
                                    @endif
                                </select>
                                @endforeach
                            @endif
                            {{-- select 검색 끝 ---------------------------}}

                            {{-- select chain 검색 ------------------------------}}
                            @if(isset($result['searchTypeChain']))
                                @foreach ($result['searchTypeChain'] as $select)
                                    {{ $select['searchTypeSubject'] ?? '' }}
                                    {!! Func::printChainOption($select['searchTypeTitle'], $select['searchTypeArray'], $select['searchTypeNm'], $select['searchTypeSubNm'], $select['searchTypeVal'], $select['searchTypeSubVal'], $select['searchTypeAction']) !!}
                                @endforeach
                            @endif
                            {{-- select chain 검색 끝 ---------------------------}}
                        
                            {{-- select multi chain 검색 ------------------------------}}
                            @if(isset($result['searchTypeMultiChain']))
                                @foreach ($result['searchTypeMultiChain'] as $select)
                                    {{ $select['searchTypeSubject'] ?? '' }}
                                    {!! Func::printMultiChainOption($select['searchTypeTitle'], $select['searchTypeArray'], $select['searchTypeNm'], $select['searchTypeSubNm'], $select['searchTypeVal'], $select['searchTypeSubVal'], $select['searchTypeAction'], $select['searchTypeSubTitle'], $select['searchLive']) !!}
                                @endforeach
                            @endif
                            {{-- select multi chain 검색 끝 ---------------------------}}

                            {{-- 구간 상세 검색 -------------------------}}
                            @if(isset($result['rangeSearchDetail']))
                                <div class="input-group align-center">
                                    <select class=" form-control form-control-sm mr-1 selectpicker" name="rangeSearchDetail" id="rangeSearchDetail">
                                        <option value="">구간검색</option>
                                        {{ Func::printOption($result['rangeSearchDetail']['rangeSearchDetailArray'],Func::getArrayName($result,'rangeSearchDetailSet'))  }}
                                    </select>
                                    <div class="input-group" style="width:100px">
                                        <input type="text" name="sRangeSearchString" class="form-control form-control-sm" placeholder="{{ $result['rangeSearchDetail']['rangePlHolder'] ?? '' }}" id="sRangeSearchString" value="{{ $result['rangeSearchDetail']['sRangeSearchStringSet'] ?? '' }}" onkeyup="chkNumber();">
                                    </div>
                                    <span class="ml-1 mr-1">-</span>
                                    <div class="input-group mr-1" style="width:100px">
                                        <input type="text" name="eRangeSearchString" class="form-control form-control-sm" placeholder="{{ $result['rangeSearchDetail']['rangePlHolder'] ?? '' }}" id="eRangeSearchString" value="{{ $result['rangeSearchDetail']['eRangeSearchStringSet'] ?? '' }}" onkeyup="chkNumber();">
                                    </div>
                                </div>
                            @endif

                            @if(isset($result['rangeSearchDetail2']))
                                <div class="input-group align-center">
                                    <select class=" form-control form-control-sm mr-1 selectpicker" name="rangeSearchDetail2" id="rangeSearchDetail2">
                                        <option value="">구간검색</option>
                                        {{ Func::printOption($result['rangeSearchDetail2']['rangeSearchDetailArray'],Func::getArrayName($result,'rangeSearchDetailSet'))  }}
                                    </select>
                                    <div class="input-group" style="width:100px">
                                        <input type="text" name="sRangeSearchString2" class="form-control form-control-sm" placeholder="{{ $result['rangeSearchDetail2']['rangePlHolder'] ?? '' }}" id="sRangeSearchString2" value="{{ $result['rangeSearchDetail2']['sRangeSearchStringSet'] ?? '' }}" onkeyup="chkNumber();">
                                    </div>
                                    <span class="ml-1 mr-1">-</span>
                                    <div class="input-group mr-1" style="width:100px">
                                        <input type="text" name="eRangeSearchString2" class="form-control form-control-sm" placeholder="{{ $result['rangeSearchDetail2']['rangePlHolder'] ?? '' }}" id="eRangeSearchString2" value="{{ $result['rangeSearchDetail2']['eRangeSearchStringSet'] ?? '' }}" onkeyup="chkNumber();">
                                    </div>
                                </div>
                            @endif
                            {{-- 구간 상세 검색 끝 ----------------------}}
                            {{-- 상세 검색 ------------------------------}}
                            @if(isset($result['searchDetail']))
                                <div class="input-group">
                                    <select class="@if(0)form-control input-sm @else form-control form-control-sm @endif mr-1 mb-1 mt-1 selectpicker" name="searchDetail" id="searchDetail">
                                        <option value=""> 상세검색</option>
                                        {{ Func::printOption($result['searchDetail'],Func::getArrayName($result,'searchDetailSet'))  }}
                                    </select>
                                    <div class="input-group mt-0 mb-0 mr-1 mb-1 mt-1" style="width:120px;">
                                    <input type="text" name="searchString" class="form-control form-control-sm" placeholder="Search" id="searchString" value="{{ $result['searchStringSet'] ?? '' }}" {{ $result['searchStringReadOnly'] }}
                                    @if(isset($result['searchStringReadOnly']) && $result['searchStringReadOnly'] == 'Y') readonly @endif>
                                    </div>
                                </div>
                            @endif
                            {{-- 상세 검색 끝 ------------------------------}}
                            @if(isset($result['statusCheckBox']))
                            <div class="input-group">
                                <input type="checkbox" name="status_e_yn" id="status_e_yn" class="list-check" value="Y" style="opacity: 0;" onchange="{!! $result['statusCheckBox'] !!}">
                                <label class="form-check-label ml-1 font-weight-bold" for="status_e_yn">완제포함</label>
                            </div>
                            @endif
                            @if(!isset($result['Tabs'])|| (isset($result['Tabs']) && $result['Tabs']['tabsSelect'] !== 'X' && $result['Tabs']['tabsSelect'] !== 'E' && $result['Tabs']['tabsSelect'] !== 'SMS'))
                                
                                <div class="input-group mt-1 mb-1  input-group-sm ml-1 align-center">
                                    한페이지
                                    <input type="text" name="listLimit" class="form-control form-control-sm ml-1" placeholder="한페이지" id="listLimit{{ $result['listName'] }}" value="{{ $_COOKIE[$result['listName']] }}" onkeyup="NotZero(this);" style="width:50px;height:29px;">
                                    <div class="input-group-append">
                                        <button type="submit" title="조회" class="btn btn-primary" id="btn_search" onclick="getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize()); return false;" style="height:29px;"><i class="fa fa-search"></i>조회</button>

                                        <!-- <button type="button" title="새로고침" class="btn btn-default" onclick="@if(isset($result['isPopup']) && $result['isPopup']==='Y' && isset($result['popupListAction'])) {{ $result['popupListAction'] }} @elseif($result['refresh']!='') {{ $result['refresh'] }} @else location.href='{{ $result['listAction'] }}'; @endif ;return false;" style="height:29px;"><i class="fas fa-sync"></i></button> -->

                                        @if(isset($result['plusButton']))
                                            <button type="button" class="btn btn-default" onclick="{!! $result['plusButton'] !!}" title="등록" style="height:29px;"><i class="fa fa-plus-square text-primary"></i></button>
                                        @endif

                                        @if(isset($result['multiButton']))
                                            <button type="button" class="btn btn-default" onclick="{!! $result['multiButton'] !!}" title="멀티검색" style="height:29px;"><i class="fa fa-cog"></i></button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if (isset($result['Tabs']) && ($result['Tabs']['tabsSelect'] === 'X' || $result['Tabs']['tabsSelect'] === 'E'))
                                <div class="input-group mt-1 mb-1 input-group-sm">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default" onclick="getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize()); return false;">실행</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if(isset($result['multiButton']))
                        <div class="input-group mt-1 mb-1 input-group-sm align-right" style="display:none; margin-left:540px;" id="row_select_multi">
                            <select class="form-control form-control-sm text-xs col-md-1" name="multi_detail" id="multi_detail">
                                <option value=''>멀티검색</option>
                                    {{ Func::printOption($result['multiArray'])  }}
                            </select>
                            &nbsp;&nbsp;
                            <textarea name="multi_content" cols="40" rows="10" style="width:58%"></textarea>
                        </div>
                        @endif
                    </div>
                    {{-- box-header searchBox --}}
                    <div class="card-body p-0" id="dataTable">
                        <table class="table table-hover table-striped table-sm mb-1 floating-thead" id="tb_{{ $result['listName'] }}">
                        <thead>
                        {{-- 사용 x
                        @if (isset($result['listTopTitle']))
                            <tr>
                                @foreach($result['listTopTitle'] as $key=>$val)
                                <th class="text-center" colspan='{{ $val[1] }}' bgcolor='@if($val[2]){{ $val[2] }} @endif'>{!! $val[0] !!}</th>
                                @endforeach
                            </tr>
                        @endif 
                        --}}
                        @php 
                            $orderColCnt = 0;
                            $orderContinue = '';
                        @endphp
                        <tr id="{{ $result['listName'] }}ListHeader">
                            @if($result['viewNum']==true)
                                <th class="text-center ml-0" style="width:45px">NO</th>
                                @php 
                                    $orderTabs[$orderColCnt] = '';
                                    $orderDisplay[$orderColCnt] = '';
                                    $orderColCnt ++; 
                                @endphp
                            @endif
                            {{-- 선택 ------------------------------}}
                            @if(isset($result['checkbox']))
                                <th class="text-center" style="width:20px">
                                    <input type="checkbox" name="check-all" id="check-all" class="check-all">
                                </th>
                                @php
                                    $orderContinue = $orderColCnt; 
                                    $orderColCnt ++; 
                                @endphp
                            @endif
                            
                            @foreach($result['listTitle'] as $Tabs=>$listTitle)
                                @foreach($listTitle as $key=>$val)
                                    @if(!isset($val[6]) && !isset($val[7]))
                                        <th class="text-center listHeader-{{$Tabs}} @if(isset($val[4]) && $val[4]!='') rightline @endif" style="@if(isset($val[5]) && !empty($val[5]) && !in_array($val[5], $arrayAllCol))cursor:pointer;@endif @if(isset($val[2])) width:{{ $val[2] }}; @endif  @if($Tabs!='common' && $Tabs!='commonEnd' && $Tabs!=$result['Tabs']['tabsSelect']) display:none; @endif"  @if(isset($val[5]) && !empty($val[5]) && !in_array($val[5], $arrayAllCol)) onclick="nameorder('{{$val[5]}}', this)" @endif>
                                            {!! $val[0] !!} <i class="orderIcon"></i>
                                        </th>
                                    @elseif(isset($val[6]) && !empty($val[6]))
                                        <th class="text-center listHeader-{{$Tabs}} @if(isset($val[4]) && $val[4]!='') rightline @endif" @if($Tabs!="common" && $Tabs!=$result['Tabs']['tabsSelect']) display:none; @endif>
                                            <span style="@if(isset($val[5]) && !empty($val[5]) && !in_array($val[5], $arrayAllCol))cursor:pointer;@endif @if(isset($val[2])) width:{{ $val[2] }}; @endif" @if(isset($val[5]) && !empty($val[5]) && !in_array($val[5], $arrayAllCol)) onclick="nameorder('{{$val[5]}}', this);" @endif>{!! $val[0] !!} <i class="orderIcon"></i></span>
                                            @foreach ($val[6] as $k => $v)
                                                {!! $v[2] !!}<span @if(isset($v[1]) && !empty($v[1]) && !in_array($v[1], $arrayAllCol)) style="cursor: pointer;" onclick="nameorder('{{$v[1]}}', this);" @endif>{!! $v[0] !!} <i class="orderIcon"></i></span>
                                            @endforeach
                                        </th>
                                    @else
                                        <th class="text-center listHeader-{{$Tabs}} @if(isset($val[4]) && $val[4]!='') rightline @endif" style="@if(isset($val[7]) && !empty($val[7]) && !in_array($val[7], $arrayAllCol))cursor:pointer;@endif @if(isset($val[2])) width:{{ $val[2] }}; @endif  @if($Tabs!='common' && $Tabs!='commonEnd' && $Tabs!=$result['Tabs']['tabsSelect']) display:none; @endif"  @if(isset($val[7]) && !empty($val[7]) && !in_array($val[7], $arrayAllCol)) onclick="{{ $val[7] ?? '' }}" @endif>
                                            {!! $val[0] !!} <i class="orderIcon"></i>
                                        </th>
                                    @endif
                                    @php 
                                        $orderTabs[$orderColCnt] = $Tabs;
                                        $orderDisplay[$orderColCnt] = '';
                                        if($Tabs!='common' && $Tabs!='commonEnd' && $Tabs!=$result['Tabs']['tabsSelect'])
                                            $orderDisplay[$orderColCnt] = 'display:none';

                                        $orderColCnt ++;                                     
                                    @endphp
                                @endforeach
                            @endforeach
                        </tr>
                        @if($result['resultOrder']==true && $orderColCnt>0)
                        <tr align="center" title="결과내정렬" style='font-size:10px'>
                            @for($i=0; $i<$orderColCnt; $i++)
                                @if($orderContinue==$i)
                                    <th class="result-order" id="order-{{ $i }}"></td>
                                @else
                                    <th class='hand result-order order-text p-1 m-0 listHeader-{{ $orderTabs[$i] }} ' style='{{ $orderDisplay[$i] }}' id="order-{{ $i }}" onClick="javascript:orderTable('tb_{{ $result['listName'] }}', {{ $i }});">△</th>
                                @endif
                            @endfor
                        </tr>
                        @endif 
                        </thead>
                        <tbody id="listData_{{ $result['listName'] }}">
                            </tbody>
                        </table>
                        <div id="listError_{{ $result['listName'] }}"></div>
                    </div>

                    {{-- 일괄처리 & 페이지 버튼 --}}
                    <div class="card-footer mb-0">
                        <div class="col-md-12 p-0 pt-2 m-0 row">
                            <div class="col-md-6">
                                @if(isset($result['lumpForm']))
                                    @foreach( $result['lumpForm'] as $lumpcd => $lumpv )
                                    <button class="btn btn-sm @if (isset($lumpv['BTN_COLOR']) && $lumpv['BTN_COLOR'] != '') {{ $lumpv['BTN_COLOR'] }} @else btn-info @endif" id="LUMP_BTN_{{ $lumpcd }}" onclick="{{ ( isset($lumpv['BTN_ACTION']) && $lumpv['BTN_ACTION'] ) ? $lumpv['BTN_ACTION'].';' : "lump_btn_click('".$lumpcd."', '".$lumpv['BTN_NAME']."');" }} return false;">{{ $lumpv['BTN_NAME'] }}</button>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-md-6 text-right" id="pageList_{{ $result['listName'] }}"></div>
                            </div>
                            @if(isset($result['rightlumpForm']))
                                @foreach( $result['rightlumpForm'] as $lumpcd => $lumpv )
                                <button style="float: right;" class="btn btn-sm @if (isset($lumpv['BTN_COLOR']) && $lumpv['BTN_COLOR'] != '') {{ $lumpv['BTN_COLOR'] }} @else btn-info @endif" id="LUMP_BTN_{{ $lumpcd }}" onclick="{{ ( isset($lumpv['BTN_ACTION']) && $lumpv['BTN_ACTION'] ) ? $lumpv['BTN_ACTION'].';' : "lump_btn_click('".$lumpcd."', '".$lumpv['BTN_NAME']."');" }} return false;">{{ $lumpv['BTN_NAME'] }}</button>
                                @endforeach
                            @endif
                        </div>
                    @endif
                    </form>
                </div>
            </div>
        </div>
        {{--
        일단 주석했다가 삭제, 나중에 필요할 때 방법 다시 고민하기로... --}}
        @if( isset($result['incSum']) && $result['incSum']!='' )
            @include($result['incSum'])
        @endif
       
    </section>
</div>
@if(isset($result['isModal']))
<div class="modal fade" id="modal01" @if (isset($result['isModal']['modalOption']) ) {{ $result['isModal']['modalOption'] }} @endif>
    <div class="modal-dialog @if (isset($result['isModal']['modalSize'])) {{ $result['isModal']['modalSize'] }} @endif">
        <div class="modal-content" id="modalContents">
            <div class="modal-header">
                
                <h4 class="modal-title" style="font-weight:bold;" id="modalTitle">{{ $result['isModal']['modalTitle'] }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div id="modalBody">
            </div>
        </div>
    </div>
</div>
@endif

@if(!isset($excelUseNone))
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
                    <div class="row mt-1">
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
                    <div class="row mt-1" style="display:none">
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
                <button type="button" class="btn btn-sm btn-primary" onclick="excelDown('@if(!isset($excelSelectId))form_'+'{{$result['listName']}}@endif');">다운로드</button>
            </div>
        </div>
    </div>
</div>
@endif

<script language='javascript'>

    
    @if(isset($result['Tabs']))
    window.onload = function() {
            // 체크박스모양
            $('input[name="listChk[]"]').iCheck({
                checkboxClass: 'icheckbox_square-blue',
            });
            $('input[name="check-all"]').iCheck({
                checkboxClass: 'icheckbox_square-blue',
            });
            // 리스트 전체 선택/해제
            $('#{{ $result['listName'] }}ListHeader').on('ifChecked', '.check-all', function(event) {
                $('.list-check').iCheck('check');
            });
            $('#{{ $result['listName'] }}ListHeader').on('ifUnchecked', '.check-all', function(event) {
                $('.list-check').iCheck('uncheck');
            });
            
            $(".datetimepicker").datetimepicker({
                format: 'YYYY-MM-DD',
                locale: 'ko',
                useCurrent: false,
            });

            if("{{ $result['listName'] }}" == 'mydata' && "{{ $result['Tabs']['tabsSelect'] }}" == 'MASTER')
            {
                $("#searchDetail").parent("div").parent("div").css('display', 'none');
            }

            // 예약 다운 시 파일명 입력칸 보이기
            if($('input[name="excel_down_div"]:checked').val() == "S")
            {
                $('#down_filename').css('display', 'block');
            }

            @if(isset($result['isPopup']))
                // 메뉴없는 팝업페이지 진입시 데이터 가져오기
                getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());              
            @endif
        }
    @else
        window.onload = function() {
            // 체크박스모양
            $('input[name="listChk[]"]').iCheck({
                checkboxClass: 'icheckbox_square-blue',
            });
            $('input[name="check-all"]').iCheck({
                checkboxClass: 'icheckbox_square-blue',
            });
            // 리스트 전체 선택/해제
            $('#{{ $result['listName'] }}ListHeader').on('ifChecked', '.check-all', function(event) {
                $('.list-check').iCheck('check');
            });
            $('#{{ $result['listName'] }}ListHeader').on('ifUnchecked', '.check-all', function(event) {
                $('.list-check').iCheck('uncheck');
            });
            
            $(".datetimepicker").datetimepicker({
                format: 'YYYY-MM-DD',
                locale: 'ko',
                useCurrent: false,
            });

            // 예약 다운 시 파일명 입력칸 보이기
            if($('input[name="excel_down_div"]:checked').val() == "S")
            {
                $('#down_filename').css('display', 'block');
            }

             @if(isset($result['isPopup']))
                // 메뉴없는 팝업페이지 진입시 데이터 가져오기
                getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());
            @endif
        }
    @endif

</script>
