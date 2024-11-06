@extends('layouts.master')
@section('content')
@include('inc/list')



<div class="modal fade" id="batchModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">배치관리</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="batchForm">
                [[[CONTENTS]]]
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
                <div class="p-0">
                    <button type="button" class="btn btn-sm btn-danger" id="batch_btn_del" onclick="batchAction('DEL');">삭제</button>
                    <button type="button" class="btn btn-sm btn-info" id="batch_btn_save" onclick="batchAction('');">저장</button>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

@endsection


<!-- 자바스크립트 -->

@section('javascript')
<script>

    // 배치입력 폼 세팅
    function setBatchForm(no)
    {
        $("#batchModal").modal('show');

        // CORS 에러방지
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // 로딩 스피너
        $("#batchForm").html(loadingString);

        // 데이터 가져와서 리스트로 만들기
        $.get("/config/batchform", {no:no}, function(data) {
            $("#batchForm").html(data);
            $("#batch_btn_del").attr("disabled", ( $("#mode").val()=="INS" || $("#status").val()=="N"));
            $("#batch_btn_save").attr("disabled", ( $("#status").val()=="N" ));

            afterAjax();
            
        });
    }

    // 배치폼 저장
    function batchAction(md)
    {
        if(md=="DEL")
        {
            if(!confirm('삭제한 배치는 다시 복구가 불가능합니다. 정말 삭제하시겠습니까?'))
                return; 

            $("#mode").val(md);
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#batchInForm').serialize();

        $.ajax({
            url  : "/config/batchformaction",
            type : "post",
            data : postdata,
            success : function(result)
            {
                alert(result.rs_msg);

                if(result.rs_code == "Y")
                {
                    $("#batchModal").modal('hide');
                    listRefresh();
                }
            },
            error : function(xhr)
            {
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }

    function chkStar(id)
    {
        
        var selectVal = $('#'+id+'s').val();

        // *와 다른것 중복선택시 *로만 지정
        if(selectVal!='*' && selectVal.indexOf('*')==0)
        {
            alert('전체(*)와 다른 항목을 중복으로 선택할 수 없습니다.\n\n다시 선택해주세요');
            $('#'+id+'s').val('');
            $('#'+id).val('');
            $('#'+id+'s').selectpicker('refresh');
        }
        else
        {
            $('#'+id).val(selectVal);
        }
    }

    function getHelp()
    {
        var title = "실행주기 설정법";
        var memo = "<div class='popover-content'>";
        memo += "1. 선택은 입력을 돕기위한 것으로 실제 실행은 아래 텍스트로 실행이 됩니다.<br>";
        memo += "2. 선택하지 않고 직접입력해서 설정 가능<br>(예시 : 분 항목에 */10 로 설정시 매 10분마다 실행))";
        memo += "</div>";
        viewPopover('#help', title, memo);
    }
    
</script>
@endsection