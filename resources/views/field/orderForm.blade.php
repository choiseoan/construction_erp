<? $star = "<font class='text-red'>*</font>"; ?>

<form class="form-horizontal" name="order_form" id="order_form">
    @csrf
    <div class="card card-lightblue">
        <div class="card-header">
            <h2 class="card-title">발주 등록</h2>
        </div>
        
        <div class="card-body mr-3 p-3">
                <div class="form-group row m-2">
                    <label for="name" class="col-sm-4 col-form-label">{!! $star !!} 현장명</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control form-control-sm" id="field_name" name="field_name" placeholder=""/>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="saveAction('');">발주등록</button>
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

    var postdata = $('#order_form').serialize();

    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajax({
        url  : "/field/orderformaction",
        type : "post",
        data : postdata,
        success : function(data)
        {
            // 성공알림 
            if(data['rs_code'] == "Y") 
            {
                globalCheck = false;
                alert(data['result_msg']);
                location.href='/field/order';
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

// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
      if (event.keyCode === 13)
      {
        event.preventDefault();
        saveAction();
      };
    });

    $("input[data-bootstrap-switch]").each(function() {
    $(this).bootstrapSwitch('state', $(this).prop('checked'));
  });
}

enterClear();

</script>