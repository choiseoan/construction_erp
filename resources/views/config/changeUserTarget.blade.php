

<div class="card-body table-responsive p-0" style="height: 630px;">
<table class="table table-sm table-hover table-head-fixed text-nowrap">
<thead>
<tr>
<th><a role="button" onclick="setUserList('ID','{{ $order_type=='DESC' ? 'ASC' : 'DESC' }}')">사번 @if($order_colm=="ID")<i class="orderIcon fas fa-arrow-{{ $order_type=='DESC' ? 'down' : 'up' }}"></i> @endif</a></th>
<th><a role="button" onclick="setUserList('NAME','{{ $order_type=='DESC' ? 'ASC' : 'DESC' }}')">이름 @if($order_colm=="NAME")<i class="orderIcon fas fa-arrow-{{ $order_type=='DESC' ? 'down' : 'up' }}"></i> @endif</a></th>
<th><a role="button" onclick="setUserList('BRANCH_NAME','{{ $order_type=='DESC' ? 'ASC' : 'DESC' }}')">부서 @if($order_colm=="BRANCH_NAME")<i class="orderIcon fas fa-arrow-{{ $order_type=='DESC' ? 'down' : 'up' }}"></i> @endif</a></th>
</tr>
</thead>
<tbody>

@forelse( $users as $value )


<tr onclick="setChangeUserInfo('{{ $value->id }}');" style="cursor:pointer">
<td>{{ $value->id }}</td>
<td>{{ $value->name }}</td>
<td>{{ $value->branch_name }}</td>
</tr>

@empty

<tr>
<td colspan=3 class='text-center p-4'>등록된 직원정보가 없습니다.</td>
</tr>

@endforelse

</table>
</div>

