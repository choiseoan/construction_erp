<? $star = "<font class='text-red'>*</font>"; ?>

<form class="form-horizontal" name="management_form" id="management_form">
    @csrf
    <div class="card card-lightblue">
        <div class="card-header">
            <h2 class="card-title">현장 등록</h2>
        </div>
        
        <div class="card-body mr-3 p-3">
            <div class="form-group row mt-2">
                <label for="code" class="col-sm-2 col-form-label">{!! $star !!}코드</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="code" name="code" placeholder=""/>
                </div>
            </div>
            <div class="form-group row">
                <label for="name" class="col-sm-2 col-form-label">{!! $star !!}현장명</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder=""/>
                </div>
            </div>
            <div class="form-group row">
                <label for="orderer" class="col-sm-2 col-form-label">발주처</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="orderer" name="orderer" placeholder=""/>
                </div>
            </div>
            <div class="form-group row">
                <label for="div" class="col-sm-2 col-form-label">구분</label>
                <div class="col-sm-4">
                    <div class="row">
                        <div class="col-sm-12">
                            <select class="form-control form-control-sm" name="div" id="div" >
                                <option value=''>구분</option>
                                    {{ Func::printOption($arrayConfig['management_div'], "") }}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="cust_bank_ssn" class="col-sm-2 col-form-label">공사시작일</label>
                <div class="col-sm-4">
                    <div class="row">
                        <div class="col-md-9 m-0 pr-0">
                            <div class="input-group date datetimepicker" id="contract_date_div" data-target-input="nearest">
                                <input type="text" class="form-control form-control-sm dateformat" name="contract_date" id="contract_date" inputmode="text" value="{{ date('Y-m-d') }}"/>
                                <div class="input-group-append" data-target="#contract_date_div" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <label for="cust_ssn" class="col-sm-2 col-form-label">공사종료일</label>
                <div class="col-sm-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group date datetimepicker" id="contract_end_date_div" data-target-input="nearest">
                                <input type="text" class="form-control form-control-sm dateformat" name="contract_end_date" id="contract_end_date" inputmode="text" value="{{ date('Y-m-d') }}"/>
                                <div class="input-group-append" data-target="#contract_end_date_div" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="saveAction('');">계약등록</button>
            <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</form>

<script>

$(document).ready(function()
{    
    $(".datetimepicker").datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
        useCurrent: false,
    });
});

// 등록 Action
function saveAction() 
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
        alert('현장명을 입력해주세요.');
        return false;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#management_form').serialize();

    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajax({
        url  : "/field/managementformaction",
        type : "post",
        data : postdata,
        success : function(data)
        {
            // 성공알림 
            if(data['rs_code'] == "Y") 
            {
                globalCheck = false;
                alert(data['result_msg']);
                location.href='/field/management';
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
            globalCheck = false;
        }
    });
}

</script>