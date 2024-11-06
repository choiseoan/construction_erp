@extends('layouts.masterPop')
@section('content')

@if($msgDiv=='RECV' || $msgDiv=='SEND_MSG')
<div class="card card-{{$msg_card ?? 'default' }}" id="container">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-{{$msg_fas ?? 'envelope' }} mr-1"></i>
      {{$msg_type_str ?? 'Message' }} 
    </h3>
    <button type="button" class="close" onclick="window.close()">
      <span aria-hidden="true">×</span>
    </button>
  </div>
  <div class="card-body" id="myMsgModalBody">
    <table class="table table-sm">
    <tr>
        <th>보낸사람</th>
        <td>{{ $result->send_name ?? '' }} ({{ $result->send_id ?? '' }})</td>
        @if(isset($result->send_time))
        <td class='text-right'>{{ Func::dateFormat($result->send_time) }}</td>
        @else
        <td></td>
        @endif

      </tr>
      <tr>
        <th>받는사람</th>
        <td>{{$result->recv_name ?? '' }} ({{$result->recv_id ?? ''}} )</td>
        @if(isset($result->recv_time))
        <td class='text-right'>{{ Func::dateFormat($result->recv_time) }}</td>
        @else
        <td></td>
        @endif
      </tr>
      @if(isset($with))
      <tr>
        <th>함께받는사람</th>
        <td>{{ $with ?? '' }}</td>
        <td></td>
      </tr>
      @endif
      <tr>
        <td colspan=3 class="p-0">
          <div class="card mt-2">
            <div class="card-header p-2">
              {{$result->title ?? ''}}
            </div>
            @if(strlen($result->contents)/3>200 && strlen($result->contents)/3<800)
            <div class="card-body p-2" style="overflow:auto; width:100%; height: {{strlen($result->contents)/3}}px;"> 
            @else
            <div class="card-body p-2" style="overflow:auto; width:100%; height: 200px;">
            @endif
              {!! str_replace("\n","<br>",str_replace("<br>","\n",$result->contents)) ?? '' !!}
            </div>
          </div>
        </td>
      </tr>
    @if(isset($result->msg_link))
    <tr>
      <td colspan=3>
      <button type="button" class="btn btn-sm bg-gradient-primary"  onclick="msgLinkOpen('{{ $result->msg_link }}')" ><i class="fas fa-paperclip"></i> 바로가기</button>
      </td>
    </tr>
    @endif
    @if(!empty($result->json_link))
    <tr>
      <td colspan=3>
        바로가기 : 
        @foreach($result->json_link as $link)
        [{!! $link !!}]
        @endforeach
      </td>
    </tr>
    @endif
    <tr><td colspan=3>
    <button type="button" class="btn btn-default float-right btn-xs" onclick="msgDelete();"><i class="fas fa-trash-alt mr-1 text-gray"></i>삭제</button>
    @if( $result->send_id!="SYSTEM" && $msgDiv=="RECV")
    <button type="button" class="btn btn-default float-right btn-xs mr-2" onclick="msgReply();"><i class="fas fa-reply mr-1 text-gray"></i>답장하기</button>
    @endif
    </td></tr>
    </table>
  </div>
</div>
<script>
  window.opener.getMyMsg();
</script>

@else

<form id="myMsgForm">
<div class="card card-default" id="container">
  <div class="card-header text-gray">
    <div class="row clearfix p-2">
      <div class="col-10">
      <div class="icheck-secondary d-inline">
        <input type="radio" id="radioPrimary2" name="msg_type" value="N">
        <label for="radioPrimary2">
        <i class="fas fa-bullhorn mr-1"></i> 공지사항
        </label>
      </div>
      <div class="icheck-secondary d-inline" style="padding-left:15px;">
        <input type="radio" id="radioPrimary1" name="msg_type" checked="" value="M">
        <label for="radioPrimary1">
        <i class="fas fa-envelope mr-1"></i> 메세지
        </label>
      </div>
      </div>
      <div class="col-2 text-right">
        <select class="form-control form-control-sm" name="msg_level" id="msg_level">
        <? Func::printOption(Vars::$arrayMsgLevel); ?>
        </select>
      </div>
    </div>
  </div>
  <div class="card-body" id="myMsgModalBody">
    <div class="form-group">
        <label for="send_id">보낸사람:</label>
        <input type="hidden" id="send_id" name="send_id" value="{{ Auth::user()->id }}">
        {{ Func::chungDecOne(Auth::user()->name) }}
    </div>
    <div>
        <label for="recv_id">받는사람:</label>
    </div>
    <div class="card">
      <div class="card-header">
        <h4 class="card-title w-100">
          <a class="d-block w-100 text-sm text-gray" data-toggle="collapse" href="#collapseOne" aria-expanded="true">
            부서선택
          </a>
        </h4>
      </div>
      <div id="collapseOne" class="collapse">
        <div class="card-body">
          <div class="row">
            <div class="col-6 table-responsive p-0" style="height:200px;">
            <table class="table table-hover table-sm">
              @forelse( $branchList as $value )
              <tr onclick="setBranchView('{{$value['code']}}')">
                <td style="padding-left: {{ $value['branch_depth']*10 }}px;">{{ $value['branch_name'] }}</td>
              </tr>
              @empty
              @endforelse
            </table>
            </div>
            <div class="col-6 table-responsive" style="height:200px;">
              <table class="table table-hover table-sm">
                @forelse( $branchList as $value )
                <tr class="bch-tb bch-{{$value['code']}}" style="display:none;" >
                  <td class="text-gray">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="chk_bch_{{$value['code']}}" value="{{$value['code']}}" onclick="setBchChk('{{$value['code']}}')">
                    <label for="chk_bch_{{$value['code']}}">{{ $value['branch_name']}} 전체</label>
                  </div>
                  </td>
                </tr>
                @empty
                @endforelse
                @forelse($userList as $userId =>$user )
                <tr class="bch-tb bch-{{$user->branch_code}}" style="display:none;" >
                  <td class="text-gray">
                    <div class="form-check">
                      <input class="form-check-input bch-{{$user->branch_code}}-chk" type="checkbox" id="chk_{{$userId}}" value="{{$userId}}" onclick="setSelectBch('{{$userId}}');">
                      <label for="chk_{{$userId}}">{{$user->name}}</label>
                    </div>
                  </td>
                </tr>
                @empty
                @endforelse
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group">       
      <select class="select2" multiple="multiple" style="width:100%;" id="recv_id" name="recv_id[]" data-placeholder="직원 선택">
        @forelse($userList as $userId =>$user )
            <option value="{{$userId}}" {{ ( isset($to) && $userId==$to ) ? "selected" : "" }}>{{$user->name}} ({{ $branchList[$user->branch_code]['branch_name'] ?? '' }})</option>
        @empty
        @endforelse
        @forelse($branchList as $code =>$b )
            <option value="bch_{{$code}}">{{$b['branch_name']}}전체</option>
        @empty
        @endforelse
      </select>
    </div>     
    <div class="form-group">
        <label>제목:</label>
        <input class="form-control" id="title" placeholder="제목:" name="title" value="@if(isset($re_title) && $re_title!=''){{ $re_title ?? '' }}@endif">
    </div>
    <div class="form-group">
        <label>내용:</label>
        <textarea id="contents" class="form-control" name="contents" style="height:150px"></textarea>
    </div>
    <div class="form-group">
        <input class="form-control" placeholder="바로가기 링크 " name="msg_link" value="{{$result->msg_link ?? ''}}">
    </div>
  </div>
  <div class="card-footer m-0">
    <button type="button" class="btn btn-sm btn-default" onclick="window.close()">Close</button>
    <button type="button" class="btn btn-sm btn-info float-right" onclick="msgAction()" id="msgSendBtn">보내기</button>
  </div>
</div>
</form>
@endif
@endsection

@section('javascript')
<script>
  $('select').select2({
    minimumResultsForSearch: Infinity
  });

  function msgAction()
  {
    
    if($('#recv_id').val()=='')
    {
      alert('받는 사람을 입력해주세요.');
      $('#recv_id').focus();
      return false;
    }
    if($('#title').val()=='' && $('#contents').val()=='')
    {
      alert('제목과 내용 중 하나는 꼭 입력해주세요.');
      $('#title').focus();
      return false;
    }

    var conLength = $('#contents').val().length;
    if(conLength>1500)
    {
      alert('메시지는 최대 1500자까지 가능합니다. 내용을 확인해주세요.\n(현재 글자수 : '+conLength+')');
      $('#contents').focus();
      return false;
    }

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var postdata = $('#myMsgForm').serialize();
    $.ajax({
      url  : "/intranet/msgaction",
      type : "post",
      data : postdata,
      success : function(result)
      {
        alert("전송 완료 되었습니다.");
        window.close();
      },
      error : function(xhr)
      {
        alert("통신오류입니다. 관리자에게 문의해주세요.");
      }
    });
  }

  function setBranchView(bchId){
    $('.bch-tb').css('display','none');
    $('.bch-'+bchId).css('display','');
  }
  
  function setBchChk(bchId)
  {
    //전체 체크 여부
    if($("input:checkbox[id='chk_bch_"+bchId+"']").is(":checked") == true){ 
      $("input[type='checkbox'].bch-"+bchId+"-chk").prop("checked", true);
      $("input[type='checkbox'].bch-"+bchId+"-chk").attr("disabled", "disabled");
    }else{
      $("input[type='checkbox'].bch-"+bchId+"-chk").prop("checked", false); 
      $("input[type='checkbox'].bch-"+bchId+"-chk").removeAttr("disabled");
    }
    setSelectBch('bch_'+bchId);
    
  }

  function setSelectBch(sel){
   
    var sel2 = $("#recv_id").select2();
    var sel2_val=sel2.val();

    //전체 체크 여부
    if($("input:checkbox[id='chk_"+sel+"']").is(":checked") == true){ 
      sel2_val.push(sel);
    }else{
      const idx = sel2_val.indexOf(sel);
      if (idx > -1) sel2_val.splice(idx, 1);
    }
    sel2.val(sel2_val).trigger("change");
  }

  function msgLinkOpen(link)
  {
    window.open(link,'msgpop','width=' + screen.width + ',' +'height=' + screen.height + ',fullscreen=yes');
  }

  $(document).ready(function(){

    var strWidth;
    var strHeight;

    //innerWidth / innerHeight / outerWidth / outerHeight 지원 브라우저 
    if ( window.innerWidth && window.innerHeight && window.outerWidth && window.outerHeight )
    {
      strWidth  = $('#container').outerWidth()  + (window.outerWidth - window.innerWidth);
      strHeight = $('#container').outerHeight() + 30;
      //alert((window.outerHeight));
      //alert((window.innerHeight));
    }
    else
    {
      var strDocumentWidth = $(document).outerWidth();
      var strDocumentHeight = $(document).outerHeight();
      window.resizeTo( strDocumentWidth, strDocumentHeight );

      var strMenuWidth = strDocumentWidth - $(window).width();
      var strMenuHeight = strDocumentHeight - $(window).height();

      strWidth = $('#container').outerWidth() + strMenuWidth;
      strHeight = $('#container').outerHeight() + strMenuHeight;
    }

    strWidth  = 600;
    //strHeight = 620;

    window.resizeTo( strWidth, strHeight+58 );
  });

  @if(isset($re_title) && $re_title!='')
    $('#contents').focus();
  @endif

@if($msgDiv=='RECV')
  function msgDelete()
  {   
    $.ajaxSetup({
          headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });

    $.ajax({
      url  : "/intranet/lumpmsgaction",
      type : "post",
      data : {
        mode  : 'RDEL',
        no : "{{ $result->no }}",
      },
      success : function(result)
      {
        if( result.rst=="Y" )
        {
          alert("정상처리되었습니다.");
          opener.location.reload();
          window.close();
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
  function msgReply()
  {
    window.open("/intranet/msgpop?to={{ $result->send_id }}&re_title={{ $result->title ?? '' }}", "msgInfo", "width=600, height=800, scrollbars=no");
  }
@elseif ($msgDiv=='SEND_MSG') {
  function msgDelete()
  {   
    $.ajaxSetup({
          headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });

    $.ajax({
      url  : "/intranet/lumpmsgaction",
      type : "post",
      data : {
        mode  : 'SDEL',
        no : "{{ $result->no }}",
      },
      success : function(result)
      {
        if( result.rst=="Y" )
        {
          alert("정상처리되었습니다.");
          opener.location.reload();
          window.close();
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
}
@endif
</script>
@endsection











