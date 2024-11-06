@extends('layouts.master')

@section('content')
@include('inc/list')
@endsection

@section('javascript')
<script>

// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
      if (event.keyCode === 13)
      {
        event.preventDefault();
        listRefresh();
      };
    });

    $("input[data-bootstrap-switch]").each(function() {
    $(this).bootstrapSwitch('state', $(this).prop('checked'));
  });
}

enterClear();

//예약된 엑셀을 다운로드 해보자
function excelDownload(excel_no, filename, record_count, down_filename, excel_down_div, origin_filename)
{
    var f = document.getElementById('form_reservationdetail');
    $( "<input>",{type:'hidden',name:'excel_no',value:excel_no}).appendTo(f);
    $( "<input>",{type:'hidden',name:'filename',value:filename}).appendTo(f);
    $( "<input>",{type:'hidden',name:'record_count',value:record_count}).appendTo(f);
    $( "<input>",{type:'hidden',name:'down_filename',value:down_filename}).appendTo(f);
    $( "<input>",{type:'hidden',name:'excel_down_div',value:excel_down_div}).appendTo(f);
    $( "<input>",{type:'hidden',name:'origin_filename',value:origin_filename}).appendTo(f);
    f.action = "/config/exceldown";
    f.method = 'POST';
    f.submit();
}

</script>

@endsection