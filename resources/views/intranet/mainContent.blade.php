@extends('layouts.master')
@section('content')

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header border-0">
            <h3 class="card-title font-weight-bold text-gray-dark text-sm">공지사항 게시판</h3>
            <div class="card-tools">
              <a href="#" class="btn btn-tool btn-sm">
                <i class="fas fa-bars"></i>
              </a>
            </div>
          </div>
          
          <div class="card-body table-responsive p-0">
            <table class="table table-striped table-valign-middle table-hover table-condensed">
              <col width="10%"></col>
              <col width="7%"></col>
              <col width=""></col>
              <col width="30%"></col>
              <thead>
                <tr>
                  <th class='p-2 pl-2 text-center'>번호</th>
                  <th></th>
                  <th class='p-2 pl-2'>제목</th>
                  <th class='p-2 mr-2 text-center'>작성일시</th>
                </tr>
              </thead>
              <tbody>

                @forelse( $notice as $val )

                  <tr role="button" onclick="location.href='/intranet/board/notice?no={{ $val->no }}'">
                    <td class='p-2 pl-2 text-center'>{{ $val->no }}</td>
                    <td class='p-0 pl-2 text-center'>{!! Func::dateTerm( substr($val->save_time,0,8), date("Ymd") )<=2 ? "<i class='fas fa-bullhorn m-0 text-awesome'></i>" : "" !!}</td>
                    <td class='p-2 pl-2'>{{ ( $val->title=="" ) ? "제목없음" : $val->title }}</td>
                    <td class='p-2 pr-2 text-center'>{{ Func::dateFormat($val->save_time) }}</td>
                  </tr>

                @empty

                  <tr>
                    <td colspan=4 class="text-center p-4 text-muted bg-white">
                      등록된 공지가 없습니다.
                    </td>
                  </tr>

                @endforelse

              </tbody>
            </table>
          </div>
        </div>
        <!-- /.card -->
      </div>
      
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header border-0">
            <h3 class="card-title font-weight-bold text-gray-dark text-sm">읽지않은 메세지</h3>
            <div class="card-tools">
              <a href="#" class="btn btn-tool btn-sm">
                <i class="fas fa-bars"></i>
              </a>
            </div>
          </div>

          <div class="card-body table-responsive p-0">
            <table class="table table-striped table-valign-middle table-hover table-condensed">
              <col width="15%"></col>
              <col width=""></col>
              <col width="15%"></col>
              <col width="25%"></col>
              <thead>
                <tr>
                  <th class='p-2 pl-2 text-center'>번호</th>
                  <th class='p-2 pl-2'>제목</th>
                  <th class='p-2 pl-2 text-center'>보낸이</th>
                  <th class='p-2 mr-2 text-center'>보낸시간</th>
                </tr>
              </thead>
              <tbody>

                @forelse( $message as $val )

                  <tr role="button" onclick="setMsgForm({{$val->no}});">
                    <td class='p-2 pl-2 text-center'>

                      @if( $val->msg_type=="M" )
                          <i class="fas fa-envelope text-{{ $val->msg_level ?? 'gray' }}"></i>
                      @elseif ( $val->msg_type=="N" )
                          <i class="fas fa-bullhorn text-{{ $val->msg_level ?? 'gray' }}"></i>
                      @elseif ( $val->msg_type=="S" )
                          <i class="fas fa-bell text-{{ $val->msg_level ?? 'gray' }}"></i>
                      @endif

                    </td>
                    <td class='p-2'>{{ $val->title }}</td>
                    <td class='p-2 text-center'>{{ Func::getUserId($val->send_id)->name ?? '' }}</td>
                    <td class='p-2 pr-2 text-center'>{{ Func::dateFormat($val->send_time) }}</td>

                  </tr>

                @empty

                  <tr>
                    <td colspan=4 class="text-center p-4 text-muted bg-white">
                      읽지 않은 메세지가 없습니다.
                    </td>
                  </tr>

                @endforelse

              </tbody>
            </table>
          </div>
        </div>
        <!-- /.card -->
      </div>
    </div>
  </div>
</section>
<!-- /.content -->

<form id="myMsgForm">
  @csrf
  <input type="hidden" id="mdiv" name="mdiv" value="recv">
  <input type="hidden" id="mtype" name="mtype" value="">
  <input type="hidden" id="msgNo" name="msgNo" value="">
</form>

@endsection

@section('javascript')
<script src="/plugins/chart.js/Chart.min.js"></script>
<script>

  $(function () {
    'use strict'

    var ticksStyle = {
      fontColor: '#495057',
      fontStyle: 'bold'
    }

    var mode = 'index'
    var intersect = true

  });

  function setMsgForm(no)
  {
    $('#msgNo').val(no);
    $('#myMsgForm').attr("action", "/intranet/msgpop");
    $('#myMsgForm').attr("method", "post");
    $('#myMsgForm').attr("target", "msgInfo");
    
    window.open("", "msgInfo", "width=600, height=800, scrollbars=no");
    $("#myMsgForm").submit();

  }

</script>

<script>

@if(session('warning'))
  alert("{{ session('warning') ?? '' }}");
  location.href = "/intranet/myinfo?tab=pwd";
@endif

</script>

@endsection