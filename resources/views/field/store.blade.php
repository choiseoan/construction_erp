@extends('layouts.master')


@section('content')
@include('inc/list')
<!-- 계약명세 모달 -->
@endsection


@section('lump')
일괄처리할거 입력
@endsection

@section('javascript')

<script>

// 리스트 클릭 팝업
function storePop(order_info_no, no)
{
    window.open("/field/orderpop?no="+order_info_no,"orderpop","'','','','', scrollbars=yes");
}

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
</script>
@endsection