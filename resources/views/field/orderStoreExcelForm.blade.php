<form class="form-horizontal" role="form" name="excel_upload_form" id="excel_upload_form" method="post">
    @csrf
    <input type="hidden" id="order_info_no" name="order_info_no" value="{{ $order_info_no }}">
    <br>
    <div class="col-md-12">
        <div class="card card-outline card-lightblue">        
            <div class="card-header p-1">
                <h3 class="card-title"><i class="fas fa-user m-2" size="9px">입고수량 엑셀업로드</i>
            </div>

            <div class="card-body p-1">
                <div class="input-group input-group-sm ">
                    <button type="button" class="btn btn-sm btn-info float-right ml-1" id="sampleBtn" onclick="downSample();">샘플다운로드</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <!--파일선택-->
                    <div class="btn-xs btn-default btn-file">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" name="excel_data" id="excel_data"  value=""   >
                    </div>

                    <div class="input-group-append">
                        <button type="button" class="btn btn-sm btn-info" onclick="saveExcel();" >등록</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-sm btn-default float-right" data-dismiss="modal">Close</button>
    </div>
</form>

<script>

    //엑셀 등록 정보 가져오기
    function saveExcel()
    {
        var form     = $('#excel_upload_form')[0];
        var formData = new FormData(form);
        var files    = $('#excel_upload_form')[0].files;

        // 중복클릭 방지
        if(ccCheck()) return;

        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
        $.ajax({
            url:"/field/orderstoreexcelaction",
            type:"POST",
            data:formData,
            processData:false,
            contentType:false,
            dataType : 'json',
            success:function(data)
            {
                if(data.rs_code == 'Y')
                {
                    alert(data.rs_msg);
    
                    $(".modal-backdrop").remove();
                    $("#excelUploadModal").modal('hide');
                    
                    getOrderData('orderstore');

                    globalCheck = false;
                }
                else
                {
                    alert(data.rs_msg);

                    globalCheck = false;
                }
            },
            error:function(request,status,error)
            {
                alert("[ERROR]관리자에게 문의하세요!");
                globalCheck = false;
            }
        });
    }

    function downSample()
    {
        location.href='/field/orderstoreexcelsample';
    }

</script>