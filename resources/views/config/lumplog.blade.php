@extends('layouts.master')
@section('content')
@include('inc/list')

<!-- Main content -->


@endsection

@section('javascript')
<script>
// 부서에 따른 담당자 세팅 
function setMemBranch(id,branch_code)
{
    var arr_list = @json($arr_list);
    var arr_list_name = @json($arr_list_name);
    var setId = "#"+id;
   
    $(setId).empty();
    $(setId).append("<option value=''>작업자</option>");
 
    console.log(setId);
    console.log(arr_list);
    console.log(arr_list_name);
   
    if(branch_code)
    {
        arr_list[branch_code].forEach(function(val){
            arr_list_name[val].forEach(function(val2){
                $(setId).append('<option value="'+val2+'">'+val+'</option>');
            })
        });

        if(id=="reg_id") 
        {
            $(setId).selectpicker({
                width: '5%',
                style: 'btn-default form-control-sm bg-white',
            });   
            $(setId).selectpicker('refresh');
        }
    }
}



</script>
@endsection