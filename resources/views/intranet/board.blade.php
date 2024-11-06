@extends('layouts.master')
@section('content')
<div id="boardListView" >
    @include('inc/list')
</div>
<div id="boardDiv" >
</div>
@endsection

@section('javascript')

<script>

  
	//	작업하기
	function workerChg(obj,no)
	{
    var target = obj.value; 

		$.ajaxSetup({
			headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
    console.log(worker);
		$.ajax({
			url  : "/intranet/board/saveworker",
			type : "post",
			data : {
				no         : no,
        target  : target
			},
			success : function(result)
			{
				if( result == "Y" )
				{
					alert("성공");
					listRefresh();
				}
				else
				{
					alert("실패");
				}

			},
			error:function(request, error) {

				alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error)

			}
		});
	}

  
  function printView()
  {
    var popupWindow = window.open("", "_blank" );
    popupWindow.document.write( "");        // debugbar와 충돌로 body 삭제
    popupWindow.document.write( $('#board_title').html() );
    popupWindow.document.write( '<div>' );
    popupWindow.document.write( $('#board_contents').html() );
    popupWindow.document.write( '</div>' ); // debugbar와 충돌로 body 삭제
    popupWindow.document.close();
    popupWindow.print();
  }

  // 게시글 보기
  function boardView(no)
  {
    $("#boardDiv").html(loadingString);
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
      url  : "/intranet/board/detail",
      type : "post",
      data : {
        no      : no
      },
      success : function(result)
      {
        $('#boardListView .content').css('display','none');
        $('#boardDiv').css('display','');
        $("#boardDiv").html(result);
      },
      error:function(request, error) {

        alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error)
    
      }
    });
  }
  // 댓글 등록
  function boardCmtAction(boardNo, no)
  {
    $("#cmt_div").html(loadingString);
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
      url  : "/intranet/boardcomment",
      type : "post",
      data : {
        comment      : $('#board_cmt').val(),
        board_no     : boardNo,
        no           : no
      },
      success : function(result)
      {
        boardView(boardNo);
      },
      error:function(request, error) {

        alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error)
      }
    });
  }

  // 게시글 보기
  function backBoardList()
  {
    $("#boardDiv").html(''); 
    $('#boardListView .content').css('display','');
    getDataList('', $("#nowPageboard").val(), '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());
    return false;
  }

  @if( $select_no>0 )
  $.isReady = true;
  boardView({{ $select_no }});
  @endif

</script>
@endsection