@extends('layouts.masterPop')

@section('content')
<div class="card" style="width:800px;">
    <div class="card-header">
        <h5 class="card-title" >
        <i class="fas fa-caret-right fa-search"></i> 직업코드 검색 
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class=" col-sm-10">
                <div class=" form-group">
                    <input type="text" class="form-control form-control-sm" maxlength=4 id="jobCodeText" name="jobCodeText" placeholder="직업코드 선택해주세요." readOnly  value="">
                </div>
            </div>
        </div>
        <div class="row">
            @for ($i = 1; $i < 4; $i++)
                <input type="hidden" name="jobcode{{ $i }}" id="jobcode{{ $i }}" value="">
                <input type="hidden" name="jobname{{ $i }}" id="jobname{{ $i }}" value="">
                <div class="col-md-3 table-responsive " id="jobList{{$i}}"  style="height:200px;">
                    @forelse($arrCodeList[$i] as $pcode => $subCode)
                        <table class="table table-hover table-sm jobList{{$i}} " id="jobList_{{$pcode}}" @if( $i > 1) style="display:none;" @endif  >
                            @foreach($subCode as $v)
                                <tr onclick="setJobView('{{ $v->jobcode ?? ''}}','{{ $v->jobname ?? ''}}','{{ $i }}')">
                                    <td class="jobList{{$i}}-td " id="jobList_td_{{ $v->jobcode ?? ''}}">
                                    ({{ substr($v->jobcode,0,1+(($i-1)*2)) ?? '' }}) {{ $v->jobname ?? '' }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @empty
                        등록된 코드가 없습니다.
                    @endforelse
                </div>
            @endfor
        </div>
    </div>
</div>
@endsection


@section('javascript')
<script>
var param = "{{ $jobId ?? ''}}";

/**
* 직업코드 리스트 보여주기
*/
function setJobView(jobId,jobName,seq){

    $('#jobcode'+seq).val(jobId);
    $('#jobname'+seq).val(jobName);

    // view reset
    for(var j=1; j<=3; j++){
        if(j>seq) $('.jobList'+j).css('display','none');
        if(j>=seq) $('.jobList'+j+'-td').removeClass('list-group-item-secondary'); 
    }
    
    $('#jobList_td_'+jobId).addClass('list-group-item-secondary');
    $('#jobList_'+jobId).css('display','');
    
    //직업코드 전체 text 만들기
    var code_text   = "";
    for(var i =1; i<=seq; i++){   
        if(i>1) code_text+='->';
        code_text   += $('#jobname'+i).val();
    }
    $('#jobCodeText').val(code_text);
    
    //최종코드 선택시 적용
    if(seq>1 && typeof $('#jobList_'+jobId).css('display')=="undefined"){
        if(confirm("적용하시겠습니까?")) setParentView(seq);
    }
}

/**
* 직업코드 부모창 값전달
*최종 코드 전달  :  jobId 파라미터
*[jobId]1~4 
*[jobId]name1~4 
*[jobId]str
*/
function setParentView(seq){
    if(param==''){
        alert('유효하지 않습니다. 다시 실행해주세요');
    }else{
        for(var i =1; i<=4; i++){
            $(opener.document).find("#"+param+i).val($('#jobcode'+i).val()); //find를 이용한 jquery
            $(opener.document).find("#"+param+"name"+i).val($('#jobname'+i).val()); //find를 이용한 jquery
        }
        $(opener.document).find("#"+param+"str").val($('#jobCodeText').val()); //find를 이용한 jquery
        $(opener.document).find("#"+param).val($('#jobcode'+seq).val()); //find를 이용한 jquery
    }

    window.close();
}
</script>

@endsection




