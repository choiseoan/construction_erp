
<div class="card-body table-responsive p-0" style="height: 500px;">
    <table class="table table-sm table-hover table-head-fixed text-nowrap">
        <thead>
            <tr>
                <th>부서코드</th>
                <th>부서명</th>
            </tr>
        </thead>
        <tbody>

            @forelse( $result as $value )

                <tr onclick="setBranchForm('{{ $value['code'] }}');" class="hand">
                    <td>{{ $value['code'] }}</td>
                    <td style="padding-left: {{ $value['branch_depth']*40 }}px;">{{ $value['branch_name'] }}</td>
                </tr>

            @empty
            
                <tr>
                    <td colspan=2 class='text-center p-4'>등록된 부서정보가 없습니다.</td>
                </tr>

            @endforelse
        </tbody>
    </table>
</div>

