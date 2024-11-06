
 
<div class="card-body p-0">  
    <div class="table-responsive mailbox-messages" id="msglist">
        <table class="table table-hover table-striped table-sm">
        <col width="6%" />
        <col width="5%" />

        <col width="10%" />
        <col width="10%" />
        <col width="" />
        <col width="12%" />
        @if( $mdiv=="send" )
        <col width="12%" />
        @endif
        <col width="12%" />
        <col width="6%" />
            <thead>
                <tr >
                    <th><input type='checkbox' name='check-all'class='check-all' value=''></th>
                    <th></th>
                    <th>받는이</th>
                    <th>보낸이</th>
                    <th>제목</th>
                    <th>보낸시간</th>
                    @if( $mdiv=="send" )
                    <th>예약시간</th>
                    @endif
                    <th>읽은시간</th>
                    <th>링크</th>
                </tr>    
            </thead>
            <tbody>
                @forelse( $result as $v )
                    <tr onclick="setMsgForm({{$v->no}});" class="hand">
                    <td><input type='checkbox' name='listChk[]'  class='list-check' value='{{ $v->no ?? '' }}' ></td>
                    <td>
                        @if( $v->msg_type=="M" )
                            <i class="fas fa-envelope mr-2 text-{{ $v->msg_level ?? 'gray' }}"></i>
                        @elseif ( $v->msg_type=="N" )
                            <i class="fas fa-bullhorn mr-2 text-{{ $v->msg_level ?? 'gray' }}"></i>
                        @elseif ( $v->msg_type=="S" )
                            <i class="fas fa-bell mr-2 text-{{ $v->msg_level ?? 'gray' }}"></i>
                        @endif
                    </td>
                    <td class="mailbox-name">{{ $infoUser[$v->recv_id]->name ?? '' }}({{ $v->recv_id }})</td>
                    <td class="mailbox-name">{{ $infoUser[$v->send_id]->name ?? '' }}({{ $v->send_id }})</td>
                    <td class="mailbox-title">{{ $v->title }} @if($v->recv_time=='')<span class="right badge badge-danger ml-2">New</span>@endif</td>
                    <td class="mailbox-date">{{ Func::dateFormat($v->send_time) }}</td>
                    @if( $mdiv=="send" )
                    <td class="mailbox-date">{{ ( $v->send_time==$v->reserve_time ) ? "" : Func::dateFormat($v->reserve_time) }}</td>
                    @endif
                    <td class="mailbox-date">{{ Func::dateFormat($v->recv_time) }}</td>
                    <td class="mailbox-attachment">
                        @if(isset($v->msg_link))<a class="fas fa-paperclip" href="#"></a>@endif
                        @if(isset($v->json_link))<a class="fas fa-paperclip" href="#"></a>@endif
                    </td>
                    </tr>
                @empty
                    @if($mdiv=="send")
                    <tr>
                        <td colspan=9 class='text-center p-4'>보낸쪽지가 없습니다.</td>
                    </tr>
                    @elseif($mdiv=='recv')
                    <tr>
                        <td colspan=9 class='text-center p-4'>받은쪽지가 없습니다.</td>
                    </tr>
                    @endif
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- /.mail-box-messages -->
</div>
<!-- /.card-body -->

<div class="card-footer p-2">
    <!-- /.btn-group -->
    <button type="button" class="btn btn-default btn-sm" onclick="setMsgList({{$page['start'] ?? 0}})">
        <i class="fas fa-sync-alt"></i>
    </button>
    <div class="float-right" >
        {{ $pageTerm }} / {{$page['total']}}
        <div class="btn-group ml-2">
            <button type="button" class="btn btn-default btn-sm" onclick="setMsgList({{$page['before'] ?? 0}})">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button type="button" class="btn btn-default btn-sm" @if( !isset($page['next']) ) disabled @else onclick="setMsgList({{$page['next']}})" @endif>
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <!-- /.btn-group -->
    </div>
    <!-- /.float-right -->
</div>
<script>

$('input[name="check-all"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});

$('input[name="listChk[]"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});

// 리스트 전체 선택/해제
$('#msglist').on('ifChecked', '.check-all', function(event) {
    $('.list-check').iCheck('check');
});
$('#msglist').on('ifUnchecked', '.check-all', function(event) {
    $('.list-check').iCheck('uncheck');
});

</script>