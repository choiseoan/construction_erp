<!--수량비교 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <h6 class="card-title">수량비교</h6>
        <div class="card-tools pr-2">
        </div>
    </div>
    @include('inc/list')
</div>

<script>

setInputMask('class', 'moneyformat', 'money');

getDataList('{{ $result['listName'] }}', '{{ $result['page'] ?? 1 }}', '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());

</script>