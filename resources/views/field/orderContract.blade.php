<div class="modal fade" id="contractModal">
    <div class="modal-dialog modal-sl">
      <div class="modal-content" id="contractModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="excelUploadModal">
    <div class="modal-dialog modal-sl">
      <div class="modal-content" id="excelUploadModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- 계약수량 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <h6 class="card-title">계약수량</h6>
        <div class="card-tools pr-2">
        </div>
    </div>
    @include('inc/list')
</div>

<script>

setInputMask('class', 'moneyformat', 'money');

getDataList('{{ $result['listName'] }}', '{{ $result['page'] ?? 1 }}', '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());

// 계약수량 일괄삭제
function orderContractAllClear(order_info_no)
{
    if(!confirm('정말 삭제하시겠습니까?'))
    {
        return false;
    }

  $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$.post("/field/ordercontractallclear", { order_info_no: order_info_no }, function (data)
    {
        alert('삭제 완료하였습니다.');
        getOrderData('ordercontract');
	});
}

// 계약수량 모달
function orderContractForm(order_info_no, contract_no)
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#contractModal").modal('show');
	$("#contractModalContent").html(loadingString);
	$.post("/field/ordercontractform", { order_info_no: order_info_no, contract_no: contract_no }, function (data) {
		$("#contractModalContent").html(data);
	});
}

// 자재단가표 엑셀업로드
function orderContractExcelForm(order_info_no)
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#excelUploadModal").modal('show');
	$("#excelUploadModalContent").html(loadingString);
	$.post("/field/ordercontractexcelform", { order_info_no: order_info_no }, function (data) {
		$("#excelUploadModalContent").html(data);
	});
}

</script>