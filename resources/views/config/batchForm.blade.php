<?
// 단순 숫자배열
$arrayConfDate = ['minute'=>'분', 'hour'=>'시', 'day'=>'일', 'month'=>'월', 'week'=>'요일'];
// 분
$minute = null;
for($i=0; $i<60; $i++)
    $minute[$i] = $i.'분';

// 시
$hour = null;
for($i=0; $i<24; $i++)
    $hour[$i] = $i.'시';

// 일
$day = null;
for($i=1; $i<=31; $i++)
    $day[$i] = $i.'일';

// 월
$month = null;
for($i=1; $i<=12; $i++)
    $month[$i] = $i.'월';

// 요일
$week = [1=>'월', 2=>'화', 3=>'수', 4=>'목', 5=>'금', 6=>'토', 0=>'일'];


?>
<form class="form-horizontal" role="form" name="batchInForm" id="batchInForm" method="post">
<input type="hidden" id="mode" name="mode" value="{{ $mode ?? 'INS' }}">
<input type="hidden" id="status" name="status" value="{{ $v->status ?? 'Y' }}">


<div class="form-group row">
    <label for="bank_name" class="col-sm-2 col-form-label text-center">작업ID</label>
    <div class="col-sm-4">
        <input type="text" class="form-control form-control-sm" id="no" name="no" value="{{ $v->no ?? '' }}" readonly >
    </div>
    <label for="bank_name" class="col-sm-2 col-form-label text-center">사용여부</label>
    <div class="col-sm-4">
        <label class="col-sm-1">
        <input type="checkbox" name="use_yn" data-toggle="toggle" id="use_yn" data-bootstrap-switch value="Y" {{ ( isset($v->use_yn) && $v->use_yn=='Y' ) ? 'checked' : '' }}>
    </label>
    </div>
</div>

<div class="form-group row">
    <label for="ssn11" class="col-sm-2 col-form-label text-center"><span class="text-danger font-weight-bold h6 mr-1">*</span>작업명</label>
    <div class="col-sm-4">
        <input type="text" class="form-control form-control-sm" id="sch_name" name="sch_name" placeholder="작업명" value="{{ $v->sch_name ?? '' }}" >
    </div>
    <label for="ssn11" class="col-sm-2 col-form-label text-center"><span class="text-danger font-weight-bold h6 mr-1">*</span>작업명령</label>
    <div class="col-sm-4">
        <input type="text" class="form-control form-control-sm" id="sch_command" name="sch_command" placeholder="Batch:batchExcute" value="{{ $v->sch_command ?? '' }}" >
    </div>
</div>


<div class="form-group row">
    <label for="execTerm" class="col-sm-2 col-form-label text-center" ><span class="text-danger font-weight-bold h6 mr-1">*</span>실행주기 <i class="fas fa-question-circle hand" id="help" onClick="getHelp();"></i></label>    
    <div class="col-sm-10">
        
        <table width="100%">
        <tr>
        @foreach($arrayConfDate as $k=>$val)
            <td>
            <select class="form-control form-control-sm selectpicker" name="sch_{{ $k }}s[]" id="sch_{{ $k }}s" multiple data-selected-text-format="count > 5" data-live-search="true" title="{{ $val }} 선택" onChange="chkStar('sch_{{ $k }}');">
                <option value="*">전체(*)</option>
                {{ Func::printOptionMulti(${$k}) }}
            </select>  
            </td>
        @endforeach
        </tr>
        <tr>
            <td>(분) <input type="text" class="form-control form-control-sm" id="sch_minute" name="sch_minute" value="{{ $v->sch_minute ?? '' }}" placeholder="0~59"></td>
            <td>(시) <input type="text" class="form-control form-control-sm" id="sch_hour" name="sch_hour" value="{{ $v->sch_hour ?? '' }}" placeholder="0~23"></td>
            <td>(일) <input type="text" class="form-control form-control-sm" id="sch_day" name="sch_day" value="{{ $v->sch_day ?? '' }}" placeholder="1~31"></td>
            <td>(월) <input type="text" class="form-control form-control-sm" id="sch_month" name="sch_month" value="{{ $v->sch_month ?? '' }}" placeholder="1~12"></td>
            <td>(요일) <input type="text" class="form-control form-control-sm" id="sch_week" name="sch_week" value="{{ $v->sch_week ?? '' }}" placeholder="0:일요일~6:토요일"></td>
        </tr>
        </table>
    </div>
</div>

<div class="form-group row">
    <label for="sch_note" class="col-sm-2 col-form-label text-center">비고</label>
    <div class="col-sm-10">
        <input type="text" class="form-control form-control-sm" id="sch_note" name="sch_note" placeholder="비고" value="{{ $v->sch_note ?? '' }}" >
    </div>
</div>

</form>