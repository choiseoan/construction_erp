@extends('layouts.master')


@section('content')
@include('inc/list')

<!-- 협력사 모달 -->
<div class="modal fade" id="partnerModal">
    <div class="modal-dialog modal-sl">
      <div class="modal-content" id="partnerModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@endsection

@section('lump')
일괄처리할거 입력
@endsection

@section('javascript')

<script>

// 계약수량 모달
function partnerForm(partner_no)
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#partnerModal").modal('show');
	$("#partnerModalContent").html(loadingString);
	$.post("/field/partnerform", { partner_no: partner_no }, function (data) {
		$("#partnerModalContent").html(data);
	});
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