@extends('layouts.master')


@section('content')
@include('inc/list')


<!-- 마감 모달 -->
<div class="modal fade" id="dayEndModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">마감등록</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
  
        <div class="modal-body" id="dayEndModalBody">
        </div>
  
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
          <div class="p-0">
          {{-- <button type="button" class="btn btn-sm btn-danger" id="branch_btn_del" onclick="agentActionDel();">마감취소</button>
          <button type="button" class="btn btn-sm btn-info" onclick="agentAction();">마감등록</button> --}}
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('javascript')
<script>
function setDayEnd(no)
{
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $("#dayEndModal").modal('show');
  $("#agentModalBody").html(loadingString);
  $.post("/config/dayendform", { no:no }, function(data) {
      
      if(data.status=='N')
      {
        $("#dayEndModalBody").html("<span class='text-red'>" + data.msg + "</span>");
      }
      else 
      {
        $("#dayEndModalBody").html(data);
        afterAjax();
      }
    });

}

function dayendAction(mode)
{
    if(mode=='INS')
    {
        if(!confirm("마감등록을 하시겠습니까?"))
        {   
            return false;
        }
    }
    else if(mode=='UPD')
    {
        if(!confirm("마감등록을 취소하시겠습니까?"))
        {   
            return false;
        }
    }

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.post("/config/dayendformaction", { mode:mode, no:$('#no').val() }, function(data) {
        
        if(data.status=='N')
        {
            if(data.msg)
            {
                alert(data.msg);
            }
            else
            {
                alert('처리중 오류가 발생했습니다. 관리자에게 문의해주세요');
            }
        }
        else 
        {
            alert('정상처리되었습니다.');
            listRefresh();
            $("#dayEndModal").modal('hide');
        }
        });

}
</script>
@endsection