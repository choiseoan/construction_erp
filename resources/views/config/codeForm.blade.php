
@if( $gubun=='CATE' )

<input type="hidden" id="gubun" name="gubun" value="{{ $gubun }}">
<input type="hidden" id="mode"  name="mode"  value="{{ $mode }}">

<div class="form-group row">

  <label for="cat_code" class="col-sm-3 col-form-label">카테고리코드</label>
  <div class="col-sm-9">
    <input type="text" class="form-control form-control-sm" id="cat_code" name="cat_code" placeholder="공백없는 영문 또는 숫자" {{ $readonly }} value="{{ $result->cat_code ?? '' }}">
  </div>

</div>

<div class="form-group row">
  <label for="cat_name" class="col-sm-3 col-form-label">카테고리명</label>
  <div class="col-sm-9">
    <input type="text" class="form-control form-control-sm" id="cat_name" name="cat_name" placeholder="한글등록" value="{{ $result->cat_name ?? '' }}">
  </div>
</div>

<div class="form-group row">
  <label for="cat_name" class="col-sm-3 col-form-label">비고</label>
  <div class="col-sm-9">
    <input type="text" class="form-control form-control-sm" id="cat_memo" name="cat_memo" placeholder="비고 및 키워드 입력(#)" value="{{ $result->cat_memo ?? '' }}">
  </div>
</div>




@else


<input type="hidden" id="gubun" name="gubun" value="{{ $gubun }}">
<input type="hidden" id="mode"  name="mode"  value="{{ $mode }}">
<input type="hidden" id="cat_code" name="cat_code"  value="{{ $cat_code }}">

<div class="form-group row">
  <label for="code" class="col-sm-1 col-form-label">코드</label>
  <div class="col-sm-3">
      <input type="text" class="form-control form-control-sm" id="code" name="code" placeholder="영문,숫자 공백없이" {{ $readonly }} value="{{ $result->code ?? '' }}">
  </div>
  <label for="code_order" class="col-sm-1 col-form-label">정렬순서</label>
  <div class="col-sm-2">
      <input type="text" class="form-control form-control-sm" id="code_order" name="code_order" placeholder="숫자 최대4자리" value="{{ $result->code_order ?? '' }}">
  </div>
  <div class="col-sm-5">
    <button type="button" class="btn btn-sm btn-primary" id="sub_code_btn" onclick="subCodeForm();">하위코드관리</button>
  </div>
</div>

<div class="form-group row">
  <label for="name" class="col-sm-1 col-form-label">코드명</label>
  <div class="col-sm-6">
      <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="코드설명" value="{{ $result->name ?? '' }}">
  </div>
  <div class="col-sm-5">
    [ 하위코드 : {{ $sub_cnt }}개 ]
  </div>
</div>

<div class="form-group row">
  <label for="note" class="col-sm-1 col-form-label">비고</label>
  <div class="col-sm-6">
      <input type="text" class="form-control form-control-sm" id="note" name="note" placeholder="비고" value="{{ $result->note ?? '' }}">
  </div>
</div>

@endif