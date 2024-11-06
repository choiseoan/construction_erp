<!-- 현장내역 -->
<div class="col-md-11 p-0 m-0 " >
    <div class="card-body ml-5" id="managementinfoInput">
    <form  class="mb-0" name="management_form" id="management_form" method="post" enctype="multipart/form-data">
    <input type="hidden" id="contract_info_no" name="contract_info_no" value="{{ $v->no ?? '' }}">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group row usr_collapse" id="collapseSearch">
                    <label class="col-sm-2 col-form-label"></label>
                    <div class="col-sm-10" id="usrCollapseSearchResult">
                    </div>
                </div>

                <div class="row" id="invest_input">
                    <div class="col-md-12">
                        <h3 class="card-title"><i class="fas fa-clipboard m-2" size="9px"></i>현장 정보</h3>
                    </div>
                    <div class="card-body p-1">
                        <table class="table table-sm table-bordered table-input text-xs">
                            <colgroup>
                                <col width="17%"/>
                                <col width="33%"/>
                                <col width="15%"/>
                                <col width="35%"/>
                            </colgroup>
                            <tbody>
                                <tr height="34">
                                    <th>
                                        <span class="text-danger font-weight-bold h6 mr-1">*</span>코드
                                    </th>
                                    <td>
                                        <input type="text" class="form-control form-control-sm col-md-8 text-center" id="code" name="code" placeholder="" data-target="#code" value="{{ $v->code ?? '' }}" />
                                    </td>
                                    <th><span class="text-danger font-weight-bold h6 mr-1">&nbsp;&nbsp;</span>담당부서</th>
                                    <td>
                                        <div class="col-sm-12 ml-lg-n2">
                                            <div class="row">
                                                <select class="form-control form-control-sm col-md-8 mt-1 ml-2 text-center" name="branch_code" id="branch_code">
                                                    <option value=''>미지정</option>
                                                        {{ Func::printOption(Func::getBranch(), $v->branch_code ?? '') }}
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr height="34">
                                    <th><span class="text-danger font-weight-bold h6 mr-1">*</span>현장명</th>
                                    <td>
                                        <input type="text" class="form-control form-control-sm col-md-8 text-center" id="name" name="name" placeholder="" data-target="#name" value="{{ $v->name ?? '' }}" />
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr height="34">
                                    <th><span class="text-danger font-weight-bold h6 mr-1">&nbsp;&nbsp;</span>구분</th>
                                    <td>
                                        <div class="col-sm-12 ml-lg-n2">
                                            <div class="row">
                                                <select class="form-control form-control-sm col-md-8 mt-1 ml-2 text-center" name="div" id="div">
                                                    <option value=''>구분</option>
                                                        {{ Func::printOption($configArr['management_div'], $v->div ?? '') }}
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr height="34">
                                    <th>
                                        <span class="text-danger font-weight-bold h6 mr-1">&nbsp;&nbsp;</span>발주처
                                    </th>
                                    <td>
                                        <input type="text" class="form-control form-control-sm col-md-8 text-center" id="orderer" name="orderer" placeholder="" data-target="#orderer" value="{{ $v->orderer ?? '' }}" />
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr height="34">
                                    <th><span class="text-danger font-weight-bold h6 mr-1">&nbsp;&nbsp;</span>공사금액</th>
                                    <td>
                                        <input type="text" class="form-control form-control-sm col-sm-8 text-right moneyformat" id="balance" value="{{ number_format($v->balance ?? 0) }}" disabled/>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr height="34">
                                    <th><span class="text-danger font-weight-bold h6 mr-1">&nbsp;&nbsp;</span>공사시작일</th>
                                    <td>
                                        <div class="row">
                                            <div class="col-md-9 m-0 pr-0">
                                                <div class="input-group date datetimepicker" id="contract_date_div" data-target-input="nearest">
                                                    <input type="text" class="form-control form-control-sm dateformat text-center" name="contract_date" id="contract_date" inputmode="text" value="{{ Func::dateFormat($v->contract_date) }}">
                                                    <div class="input-group-append" data-target="#contract_date_div" data-toggle="datetimepicker">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <th><span class="text-danger font-weight-bold h6 mr-1">&nbsp;&nbsp;</span>공사종료일</th>
                                    <td colspan='4'>
                                        <div class="row">
                                            <div class="col-md-9 m-0 pr-0">
                                                <div class="input-group date datetimepicker" id="contract_end_date_div" data-target-input="nearest">
                                                    <input type="text" class="form-control form-control-sm dateformat text-center" name="contract_end_date" id="contract_end_date" inputmode="text" value="{{ Func::dateFormat($v->contract_end_date) }}">
                                                    <div class="input-group-append" data-target="#contract_end_date_div" data-toggle="datetimepicker">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <br/><br/>
                    <br/><br/>

                    <div class="col-md-12 mt-5" >
                        <h3 class="card-title"><i class="fas fa-pen m-2" size="9px"></i>메모</h3>
                    </div>
                    <div class="card-body p-1">
                        <table class="table table-sm table-bordered table-input text-xs" id='memo_title'>
                            <colgroup>
                            <col width="17%"/>
                            <col width="83%"/>
                            </colgroup>
                            <tbody>
                                <tr>
                                    <th class="text-center bold">메&nbsp;&nbsp;&nbsp;&nbsp;모</th>
                                    <td>
                                        <textarea class="form-control form-control-xs" name="contract_memo" id="contract_memo" placeholder=" 메모입력...." rows="4" style="resize:none;">{{ $v->contract_memo ?? '' }}</textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="pt-1 pb-1" id="input_footer">
                            <button type="button" class="btn btn-sm mt-3 btn-danger float-right" id="memo_confirm" onclick="confirmSave('DEL');">삭제</button>
                            <button type="button" class="btn btn-sm mt-3 mr-1 btn-info float-right" id="memo_confirm" onclick="confirmSave('UPD');">수정</button>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
    </form>
    </div>
</div>

<script>

$('.datetimepicker').datetimepicker({
    format: 'YYYY-MM-DD',
    locale: 'ko',
    useCurrent: false,
});

setInputMask('class', 'moneyformat', 'money');

// 현장등록
function confirmSave(div)
{
    if(div == 'UPD')
    {
        // 입력값 확인
        var code = $('#code').val();
        var name = $('#name').val();

        if(code == '')
        {
            alert('코드를 입력해주세요.');
            return false;
        }
        if(name == '')
        {
            alert('현장명을 입력해주세요.');
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

    var postdata = $('#management_form').serialize();
    postdata = postdata + '&mode=' + div;

    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajax({
        url  : "/field/managementinfoaction",
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
                    document.location.href = "/field/managementpop?no="+$('#contract_info_no').val();
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
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}

</script>