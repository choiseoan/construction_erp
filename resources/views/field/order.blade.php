@extends('layouts.master')


@section('content')
@include('inc/list')
<!-- 계약명세 모달 -->
@endsection


@section('lump')
일괄처리할거 입력
@endsection

@section('javascript')

<div class="modal fade" id="orderModal">
    <div class="modal-dialog modal-sm">
      <div class="modal-content" id="orderModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script>

// modal show 동작
function orderForm()
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#orderModal").modal('show');
	$("#orderModalContent").html(loadingString);
	$.post("/field/orderform", { no: 0 }, function (data) {
		$("#orderModalContent").html(data);
	});
}

</script>
@endsection