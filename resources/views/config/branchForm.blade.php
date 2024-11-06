<form class="form-horizontal" role="form" name="br_form" id="br_form" method="post">
<input type="hidden" id="mode" name="mode" value="{{ $mode }}">
<input type="hidden" id="save_status" name="save_status" value="{{ $v->save_status ?? 'Y' }}">

<div class="form-group row">
  <label for="code" class="col-sm-2 col-form-label">부서코드</label>
  <div class="col-sm-4">
    <input type="text" class="form-control form-control-sm" id="code" name="code" placeholder="숫자코드" {{ $readonly }} value="{{ $v->code ?? '' }}">
  </div>
</div>

<div class="form-group row">
  <label for="parent_code" class="col-sm-2 col-form-label">상위부서</label>
  <div class="col-sm-4">
    @if( $mode=='UPD' && $v->parent_code == 'TOP' )
    <label class="col-form-label">최상위</label>
    <input type="hidden" id="parent_code" name="parent_code" value="TOP">
    @else
    <select class="form-control select2 form-control-sm" style="width: 100%;" id="parent_code" name="parent_code">
    {{ Func::printOptionArray($array_branch, 'branch_name', $v->parent_code ?? '001') }}
    </select>
    @endif
  </div>
  <label for="branch_name" class="col-sm-2 col-form-label">부서명</label>
  <div class="col-sm-4">
    <input type="text" class="form-control form-control-sm" id="branch_name" name="branch_name" placeholder="한글등록" value="{{ $v->branch_name ?? '' }}">
  </div>
</div>

<div class="form-group row">
  <label for="ceo_name" class="col-sm-2 col-form-label">지점장</label>
  <div class="col-sm-4">
    <select class="form-control select2 form-control-sm" style="width: 100%;" id="ceo_name" name="ceo_name">
      <option value=''>지점장</option>
      {{ Func::printOption($arrayUsers, $v->ceo_name ?? '') }}
    </select>
  </div>
</div>

<div class="form-group row">
  <label for="phone" class="col-sm-2 col-form-label">전화번호</label>
  <div class="col-sm-4">
    <input type="text" class="form-control form-control-sm phformat" id="phone" name="phone" placeholder="숫자만입력" value="{{ $v->phone ?? '' }}">
  </div>

  <label for="phone" class="col-sm-2 col-form-label">예비번호</label>
  <div class="col-sm-4">
    <input type="text" class="form-control form-control-sm phformat" id="phone_extra" name="phone_extra" placeholder="숫자만입력" value="{{ $v->phone_extra ?? '' }}">
  </div>
</div>

</form>
