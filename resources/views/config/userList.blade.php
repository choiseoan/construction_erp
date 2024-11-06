<?
$upDown = $orderType == 'ASC' ? 'up' : 'down';
?>

<table class="table table-hover text-nowrap  table-striped table-sm">

    <thead>
        <tr style="cursor: pointer;">
            <th class="text-center" onclick="setUserOrder('branch_code', this);">부서 <i class="fas {{ $order == 'branch_code' ? 'fa-sort-'.$upDown : '' }}"></i></th>
            <th class="text-center" onclick="setUserOrder('id', this);">사번 <i class="fas {{ $order == 'id' ? 'fa-sort-'.$upDown : '' }}"></i></th>
            <th class="text-center" onclick="setUserOrder('name', this);">이름 <i class="fas {{ $order == 'name' ? 'fa-sort-'.$upDown : '' }}"></i></th>
            <th class="text-center" onclick="setUserOrder('user_rank_cd', this);">직급 <i class="fas {{ $order == 'user_rank_cd' ? 'fa-sort-'.$upDown : '' }}"></i></th>
            <th class="text-center" onclick="setUserOrder('user_position_cd', this);">직책 <i class="fas {{ $order == 'user_position_cd' ? 'fa-sort-'.$upDown : '' }}"></i></th>
            <th class="text-center" onclick="setUserOrder('ph34', this);">내선 <i class="fas {{ $order == 'ph34' ? 'fa-sort-'.$upDown : '' }}"></i></th>
            <th class="text-center" onclick="setUserOrder('email', this);">이메일 <i class="fas {{ $order == 'email' ? 'fa-sort-'.$upDown : '' }}"></i></th>
            <th class="text-center" onclick="setUserOrder('ph21 || ph22 || ph23', this);">전화번호 <i class="fas {{ $order == 'ph21 || ph22 || ph23' ? 'fa-sort-'.$upDown : '' }}"></i></th>
            <th class="text-center" onclick="setUserOrder('birthday', this);">생년월일 <i class="fas {{ $order == 'birthday' ? 'fa-sort-'.$upDown : '' }}"></i></th>
            <th class="text-center" onclick="setUserOrder('ipsa', this);">입사일 <i class="fas {{ $order == 'ipsa' ? 'fa-sort-'.$upDown : '' }}"></i></th>
            <th class="text-center" onclick="setUserOrder('toesa', this);">퇴사일 <i class="fas {{ $order == 'toesa' ? 'fa-sort-'.$upDown : '' }}"></i></th>
            <th class="text-center" onclick="setUserOrder('save_time', this);">저장일 <i class="fas {{ $order == 'toesa' ? 'fa-sort-'.$upDown : '' }}"></i></th>
        </tr>
    </thead>

    <tbody>
    @forelse( $result as $v )
        <tr onclick="setUserForm('{{ $v->id }}');" style="cursor: pointer;">
            <td class="text-center">{{ $v->branch_name }}</td>
            <td class="text-center">{{ $v->id }}</td>
            <td class="text-center">{{ $v->name }}</td>
            <td class="text-center">{{ $v->user_rank_cd }}</td>
            <td class="text-center">{{ $v->user_position_cd }}</td>
            <td class="text-center">{{ $v->ph34 }}</td>
            <td class="text-center">{{ $v->email }}</td>
            <td class="text-center">{{ $v->ph2 ?? '' }}</td>
            <td class="text-center">{{ $v->birthday }}</td>
            <td class="text-center">{{ $v->ipsa }}</td>
            <td class="text-center">{{ $v->toesa }}</td>
            <td class="text-center">{{ $v->save_time }}</td>
        </tr>
    @empty
        <tr>
            <td colspan=12 class='text-center p-4'>등록된 직원정보가 없습니다.</td>
        </tr>
    @endforelse
    </tbody>
</table>






<div class="card-footer row m-0">

    <div class="col-md-6">
        <button type="button" class="btn btn-sm btn-default" onclick="lump_btn_click();">일괄처리</button>
    </div>

    <div class="col-md-6 text-right">
        <ul class="pagination pagination-sm m-0 float-right">
            {!! $paging !!}
        </ul>
    </div>


<!-- /.card-body -->


</div>
