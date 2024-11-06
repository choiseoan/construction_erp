<?
    if(!isset($_COOKIE[$result['listName']]))
    {
        $_COOKIE[$result['listName']] = 10;
    }

    if(!isset($result['tabsSelect']))
    {
        $result['tabsSelect'] = '';
    }       
?>

<script language='javascript'>
    // 리스트 표현 태그
    function getMarkup{{ $result['listName'] }}() 
    {
        var markup = "";
        markup = "<tr id='no_${no}' style='${line_style}' onclick='${onclick}'>";
        
        // checkbox 
        @if (isset($result['checkbox']) && $result['checkbox'] === 'Y')
            markup+= "<td class='text-center'><input type='checkbox' name='listChk[]' id='listChk${<?=$result['checkboxNm']?>}' class='list-check' value='${<?=$result['checkboxNm']?>}'></td>";
        @endif

        @foreach($result['listTitle'] as $key=>$val)
            @if(isset($val[6]) && is_array($val[6]))
            markup+= "<td class='text-{{ $val[3] }} @if(isset($val[4]) && $val[4]!='') rightline @endif'>";
                    markup+= "{{html <?=$key?>}}";
                @foreach ($val[6] as $k => $arr)
                    markup+= "<?=$arr[2]?>{{html <?=$k?>}}";
                @endforeach
            markup+= "</td>";
            @else
            markup+= "<td class='text-{{ $val[3] }} @if(isset($val[4]) && $val[4]!='') rightline @endif'>{{html <?=$key?>}}</td>";
            @endif
        @endforeach
        markup+= "</tr>";
        return markup;
    }

    @if ($result['isModal'] === 'Y')
    function modalAction
    (
        @for ($i=0; $i<count($result['modalParams']); $i++) @if ($i>0) ,@endif {{ $result['modalParams'][$i] }} @endfor
    )
    {
        $("#modalTitle").text('{{ $result['modalTitle'] }}');
        $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        $.post("{{ $result['modalAction'] }}", {
            @for ($i=0; $i<count($result['modalParams']); $i++) @if ($i>0) ,@endif {{ $result['modalParams'][$i].' : ' }} {{ $result['modalParams'][$i] }} @endfor
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
                break;
            case 'month' :
                $('#' + startDt).val('{{ date("Y-m") }}-01');
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
</script>

<!-- Content Wrapper. Contains page content -->
<div class="col-12 text-xs p-0" @if(isset($result['isPopup']) && $result['isPopup']==='Y') style="padding-left:0px;" @endif >
    @if(isset($result['title']) && $result['title']!='')
        <section class="content-header">
        <h1>{{ $result['title'] }}<small> {{ $result['subTitle'] }} </small></h1>
        </section>
    @endif

    <!-- Main content -->
    <section class="content m-0"  style="border:none; box-shadow:none;">
		<!-- 서류함 ------------------------------>
        @if (isset($result['tabs']) && $result['tabs'] === 'Y')
        <div class="col-md-12">
            <nav id="tabsBox">
                <ul class="nav nav-tabs border-bottom-0" role="tablist">
                @for ($i=0; $i<count($result['tabsArray']); $i++)
                    @foreach($result['tabsArray'][$i] as $key=>$val)
                    <li class="nav-item">
                        <a class="nav-link @if ($result['tabsSelect']===$key) active @endif" id="tab{{ $key }}" style='margin-right:0px; cursor: pointer;' onClick='goTab("{{ $result['listAction'] }}", "{{ $key }}", "{{ $result['listName'] }}");'><span id='tab{{ $key }}'>{{ $val }}</span></a>
                    </li>
                    @endforeach
                @endfor
                </ul>
            </nav>
        </div>
        @endif
        <!-- 서류함 끝 ------------------------------>

        <div class="col-md-12">
            <form class="form-horizontal" onsubmit="getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize()); return false;" method="post" name="form_{{ $result['listName'] }}" id="form_{{ $result['listName'] }}">
            <input type="hidden" name="listName" id="listName{{ $result['listName'] }}" value="{{ $result['listName'] }}">
            <input type="hidden" name="listOrder" id="listOrder{{ $result['listName'] }}">
            <input type="hidden" name="listOrderAsc" id="listOrderAsc{{ $result['listName'] }}">
            <input type="hidden" name="isFirst" id="isFirst{{ $result['listName'] }}" value="1">
            <input type="hidden" name="tabsSelect" id="tabsSelect{{ $result['listName'] }}" value="{{ $result['tabsSelect'] }}">
            <input type="hidden" name="tabsChange" id="tabsChange{{ $result['listName'] }}">
            <input type="hidden" name="mode" id="GET_LIST{{ $result['listName'] }}">
            <input type="hidden" name="nowPage" id="nowPage{{ $result['listName'] }}">
            <input type="hidden" name="searchCnt" id="searchCnt{{ $result['listName'] }}">
            <input type="hidden" name="customSearch" id="customSearch{{ $result['listName'] }}">

            {{ csrf_field() }}
            @if(isset($result['hidden']) && $result['hidden']==='Y')
                @foreach($result['array_hidden'] as $key=>$val)
                    <input type="hidden" name='{{ $key }}' id='{{ $key }}{{ $result['listName'] }}' value='{{ $val }}'>
                @endforeach
            @endif
            @if($result['tabsSelect']=='calculation')
                @include('inc.list_cal')
            @else
                <!-- box-header searchBox -->
                <div class="col-md-12 p-0">

                    <!-- 체크박스 클릭 시 리스트 출력 컬럼 추가 ----------------->
                    @if(isset($result['checkboxListAdd']) && $result['checkboxListAdd'] === 'Y')
                        <div class="pt-2 ml-2" style="float:left;">
                            @foreach($result['checkboxListInfo'] as $key=>$val)
                                <input type="checkbox" class="form-check-input" name="lists[{{$key}}]" id="lists[{{$key}}]" onclick="listAdd();" value="{{$val}}">
                                <label class="form-check-label mr-4" for="lists[{{$key}}]">{{$val}} </label>
                            @endforeach
                        </div>
                    @endif

                
                    <div class="form-inline p-0" id="searchBox">
                        <!-- 버튼 추가 ------------------------------->
                        @if(isset($result['button']) && $result['button'] === 'Y')
                        <button type="button" class="btn btn-sm btn-primary mr-1 mb-1 mt-1" onclick="{!! $result['buttonAction'] !!}">{{ $result['buttonName'] }}</button>
                        @endif
                        <!-- 버튼 추가 끝 ------------------------------->


                        <!-- 버튼 Array 추가 ------------------------------->
                        @if(isset($result['buttonArray']) && $result['buttonArray'] === 'Y')
                            <div class="mr-1 mb-1 mt-1">
                            @for ($i=0; $i<count($result['buttonArrayNm']); $i++)
                                <button type="button" class="btn btn-sm {{ $result['buttonArrayClass'][$i] ?? 'btn-primary' }}" onclick="{!! $result['buttonArrayAction'][$i] !!}">{{ $result['buttonArrayNm'][$i] }}</button>
                            @endfor
                            </div>
                        @endif
                        <!-- 버튼 Array 추가 끝 ------------------------------->


                        <!-- 일자 검색 ------------------------------>
                        @if (isset($result['searchDate']) && $result['searchDate'] === 'Y')
                            @for ($i=0; $i<count($result['searchDateNm']); $i++)
                                @if ( isset($result['searchDateText'][$i]) )
                                    <span class='p-1'>{{$result['searchDateText'][$i]}}</span>
                                @endif

                                @if( isset($result['searchDateArray'][$i]) && count($result['searchDateArray'][$i]) > 0 )
                                    <select class="
                                    form-control text-xs
                                    @if(0)	
                                    input-sm
                                    @else
                                    form-control-sm selectpicker mr-1 mb-1 mt-1
                                    @endif
                                    " name="{{ $result['searchDateNm'][$i] }}" id="{{ $result['searchDateNm'][$i] }}">
                                        <option value="">{{ $result['searchDateTitle'][$i] }}</option>
                                        @foreach($result['searchDateArray'][$i] as $key=>$val)
                                            <option value='{{ $key }}'
                                            @if ( isset($result['searchDateSelect'][$i]) && $result['searchDateSelect'][$i]==$key ) selected @endif
                                            >{{ $val }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <div class="input-group mt-0 mb-0 date datetimepicker" id="searchDateStringStart" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#searchDateStringStart" name="{{ $result['searchDateNm'][$i] }}String" id="{{ $result['searchDateNm'][$i] }}String" DateOnly="true"
                                    @if ( isset($result['searchDateTxt'][$i]) ) value='{{ $result['searchDateTxt'][$i] }}'  @endif size="6">
                                    <div class="input-group-append" data-target="#searchDateStringStart" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                                    </div>
                                </div>
                                
                                @if ($result['searchDatePair'][$i] === 'Y')
                                <span class="ml-1 mr-1">-</span>
                                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1 mt-1" id="searchDateStringEnd" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#searchDateStringEnd" name="{{ $result['searchDateNm'][$i] }}StringEnd" id="{{ $result['searchDateNm'][$i] }}StringEnd" DateOnly="true" @if ( isset($result['searchDateTxt'][$i]) ) value='{{ $result['searchDateTxt'][$i] }}' @endif size="6">
                                        <div class="input-group-append" data-target="#searchDateStringEnd" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                                        </div>
                                    </div>
                                    @if (!isset($result['searchDateNoBtn'][$i]) || $result['searchDateNoBtn'][$i] != 'Y')
                                    <div class="btn-group mr-1 mb-1 mt-1">
                                        @if(isset($result['searchDateNoBtn'][$i]) && $result['searchDateNoBtn'][$i]=='YESTERDAY')
                                            <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("yesterday", "{{ $result['searchDateNm'][$i] }}String", "{{ $result['searchDateNm'][$i] }}StringEnd")'>{{ $result['searchDateNoBtnNm'][$i] }}</button>
                                        @endif
                                    <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("today", "{{ $result['searchDateNm'][$i] }}String", "{{ $result['searchDateNm'][$i] }}StringEnd")'>금일</button>
                                    <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("week", "{{ $result['searchDateNm'][$i] }}String", "{{ $result['searchDateNm'][$i] }}StringEnd")'>금주</button>
                                    <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("month", "{{ $result['searchDateNm'][$i] }}String", "{{ $result['searchDateNm'][$i] }}StringEnd")'>금월</button>
                                    </div>
                                    @endif
                                @else
                                    @if (!isset($result['searchDateNoBtn'][$i]) || $result['searchDateNoBtn'][$i] != 'Y')
                                        @if(isset($result['searchDateNoBtn'][$i]) && $result['searchDateNoBtn'][$i]=='YESTERDAY')
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("yesterday", "{{ $result['searchDateNm'][$i] }}String")'>{{ $result['searchDateNoBtnNm'][$i] }}</button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("today", "{{ $result['searchDateNm'][$i] }}String")'>금일</button>
                                        @endif
                                    @endif
                                @endif
                            @endfor
                        @endif
                        <!-- 일자 검색 끝 --------------------------->


                        <!-- select 검색 ------------------------------>
                        @if (isset($result['searchType']) && $result['searchType'] === 'Y')
                            @for ($i=0; $i<count($result['searchTypeNm']); $i++)
                            {{ $result['searchTypeSubject'][$i] ?? '' }}
                            <select class="
                                form-control text-xs
                                @if(0) 
                                input-sm
                                @else
                                form-control-sm mr-1 mb-1 mt-1
                                @endif
                                " name="{{ $result['searchTypeNm'][$i] }}" id="{{ $result['searchTypeNm'][$i] }}" {!! $result['searchTypeAction'][$i] ?? '' !!}>
                                @if(isset($result['searchTypeTitle'][$i]))
                                    <option value="">{{ $result['searchTypeTitle'][$i] }}</option>
                                @endif
                                @if(isset($result['searchTypeArray'][$i]))
                                    @foreach($result['searchTypeArray'][$i] as $key=>$val)
                                        <option value='{{ $key }}'
                                        @if (isset($result['searchTypeVal'][$i]) && $result['searchTypeVal'][$i] ==$key)  selected @endif
                                        >{{ $val }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @endfor
                        @endif
                        <!-- select 검색 끝 --------------------------->


                        <!-- 구간 상세 검색 ------------------------->
                        @if (isset($result['rangeSearchDetail']) && $result['rangeSearchDetail'] === 'Y')
                            <select class="@if(0)form-control input-sm @else form-control form-control-sm @endif mr-1 text-xs" name="rangeSearchDetail" id="rangeSearchDetail">
                                <option value="">구간상세검색</option>
                                @foreach($result['rangeSearchDetailArray'] as $key=>$val)
                                    <option value='{{ $key }}' @if(isset($result['rangeSearchDetailSet']) && $result['rangeSearchDetailSet']===$key) selected @endif ) >{{ $val }}</option>
                                @endforeach
                            </select>
                            <div class="input-group" style="width:120px">
                            <input type="text" name="sRangeSearchString" class="form-control form-control-sm" placeholder="{{ $result['rangePlHolder'] ?? '' }}" id="sRangeSearchString" value="{{ $result['sRangeSearchStringSet'] ?? '' }}" onkeyup="chkNumber();">
                            </div>
                            <span class="ml-1 mr-1">-</span>
                            <div class="input-group mr-1" style="width:120px">
                            <input type="text" name="eRangeSearchString" class="form-control form-control-sm" placeholder="{{ $result['rangePlHolder'] ?? '' }}" id="eRangeSearchString" value="{{ $result['eRangeSearchStringSet'] ?? '' }}" onkeyup="chkNumber();">
                            </div>
                        @endif
                        <!-- 구간 상세 검색 끝 ---------------------->


                        <!-- 상세 검색 ------------------------------>
                        @if (isset($result['searchDetail']) && $result['searchDetail'] === 'Y')
                            @if ( isset($result['searchDetailLineChg']) && $result['searchDetailLineChg'] === 'Y' )
                                </div><div class="form-inline p-0">
                            @endif
                            <select class="@if(0)form-control input-sm @else form-control form-control-sm @endif mr-1 mb-1 mt-1 text-xs" name="searchDetail" id="searchDetail">
                                <option value=""> 상세검색</option>
                                @foreach($result['searchDetailArray'] as $key=>$val)
                                    <option value='{{ $key }}' @if(isset($result['searchDetailSet']) && $result['searchDetailSet']===$key) selected @endif ) >{{ $val }}</option>
                                @endforeach
                            </select>
                            <!--
                            <div class="input-group mt-0 mb-0 mr-1 mb-1 mt-1" style="width:120px">
                                <input type="text" name="searchString" class="form-control form-control-sm text-xs" placeholder="Search" id="searchString" value="{{ $result['searchStringSet'] ?? '' }}">
                            </div>
                            -->
                            <div class="input-group mt-1 mb-1  input-group-sm ml-1">
                                <input type="hidden" name="listLimit" class="form-control" placeholder="한페이지" id="listLimit{{ $result['listName'] }}" value="{{ $result['listlimit'] ?? 10 }}">
                                <input type="text" name="searchString" class="form-control form-control-sm text-xs" placeholder="Search" id="searchString" value="{{ $result['searchStringSet'] ?? '' }}">

                                <div class="input-group-append">
                                    <button type="button" class="btn btn-default" onclick="getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize()); return false;"><i class="fa fa-xs fa-search"></i></button>
                                    @if ($result['plusButton'] === 'Y')
                                    <button type="button" class="btn btn-default" onclick="{!! $result['plusButtonAction'] !!}"><i class="fa fa-xs fa-plus-square text-primary"></i></button>
                                    @endif
                                </div>
                            </div>
                        @elseif(isset($result['searchDetail']) && $result['searchDetail'] === 'N')
                            <div class="input-group mt-1 mb-1  input-group-sm">
                                <input type="hidden" name="listLimit" class="form-control" placeholder="한페이지" id="listLimit{{ $result['listName'] }}" value="{{ $result['listlimit'] ?? 10 }}">

                                <button type="button" class="btn btn-default btn-sm" onclick="getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize()); return false;"><i class="fa fa-xs fa-search"></i></button>
                                @if ($result['plusButton'] === 'Y')
                                <button type="button" class="btn btn-default btn-sm" onclick="{!! $result['plusButtonAction'] !!}"><i class="fa fa-xs fa-plus-square text-primary"></i></button>
                                @endif
                            </div>
                        @endif

                        <!-- 상세 검색 끝 ------------------------------>
                    </div>
                </div>
                <!-- box-header searchBox -->
                <div class="col-md-12 p-0" id="dataTable">
                    <table class="table table-hover table-striped table-sm mb-1 {{$result['tableClass'] ?? ''}}">
                        <thead>
                            @if (isset($result['listTopTitle']))
                                <tr>
                                    @foreach($result['listTopTitle'] as $key=>$val)
                                    <th class="text-center" colspan='{{ $val[1] }}' bgcolor='@if($val[2]){{ $val[2] }} @endif'>{!! $val[0] !!}</th>
                                    @endforeach
                                </tr>
                            @endif
                            <tr id="{{ $result['listName'] }}ListHeader">
                                <!-- 선택 ------------------------------>
                                @if (isset($result['checkbox']) && $result['checkbox'] === 'Y')
                                    <th class="text-center" style="width:20px">
                                        <input type="checkbox" name="check-all" id="check-all" class="check-all">
                                    </th>
                                @endif
                                @foreach($result['listTitle'] as $key=>$val)
                                    @if(!isset($val[6]))
                                        <th class="text-center @if(isset($val[4]) && $val[4]!='') rightline @endif" style="@if(isset($val[5]))cursor:pointer;@endif @if(isset($val[2])) width:{{ $val[2] }}; @endif "  @if(isset($val[5])) onclick="nameorder('{{$val[5]}}', this)" @endif>
                                            {!! $val[0] !!} <i class="orderIcon"></i>
                                        </th>
                                    @else
                                        <th class="text-center @if(isset($val[4]) && $val[4]!='') rightline @endif" style="@if(isset($val[5]))cursor:pointer;@endif @if(isset($val[2])) width:{{ $val[2] }}; @endif ">
                                            <span @if(isset($val[5])) onclick="nameorder('{{$val[5]}}', this);">@endif{!! $val[0] !!} <i class="orderIcon"></i></span>
                                            @foreach ($val[6] as $k => $v)
                                                {!! $v[2] !!}<span @if(isset($v[1])) style="cursor: pointer;" onclick="nameorder('{{$v[1]}}', this);" @endif>{!! $v[0] !!} <i class="orderIcon"></i></span>
                                            @endforeach
                                        </th>
                                    @endif
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="listData_{{ $result['listName'] }}"></tbody>
                    </table>
                    <div id="listError_{{ $result['listName'] }}"></div> 
                </div>
                
                <div id="pageList_{{ $result['listName'] }}" class="card-footer"></div>
            @endif
            </form>
        </div>
        @if (isset($result['incBatch']) && $result['incBatch']!='')
            <button class="btn btn-sm btn-info" onclick="lump_btn_click();">일괄처리</button>
            <!-- 일괄처리 버튼시작 -->
            @if(isset($result['batchButtonArray']) && $result['batchButtonArray'] === 'Y')
                <div class="mr-1 mb-1 mt-1">
                    @for ($i=0; $i<count($result['batchButtonArrayNm']); $i++)
                        <button type="button" class="btn btn-sm {{ $result['batchButtonArrayClass'][$i] ?? 'btn-primary' }}" onclick="{!! $result['batchButtonArrayAction'][$i] !!}">{{ $result['batchButtonArrayNm'][$i] }}</button>
                    @endfor
                </div>
            @endif
            <!-- 일괄처리 버튼끝 -->
            @section('lump')
                @include($result['incBatch'])
            @endsection
        @endif
        @if (isset($result['incSum']) && $result['incSum']!='')
            @include($result['incSum'])
        @endif
    </section>
</div>

@if ($result['isModal'] === 'Y')
<div class="modal fade" id="modal01" @if (isset($result['modalOption']) ) {{ $result['modalOption'] }} @endif>
    <div class="modal-dialog @if (isset($result['modalSize'])) {{ $result['modalSize'] }} @endif">
        <div class="modal-content" id="modalContents">
            <div class="modal-header">
                
                <h4 class="modal-title" style="font-weight:bold;" id="modalTitle">{{ $result['modalTitle'] }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div id="modalBody">
            </div>
        </div>
    </div>
</div>
@endif

<script language='javascript'>
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
    }

    @if(isset($result['isPopup']) && $result['isPopup']==='Y')
        // 메뉴없는 팝업페이지 진입시 데이터 가져오기
        getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());
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
    @endif
</script>