
@if( $gubun=='TOP' )

<input type="hidden" id="top_menu_gubun" value="{{ $gubun }}">
<input type="hidden" id="top_menu_mode"  value="{{ $mode }}">

<div class="form-group row">

<label for="top_menu_cd" class="col-sm-2 col-form-label">메뉴코드</label>
<div class="col-sm-4">
  <input type="text" class="form-control form-control-sm" id="top_menu_cd" placeholder="숫자3자리" {{ $readonly }} value="{{ $result->menu_cd ?? '' }}">
</div>
<label for="top_menu_icon" class="col-sm-2 col-form-label">아이콘</label>
<div class="col-sm-4">
  <div class="input-group input-group-sm">
    <input class="form-control" type="text" placeholder="아이콘코드" id="top_menu_icon" value="{{ $result->menu_icon ?? '' }}">
    <div class="input-group-append">
    <button class="btn btn-info btn-sm" onclick="window.open('https://fontawesome.com/icons?d=gallery&m=free');">
    <i class="fas fa-search"></i>
    </button>
    </div>
  </div>
</div>
</div>

<div class="form-group row">
<label for="top_menu_nm" class="col-sm-2 col-form-label">메뉴명</label>
<div class="col-sm-10">
  <input type="text" class="form-control form-control-sm" id="top_menu_nm" placeholder="한글등록" value="{{ $result->menu_nm ?? '' }}">
</div>
</div>




@else


<input type="hidden" id="sub_menu_gubun" value="{{ $gubun }}">
<input type="hidden" id="sub_menu_mode"  value="{{ $mode }}">

<div class="form-group row">
<label for="sub_menu_cd" class="col-sm-1 col-form-label">메뉴코드</label>
<div class="col-sm-3">
    <input type="text" class="form-control form-control-sm" id="sub_menu_cd" placeholder="숫자6자리" {{ $readonly }} value="{{ $result->menu_cd ?? $pcode }}">
</div>
<label for="sub_menu_nm" class="col-sm-1 col-form-label">메뉴명</label>
<div class="col-sm-4">
    <input type="text" class="form-control form-control-sm" id="sub_menu_nm" placeholder="한글등록" value="{{ $result->menu_nm ?? '' }}">
</div>
<label for="sub_menu_icon" class="col-sm-1 col-form-label">아이콘</label>
<div class="col-sm-2">
  <div class="input-group input-group-sm">
    <input class="form-control" type="text" placeholder="아이콘코드" id="sub_menu_icon" value="{{ $result->menu_icon ?? '' }}">
    <div class="input-group-append">
    <button class="btn btn-info btn-sm" onclick="window.open('https://fontawesome.com/icons?d=gallery&m=free');">
    <i class="fas fa-search"></i>
    </button>
    </div>
  </div>
</div>

</div>

<div class="form-group row">
<label for="sub_menu_order" class="col-sm-1 col-form-label">정렬순서</label>
<div class="col-sm-2">
    <input type="text" class="form-control form-control-sm" id="sub_menu_order" placeholder="숫자 최대4자리" value="{{ $result->menu_order ?? '' }}">
</div>
<label class="col-sm-1">
    <input type="checkbox" name="my-checkbox" id="sub_use_yn" data-bootstrap-switch value="Y" {{ ( isset($result->use_yn) && $result->use_yn=='Y' ) ? 'checked' : '' }}>
</label>
<label for="sub_menu_uri" class="col-sm-1 col-form-label">링크주소</label>
<div class="col-sm-4">
    <input type="text" class="form-control form-control-sm" id="sub_menu_uri" placeholder="/ 로 시작하는 URI" value="{{ $result->menu_uri ?? '' }}">
</div>
<label for="sub_menu_all_view" class="col-sm-1 col-form-label">기본메뉴</label>
<label class="col-sm-1">
    <input type="checkbox" name="my-checkbox" id="sub_menu_all_view" data-bootstrap-switch value="Y" {{ ( isset($result->menu_all_view) && $result->menu_all_view=='Y' ) ? 'checked' : '' }}>
</label>
</div>

@endif