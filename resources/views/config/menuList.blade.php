
<!--
{{ $gubun }}
-->

@if( $gubun=='TOP' )



<div class="card-body table-responsive p-0" style="height: 450px;">
<table class="table table-sm table-hover table-head-fixed text-nowrap">
<thead>
<tr>
<th>메뉴코드</th>
<th>메뉴명</th>
<th>아이콘</th>
</tr>
</thead>
<tbody>

@forelse( $result as $value )

<tr class="hand" onclick="setTopMenuForm('{{ $value->menu_cd }}');">
<td>{{ $value->menu_cd }}</td>
<td>{{ $value->menu_nm }}</td>
<td>{{ $value->menu_icon }}</td>
</tr>

@empty

<tr>
<td colspan=3 class='text-center p-4'>등록된 메뉴가 없습니다.</td>
</tr>

@endforelse

</table>
</div>



@else



<div class="card-body table-responsive p-0" style="height: 450px;">
<table class="table table-sm table-hover table-head-fixed text-nowrap">
<thead>
<tr>
<th>메뉴코드</th>
<th>메뉴명</th>
<th>아이콘</th>
<th>주소</th>
<th>사용</th>
<th>정렬</th>
<th>기본메뉴</th>
</tr>
</thead>
<tbody>
@if(isset($result))
@foreach( $result as $value )

<tr class="hand" onclick="setSubMenuForm('{{ $value->menu_cd }}');">
<td>{{ $value->menu_cd }}</td>
<td>{{ $value->menu_nm }}</td>
<td>{{ $value->menu_icon }}</td>
<td>{{ $value->menu_uri }}</td>
<td>{{ $value->use_yn=="Y" ? "ON" : "OFF" }}</td>
<td>{{ $value->menu_order }}</td>
<td>{{ $value->menu_all_view=="Y" ? "ON" : "" }}</td>
</tr>
@endforeach

@else

<tr>
<td colspan=6 class='text-center p-4'>등록된 서브메뉴가 없습니다.</td>
</tr>

@endif

</table>
</div>


@endif