
<table class="table table-hover table-sm">
    <tbody>
    @forelse($result as $job)
        @if( $jobSeq < 3 )
        <tr data-widget="expandable-table" aria-expanded="false" onclick="setJobForm('{{ $job->jobcode }}','{{ $job->jobname }}');setJobList('{{ $job->jobcode }}');">
            <td>
                <i class="fas fa-caret-right fa-fw"></i>({{ $job->jobcode ?? '' }}) {{ $job->jobname ?? '' }}
            </td>
        </tr>
        <tr class="expandable-body">
            <td>
                <div class="p-0" style="display: none;" id="jobList{{ $job->jobcode ?? '' }}"></div>
            </td>
        </tr>
        @else
        <tr>
            <td  onclick="setJobForm('{{ $job->jobcode }}','{{ $job->jobname }}');">
                <i class="fas fa-caret-right fa-minus"></i> &nbsp;({{ $job->jobcode ?? '' }}) {{ $job->jobname ?? '' }}
            </td>
        </tr>
        @endif
    @empty
    <tr>
        <td>등록된 코드가 없습니다. </td>
    </tr>
    @endforelse
    </tbody>
</table>