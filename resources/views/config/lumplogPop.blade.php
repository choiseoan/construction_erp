@extends('layouts.masterPop')
@section('content')

<?
 $division =  Vars::$arrayLumpLogPopList;
?>
<div class="col-12">
    <div class="card">
        <form id="file_data" name="file_data" >
        @csrf
        <div class="card-header">
            <h3 class="card-title"><i class="far fa-list-alt"></i> {{$division[$div] ?? '' }} </h3>
            <div class="card-tools">
                <div class="input-group input-group-sm ">                         
                    <button type="button" class="btn btn-sm btn-info float-right ml-1" id="sampleBtn" onclick="location.href = '/config/lumplogsample?division={{$div}}';">{{$division[$div] ?? '' }} 샘플다운로드</button>
                    <!--파일선택-->
                    <div class="btn-xs btn-default btn-file ">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" name="lump_log_data" id="lump_log_data"  value=""   >
                    </div>

                    <div class="input-group-append">  
                        <button type="button" class="btn btn-sm btn-info float-right ml-2" onclick="getPopList('read','{{$div}}')" >등록</button>
                    </div>
                    <div class="input-group-append">  
                    <button type="button" class="btn btn-sm btn-danger"  style="margin-left:5px;" onclick="tradeActionEnd();">닫기</button>
                </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="pop_action" id="pop_action" vlaue="">
        </form>
        
        @include('inc/list')
    </div>
</div>
@endsection



@section('javascript')



<!-- Summernote -->
<script>
    $(document).ready(function(){
        window.resizeTo( 1500, 710 );  
    });
    //엑셀 등록 정보 가져오기
    function getPopList(action, div)
    {
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
       
        var form        = $('#file_data')[0];
        var fd          = new FormData(form);
        var files       = $('#lump_log_data')[0].files;
        var fileName    = $('#lump_log_data')[0].name;
        var ext         = $('#lump_log_data').val().split('.').pop().toLowerCase();

        fd.append("action", action);
        fd.append("division", div);

        if(files.length > 0 )
        {
            $.ajax({
                url:"/config/lumplogupload",
                type:"POST",
                data:fd,
                processData:false,
                contentType:false,
                dataType : 'json',
                success:function(data)
                {	
                    if(data.rs_msg!='')
                    {
                        alert(data.rs_msg);
                        location.reload();
                    }
                },
                error:function(request,status,error)
                {
                    alert("[ERROR]관리자에게 문의하세요!");
                }
            })
        }
        else
        {
            alert('업로드할 파일을 선택해주세요.');
            location.reload();
        }
    }

    function tradeActionEnd()
    {
        window.close();
    }

</script>
 @endsection
