
<!--
{{ $gubun }}
-->

@if( $gubun=='CATE' )



<div class="card-body table-responsive p-0" style="height: 450px;">
<table class="table table-sm table-hover table-head-fixed text-nowrap">
<thead>
<tr>
<th>카테고리코드</th>
<th>카테고리명</th>
<th width="40" class="text-center">수정불가</th>
</tr>
</thead>
<tbody>

@forelse( $result as $value )

<tr onclick="setCateForm('{{ $value->cat_code }}');" class="hand">
<td>{{ $value->cat_code }}</td>
<td>{{ $value->cat_name }}</td>
<td class='text-center'><?=( $value->readonly=="Y" ) ? "<i class='fas fa-lock text-gray'></i>" : "-" ?></td>
</tr>

@empty

<tr>
<td colspan=3 class='text-center p-4'>등록된 카테고리가 없습니다.</td>
</tr>

@endforelse

</table>
</div>



@else


    @if( $result==null )

        <div class="text-center" style='padding-top:40px;'>
        <div class="display-3"><i class="fas fa-info-circle text-gray"></i></div>
        <div class="pt-4">카테고리를 선택해주세요.</div>
        </div>


    @else 

        <div class="card-body table-responsive p-0" style="height: 450px;">
        <table class="table table-sm table-hover table-head-fixed text-nowrap">
        <thead>
        <tr>
        <th>카테고리</th>
        <th>코드</th>
        <th>코드명</th>
        <th>정렬</th>
        </tr>
        </thead>
        <tbody>

        @forelse( $result as $value )

        <tr onclick="setCodeForm('{{ $value->code }}');" class="hand">
        <td>{{ $value->cat_code }}</td>
        <td>{{ $value->code }}</td>
        <td>{{ $value->name }}</td>
        <td>{{ $value->code_order }}</td>
        </tr>

        @empty

        <tr>
        <td colspan=6 class='text-center p-4'>등록된 코드가 없습니다.</td>
        </tr>

        @endforelse

        </table>
        </div>

    @endif


@endif
