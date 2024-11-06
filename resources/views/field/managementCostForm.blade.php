<? $star = "<font class='text-red'>*</font>"; ?>

<form class="form-horizontal" name="cost_form" id="cost_form">
<input type="hidden" id="contract_info_no" name="contract_info_no" value="{{ isset($contract_info_no) ? $contract_info_no : 0 }}">
    @csrf
    <div class="card card-lightblue">
        <div class="card-header">
            <h2 class="card-title">일위대가 등록</h2>
        </div>
        
        <div class="card-body mr-3 p-3">
            <div class="form-group row">
                <label for="code" class="col-sm-4 col-form-label">{!! $star !!}코드</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="code" name="code" placeholder=""/>
                </div>
            </div>
            <div class="form-group row">
                <label for="name" class="col-sm-4 col-form-label">{!! $star !!}품명</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder=""/>
                </div>
            </div>
            <div class="form-group row">
                <label for="standard1" class="col-sm-4 col-form-label">규격(1)</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="standard1" name="standard1" placeholder=""/>
                </div>
            </div>
            <div class="form-group row">
                <label for="standard2" class="col-sm-4 col-form-label">규격(2)</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="standard2" name="standard2" placeholder=""/>
                </div>
            </div>
            <div class="form-group row">
                <label for="type" class="col-sm-4 col-form-label">단위</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="type" name="type" placeholder=""/>
                </div>
            </div>
            <div class="form-group row">
                <label for="etc" class="col-sm-4 col-form-label">기타</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="etc" name="etc" placeholder=""/>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="saveAction('INS');">등록</button>
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

setInputMask('class', 'moneyformat', 'money');

// 저장 Action
function saveAction(type) 
{
    var code     = $('#code').val();
    var name     = $('#name').val();
    
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

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#cost_form').serialize();
    postdata = postdata + '&mode=' + type;

    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajax({
        url  : "/field/managementcostformaction",
        type : "post",
        data : postdata,
        success : function(data)
        {
            // 성공알림 
            if(data['rs_code'] == "Y") 
            {
                globalCheck = false;
                alert(data['result_msg']);
                $(".modal-backdrop").remove();
                $("#costModal").modal('hide');
                getManagementData('managementcost');
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