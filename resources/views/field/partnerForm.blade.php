<? $star = "<font class='text-red'>*</font>"; ?>

<form class="form-horizontal" name="partner_form" id="partner_form">
<input type="hidden" id="partner_no" name="partner_no" value="{{ $v->no ?? 0 }}">
    @csrf
    <div class="card card-lightblue">
        <div class="card-header">
            @if(isset($v->no) && $v->no > 0)
                <h2 class="card-title">협력사 수정</h2>
            @else
                <h2 class="card-title">협력사 등록</h2>
            @endif
        </div>
        
        <div class="card-body mr-3 p-3">
            <div class="form-group row mt-2">
                <label for="partner_name" class="col-sm-4 col-form-label">{!! $star !!}협력사명</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="partner_name" name="partner_name" placeholder="" value="{{ $v->partner_name ?? '' }}"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="manager_name" class="col-sm-4 col-form-label">담당자명</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="manager_name" name="manager_name" placeholder="" value="{{ $v->manager_name ?? '' }}"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="manager_ph" class="col-sm-4 col-form-label">담당자연락처</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="manager_ph" name="manager_ph" placeholder="" value="{{ $v->manager_ph ?? '' }}"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="etc" class="col-sm-4 col-form-label">분류</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="etc" name="etc" placeholder="" value="{{ $v->etc ?? '' }}"/>
                </div>
            </div>
        </div>
        <div class="card-footer">
            @if(isset($v->no) && $v->no > 0)
                <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="saveAction('DEL');">삭제</button>
                <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="saveAction('UPD');">수정</button>
            @else
                <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="saveAction('INS');">등록</button>
            @endif
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
    if(type != 'DEL')
    {
        var partner_name = $('#partner_name').val();

        if(partner_name == '')
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

    var postdata = $('#partner_form').serialize();
    postdata = postdata + '&mode=' + type;

    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajax({
        url  : "/field/partnerformaction",
        type : "post",
        data : postdata,
        success : function(data)
        {
            // 성공알림 
            if(data['rs_code'] == "Y") 
            {
                globalCheck = false;
                alert(data['result_msg']);
                location.reload();
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