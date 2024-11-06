<div class="col-12"> 
    <div class="card card-primary card-outline ">
        <!-- /.card-header -->
        <div class="card-body p-0 ">
            <div class="mailbox-read-info " id="board_title"  style="padding:11px;">
                <div class="row">
                    <div class="h4 col-6" > 
                        @if($board->emergency_yn=='1')
                        <span class='text-red'>(긴급)</span>
                        @endif
                        [{{ $board->no }}] 
                        {{ $board->title ?? '제목없음'}}
                    </div>
                    <div class="h6 col-4 text-right" style="margin-top:8px;">
                        @if(isset($sta))
                        상태 : {{ $sta }}    &nbsp; / &nbsp; 
                        @endif
                        작성자 : {{$board->name ?? ''}} ( {{ $board->save_id ?? ''}} ) 
                    </div>
                    <div class="col-2 text-right mailbox-read-time" style="margin-top:13px;">{{ Func::dateFormat($board->save_time) }}</div>
                </div>
            </div>
            <div class="mailbox-controls with-border text-right">
                @if( Auth::user()->id == $board->save_id || $board_admin)
                <button type="button" class="btn btn-default btn-sm" data-container="body" title="글수정" onclick="modalAction('{{ $board->no ?? '' }}','{{ $board->div ?? '' }}', '{{ $board->status ?? '' }}', '{{ $board->emergency_yn ?? '' }}')">
                <i class="fas fa-edit"></i>
                </button>
                @endif
                <button type="button" class="btn btn-default btn-sm" title="프린트" onclick="printView();">
                  <i class="fas fa-print"></i>
                </button>
              
                <button type="button" class="btn btn-default btn-sm" title="다시 불러오기" onclick="boardView({{$board->no ?? ''}})">
                    <i class="fas fa-redo-alt"></i>
                </button>
               
                <button type="button" class="btn btn-default btn-sm text-red" title="목록으로" onclick="backBoardList()">
                    <i class="fas fa-list"></i>
                </button>
                </div>
            </div>
            <!-- /.mailbox-controls -->
            <div class="mailbox-read-message " id="board_contents">
                {!! $board->contents ?? '' !!}
            </div>
            <!-- /.mailbox-read-message -->
        </div>
        <hr>
        <!-- /.card-body -->
        <div class="card-footer bg-white ">
            <ul class="mailbox-attachments d-flex align-items-stretch clearfix">
                @forelse( $board_file as $data )
                <li>
                    @if(Func::checkImg($data->file_ext))
                    <div class="col-12 " style="height:130px;background-image: url('/intranet/board/filedown/{{ $data->no}}');background-size:contain;background-repeat:no-repeat;background-position: center;">
                    &nbsp;</div>
                    @else
                    <span class="mailbox-attachment-icon"><i class="far fa-file-alt"></i></span>
                    @endif
                    <div class="mailbox-attachment-info">
                        <a href="/intranet/board/filedown/{{ $data->no}}" class="mailbox-attachment-name">{{ $data->file_origin}}</a>
                        <span class="mailbox-attachment-size clearfix mt-1">
                            <a href="/intranet/board/filedown/{{ $data->no}}" class="btn btn-default btn-sm float-right"><i class="fas fa-download"></i></a>
                        </span> 
                    </div>
                </li>
                @empty
                @endforelse
            </ul>
        </div>
        <div class="card-footer card-comments" >
            <div id="cmt_div"> 
            @forelse( $board_cmt as $cmt )
                <div class="card-comment ">
                    <!-- User image -->
                    <div class="comment-text">
                        <span class="username">
                        {{$cmt->name ?? ''}} ( {{ $cmt->save_id }} )
                            <span class="text-muted float-right">{{  Func::dateFormat($cmt->save_time) }}</span>
                        </span> 
                        {!! $cmt->comment !!}
                        @if( Auth::user()->id == $cmt->save_id || $board_admin)
                        <div style="float:right;">
                            <button type="button" class="btn btn-tool" onclick="boardCmtAction({{ $board->no ?? '' }},'{{ $cmt->no ?? 'N' }}')" >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endif
                    </div>
                    <!-- /.comment-text -->
                </div>
            @empty
            @endforelse
            </div>
            <!-- /.card-footer -->
            <div class="card-footer" >
                <div class="input-group">
                    <input type="text" name="board_cmt" id="board_cmt" placeholder="댓글을 입력해주세요." class="form-control">
                    <span class="input-group-append" >
                        <button type="comment" class="btn btn-default btn-sm" onclick="boardCmtAction({{ $board->no ?? '' }},'N')">등록</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
//뒤로가기 구현
// $(window).on('popstate', function(){
//     $(location).attr('href','/intranet/board/{{ $board->div}}');
//   });
</script>