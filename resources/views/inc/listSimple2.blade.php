
<script>


    // 리스트 표현 태그
    function getMarkup{{ $result['listName'] }}()
    {
        var markup = "<tr><th class='p-0' style='border-top-width: 2px; border-color: gray;' colspan=20></th></tr>";
        
        @foreach($result['listTitle'] as $idx=>$value)
            markup += "<tr id='no_${no}' style='${line_style}' onclick='${onclick}'>";
            
            @foreach( $value as $key=>$val )
                @if( $val[1] != '100%' )
                markup+= "<td class='text-{{ $val[2] }} p-0' style='${important_color}' >{{html <?=$key?>}}</td>";
                @else
                markup+= "<td class='text-{{ $val[2] }} pl-3' style='${memo_color} ${important_color}' colspan=20>{{html <?=$key?>}}<br><br></td>";
                @endif
            @endforeach
            markup+= "</tr>";
        @endforeach
        
        //markup+= "<tr><th class='p-0' style='border-top-width: 2px; border-color: gray;' colspan=4></th></tr>";
        return markup;
    }

    function nameorderSimple(order, element)
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
</script>

<!-- Content Wrapper. Contains page content -->
<div class="col-12 text-xs p-0">

    <!-- Main content -->
    <section class="content m-0" style="border:none; box-shadow:none;">
        <div class="col-md-12">
            <form class="form-horizontal" onsubmit="getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize()); return false;" method="post" name="form_{{ $result['listName'] }}" id="form_{{ $result['listName'] }}">
            <input type="hidden" name="listName" id="listName{{ $result['listName'] }}" value="{{ $result['listName'] }}">
            <input type="hidden" name="listOrder" id="listOrder{{ $result['listName'] }}">
            <input type="hidden" name="listOrderAsc" id="listOrderAsc{{ $result['listName'] }}">
            <input type="hidden" name="isFirst" id="isFirst{{ $result['listName'] }}" value="1">
            <input type="hidden" name="mode" id="GET_LIST{{ $result['listName'] }}">
            <input type="hidden" name="nowPage" id="nowPage{{ $result['listName'] }}">
            <input type="hidden" name="searchCnt" id="searchCnt{{ $result['listName'] }}">
            <input type="hidden" name="customSearch" id="customSearch{{ $result['listName'] }}">

            {{ csrf_field() }}
                <!-- box-header searchBox -->
                <div class="col-md-12 p-0">

                    <div class="form-inline p-0" id="searchBox">

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
                                    form-control-sm mr-1 mb-1 mt-1
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
                    <table class="table table-hover table-bordered loan-info-table table-sm p-0">
                        <thead class="">
                            {{-- [ Title, width, align, colum, orderby사용여부 ] --}}
                            @if ( isset( $result['listTitle'] ) )
                                @foreach($result['listTitle'] as $idx=>$value)
                                    <tr>
                                    @foreach( $value as $key=>$val )
                                        @if( $val[1] != '100%' )
                                        <th class="text-center p-0" style="@if(isset($val[3]))cursor:pointer;@endif @if(isset($val[1])) width:{{ $val[1] }}; @endif "  @if(isset($val[4]) && $val[4]=='Y') onclick="nameorderSimple('{{$val[3]}}', this)" @endif>
                                            {!! $val[0] !!} <i class="orderIcon"></i>
                                        </th>
                                        @endif
                                    @endforeach
                                    </tr>
                                @endforeach
                            @endif
                        </thead>
                        <tbody id="listData_{{ $result['listName'] }}"></tbody>
                    </table>
                    <div id="listError_{{ $result['listName'] }}"></div> 
                </div>
                
                <div id="pageList_{{ $result['listName'] }}" class="card-footer"></div>
            </form>
        </div>
    </section>

</div>


