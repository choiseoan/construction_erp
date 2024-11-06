@extends('layouts.masterPop')
@section('content')

<div class="card card-lightblue">
    <div class="card-header-no-round">
    <h2 class="card-title">녹취파일검색</h2>
    </div>
</div>

<div class="p-1">

@include('inc.list')

</div>

@endsection

@section('javascript')

<script>

if($('#searchString').val())
{
    getDataList('{{ $result['listName'] }}', 1, 'recordlist', $('#form_{{ $result['listName'] }}').serialize());
}


function selectRecord(filename,filepath)
{
    $("#server_filename",opener.document).val(filename); 
    $("#folder_name",opener.document).val(filepath); 
    $("#server_url",opener.document).val(filepath); 
    self.close();
}

function reloadAudio(id)
{
    
}

function downRecord(url)
{
    location.href = url;
}

// 오디오 로드 실패시 리로드
function playRecord(url,id)
{

    var video = document.getElementById(id);
    console.log(id+" 오디오 로딩중...");
    console.log(url);

    $( '#'+id ).empty();
    var source = document.createElement('source');
    source.setAttribute('src', url);
    source.setAttribute('type', 'audio/mp3');
    source.setAttribute('onerror', "reloadAudio('"+id+"')");
    video.appendChild(source);
    video.load();
    console.log(id+" 로딩완료");
}


</script>

@endsection
