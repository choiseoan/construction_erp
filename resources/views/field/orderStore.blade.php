<div class="modal fade" id="storeModal">
    <div class="modal-dialog modal-sl">
      <div class="modal-content" id="storeModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!--엑셀업로드 -->
<div class="modal fade" id="excelUploadModal">
    <div class="modal-dialog modal-sl">
      <div class="modal-content" id="excelUploadModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!--입고수량 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <h6 class="card-title">입고수량</h6>
        <div class="card-tools pr-2">
        </div>
    </div>
    @include('inc/list')
</div>

<script>

setInputMask('class', 'moneyformat', 'money');

getDataList('{{ $result['listName'] }}', '{{ $result['page'] ?? 1 }}', '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());

// 입고수량 일괄삭제
function orderStoreAllClear(order_info_no)
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

	$.post("/field/orderstoreallclear", { order_info_no: order_info_no }, function (data)
    {
        alert('삭제 완료하였습니다.');
        getOrderData('orderstore');
	});
}

// 입고수량 모달
function orderStoreForm(order_info_no, store_no)
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#storeModal").modal('show');
	$("#storeModalContent").html(loadingString);
	$.post("/field/orderstoreform", { order_info_no: order_info_no, store_no: store_no}, function (data) {
		$("#storeModalContent").html(data);
	});
}

// 입고수량 엑셀업로드
function orderStoreExcelForm(order_info_no)
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#excelUploadModal").modal('show');
	$("#excelUploadModalContent").html(loadingString);
	$.post("/field/orderstoreexcelform", { order_info_no: order_info_no }, function (data) {
		$("#excelUploadModalContent").html(data);
	});
}

</script>