<div class="modal fade" id="materialModal">
    <div class="modal-dialog modal-sl">
      <div class="modal-content" id="materialModalContent">
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

<!-- 자재단가표 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <h6 class="card-title">자재단가표</h6>
        <div class="card-tools pr-2">
        </div>
    </div>
    @include('inc/list')
</div>

<script>
$('.datetimepicker').datetimepicker({
    format: 'YYYY-MM-DD',
    locale: 'ko',
    useCurrent: false,
});

setInputMask('class', 'moneyformat', 'money');

getDataList('{{ $result['listName'] }}', '{{ $result['page'] ?? 1 }}', '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());

// 자재단가표 일괄삭제
function managementMaterialAllClear(contract_info_no)
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

	$.post("/field/managementmaterialallclear", { contract_info_no: contract_info_no }, function (data)
    {
        alert('삭제 완료하였습니다.');
        getManagementData('managementmaterial');
	});
}

// 자재단가표 모달
function managementMaterialForm(contract_info_no, material_no)
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#materialModal").modal('show');
	$("#materialModalContent").html(loadingString);
	$.post("/field/managementmaterialform", { contract_info_no: contract_info_no, material_no: material_no }, function (data) {
		$("#materialModalContent").html(data);
	});
}

// 자재단가표 엑셀업로드
function managementMaterialExcelForm(contract_info_no)
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#excelUploadModal").modal('show');
	$("#excelUploadModalContent").html(loadingString);
	$.post("/field/managementmaterialexcelform", { contract_info_no: contract_info_no }, function (data) {
		$("#excelUploadModalContent").html(data);
	});
}

</script>