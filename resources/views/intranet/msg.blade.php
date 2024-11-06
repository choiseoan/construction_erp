@extends('layouts.master')
@section('content')

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-3">
      <button  class="btn btn-primary btn-block mb-3" onclick="setMsgForm('N')">쪽지보내기</button>
      <form id="myMsgForm">
        @csrf
        <input type="hidden" id="mdiv" name="mdiv" value={{ $mdiv ?? 'send' }}>
        <input type="hidden" id="mtype" name="mtype" value={{ $mtype ?? '' }}>
        <input type="hidden" id="msgNo" name="msgNo" value="">
      </form>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Folders</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body p-0">
          <ul class="nav nav-pills flex-column">
            <li class="nav-item active">
              <a href="?mdiv=recv" class="nav-link">
                <i class="fas fa-inbox mr-2"></i> 받은쪽지함
              </a>
            </li>
            <li class="nav-item pl-3">
              <a href="?mtype=M" class="nav-link">
              <i class="fas fa-envelope mr-2"></i> 메세지
              </a>
            </li>
            <li class="nav-item pl-3">
              <a href="?mtype=N" class="nav-link">
              <i class="fas fa-bullhorn mr-2"></i> 공지사항
              </a>
            </li>
            <li class="nav-item pl-3">
              <a href="?mtype=S" class="nav-link">
              <i class="fas fa-bell mr-2"></i> 시스템알림
              </a>
            </li>
            <li class="nav-item" >
              <a href="?mdiv=send" class="nav-link">
                <i class="far fa-envelope mr-2"></i> 보낸쪽지함
              </a>
            </li>
          </1l>
        </div>
      </div>
        <!-- /.card-body -->
    </div>
    <!-- /.col -->
    <div class="col-md-9">
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title">@if($mdiv=='send') 보낸 @else 받은 @endif 쪽지함</h3>
          @if( $mdiv=="recv" ) 
          <button type="button" class="btn btn-default float-right btn-xs" onclick="lumpMsgAction('RDEL');"><i class="fas fa-trash-alt mr-1 text-gray"></i>삭제</button>
          <button type="button" class="btn btn-default float-right btn-xs mr-2" onclick="lumpMsgAction('RECV');"><i class="fas fa-envelope-open mr-1 text-gray"></i>읽음표시</button>
          @else
          <button type="button" class="btn btn-default float-right btn-xs" onclick="lumpMsgAction('SDEL');"><i class="fas fa-trash-alt mr-1 text-gray"></i>삭제</button>          
          @endif
        </div>
        <div id="msgList">
        </div>
      </div>
      <!-- /.card -->
    </div>
    <!-- /.col -->
  </div>
  <!-- /.row -->
</section>
@endsection

@section('javascript')
<script>

  function setMsgList(pageNum)
  {
    $("#msgList").html(loadingString);
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
      url  : "/intranet/msglist",
      type : "post",
      data : {
        mdiv  : $('#mdiv').val(),
        mtype : $('#mtype').val(),
        page  : pageNum
      },
      success : function(result)
      {
        $("#msgList").html(result);
      },
      error:function(request, error) {

        alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error)
    
        $("#msgList").html("통신오류입니다. 관리자에게 문의해주세요.");
      }
      });
  }
  setMsgList(0);


  //메세지 작성 popUp
  function setMsgForm(no)
  {
    $('#msgNo').val(no);
    $('#myMsgForm').attr("action", "/intranet/msgpop");
    $('#myMsgForm').attr("method", "post");
    $('#myMsgForm').attr("target", "msgInfo");
    
    window.open("", "msgInfo", "width=600, height=800, scrollbars=no");
    $("#myMsgForm").submit();
  }

  function lumpMsgAction(md)
  {
    var msg_nos = $("#msglist input:checkbox:checked").map(function(){
      return $(this).val();
    }).get();

    $.ajaxSetup({
          headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });

    $.ajax({
      url  : "/intranet/lumpmsgaction",
      type : "post",
      data : {
        mode  : md,
        nos : msg_nos,
      },
      success : function(result)
      {
        if( result.rst=="Y" )
        {
          alert("정상처리되었습니다.");
          location.reload();
        }
        else
        {
          alert(result.msg);
        }
      },
      error:function(request, error) {
        alert('통신오류')
      }
      });


  }
</script>
@endsection