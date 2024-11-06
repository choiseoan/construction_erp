<table class="table text-nowrap table-sm table-hover">

<thead>
<tr style='text-align:center'></tr>
<tr style='text-align:center'>
<th>주메뉴</th>
<th>권한</th>
@foreach ($users as $v )
    {{-- 기능권한 변경한 날짜 --}}
    <th>{{ substr($v->save_time,0,16) }}
    <p class='mb-0'>({{ $v->worker_id ?? '' }})</p>
    </th>
@endforeach
</tr>
</thead>
<tbody class="border-bottom">

{{-- 리스트 출력 --}}
@forelse( $menus as $v )
    <tr style='text-align:center'>
    <td class="p-0 border-right" >
        <table class="w-100">
        {{--  주메뉴 --}}
        <tr><td>
            {{ $func_name['SIDE'][$v->menu_cd]['name'] ?? ''}}
        </tr></tr>
        </table>
    </td>
    <td class="p-0 border-right">
    <table class="w-100">
        {{-- 주메뉴 => 기능권한  --}}
        @if(isset($func_array[$v->menu_cd]))
            @foreach($func_array[$v->menu_cd] as $key => $val)    
                <tr>
                    <td>{{ $val ?? ''}}</td>
                </tr>
            @endforeach
        @else
            <tr><td></td></tr>
        @endif
    </table>
    </td>

    {{-- 기능권한 리스트 --}}
    @foreach ($func_permit as $value)
        <td class="p-0 border-right">
        <table class="w-100" style="">
            {{-- 변경날짜 별 기능권한 --}}
            @if(isset($func_array[$v->menu_cd]))
                @foreach($func_array[$v->menu_cd] as $key => $val)  
                    @if(in_array($key, $value))
                        <tr><td><i class="fas fa-check text-primary"></i></td></tr>
                    @else
                        <tr><td><i class="fas fa-times text-danger"></i></td></tr>
                    @endif
                @endforeach
            @else
                <tr><td></td></tr>
            @endif
        </table>
        </td>
    @endforeach
@empty
    <tr>
    <td colspan=12 class='text-center p-4'>표시 할 데이터가 없습니다.</td>
@endforelse
</tr>

</tbody>
</table>


