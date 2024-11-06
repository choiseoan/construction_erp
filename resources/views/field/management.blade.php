@extends('layouts.master')


@section('content')
@include('inc/list')
<!-- 계약명세 모달 -->
@endsection


@section('lump')
일괄처리할거 입력
@endsection

@section('javascript')

<div class="modal fade" id="managementModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" id="managementModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

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

// modal show 동작
function managementForm()
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#managementModal").modal('show');
	$("#managementModalContent").html(loadingString);
	$.post("/field/managementform", { no: 0 }, function (data) {
		$("#managementModalContent").html(data);
	});
}

enterClear();
</script>
@endsection