<? $star = "<font class='text-red'>*</font>"; ?>

<form class="form-horizontal" name="contract_form" id="contract_form">
<input type="hidden" id="contract_no" name="contract_no" value="{{ $v->no ?? 0 }}">
<input type="hidden" id="order_info_no" name="order_info_no" value="{{ isset($order_info_no) ? $order_info_no : 0 }}">
    @csrf
    <div class="card card-lightblue">
        <div class="card-header">
            @if(isset($v->no) && $v->no > 0)
                <h2 class="card-title">계약수량 수정</h2>
            @else
                <h2 class="card-title">계약수량 등록</h2>
            @endif
        </div>
        
        <div class="card-body mr-3 p-3">
            <div class="form-group row mt-2">
                <label for="category" class="col-sm-4 col-form-label">{!! $star !!}구분</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="category" name="category" placeholder="" value="{{ $v->category ?? '' }}"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="code" class="col-sm-4 col-form-label">{!! $star !!}코드</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="code" name="code" placeholder="" value="{{ $v->code ?? '' }}"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="name" class="col-sm-4 col-form-label">{!! $star !!}품명</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="" value="{{ $v->name ?? '' }}"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="standard1" class="col-sm-4 col-form-label">규격(1)</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="standard1" name="standard1" placeholder="" value="{{ $v->standard1 ?? '' }}"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="standard2" class="col-sm-4 col-form-label">규격(2)</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="standard2" name="standard2" placeholder="" value="{{ $v->standard2 ?? '' }}"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="type" class="col-sm-4 col-form-label">단위</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm" id="type" name="type" placeholder="" value="{{ $v->type ?? '' }}"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="count" class="col-sm-4 col-form-label">수량</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm moneyformat" id="count" name="count" placeholder="" value="{{ $v->count ?? '' }}"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="price" class="col-sm-4 col-form-label">단가</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control form-control-sm text-right" id="price" name="price" placeholder="" value="{{ $v->price ?? '' }}" onkeyup="setCommaInput();"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="etc" class="col-sm-4 col-form-label">기타</label>
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

// 소수점 콤마 세팅
function setCommaInput()
{
    var inputBox = document.getElementById('price');
    var cursorPosition = inputBox.selectionStart;

    var price = $('#price').val().replace(/,/gi, "");
    price = price.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
    
    $('#price').val(price);

    inputBox.setSelectionRange(cursorPosition, cursorPosition);
}

setInputMask('class', 'moneyformat', 'money');

// 저장 Action
function saveAction(type) 
{
    if(type != 'DEL')
    {
        var category = $('#category').val();
        var code     = $('#code').val();
        var name     = $('#name').val();

        if(category == '')
        {
            alert('구분을 입력해주세요.');
            return false;
        }
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

    var postdata = $('#contract_form').serialize();
    postdata = postdata + '&mode=' + type;

    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajax({
        url  : "/field/ordercontractformaction",
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
                $("#contractModal").modal('hide');
                getOrderData('ordercontract');
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