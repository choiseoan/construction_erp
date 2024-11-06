<div class="modal fade" id="costModal">
    <div class="modal-dialog modal-sl">
      <div class="modal-content" id="costModalContent">
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

<!-- 일위대가 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <h6 class="card-title">일위대가</h6>
        <div class="card-tools pr-2">
        </div>
    </div>
    @include('inc/list')
</div>

<script>

setInputMask('class', 'moneyformat', 'money');

getDataList('{{ $result['listName'] }}', '{{ $result['page'] ?? 1 }}', '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());

// 자재단가표 일괄삭제
function managementCostAllClear(contract_info_no)
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

	$.post("/field/managementcostallclear", { contract_info_no: contract_info_no }, function (data)
    {
        alert('삭제 완료하였습니다.');
        getManagementData('managementcost');
	});
}

// 일위대가 모달
function managementCostForm(contract_info_no)
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#costModal").modal('show');
	$("#costModalContent").html(loadingString);
	$.post("/field/managementcostform", { contract_info_no: contract_info_no}, function (data) {
		$("#costModalContent").html(data);
	});
}

// 일위대가 엑셀업로드
function managementCostExcelForm(contract_info_no)
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#excelUploadModal").modal('show');
	$("#excelUploadModalContent").html(loadingString);
	$.post("/field/managementcostexcelform", { contract_info_no: contract_info_no }, function (data) {
		$("#excelUploadModalContent").html(data);
	});
}

</script>