

<form class="form-horizontal" role="form" name="subCodeInputForm" id="subCodeInputForm" method="post">
<input type="hidden" name="cat_code"  value="{{ $cat_code }}">
<input type="hidden" name="conf_code"  value="{{ $conf_code }}">
    
        <div class="row" >
           
            <div class="text-left col-md-10">
                [ 카테고리 : {{ $cat_code }} ]
                &nbsp; 
                [ 코드 : {{ $conf_code }} ]
            </div>
            <div class="text-right col-md-2">
                
                <button type="button" class="btn btn-default btn-sm text-xxs" id="subAddrow" onclick="addSubRow()">
                    <i class="fas fa-plus-circle p-1 text-green"></i>추가
                </button>                
            </div>
        </div>
    
        <div class="p-0">
            <table class="table table-sm table-hover loan-info-table card-secondary card-outline " id="law_cost_table">
                <thead>
                    <tr>
                        <th class="text-center" style='width:30%'>하위코드</th>
                        <th class="text-center" style='width:30%'>코드명</th>
                        <th class="text-center" style='width:20%'>정렬</th>
                        <th class="text-center" style='width:20%'>삭제</th>
                    </tr>
                </thead>
                <tbody  id="sub_body">
                <? $i = 1; ?>
                @forelse ($sub_v as $idx => $sv)
                    <tr id="addRow{{ $i }}">
                        <td class="text-center"><input type="text" class="form-control form-control-sm text-center"  name="sub_code[]" value="{{ $sv->sub_code }}" readonly></td>
                        <td class="text-center"><input type="text" class="form-control form-control-sm text-center"  name="sub_code_name[]" value="{{ $sv->sub_code_name }}" ></td>
                        <td class="text-center"><input type="text" class="form-control form-control-sm text-center"  name="code_order[]" value="{{ $sv->code_order ?? $i }}" ></td>    
                        <td class="text-center"><button onclick="delRow({{ $i++ }})" type='button' class='btn btn-default btn-sm text-xxs'><i class='fas fa-minus-circle p-1 text-red'></i>삭제</button></td>    
                    </tr>
                @empty
     
                @endforelse
    
                </tbody>
            </table>
    
    
    
        </div>
    
    
    
    
</form>

<script>
var i = {{ $i }};

function addSubRow()
{
    var rowString = '<tr id="addRow'+i+'">'+
                        '<td class="text-center"><input type="text" class="form-control form-control-sm text-center"  name="sub_code[]" value=""></td>'+
                        '<td class="text-center"><input type="text" class="form-control form-control-sm text-center"  name="sub_code_name[]" value="" ></td>'+
                        '<td class="text-center"><input type="text" class="form-control form-control-sm text-center"  name="code_order[]" value="'+i+'" ></td>'+
                        '<td class="text-center"><button onclick="delRow('+i+')" type="button" class="btn btn-default btn-sm text-xxs"><i class="fas fa-minus-circle p-1 text-red"></i>삭제</button></td>'+
                    '</tr>';

    $('#sub_body').append(rowString);
    afterAjax();

    i++;
}

function delRow(id)
{
    $('#addRow'+id).remove();
}
</script>