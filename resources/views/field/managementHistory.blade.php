<style>
    .table {
        margin-bottom: 0;
    }
    .card-body {
        padding-bottom: 0;
    }
    .table td, .table th{
        padding: 0.3rem; /* 테이블 셀의 패딩을 줄입니다 */
    }
    .form-control-sm {
        padding: .25rem .5rem; /* 작은 입력 필드의 패딩을 줄입니다 */
        font-size: .875rem; /* 작은 입력 필드의 글꼴 크기를 줄입니다 */
    }
    .card-body {
        padding: 1rem; /* 카드 바디의 패딩을 줄입니다 */
    }
    .mb-2, .mt-2 {
        margin-bottom: .5rem; /* 위아래 마진을 줄입니다 */
        margin-top: .5rem;
    }
</style>

<div class="modal fade" id="excelUploadModal">
    <div class="modal-dialog modal-sl">
      <div class="modal-content" id="excelUploadModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="container-fluid" style="padding: 0;">
    <div class="col-md-12 pl-0">
        <div class="card-header p-1" style="border-bottom:none !important;">
            <h6 class="card-title">실행내역서</h6>
        </div>
        <div class="card card-lightblue card-outline">
            <form class="form-horizontal" role="form" name="form_history" id="form_history" method="post" enctype="multipart/form-data">
                <input type="hidden" name="excelUrl" value="excelUrlmanagementHistory">
                <input type="hidden" name="excelDownCd" value="">
                <input type="hidden" name="down_div" value="">
                <input type="hidden" name="excel_down_div" value="">
                <input type="hidden" name="down_filename" value="">
                <input type="hidden" name="excelHeaders" value="">
                <input type="hidden" id="contract_info_no" name="contract_info_no" value="{{ $contract_info_no }}">
                @csrf
                <section class="content" id="loading">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mt-2 mb-2 mr-2 text-right">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="plusUnit();">
                                    내역외 추가 <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="removeUnit();">
                                    내역외 삭제 <i class="fas fa-minus"></i>
                                </button>&nbsp;&nbsp;&nbsp;

                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="this.blur();plusCode()">
                                    내역내 추가 <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="this.blur();removeCode()">
                                    내역내 삭제 <i class="fas fa-minus"></i>
                                </button>&nbsp;&nbsp;&nbsp;

                                <button type="button" class="btn btn-sm btn-info" onclick="this.blur();excelfileUpload()">
                                    내역내 엑셀업로드 <i class="fas fa-upload"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-info" onclick="this.blur();excelfileRemove()">
                                    내역내 전체삭제 <i class="fas fa-trash-alt"></i>
                                </button>&nbsp;&nbsp;&nbsp;
                                
                                <button type="button" class="btn btn-secondary btn-sm" onclick="excelDownModal('/field/managementhistoryexcel', 'form_history');">
                                    엑셀다운 <i class="fas fa-download"></i>
                                </button>&nbsp;&nbsp;&nbsp;
                                <button type="button" class="btn btn-primary btn-sm" onclick="this.blur();saveReport();">
                                    저장 <i class="fas fa-save"></i>
                                </button>
                            </div>
                        <div class="table-responsive" style="padding: 0;">
                        <table class="table table-bordered">
                            <colgroup>
                                <col width="3%"/>
                                <col width="10%"/>
                                <col width="10%"/>
                                <col width="5%"/>
                                <col width="7%"/>
                                <col width="7%"/>
                                <col width="9%"/>
                                <col width="7%"/>
                                <col width="9%"/>
                                <col width="7%"/>
                                <col width="9%"/>
                                <col width="7%"/>
                            </colgroup>
                            <thead class="thead-light">
                                <tr align="center">
                                    <th style="vertical-align: middle;" rowspan="3">코드</th>
                                    <th style="vertical-align: middle;" rowspan="3">품명</th>
                                    <th style="vertical-align: middle;" rowspan="3">규격</th>
                                    <th style="vertical-align: middle;" rowspan="3">단위</th>
                                    <th colspan="7">실행내역서</th>
                                    <th style="vertical-align: middle;" rowspan="3">비고</th>
                                </tr>
                                <tr align="center">
                                    <th rowspan="2">수량</th>
                                    <th colspan="2">재료비</th>
                                    <th colspan="2">노무비</th>
                                    <th colspan="2">합계</th>
                                </tr>
                                <tr align="center">
                                    <th>단가</th>
                                    <th>금액</th>
                                    <th>단가</th>
                                    <th>금액</th>
                                    <th>단가</th>
                                    <th>금액</th>
                                </tr>
                            </thead>
                            @php ( $sum_balance1 = $sum_extra_balance1 = $sum_sum_balance1 = 0 )
                            @php ( $sum_balance2 = $sum_extra_balance2 = $sum_sum_balance2 = 0 )
                            @php ( $scheduleCnt = $scheduleCnt2 = 0 )
                            <tbody id="tbodyCheck">
                                @foreach($v as $key => $val)
                                    @php ( $sum_balance1       += ($val->balance ?? 0) )
                                    @php ( $sum_extra_balance1 += ($val->extra_balance ?? 0) )
                                    @php ( $sum_sum_balance1   += ($val->sum_balance ?? 0) )
                                    
                                    @php ( $scheduleCnt++ )
                                    <tr>
                                        <td class="text-center">
                                            <input type="hidden" id="detail_code1_{{ $scheduleCnt }}" name="detail_code1[]" value="{{ $val->code1 ?? '' }}">
                                            <input type="hidden" id="detail_code2_{{ $scheduleCnt }}" name="detail_code2[]" value="{{ $val->code2 ?? '' }}">
                                            <input type="hidden" id="detail_code3_{{ $scheduleCnt }}" name="detail_code3[]" value="{{ $val->code3 ?? '' }}">
                                            <input type="hidden" id="detail_code4_{{ $scheduleCnt }}" name="detail_code4[]" value="{{ $val->code4 ?? '' }}">
                                            <input type="hidden" id="detail_code5_{{ $scheduleCnt }}" name="detail_code5[]" value="{{ $val->code5 ?? '' }}">
                                            <input type="hidden" id="detail_code6_{{ $scheduleCnt }}" name="detail_code6[]" value="{{ $val->code6 ?? '' }}">
                                            <input type="hidden" id="detail_code7_{{ $scheduleCnt }}" name="detail_code7[]" value="{{ $val->code7 ?? '' }}">
                                            <input type="hidden" id="detail_code8_{{ $scheduleCnt }}" name="detail_code8[]" value="{{ $val->code8 ?? '' }}">
                                            <input type="hidden" id="detail_code9_{{ $scheduleCnt }}" name="detail_code9[]" value="{{ $val->code9 ?? '' }}">
                                            <input type="hidden" id="detail_code10_{{ $scheduleCnt }}" name="detail_code10[]" value="{{ $val->code10 ?? '' }}">
                                            <input type="text" class="form-control form-control-sm text-center" name="detail_code[]" value="+" onclick="detailCodeListOpen({{$scheduleCnt}});" readonly>
                                        </td>
                                        <td class="text-center">
                                            <input type="text" class="form-control form-control-sm text-center" id="detail_name{{ $scheduleCnt }}" name="detail_name[]" value="{{ $val->name ?? '' }}">
                                        </td>
                                        <td class="text-center">
                                            <input type="text" class="form-control form-control-sm text-center" id="detail_standard{{ $scheduleCnt }}" name="detail_standard[]" value="{{ $val->standard ?? '' }}">
                                        </td>
                                        <td class="text-center">
                                            <input type="text" class="form-control form-control-sm text-center" id="detail_type{{ $scheduleCnt }}" name="detail_type[]" value="{{ $val->type ?? '' }}">
                                        </td>
                                        <td class="text-right">
                                            <input type="text" class="form-control form-control-sm text-right" id="detail_volume{{$scheduleCnt}}" name="detail_volume[]" value="{{ $val->volume ?? 0 }}" onkeyup="setInput({{$scheduleCnt}}, 'detail_volume');">
                                        </td>
                                        <td class="text-right">
                                            <input type="text" class="form-control form-control-sm text-right" id="detail_price{{$scheduleCnt}}" name="detail_price[]" value="{{ $val->price ?? 0 }}" onkeyup="setInput({{$scheduleCnt}}, 'detail_price');">
                                        </td>
                                        <input type="hidden" id="detail_balance{{ $scheduleCnt }}" name="detail_balance[]" value="{{ $val->balance ?? 0 }}">
                                        <td class="text-right" id="td_detail_balance{{ $scheduleCnt }}">
                                        </td>
                                        <td class="text-right">
                                            <input type="text" class="form-control form-control-sm text-right" id="detail_extra_price{{$scheduleCnt}}" name="detail_extra_price[]" value="{{ $val->extra_price ?? 0 }}" onkeyup="setInput({{$scheduleCnt}}, 'detail_extra_price');">
                                        </td>
                                        <input type="hidden" id="detail_extra_balance{{ $scheduleCnt }}" name="detail_extra_balance[]" value="{{ $val->extra_balance ?? 0 }}">
                                        <td class="text-right" id="td_detail_extra_balance{{ $scheduleCnt }}">
                                            {{ number_format($val->extra_balance ?? 0) }}
                                        </td>
                                        <input type="hidden" id="detail_sum_price{{ $scheduleCnt }}" name="detail_sum_price[]" value="{{ $val->sum_price ?? 0 }}">
                                        <td class="text-right" id="td_detail_sum_price{{ $scheduleCnt }}">
                                            {{ number_format($val->sum_price ?? 0) }}
                                        </td>
                                        <input type="hidden" id="detail_sum_balance{{ $scheduleCnt }}" name="detail_sum_balance[]" value="{{ $val->sum_balance ?? 0 }}">
                                        <td class="text-right" id="td_detail_sum_balance{{ $scheduleCnt }}">
                                            {{ number_format($val->sum_balance ?? 0) }}
                                        </td>
                                        <td class="text-center">
                                            <input type="text" class="form-control form-control-sm" id="detail_etc{{$scheduleCnt}}" name="detail_etc[]" value="{{ $val->etc ?? '' }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        
                            <tbody id="inputTbody">
                                <tr style="background-color: #e9ecef;">
                                    <td class="text-center" id="td_sum"></td>
                                    <td class="text-center" colspan="4">[소 계]</td>
                                    <td class="text-center"></td>
                                    <td class="text-right" id="td_tot_balance1">{{ number_format($sum_balance1) }}</td>
                                    <td class="text-center"></td>
                                    <td class="text-right" id="td_tot_extra_balance1">{{ number_format($sum_extra_balance1) }}</td>
                                    <td class="text-center"></td>
                                    <td class="text-right" id="td_tot_sum_sum_balance1">{{ number_format($sum_sum_balance1) }}</td>
                                    <td class="text-center"></td>
                                </tr>
                            </tbody>

                            <tbody id="tbodyCheck2">
                                @foreach($v2 as $key => $val)
                                    @php ( $sum_balance2       += ($val->balance ?? 0) )
                                    @php ( $sum_extra_balance2 += ($val->extra_balance ?? 0) )
                                    @php ( $sum_sum_balance2   += ($val->sum_balance ?? 0) )
                                    
                                    @php ( $scheduleCnt2++ )
                                    <tr>
                                        <td class="text-center">
                                            <input type="hidden" id="code1_{{ $scheduleCnt2 }}" name="code1[]" value="{{ $val->code1 ?? '' }}">
                                            <input type="hidden" id="code2_{{ $scheduleCnt2 }}" name="code2[]" value="{{ $val->code2 ?? '' }}">
                                            <input type="hidden" id="code3_{{ $scheduleCnt2 }}" name="code3[]" value="{{ $val->code3 ?? '' }}">
                                            <input type="hidden" id="code4_{{ $scheduleCnt2 }}" name="code4[]" value="{{ $val->code4 ?? '' }}">
                                            <input type="hidden" id="code5_{{ $scheduleCnt2 }}" name="code5[]" value="{{ $val->code5 ?? '' }}">
                                            <input type="hidden" id="code6_{{ $scheduleCnt2 }}" name="code6[]" value="{{ $val->code6 ?? '' }}">
                                            <input type="hidden" id="code7_{{ $scheduleCnt2 }}" name="code7[]" value="{{ $val->code7 ?? '' }}">
                                            <input type="hidden" id="code8_{{ $scheduleCnt2 }}" name="code8[]" value="{{ $val->code8 ?? '' }}">
                                            <input type="hidden" id="code9_{{ $scheduleCnt2 }}" name="code9[]" value="{{ $val->code9 ?? '' }}">
                                            <input type="hidden" id="code10_{{ $scheduleCnt2 }}" name="code10[]" value="{{ $val->code10 ?? '' }}">
                                            <input type="text" class="form-control form-control-sm text-center" name="code[]" value="+" onclick="codeListOpen({{$scheduleCnt2}});" readonly>
                                        </td>
                                        <td class="text-center">
                                            <input type="text" class="form-control form-control-sm text-center" id="name{{ $scheduleCnt2 }}" name="name[]" value="{{ $val->name ?? '' }}">
                                        </td>
                                        <td class="text-center">
                                            <input type="text" class="form-control form-control-sm text-center" id="standard{{ $scheduleCnt2 }}" name="standard[]" value="{{ $val->standard ?? '' }}">
                                        </td>
                                        <td class="text-center">
                                            <input type="text" class="form-control form-control-sm text-center" id="type{{ $scheduleCnt2 }}" name="type[]" value="{{ $val->type ?? '' }}">
                                        </td>
                                        <td class="text-right">
                                            <input type="text" class="form-control form-control-sm text-right" id="volume{{$scheduleCnt2}}" name="volume[]" value="{{ $val->volume ?? 0 }}" onkeyup="setInput({{$scheduleCnt2}}, 'volume');">
                                        </td>
                                        <input type="hidden" id="price{{ $scheduleCnt2 }}" name="price[]" value="{{ $val->price ?? 0 }}">
                                        <td class="text-right" id="td_price{{ $scheduleCnt2 }}">
                                            {{ number_format($val->price ?? 0) }}
                                        </td>
                                        <input type="hidden" id="balance{{ $scheduleCnt2 }}" name="balance[]" value="{{ $val->balance ?? 0 }}">
                                        <td class="text-right" id="td_balance{{ $scheduleCnt2 }}">
                                            {{ number_format($val->balance ?? 0) }}
                                        </td>
                                        <td class="text-right">
                                            <input type="text" class="form-control form-control-sm text-right" id="extra_price{{$scheduleCnt2}}" name="extra_price[]" value="{{ $val->extra_price ?? 0 }}" onkeyup="setInput({{$scheduleCnt2}}, 'extra_price');">
                                        </td>
                                        <input type="hidden" id="extra_balance{{ $scheduleCnt2 }}" name="extra_balance[]" value="{{ $val->balance ?? 0 }}">
                                        <td class="text-right" id="td_extra_balance{{ $scheduleCnt2 }}">
                                            {{ number_format($val->balance ?? 0) }}
                                        </td>
                                        <input type="hidden" id="sum_price{{ $scheduleCnt2 }}" name="sum_price[]" value="{{ $val->sum_price ?? 0 }}">
                                        <td class="text-right" id="td_sum_price{{ $scheduleCnt2 }}">
                                            {{ number_format($val->sum_price ?? 0) }}
                                        </td>
                                        <input type="hidden" id="sum_balance{{ $scheduleCnt2 }}" name="sum_balance[]" value="{{ $val->sum_balance ?? 0 }}">
                                        <td class="text-right" id="td_sum_balance{{ $scheduleCnt2 }}">
                                            {{ number_format($val->sum_balance ?? 0) }}
                                        </td>
                                        <td class="text-center">
                                            <input type="text" class="form-control form-control-sm" id="etc{{$scheduleCnt2}}" name="etc[]" value="{{ $val->etc ?? '' }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tbody id="inputTbody2">
                                <tr style="background-color: #e9ecef;">
                                    <td class="text-center" id="td_sum"></td>
                                    <td class="text-center" colspan="4">[소 계]</td>
                                    <td class="text-center"></td>
                                    <td class="text-right" id="td_tot_balance2">{{ number_format($sum_balance2) }}</td>
                                    <td class="text-center"></td>
                                    <td class="text-right" id="td_tot_extra_balance2">{{ number_format($sum_extra_balance2) }}</td>
                                    <td class="text-center"></td>
                                    <td class="text-right" id="td_tot_sum_sum_balance2">{{ number_format($sum_sum_balance2) }}</td>
                                    <td class="text-center"></td>
                                </tr>
                            </tbody>
                            <tbody id="inputTbody3">
                                <tr style="background-color: #e9ecef;">
                                    <td class="text-center" id="td_sum"></td>
                                    <td class="text-center" colspan="4">[합 계]</td>
                                    <td class="text-center"></td>
                                    <td class="text-right" id="td_tot_balance3">{{ number_format($sum_balance1 + $sum_balance2) }}</td>
                                    <td class="text-center"></td>
                                    <td class="text-right" id="td_tot_extra_balance3">{{ number_format($sum_extra_balance1 + $sum_extra_balance2) }}</td>
                                    <td class="text-center"></td>
                                    <td class="text-right" id="td_tot_sum_sum_balance3">{{ number_format($sum_sum_balance1 + $sum_sum_balance2) }}</td>
                                    <td class="text-center"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 내역 내 검색 모달 -->
                    <div class="modal fade" id="modalC" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index:1000000;">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myModalLabel">내역 내 일위대가 코드</h4>
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
                                                    <input type="text" class="form-control" id="cost_search_string" placeholder="코드를 입력하세요">
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
                                                            <th>CODE</th>
                                                            <th>품명</th>
                                                            <th>규격(1)</th>
                                                            <th>규격(2)</th>
                                                            <th>단위</th>
                                                            <th>비고</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="list">
                                                        @if(isset($cost))
                                                            @foreach($cost as $key => $val)
                                                            <tr onclick="costListCheck();" style="cursor:pointer;">
                                                                <td>{{ $val->code ?? '' }}</td>                                          <!-- 1 -->
                                                                <td>{{ $val->name ?? '' }}</td>                                          <!-- 2 -->
                                                                <td>{{ $val->standard1 ?? '' }}</td>                                     <!-- 3 -->
                                                                <td>{{ $val->standard2 ?? '' }}</td>                                     <!-- 4 -->
                                                                <td>{{ $val->type ?? '' }}</td>                                          <!-- 5 -->
                                                                <td>{{ $val->etc ?? '' }}</td>                                           <!-- 6 -->
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

                    <!-- 내역 외 검색 모달 -->
                    <div class="modal fade" id="modalD" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index:1000000;">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myModalLabel">내역 외 일위대가 코드</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="searchForm">
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <div class="input-group">
                                                    <input type="hidden" name="detailCurrentPage" value="1"/>
                                                    <input type="hidden" name="detail_search_no" id="detail_search_no"/>
                                                    <input type="text" class="form-control" id="detail_cost_search_string" placeholder="코드를 입력하세요">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="detailSearch();">검색</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group" style="padding-top:50px;">
                                            <div class="col-sm-12">
                                                <table class="table table-hover table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>CODE</th>
                                                            <th>품명</th>
                                                            <th>규격(1)</th>
                                                            <th>규격(2)</th>
                                                            <th>단위</th>
                                                            <th>비고</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="detailList">
                                                        @if(isset($cost))
                                                            @foreach($cost as $key => $val)
                                                            <tr onclick="detailCostListCheck();" style="cursor:pointer;">
                                                                <td>{{ $val->code ?? '' }}</td>                                          <!-- 1 -->
                                                                <td>{{ $val->name ?? '' }}</td>                                          <!-- 2 -->
                                                                <td>{{ $val->standard1 ?? '' }}</td>                                     <!-- 3 -->
                                                                <td>{{ $val->standard2 ?? '' }}</td>                                     <!-- 4 -->
                                                                <td>{{ $val->type ?? '' }}</td>                                          <!-- 5 -->
                                                                <td>{{ $val->etc ?? '' }}</td>                                           <!-- 6 -->
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
                                            <div id="pageApi2" style="text-align:center"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 내역 외 코드 리스트 -->
                    <div class="modal fade" id="modalA" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content" id="modalContents">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myModalLabel">내역 외 일위대가 코드 리스트</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div id="modalBody">
                                    <div class="modal-body">
                                        <div class="row">     
                                            <input type="hidden" id="cost_detail_list_no" value="0"/>
                                            <div class="col-md-12">
                                                <table class="table table-bordered" style="table-layout: fixed; width: 100%;">
                                                    <colgroup>
                                                        <col width="12%">
                                                        <col width="24%">
                                                        <col width="14%">
                                                        <col width="14%">
                                                        <col width="8%">
                                                        <col width="12%">
                                                        <col width="16%">
                                                    </colgroup>
                                                    <thead style="background-color: #e9ecef;">
                                                        <tr align='center'>
                                                            <td>코드</td>
                                                            <td>품명</td>
                                                            <td>규격1</td>
                                                            <td>규격2</td>
                                                            <td>단위</td>
                                                            <td>단가</td>
                                                            <td>기타</td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @for($i=1; $i<=10; $i++)
                                                        <tr>
                                                            <td class="text-center">
                                                                <input type="text" class="form-control form-control-sm text-center" style="width: 80px;" id="detail_cost_code_{{ $i }}" onclick="detailCodeSearch({{ $i }});" readonly>                                                            </td>
                                                            <td id="detail_cost_name_{{ $i }}"></td>
                                                            <td id="detail_cost_standard1_{{ $i }}"></td>
                                                            <td id="detail_cost_standard2_{{ $i }}"></td>
                                                            <td id="detail_cost_type_{{ $i }}"></td>
                                                            <td id="detail_cost_price_{{ $i }}"></td>
                                                            <td id="detail_cost_etc_{{ $i }}"></td>
                                                        </tr>
                                                        @endfor
                                                        <tr style="background-color: #e9ecef;">
                                                            <td class="text-center" colspan="5">[합 계]</td>
                                                            <input type="hidden" id="detail_cost_sum_price">
                                                            <td class="text-right" id="td_detail_cost_sum_price"></td>
                                                            <td class="text-center"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary btn-sm float-right" id="detail_register_code" onclick="detailRegisterCode()">등록</button>
                                            <button type="button" class="btn btn-default btn-sm float-left" data-dismiss="modal">닫기</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 내역 내 코드 리스트 -->
                    <div class="modal fade" id="modalP" style="padding-left:17px;">
                        <div class="modal-dialog modal-lg" id="modal-info" style="margin-top:50px; width: 1100px;">
                            <div class="modal-content" id="modalContents">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myModalLabel">내역 내 일위대가 코드 리스트</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div id="modalBody">
                                    <div class="modal-body">
                                        <div class="row">
                                            <input type="hidden" id="cost_list_no" value="0"/>
                                            <div class="col-md-12">
                                                <table class="table table-bordered" style="table-layout: fixed; width: 100%;">
                                                    <colgroup>
                                                        <col width="12%">
                                                        <col width="24%">
                                                        <col width="14%">
                                                        <col width="14%">
                                                        <col width="8%">
                                                        <col width="12%">
                                                        <col width="16%">
                                                    </colgroup>
                                                    <thead style="background-color: #e9ecef;">
                                                        <tr align='center'>
                                                            <td>코드</td>
                                                            <td>품명</td>
                                                            <td>규격1</td>
                                                            <td>규격2</td>
                                                            <td>단위</td>
                                                            <td>단가</td>
                                                            <td>기타</td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @for($i=1; $i<=10; $i++)
                                                        <tr>
                                                            <td class="text-center">
                                                                <input type="text" class="form-control form-control-sm text-center" style="width: 80px;" id="cost_code_{{ $i }}" onclick="codeSearch({{ $i }});" readonly>
                                                            </td>
                                                            <td id="cost_name_{{ $i }}"></td>
                                                            <td id="cost_standard1_{{ $i }}"></td>
                                                            <td id="cost_standard2_{{ $i }}"></td>
                                                            <td id="cost_type_{{ $i }}"></td>
                                                            <td id="cost_price_{{ $i }}"></td>
                                                            <td id="cost_etc_{{ $i }}"></td>
                                                        </tr>
                                                        @endfor
                                                        <tr style="background-color: #e9ecef;">
                                                            <td class="text-center" colspan="5">[합 계]</td>
                                                            <input type="hidden" id="cost_sum_price">
                                                        <td class="text-right" id="td_cost_sum_price"></td>
                                                            <td class="text-center"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary btn-sm" id="nodeForceBtn" onclick="RegisterCode()">저장</button>
                                            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">닫기</button>
                                        </div>
                                    </div>
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
                                    <button type="button" class="btn btn-sm btn-primary" onclick="excelDown('form_history');">다운로드</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form> 
            </div>
        </div>
    </div>
    <div>
        자동저장시간 : <span id="auto_save"></span>
    </div> 
</div>

<script>

var scheduleCnt = {{ $scheduleCnt ?? 0 }};
var scheduleCnt2 = {{ $scheduleCnt2 ?? 0 }};

setInput(0);
pageMake(0);
detailPageMake(0);

// 5분에 한번씩 자동저장
setInterval(function()
{
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#form_history').serialize();

    $.ajax({
        url  : "/field/managementhistoryaction",
        type : "post",
        data : postdata,
        success : function(result)
        {
            if(result["rs_code"] == "Y")
            {
                $('#auto_save').html(result["save_time"].toLocaleString());
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
} ,300000);

function setInput(cnt, type='')
{    
    if(cnt > 0 && type)
    {
        var inputBox = document.getElementById(type+cnt);
        var cursorPosition = inputBox.selectionStart;
    }

    // 내역외
    // 내역외 수량 x 재료비단가 = 금액
    var get_targetDetailVolume = $("#tbodyCheck input[name^='detail_volume[]']");

    // 내역외 수량 x 재료비단가 = 금액
    $.each(get_targetDetailVolume, function (index, value)
    {
        var detail_volume_value = $('#detail_volume' + (index + 1)).val().replace(/,/gi, "");
        var detail_volume_data  = detail_volume_value.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        $('#detail_volume' + (index + 1)).val(detail_volume_data);

        var detail_price_value = $('#detail_price' + (index + 1)).val().replace(/,/gi, "");
        $('#detail_price' + (index + 1)).val(parseInt(detail_price_value).toLocaleString());
        
        var detail_balance_value = (parseInt(detail_volume_value) || 0) * (parseInt(detail_price_value) || 0);
        $('#detail_balance' + (index + 1)).val(detail_balance_value.toLocaleString());
        $('#td_detail_balance' + (index + 1)).html(detail_balance_value.toLocaleString());
    });

    // 내역외 수량 x 노무비단가 = 금액
    $.each(get_targetDetailVolume, function (index, value)
    {
        var detail_volume_value = $('#detail_volume' + (index + 1)).val().replace(/,/gi, "");
        var detail_volume_data  = detail_volume_value.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        $('#detail_volume' + (index + 1)).val(detail_volume_data);

        var detail_price_value = $('#detail_extra_price' + (index + 1)).val().replace(/,/gi, "");
        $('#detail_extra_price' + (index + 1)).val(parseInt(detail_price_value).toLocaleString());

        var detail_balance_value = (parseInt(detail_volume_value) || 0) * (parseInt(detail_price_value) || 0);
        $('#detail_extra_balance' + (index + 1)).val(detail_balance_value.toLocaleString());
        $('#td_detail_extra_balance' + (index + 1)).html(detail_balance_value.toLocaleString());
    });

    // 내역외 재료비 단가 + 노무비 단가
    $.each(get_targetDetailVolume, function (index, value)
    {
        var detail_price_value = $('#detail_price' + (index + 1)).val().replace(/,/gi, "");
        $('#detail_price' + (index + 1)).val(parseInt(detail_price_value).toLocaleString());

        var detail_extra_price_value = $('#detail_extra_price' + (index + 1)).val().replace(/,/gi, "");
        $('#detail_extra_price' + (index + 1)).val(parseInt(detail_extra_price_value).toLocaleString());

        var detail_sum_price_value = (parseInt(detail_price_value) || 0) + (parseInt(detail_extra_price_value) || 0);
        $('#detail_sum_price' + (index + 1)).val(detail_sum_price_value.toLocaleString());
        $('#td_detail_sum_price' + (index + 1)).html(detail_sum_price_value.toLocaleString());
    });

    // 내역외 재료비 금액 + 노무비 금액
    $.each(get_targetDetailVolume, function (index, value)
    {
        var detail_balance_value = $('#detail_balance' + (index + 1)).val().replace(/,/gi, "");
        $('#detail_balance' + (index + 1)).val(parseInt(detail_balance_value).toLocaleString());

        var detail_extra_balance_value = $('#detail_extra_balance' + (index + 1)).val().replace(/,/gi, "");
        $('#detail_extra_balance' + (index + 1)).val(parseInt(detail_extra_balance_value).toLocaleString());

        var detail_sum_balance_value = (parseInt(detail_balance_value) || 0) + (parseInt(detail_extra_balance_value) || 0);
        $('#detail_sum_balance' + (index + 1)).val(detail_sum_balance_value.toLocaleString());
        $('#td_detail_sum_balance' + (index + 1)).html(detail_sum_balance_value.toLocaleString());
    });

    // 내역내
    var volume_value = ($('#volume').val() || "").replace(/,/gi, "");
    var get_targetVolume = $("#tbodyCheck2 input[name^='volume[]']");

    // 내역내 수량 x 재료비단가 = 금액
    $.each(get_targetVolume, function (index, value)
    {
        var volume_value = $('#volume' + (index + 1)).val().replace(/,/gi, "");
        var volume_data  = volume_value.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        $('#volume' + (index + 1)).val(volume_data);

        var price_value = $('#price' + (index + 1)).val().replace(/,/gi, "");
        $('#price' + (index + 1)).val(parseInt(price_value).toLocaleString());

        var balance_value = (parseInt(volume_value) || 0) * (parseInt(price_value) || 0);
        $('#balance' + (index + 1)).val(balance_value.toLocaleString());
        $('#td_balance' + (index + 1)).html(balance_value.toLocaleString());
    });

    // 내역내 수량 x 노무비단가 = 금액
    $.each(get_targetVolume, function (index, value)
    {
        var volume_value = $('#volume' + (index + 1)).val().replace(/,/gi, "");
        var volume_data  = volume_value.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        $('#volume' + (index + 1)).val(volume_data);

        var price_value = $('#extra_price' + (index + 1)).val().replace(/,/gi, "");
        $('#extra_price' + (index + 1)).val(parseInt(price_value).toLocaleString());

        var balance_value = (parseInt(volume_value) || 0) * (parseInt(price_value) || 0);
        $('#extra_balance' + (index + 1)).val(balance_value.toLocaleString());
        $('#td_extra_balance' + (index + 1)).html(balance_value.toLocaleString());
    });

    // 내역내 재료비 단가 + 노무비 단가
    $.each(get_targetVolume, function (index, value)
    {
        var price_value = $('#price' + (index + 1)).val().replace(/,/gi, "");
        $('#price' + (index + 1)).val(parseInt(price_value).toLocaleString());

        var extra_price_value = $('#extra_price' + (index + 1)).val().replace(/,/gi, "");
        $('#extra_price' + (index + 1)).val(parseInt(extra_price_value).toLocaleString());

        var sum_price_value = (parseInt(price_value) || 0) + (parseInt(extra_price_value) || 0);
        $('#sum_price' + (index + 1)).val(sum_price_value.toLocaleString());
        $('#td_sum_price' + (index + 1)).html(sum_price_value.toLocaleString());
    });

    // 내역내 재료비 금액 + 노무비 금액
    $.each(get_targetVolume, function (index, value)
    {
        var balance_value = $('#balance' + (index + 1)).val().replace(/,/gi, "");
        $('#balance' + (index + 1)).val(parseInt(balance_value).toLocaleString());

        var extra_balance_value = $('#extra_balance' + (index + 1)).val().replace(/,/gi, "");
        $('#extra_balance' + (index + 1)).val(parseInt(extra_balance_value).toLocaleString());

        var sum_balance_value = (parseInt(balance_value) || 0) + (parseInt(extra_balance_value) || 0);
        $('#sum_balance' + (index + 1)).val(sum_balance_value.toLocaleString());
        $('#td_sum_balance' + (index + 1)).html(sum_balance_value.toLocaleString());
    });

    if(cnt > 0 && type)
    {
        inputBox.setSelectionRange(cursorPosition, cursorPosition);
    }

    // 변수 초기화
    var detail_cal_balance       = 0;
    var cal_balance              = 0;
    var total_cal_balance        = 0; 
    var detail_cal_extra_balance = 0;
    var cal_extra_balance        = 0;
    var total_cal_extra_balance  = 0;
    var detail_cal_sum_balance   = 0;
    var cal_sum_balance          = 0;
    var total_cal_sum_balance    = 0;

    // 재료비
    // 내역 외 재료비 금액
    get_targetDetailMoney = $("#tbodyCheck input[name^='detail_balance[]']");
    $.each(get_targetDetailMoney, function (index, value) {
        detail_cal_balance += parseInt($(value).val().replace(/,/gi, "")) || 0;
    });

    // 내역 내 재료비 금액
    get_targetMoney = $("#tbodyCheck2 input[name^='balance[]']");
    $.each(get_targetMoney, function (index, value) {
        cal_balance += parseInt($(value).val().replace(/,/gi, "")) || 0;
    });

    // 노무비
    // 내역 외 노무비 금액
    get_targetDetailExtraMoney = $("#tbodyCheck input[name^='detail_extra_balance[]']");
    $.each(get_targetDetailExtraMoney, function (index, value) {
        detail_cal_extra_balance += parseInt($(value).val().replace(/,/gi, "")) || 0;
    });

    // 내역 내 노무비 금액
    get_targetExtraMoney = $("#tbodyCheck2 input[name^='extra_balance[]']");
    $.each(get_targetExtraMoney, function (index, value) {
        cal_extra_balance += parseInt($(value).val().replace(/,/gi, "")) || 0;
    });

    // 합계
    // 내역 외 합계 금액
    get_targetDetailSumMoney = $("#tbodyCheck input[name^='detail_sum_balance[]']");
    $.each(get_targetDetailSumMoney, function (index, value) {
        detail_cal_sum_balance += parseInt($(value).val().replace(/,/gi, "")) || 0;
    });

    // 내역 내 합계 금액
    get_targetSumMoney = $("#tbodyCheck2 input[name^='sum_balance[]']");
    $.each(get_targetSumMoney, function (index, value) {
        cal_sum_balance += parseInt($(value).val().replace(/,/gi, "")) || 0;
    });

    total_cal_balance = detail_cal_balance + cal_balance;
    total_cal_extra_balance = detail_cal_extra_balance + cal_extra_balance;
    total_cal_sum_balance = detail_cal_sum_balance + cal_sum_balance;

    // 재료비 금액 합계 
    $('#td_tot_balance1').html(detail_cal_balance.toLocaleString());                // 내역외 재료비 금액 합
    $('#td_tot_balance2').html(cal_balance.toLocaleString());                       // 내역내 재료비 금액 합
    $('#td_tot_balance3').html(total_cal_balance.toLocaleString());                 // 재료비 금액 합계

    // 노무비 금액 합계
    $('#td_tot_extra_balance1').html(detail_cal_extra_balance.toLocaleString());    // 내역외 노무비 금액 합
    $('#td_tot_extra_balance2').html(cal_extra_balance.toLocaleString());           // 내역내 노무비 금액 합
    $('#td_tot_extra_balance3').html(total_cal_extra_balance.toLocaleString());     // 노무비 금액 합계

    // 합계 금액 합계
    $('#td_tot_sum_sum_balance1').html(detail_cal_sum_balance.toLocaleString());     // 내역외 합계 금액 합
    $('#td_tot_sum_sum_balance2').html(cal_sum_balance.toLocaleString());            // 내역내 합계 금액 합
    $('#td_tot_sum_sum_balance3').html(total_cal_sum_balance.toLocaleString());      // 합계 금액 합계
}

var total_val = 0;
var total_key = 0;
var total_num = 0;

function commaInput(num)
{
	num = String(num);
    var parts = num.toString().split("."); 
	parts[0] = parts[0].replace(/,/g, "");
	parts[0] = parts[0].replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
    var number = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + (parts[1] ? "." + parts[1] : "");
	return number;
}

// 일위대가 산출 추가
function plusUnit()
{
    scheduleCnt++;

    let tr = '<tr>';
        tr+= '<td class="text-center">';
        tr+= '<input type="hidden" id="detail_code1_'+scheduleCnt+'" name="detail_code1[]" value="">';
        tr+= '<input type="hidden" id="detail_code2_'+scheduleCnt+'" name="detail_code2[]" value="">';
        tr+= '<input type="hidden" id="detail_code3_'+scheduleCnt+'" name="detail_code3[]" value="">';
        tr+= '<input type="hidden" id="detail_code4_'+scheduleCnt+'" name="detail_code4[]" value="">';
        tr+= '<input type="hidden" id="detail_code5_'+scheduleCnt+'" name="detail_code5[]" value="">';
        tr+= '<input type="hidden" id="detail_code6_'+scheduleCnt+'" name="detail_code6[]" value="">';
        tr+= '<input type="hidden" id="detail_code7_'+scheduleCnt+'" name="detail_code7[]" value="">';
        tr+= '<input type="hidden" id="detail_code8_'+scheduleCnt+'" name="detail_code8[]" value="">';
        tr+= '<input type="hidden" id="detail_code9_'+scheduleCnt+'" name="detail_code9[]" value="">';
        tr+= '<input type="hidden" id="detail_code10_'+scheduleCnt+'" name="detail_code10[]" value="">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" name="detail_code[]" value="+" onclick="detailCodeListOpen('+scheduleCnt+');" readonly>';
        tr+= '</td>';
        tr+= '<td class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id=detail_name"'+scheduleCnt+'" name="detail_name[]" value="">';
        tr+= '</td>';
        tr+= '<td class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id=detail_standard"'+scheduleCnt+'" name="detail_standard[]" value="">';
        tr+= '</td>';
        tr+= '<td class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id=detail_type"'+scheduleCnt+'" name="detail_type[]" value="">';
        tr+= '</td>';
        tr+= '<td class="text-right">';
        tr+= '<input type="text" class="form-control form-control-sm text-right" id="detail_volume'+scheduleCnt+'" name="detail_volume[]" value="0" onkeyup="setInput('+scheduleCnt+', \'detail_volume\');">';
        tr+= '</td>';
        tr+= '<td class="text-right">';
        tr+= '<input type="text" class="form-control form-control-sm text-right" id="detail_price'+scheduleCnt+'" name="detail_price[]" value="0" onkeyup="setInput('+scheduleCnt+', \'detail_price\');">';
        tr+= '</td>';
        tr+= '<input type="hidden" id="detail_balance'+scheduleCnt+'" name="detail_balance[]" value="0">';
        tr+= '<td class="text-right" id="td_detail_balance'+scheduleCnt+'">';
        tr+= '</td>';
        tr+= '<td class="text-right">';
        tr+= '<input type="text" class="form-control form-control-sm text-right" id="detail_extra_price'+scheduleCnt+'" name="detail_extra_price[]" value="0" onkeyup="setInput('+scheduleCnt+', \'detail_extra_price\');">';
        tr+= '</td>';
        tr+= '<input type="hidden" id="detail_extra_balance'+scheduleCnt+'" name="detail_extra_balance[]" value="">';
        tr+= '<td class="text-right" id="td_detail_extra_balance'+scheduleCnt+'">';
        tr+= '</td>';
        tr+= '<input type="hidden" id="detail_sum_price'+scheduleCnt+'" name="detail_sum_price[]" value="">';
        tr+= '<td class="text-right" id="td_detail_sum_price'+scheduleCnt+'">';
        tr+= '</td>';
        tr+= '<input type="hidden" id="detail_sum_balance'+scheduleCnt+'" name="detail_sum_balance[]" value="">';
        tr+= '<td class="text-right" id="td_detail_sum_balance'+scheduleCnt+'">';
        tr+= '</td>';
        tr+= '<td class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="detail_etc'+scheduleCnt+'" name="detail_etc[]" value="">';
        tr+= '</td>';
        tr+= '</td>';
        tr+= '</tr>';
    $('#tbodyCheck').append(tr);
    
    setInput(0);
}

// 일위대가 산출 제거
function removeUnit()
{
    scheduleCnt--;

    $('#tbodyCheck > tr:last').remove();
    
    setInput(0);
}

// 내역서 산출 추가
function plusCode()
{
    scheduleCnt2++;

    let tr = '<tr>';
        tr+= '<td style="border-color:#00000" class="text-center">';
        tr+= '<input type="hidden" id="code1_'+scheduleCnt2+'" name="code1[]" value="{{ $val->code1 ?? '' }}">';
        tr+= '<input type="hidden" id="code2_'+scheduleCnt2+'" name="code2[]" value="{{ $val->code2 ?? '' }}">';
        tr+= '<input type="hidden" id="code3_'+scheduleCnt2+'" name="code3[]" value="{{ $val->code3 ?? '' }}">';
        tr+= '<input type="hidden" id="code4_'+scheduleCnt2+'" name="code4[]" value="{{ $val->code4 ?? '' }}">';
        tr+= '<input type="hidden" id="code5_'+scheduleCnt2+'" name="code5[]" value="{{ $val->code5 ?? '' }}">';
        tr+= '<input type="hidden" id="code6_'+scheduleCnt2+'" name="code6[]" value="{{ $val->code6 ?? '' }}">';
        tr+= '<input type="hidden" id="code7_'+scheduleCnt2+'" name="code7[]" value="{{ $val->code7 ?? '' }}">';
        tr+= '<input type="hidden" id="code8_'+scheduleCnt2+'" name="code8[]" value="{{ $val->code8 ?? '' }}">';
        tr+= '<input type="hidden" id="code9_'+scheduleCnt2+'" name="code9[]" value="{{ $val->code9 ?? '' }}">';
        tr+= '<input type="hidden" id="code10_'+scheduleCnt2+'" name="code10[]" value="{{ $val->code10 ?? '' }}">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="code'+scheduleCnt2+'" name="code[]" value="+" onclick="codeListOpen('+scheduleCnt2+');" readonly>';
        tr+= '</td>';
        tr+= '<td style="border-color:#00000" class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="name'+scheduleCnt2+'" name="name[]" value="">';
        tr+= '</td>';
        tr+= '<td style="border-color:#00000" class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="standard'+scheduleCnt2+'" name="standard[]" value="">';
        tr+= '</td>';
        tr+= '<td style="border-color:#00000" class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="type'+scheduleCnt2+'" name="type[]" value="">';
        tr+= '</td>';
        tr+= '<td style="border-color:#00000" class="text-right">';
        tr+= '<input type="text" class="form-control form-control-sm text-right" id="volume'+scheduleCnt2+'" name="volume[]" value="0" onkeyup="setInput('+scheduleCnt2+', \'volume\');">';
        tr+= '</td>';
        tr+= '<input type="hidden" id="price'+scheduleCnt2+'" name="price[]" value="0">';
        tr+= '<td style="border-color:#00000" class="text-right" id="td_price'+scheduleCnt2+'">';
        tr+= '</td>';
        tr+= '<input type="hidden" id="balance'+scheduleCnt2+'" name="balance[]" value="">';
        tr+= '<td style="border-color:#00000" class="text-right" id="td_balance'+scheduleCnt2+'">';
        tr+= '</td>';
        tr+= '<td style="border-color:#00000" class="text-right">';
        tr+= '<input type="text" class="form-control form-control-sm text-right" id="extra_price'+scheduleCnt2+'" name="extra_price[]" value="0" onkeyup="setInput('+scheduleCnt2+', \'extra_price\');">';
        tr+= '</td>';
        tr+= '<input type="hidden" id="extra_balance'+scheduleCnt2+'" name="extra_balance[]" value="">';
        tr+= '<td style="border-color:#00000" class="text-right" id="td_extra_balance'+scheduleCnt2+'">';
        tr+= '</td>';
        tr+= '<input type="hidden" id="sum_price'+scheduleCnt2+'" name="sum_price[]" value="">';
        tr+= '<td style="border-color:#00000" class="text-right" id="td_sum_price'+scheduleCnt2+'">';
        tr+= '</td>';
        tr+= '<input type="hidden" id="sum_balance'+scheduleCnt2+'" name="sum_balance[]" value="">';
        tr+= '<td style="border-color:#00000" class="text-right" id="td_sum_balance'+scheduleCnt2+'">';
        tr+= '</td>';
        tr+= '<td style="border-color:#00000" class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="etc'+scheduleCnt2+'" name="etc[]" value="">';
        tr+= '</td>';
        tr+= '</td>';
        tr+= '</tr>';
    $('#tbodyCheck2').append(tr);
    
    setInput(0);
}

// 내역서 산출 제거
function removeCode()
{
    scheduleCnt2--;

    $('#tbodyCheck2 > tr:last').remove();
    
    setInput(0);
}

// 내역서 내역 외 코드계산기 생성
function detailCodeListOpen(costListNo)
{
    // 초기화
    for(var i=1; i<=10; i++)
    {
        document.getElementById('detail_cost_code_'+i).value = '';

        $('#detail_cost_name_'+i).html('');
        $('#detail_cost_standard1_'+i).html('');
        $('#detail_cost_standard2_'+i).html('');
        $('#detail_cost_type_'+i).html('');
        $('#detail_cost_price_'+i).html('');
        $('#detail_cost_etc_'+i).html('');
    }
    $('#detail_cost_sum_price').val(0).number(true);
    $('#td_detail_cost_sum_price').html(0);

    var contract_info_no = document.getElementById('contract_info_no').value;
    var detail_code1 = document.getElementById('detail_code1_'+costListNo).value ?? '';
    var detail_code2 = document.getElementById('detail_code2_'+costListNo).value ?? '';
    var detail_code3 = document.getElementById('detail_code3_'+costListNo).value ?? '';
    var detail_code4 = document.getElementById('detail_code4_'+costListNo).value ?? '';
    var detail_code5 = document.getElementById('detail_code5_'+costListNo).value ?? '';
    var detail_code6 = document.getElementById('detail_code6_'+costListNo).value ?? '';
    var detail_code7 = document.getElementById('detail_code7_'+costListNo).value ?? '';
    var detail_code8 = document.getElementById('detail_code8_'+costListNo).value ?? '';
    var detail_code9 = document.getElementById('detail_code9_'+costListNo).value ?? '';
    var detail_code10 = document.getElementById('detail_code10_'+costListNo).value ?? '';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.post('/field/managementhistorylist', { code1:detail_code1, code2:detail_code2, code3:detail_code3, code4:detail_code4, code5:detail_code5, code6:detail_code6, code7:detail_code7, code8:detail_code8, code9:detail_code9, code10:detail_code10, contract_info_no:contract_info_no }, function(data)
    {
        for(var i=1; i<=10; i++)
        {
            if(document.getElementById('detail_code'+i+'_'+costListNo).value)
            {
                document.getElementById('detail_cost_code_'+i).value = data['code'+i] ?? '';

                $('#detail_cost_name_'+i).html(data['name'+i] ?? '');
                $('#detail_cost_standard1_'+i).html(data['standard1'+i] ?? '');
                $('#detail_cost_standard2_'+i).html(data['standard2'+i] ?? '');
                $('#detail_cost_type_'+i).html(data['type'+i] ?? '');
                $('#detail_cost_price_'+i).html((data['price'+i] ?? 0).toLocaleString());
                $('#detail_cost_etc_'+i).html(data['etc'+i] ?? '');
            }
        }

        $('#detail_cost_sum_price').val(data['sum_price']).number(true);
        $('#td_detail_cost_sum_price').html(data['sum_price'].toLocaleString());
    });

    document.getElementById('cost_detail_list_no').value = costListNo;

	$('#modalA').modal();
}

// 내역서 내역 내 코드계산기 생성
function codeListOpen(costListNo)
{
    // 초기화
    for(var i=1; i<=10; i++)
    {
        document.getElementById('cost_code_'+i).value = '';

        $('#cost_name_'+i).html('');
        $('#cost_standard1_'+i).html('');
        $('#cost_standard2_'+i).html('');
        $('#cost_type_'+i).html('');
        $('#cost_price_'+i).html('');
        $('#cost_etc_'+i).html('');
    }
    $('#cost_sum_price').val(0).number(true);
    $('#td_cost_sum_price').html(0);

    var contract_info_no = document.getElementById('contract_info_no').value;

    var code1  = document.getElementById('code1_'+costListNo).value ?? '';
    var code2  = document.getElementById('code2_'+costListNo).value ?? '';
    var code3  = document.getElementById('code3_'+costListNo).value ?? '';
    var code4  = document.getElementById('code4_'+costListNo).value ?? '';
    var code5  = document.getElementById('code5_'+costListNo).value ?? '';
    var code6  = document.getElementById('code6_'+costListNo).value ?? '';
    var code7  = document.getElementById('code7_'+costListNo).value ?? '';
    var code8  = document.getElementById('code8_'+costListNo).value ?? '';
    var code9  = document.getElementById('code9_'+costListNo).value ?? '';
    var code10 = document.getElementById('code10_'+costListNo).value ?? '';
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.post('/field/managementhistorylist', { code1:code1, code2:code2, code3:code3, code4:code4, code5:code5, code6:code6, code7:code7, code8:code8, code9:code9, code10:code10, contract_info_no:contract_info_no }, function(data)
    {
        for(var i=1; i<=10; i++)
        {
            if(document.getElementById('code'+i+'_'+costListNo).value)
            {
                document.getElementById('cost_code_'+i).value = data['code'+i] ?? '';

                $('#cost_name_'+i).html(data['name'+i] ?? '');
                $('#cost_standard1_'+i).html(data['standard1'+i] ?? '');
                $('#cost_standard2_'+i).html(data['standard2'+i] ?? '');
                $('#cost_type_'+i).html(data['type'+i] ?? '');
                $('#cost_price_'+i).html((data['price'+i] ?? 0).toLocaleString());
                $('#cost_etc_'+i).html(data['etc'+i] ?? '');
            }
        }

        $('#cost_sum_price').val(data['sum_price']).number(true);
        $('#td_cost_sum_price').html(data['sum_price'].toLocaleString());
    });    

    document.getElementById('cost_list_no').value = costListNo;

	$('#modalP').modal();
}

// 내역 내 일위대가 코드 검색
function codeSearch(index)
{
    document.getElementById('search_no').value = index;
    document.getElementById('cost_search_string').value = '';
    $('#list').html('');
    $('#modalC').modal('show');
}

// 내역 외 일위대가 코드 검색
function detailCodeSearch(index)
{
    document.getElementById('detail_search_no').value = index;
    document.getElementById('detail_cost_search_string').value = '';
    $('#detailList').html('');
    $('#modalD').modal('show');
}

// 저장
function saveReport()
{
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#form_history').serialize();

    $.ajax({
        url  : "/field/managementhistoryaction",
        type : "post",
        data : postdata,
        success : function(result)
        {
            if(result["rs_code"] == "Y")
            {
                alert("정상적으로 처리되었습니다.");
                getManagementData('managementhistory');
            }
            else
            {
                alert(result["msg"]);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}

// 수량입력하면 자동계산
function countCheck(num)
{    
    if($("input[name=val]")){
        total_val = $("input[name=val]").length;
    }

    if(document.getElementById('volume'+num).value && document.getElementById('material_price'+num).value){
        document.getElementById('material_amount'+num).value = commaInput(document.getElementById('volume'+num).value.replace(/,/g, "") * document.getElementById('material_price'+num).value.replace(/,/g, ""));
    }
    
    if(document.getElementById('volume'+num).value && document.getElementById('labor_price'+num).value){
        document.getElementById('labor_amount'+num).value = commaInput(document.getElementById('volume'+num).value.replace(/,/g, "") * document.getElementById('labor_price'+num).value.replace(/,/g, ""));
    }
    
    if(document.getElementById('material_price'+num).value && document.getElementById('labor_price'+num).value){
        document.getElementById('sum_price'+num).value = commaInput(+(Number(document.getElementById('material_price'+num).value.replace(/,/g, "")) + Number(document.getElementById('labor_price'+num).value.replace(/,/g, ""))).toFixed(3));
    } else if(document.getElementById('material_price'+num).value){
        document.getElementById('sum_price'+num).value = commaInput(document.getElementById('material_price'+num).value.replace(/,/g, ""));
    } else if(document.getElementById('labor_price'+num).value){
        document.getElementById('sum_price'+num).value = commaInput(document.getElementById('labor_price'+num).value.replace(/,/g, ""));
    }
    
    if(document.getElementById('volume'+num).value && document.getElementById('sum_price'+num).value){
        document.getElementById('sum_amount'+num).value = commaInput(document.getElementById('volume'+num).value.replace(/,/g, "") * document.getElementById('sum_price'+num).value.replace(/,/g, ""));
    }

    if(document.getElementById('volume'+num).value == ''){
        document.getElementById('labor_amount'+num).value = 0;
        document.getElementById('material_amount'+num).value = 0;
        document.getElementById('sum_amount'+num).value = 0;
    }
    if(document.getElementById('material_price'+num).value == ''){
        document.getElementById('material_amount'+num).value = 0;
    }
    if(document.getElementById('labor_price'+num).value == ''){
        document.getElementById('labor_amount'+num).value = 0;
    }
    if(document.getElementById('sum_price'+num).value == ''){
        document.getElementById('sum_amount'+num).value = 0;
    }

    if(total_val > 0){
        var summary = 0;
        for (var i = 0; i < total_val; i++) {
            if(document.getElementById('material_amount'+i).value){
                summary += parseInt(document.getElementById('material_amount'+i).value.replace(/,/g, ""));
            }
        }
        document.getElementById('material_amount_sum').value = commaInput(summary);

        if(document.getElementById('sub_material_amount_sum').value){
            document.getElementById('total_material_amount_sum').value = commaInput(parseInt(document.getElementById('material_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_material_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_material_amount_sum').value = commaInput(document.getElementById('material_amount_sum').value.replace(/,/g, ""));
        }
    }

    if(total_val > 0){
        var summary = 0;
        for (var i = 0; i < total_val; i++) {
            if(document.getElementById('labor_amount'+i).value){
                summary += parseInt(document.getElementById('labor_amount'+i).value.replace(/,/g, ""));
            }
        }
        document.getElementById('labor_amount_sum').value = commaInput(summary);

        if(document.getElementById('sub_labor_amount_sum').value){
            document.getElementById('total_labor_amount_sum').value = commaInput(parseInt(document.getElementById('labor_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_labor_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_labor_amount_sum').value = commaInput(document.getElementById('labor_amount_sum').value.replace(/,/g, ""));
        }
    }

    if(total_val > 0){
        var summary = 0;
        for (var i = 0; i < total_val; i++) {
            if(document.getElementById('sum_amount'+i).value){
                summary += parseInt(document.getElementById('sum_amount'+i).value.replace(/,/g, ""));
            }
        }
        document.getElementById('sum_amount_sum').value = commaInput(summary);

        if(document.getElementById('sub_sum_amount_sum').value){
            document.getElementById('total_sum_amount_sum').value = commaInput(parseInt(document.getElementById('sum_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_sum_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_sum_amount_sum').value = commaInput(document.getElementById('sum_amount_sum').value.replace(/,/g, ""));
        }
    }
}

// 내역서 엑셀업로드
function excelfileUpload()
{
    var contract_info_no = document.getElementById('contract_info_no').value;

    if(!contract_info_no)
    {
        alert("계약번호가 존재하지 않습니다.");
        return false;
    }

    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#excelUploadModal").modal('show');
	$("#excelUploadModalContent").html(loadingString);
	$.post("/field/managementhistoryexcelform", { contract_info_no: contract_info_no }, function (data) {
		$("#excelUploadModalContent").html(data);
	});
}

// 엑셀업로드 삭제
function excelfileRemove()
{
    var contract_info_no = document.getElementById('contract_info_no').value;

    if(!contract_info_no)
    {
        alert("계약번호가 존재하지 않습니다.");
        return false;
    }

    if(!confirm('정말 삭제하시겠습니까?'))
    {
        return false;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.post('/field/managementhistoryexcelremove', { contract_info_no: contract_info_no}, function(data)
    {
        if(data['rs_code'] == 'Y')
        {
            alert(data['rs_msg']);
            getManagementData('managementhistory');
        }
        else
        {
            alert(data['rs_msg']);
        }
    });
}

// 일위대가단가 입력하면 자동계산
function unitCheck(num)
{    
    if($("input[name=key]")){
        total_key = $("input[name=key]").length;
    }

    if($("input[name=num]")){
        total_num = $("input[name=num]").length;
    }

    if(document.getElementById('plus_volume'+num).value && document.getElementById('plus_material_price'+num).value){
        document.getElementById('plus_material_amount'+num).value = commaInput(document.getElementById('plus_volume'+num).value.replace(/,/g, "") * document.getElementById('plus_material_price'+num).value.replace(/,/g, ""));
    }
    
    if(document.getElementById('plus_volume'+num).value && document.getElementById('plus_labor_price'+num).value){
        document.getElementById('plus_labor_amount'+num).value = commaInput(document.getElementById('plus_volume'+num).value.replace(/,/g, "") * document.getElementById('plus_labor_price'+num).value.replace(/,/g, ""));
    }

    if(document.getElementById('plus_material_price'+num).value && document.getElementById('plus_labor_price'+num).value){
        document.getElementById('plus_sum_price'+num).value = commaInput(+(Number(document.getElementById('plus_material_price'+num).value.replace(/,/g, "")) + Number(document.getElementById('plus_labor_price'+num).value.replace(/,/g, ""))).toFixed(3));
    } else if(document.getElementById('plus_material_price'+num).value){
        document.getElementById('plus_sum_price'+num).value = commaInput(document.getElementById('plus_material_price'+num).value.replace(/,/g, ""));
    } else if(document.getElementById('plus_labor_price'+num).value){
        document.getElementById('plus_sum_price'+num).value = commaInput(document.getElementById('plus_labor_price'+num).value.replace(/,/g, ""));
    }
    
    if(document.getElementById('plus_volume'+num).value && document.getElementById('plus_sum_price'+num).value){
        document.getElementById('plus_sum_amount'+num).value = commaInput(document.getElementById('plus_volume'+num).value.replace(/,/g, "") * document.getElementById('plus_sum_price'+num).value.replace(/,/g, ""));
    }

    if(document.getElementById('plus_volume'+num).value == ''){
        document.getElementById('plus_labor_amount'+num).value = 0;
        document.getElementById('plus_material_amount'+num).value = 0;
        document.getElementById('plus_sum_amount'+num).value = 0;
    }
    if(document.getElementById('plus_material_price'+num).value == ''){
        document.getElementById('plus_material_amount'+num).value = 0;
    }
    if(document.getElementById('plus_labor_price'+num).value == ''){
        document.getElementById('plus_labor_amount'+num).value = 0;
    }
    if(document.getElementById('plus_sum_price'+num).value == ''){
        document.getElementById('plus_sum_amount'+num).value = 0;
    }

    if(total_num > 0){
        var summary = 0;
        for (var i = 0; i < total_num; i++) {
            if(document.getElementById('plus_material_amount'+i).value){
                summary += parseInt(document.getElementById('plus_material_amount'+i).value.replace(/,/g, ""));
            }
        }
        var sub_summary = 0;
        if(total_key > 0){
            for (var i = 0; i < total_key; i++) {
                if(document.getElementById('sub_material_amount'+i).value){
                    sub_summary += parseInt(document.getElementById('sub_material_amount'+i).value.replace(/,/g, ""));
                }
            }
        }
        document.getElementById('sub_material_amount_sum').value = commaInput(summary + sub_summary);

        if(document.getElementById('material_amount_sum').value){
            document.getElementById('total_material_amount_sum').value = commaInput(parseInt(document.getElementById('material_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_material_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_material_amount_sum').value = commaInput(document.getElementById('sub_material_amount_sum').value.replace(/,/g, ""));
        }
    }

    if(total_num > 0){
        var summary = 0;
        for (var i = 0; i < total_num; i++) {
            if(document.getElementById('plus_labor_amount'+i).value){
                summary += parseInt(document.getElementById('plus_labor_amount'+i).value.replace(/,/g, ""));
            }
        }
        var sub_summary = 0;
        if(total_key > 0){
            for (var i = 0; i < total_key; i++) {
                if(document.getElementById('sub_material_amount'+i).value){
                    sub_summary += parseInt(document.getElementById('sub_material_amount'+i).value.replace(/,/g, ""));
                }
            }
        }
        document.getElementById('sub_labor_amount_sum').value = commaInput(summary + sub_summary);

        if(document.getElementById('labor_amount_sum').value){
            document.getElementById('total_labor_amount_sum').value = commaInput(parseInt(document.getElementById('labor_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_labor_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_labor_amount_sum').value = commaInput(document.getElementById('sub_labor_amount_sum').value.replace(/,/g, ""));
        }
    }

    if(total_num > 0){
        var summary = 0;
        for (var i = 0; i < total_num; i++) {
            if(document.getElementById('plus_sum_amount'+i).value){
                summary += parseInt(document.getElementById('plus_sum_amount'+i).value.replace(/,/g, ""));
            }
        }
        var sub_summary = 0;
        if(total_key > 0){
            for (var i = 0; i < total_key; i++) {
                if(document.getElementById('sub_material_amount'+i).value){
                    sub_summary += parseInt(document.getElementById('sub_material_amount'+i).value.replace(/,/g, ""));
                }
            }
        }
        document.getElementById('sub_sum_amount_sum').value = commaInput(summary + sub_summary);

        if(document.getElementById('sum_amount_sum').value){
            document.getElementById('total_sum_amount_sum').value = commaInput(parseInt(document.getElementById('sum_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_sum_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_sum_amount_sum').value = commaInput(document.getElementById('sub_sum_amount_sum').value.replace(/,/g, ""));
        }
    }
}

// 내역 외 일위대가 코드 검색
function detailSearch()
{
    var searchdata = $('#detail_cost_search_string').val();
    searchdata = searchdata.replace(/\s/gi, ""); //공백제거

    if(searchdata != '')
    {
        var contract_info_no = document.getElementById('contract_info_no').value;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.post('/field/managementhistorysearch', { type:'search', keyword:searchdata, contract_info_no:contract_info_no, page : $('input[name=detailCurrentPage]').val() }, function(data)
        {
            var htmlStr = detailMakeList(data.cost);
            $('#detailList').html(htmlStr);
            $('#pageApi2').html('');

            detailPageMake(data.cnt);
        });
    }
}

// 내역 내 일위대가 코드 검색
function search()
{
    var searchdata = $('#cost_search_string').val();
    searchdata = searchdata.replace(/\s/gi, ""); //공백제거

    if(searchdata != '')
    {
        var contract_info_no = document.getElementById('contract_info_no').value;
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.post('/field/managementhistorysearch', { type:'search', keyword:searchdata, contract_info_no:contract_info_no, page : $('input[name=currentPage]').val() }, function(data)
        {
            var htmlStr = makeList(data.cost);
            $('#list').html(htmlStr);
            $('#pageApi').html('');

            pageMake(data.cnt);
        });
    }
}

// 내역 외 검색 결과 리스트 생성
function detailMakeList(data)
{
    if(data[0])
    {
        var tr = '';

        for(var i=0; i<data.length; i++)
        {
            tr += '<tr onclick="detailCostListCheck();" style="cursor:pointer;">';

            var code = '';
            var name = '';
            var standard1 = '';
            var standard2 = '';
            var type = '';
            var etc = '';

            if(data[i].code != null) code = data[i].code;
            if(data[i].name != null) name = data[i].name;
            if(data[i].standard1 != null) standard1 = data[i].standard1;
            if(data[i].standard2 != null) standard2 = data[i].standard2;
            if(data[i].type != null) type = data[i].type;
            if(data[i].etc != null) etc = data[i].etc;
    
            var td = '<td>' + code + '</td>';
            td += '<td>' + name + '</td>';
            td += '<td>' + standard1 + '</td>';
            td += '<td>' + standard2 + '</td>';
            td += '<td>' + type + '</td>';
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

// 내역 내 검색 결과 리스트 생성
function makeList(data)
{
    if(data[0])
    {
        var tr = '';

        for(var i=0; i<data.length; i++)
        {
            tr += '<tr onclick="costListCheck();" style="cursor:pointer;">';

            var code = '';
            var name = '';
            var standard1 = '';
            var standard2 = '';
            var type = '';
            var price = '';
            var etc = '';
            
            if(data[i].code != null) code = data[i].code;
            if(data[i].name != null) name = data[i].name;
            if(data[i].standard1 != null) standard1 = data[i].standard1;
            if(data[i].standard2 != null) standard2 = data[i].standard2;
            if(data[i].type != null) type = data[i].type;
            if(data[i].price != null) price = data[i].price;
            if(data[i].etc != null) etc = data[i].etc;
    
            var td = '<td>' + code + '</td>';
            td += '<td>' + name + '</td>';
            td += '<td>' + standard1 + '</td>';
            td += '<td>' + standard2 + '</td>';
            td += '<td>' + type + '</td>';
            td += '<td>' + (price ? commaInput(price) : '') + '</td>';
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

// 내역 내 페이지
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

// 내역 외 페이지
function detailPageMake(cnt)
{
    var total = cnt; // 총건수
	var pageNum = $('input[name=detailCurrentPage]').val();// 현재페이지
	var pageStr = "";

    if(!(total) || typeof total == "undefined" || total == '' || total == 0)
    {
		$("#pageApi2").html("");
	}
    else
    {
        // $("#detailList").html(htmlStr);
        $("#pageApi2").html("");
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

		$("#pageApi2").html(pageStr);
	}
}

// 내역내 페이지 이동
function goPage(pageNum)
{
	$('input[name=currentPage]').val(pageNum);
	search();
}

// 내역외 페이지 이동
function goPage2(pageNum)
{
	$('input[name=detailCurrentPage]').val(pageNum);
	detailSearch();
}

// 내역 내 항목 선택
function costListCheck()
{
    var obj = event.srcElement;
    var tr = getTrValues(obj.parentNode.children);
    var no = $("input[name=search_no]").val();

    $('#cost_code_'+no).val(tr[0] ?? 0);
    $('#cost_name_'+no).html(tr[1] ?? '');
    $('#cost_standard1_'+no).html(tr[2] ?? '');
    $('#cost_standard2_'+no).html(tr[3] ?? '');
    $('#cost_type_'+no).html(tr[4] ?? '');
    $('#cost_etc_'+no).html(tr[5] ?? '');

    if($('#cost_code_'+no).val())
    {
        var contract_info_no = document.getElementById('contract_info_no').value;
        var cost_code        = $('#cost_code_'+no).val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
        $.post('/field/managementcostcode', { code:cost_code, contract_info_no:contract_info_no }, function(data)
        {
            var cost_price_sum = parseInt(($('#cost_sum_price').val() || 0).replace(/,/gi, "")) || 0;
            var cost_price = data.price ?? 0;
            cost_price_sum += cost_price;
            $('#cost_price_'+no).html(cost_price.toLocaleString());
            $('#cost_sum_price').val(cost_price_sum).number(true);
            $('#td_cost_sum_price').html(cost_price_sum.toLocaleString());
        });
    }

    $('#modalC').modal('hide');
}

// 내역 외 항목 선택
function detailCostListCheck()
{
    var obj = event.srcElement;
    var tr = getTrValues(obj.parentNode.children);
    var no = $("#detail_search_no").val();

    $('#detail_cost_code_'+no).val(tr[0] ?? 0);
    $('#detail_cost_name_'+no).html(tr[1] ?? '');
    $('#detail_cost_standard1_'+no).html(tr[2] ?? '');
    $('#detail_cost_standard2_'+no).html(tr[3] ?? '');
    $('#detail_cost_type_'+no).html(tr[4] ?? '');
    $('#detail_cost_etc_'+no).html(tr[5] ?? '');

    if($('#detail_cost_code_'+no).val())
    {
        var contract_info_no = document.getElementById('contract_info_no').value;
        var cost_code        = $('#detail_cost_code_'+no).val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
        $.post('/field/managementcostcode', { code:cost_code, contract_info_no:contract_info_no }, function(data)
        {
            var detail_cost_price_sum = parseInt(($('#detail_cost_sum_price').val() || 0).replace(/,/gi, "")) || 0;
            var detail_cost_price = data.price ?? 0;
            detail_cost_price_sum += detail_cost_price;
            $('#detail_cost_price_'+no).html(detail_cost_price.toLocaleString());
            $('#detail_cost_sum_price').val(detail_cost_price_sum).number(true);
            $('#td_detail_cost_sum_price').html(detail_cost_price_sum.toLocaleString());
        });
    }

    $('#modalD').modal('hide');
}

// 내역 외 일위대가 코드 리스트 저장
function detailRegisterCode()
{
    var sum_price = 0;

     // 리스트 번호 가져오기
    var number = document.getElementById('cost_detail_list_no').value;

    if(number)
    {
        for (var i = 1; i <= 10; i++)
        {
            var detailCostCodeElement = document.getElementById('detail_cost_code_' + i);       // 리스트 코드
            var detailCodeElement = document.getElementById('detail_code' + i + '_' + number);  // 내역서 코드
            var detailCostPriceElement = document.getElementById('detail_cost_price_' + i);     // 리스트 단가

            if (detailCostCodeElement && detailCodeElement)
            {
                // 리스트 코드 내역서 코드에 넣기
                detailCodeElement.value = detailCostCodeElement.value;
            }

            if (detailCostPriceElement && detailCostPriceElement.textContent)
            {
                // 리스트 단가들의 합
                sum_price += parseInt(detailCostPriceElement.textContent.replace(/,/g, "")) || 0;
            }
        }

        // 리스트 단가 합 내역서 단가에 넣기
        document.getElementById('detail_price' + number).value = sum_price.toLocaleString();

        $("#modalA").modal('hide');
    }

    setInput(0);
}

// 내역 내 일위대가 코드 리스트 저장
function RegisterCode()
{
    var sum_price = 0;

     // 리스트 번호 가져오기
    var number = document.getElementById('cost_list_no').value;

    if(number)
    {
        for (var i = 1; i <= 10; i++)
        {
            var costCodeElement = document.getElementById('cost_code_' + i);       // 리스트 코드
            var codeElement = document.getElementById('code' + i + '_' + number);  // 내역서 코드
            var costPriceElement = document.getElementById('cost_price_' + i);     // 리스트 단가

            if (costCodeElement && codeElement)
            {
                // 리스트 코드 내역서 코드에 넣기
                codeElement.value = costCodeElement.value;
            }

            if (costPriceElement && costPriceElement.textContent)
            {
                // 리스트 단가들의 합
                sum_price += parseInt(costPriceElement.textContent.replace(/,/g, "")) || 0;
            }
        }

        // 리스트 단가 합 내역서 단가에 넣기
        document.getElementById('price' + number).value = sum_price.toLocaleString();
        $('#td_price' + number).html(sum_price.toLocaleString());

        $("#modalP").modal('hide');
    }

    setInput(0);
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
    $('#detail_cost_search_string').keydown(function() {
        if (event.keyCode === 13)
        {
            event.preventDefault();
            detailSearch();
        };
    });
    
    $('#cost_search_string').keydown(function() {
        if (event.keyCode === 13)
        {
            event.preventDefault();
            search();
        };
    });
}

</script>
