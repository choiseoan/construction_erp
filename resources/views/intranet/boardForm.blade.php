
<form id="boardForm">
    <input type="hidden" name="board_div" value="{{ $div ?? ''}}">
    <input type="hidden" name="board_no" id="board_no" value="{{ $no ?? '' }}">
    <input type="hidden" name="board_mode" id="board_mode" value="">
    <!-- /.card-header -->
    <div class="card-body">
        <div class="form-group">
            <input class="form-control" name="title" placeholder="글 제목 " value="{{ $board->title ?? '' }}">
        </div>
        <div class="form-group">
            <textarea id="compose-textarea" name="contents" class="form-control" style="height: 300px">{{ $board->contents ?? '' }}</textarea>
        </div>
        <div class="form-group">
            <span id="cntSPAN" >0</span>&nbsp;<span>bytes</span>
        </div>
        @if($div == 'comrequest')
        <div class="form-group row" style="margin-bottom:20px;">
            <div class="col-sm-6">
                <div class="icheck-primary d-inline align-sub mr-1">
                <input type="radio" id="status1" name="status" value="A" {{ $status == 'A' ? 'checked' : '' }}  @if(!is_numeric($no)) checked @endif>
                <label for="status1">요청</label>
                </div>
                <div class="icheck-primary d-inline align-sub">
                <input type="radio" id="status2" name="status" value="C" {{ $status == 'C' ? 'checked' : '' }}>
                <label for="status2">검수</label>
                </div>
                <div class="icheck-primary d-inline align-sub">
                <input type="radio" id="status3" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}>
                <label for="status3">완료</label>
                </div>
            </div>
            <div class="col-sm-2" align="right">
                완료예정일 :
            </div>
            <div class="col-sm-2">
                <div class="input-group date" id="expectedDate" data-target-input="nearest">
                    <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#expectedDate" name="expected_date" id="expected_date" value='{{ $board->expected_date ?? '' }}' maxlength="10" size="6"/>
                    <div class="input-group-append" data-target="#expectedDate" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                  </div>
                  
    
            </div>
            <div class="col-sm-2">
                <select class="form-control form-control-xs " name="emergency_yn" id="emergency_yn" >
                    <option value=''>우선순위</option>
                    {{ Func::printOption($arrayEmergency, $emergency_yn) }}
                </select>
            </div>
        </div>
        @endif
        <div class="form-group row">
            <div class="col-md-2">   
                <div class="btn btn-default btn-file">
                    <i class="fas fa-paperclip"></i> 파일첨부
                    <input type="file" name="board_data[]" value="" multiple >
                </div>
            </div>
            <div class="col-md-10" style="padding: 0.375rem 0.75rem;">   
                <div id="file_str">
                </div>
                <table class="table table-sm" >
                @if(isset($file))
                @foreach($file as $f)
                <tr id="tb_{{$f->no}}">
                    <td><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">{{ $f->file_origin }}</font></font></td>
                    <td><button type="reset" class="btn btn-xs btn-danger" onclick="fileDel('{{ $f->no }}')"><i class="fas fa-times"></i> 삭제</button></td>
                </tr>
                @endforeach
                @endif
                </table>
            </div>
        </div>
    </div>
    </form>
    <!-- /.card-body -->
    
    <div class="card-footer">
        <div class="float-right">
            @if(is_numeric($no))
            <button type="button" class="btn btn-info btn-sm" onclick="boardAction('UPD');"><i class="fas fa-pencil-alt" ></i> 수정</button>
            <button type="button" class="btn btn-danger btn-sm ml-2"  onclick="boardAction('DEL');"><i class="fas fa-trash-alt"></i> 삭제</button>
            @else
            <button type="button" class="btn btn-info btn-sm" onclick="boardAction('INS');"><i class="fas fa-pencil-alt" ></i> 저장</button>
            @endif
        </div>
    </div>
    
    <!-- Summernote -->
    <script>
        $('#compose-textarea').summernote({
                height: 300,                 // 에디터 높이
                minHeight: null,             // 최소 높이
                maxHeight: null,             // 최대 높이
                focus: true,                  // 에디터 로딩후 포커스를 맞출지 여부
                lang: "ko-KR",					// 한글 설정
                toolbar: [
                    // [groupName, [list of button]]
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']]
                ]
        });
        $('#compose-textarea').on('summernote.change', function(we, contents, $editable) {
            var cnt = getBytes(contents);
        });
        
        // byte 체크
        function getBytes(str){
            var cnt = 0;
            for(var i =0; i<str.length;i++) {
                cnt += (str.charCodeAt(i) >128) ? 2 : 1;
            }
            $('#cntSPAN').text(cnt);
            return cnt;
        }
    
        //file change
        $("input[type='file']").change(function(e){
    
            var files   = e.target.files;
            var arr     = Array.prototype.slice.call(files);
            var str     = "";
            
            //업로드 가능 파일인지 체크
            for(var i=0;i<files.length;i++){
               str += files[i].name+"  ("+files[i].size+"MB)<br>";
            }
            $('#file_str').html(str);
        });
    
        //글 등록
        function boardAction(mode)
        {
            var cnt = $('#cntSPAN').text();
            // if((cnt*1)>=32000){
            //     alert(" 내용은 32,000byte 까지만 입력이 가능합니다.");
            //     return;
            // }
            $('#board_mode').val(mode);
            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            var form        = $('#boardForm')[0];
            var postdata    = new FormData(form);
            $.ajax({
            url  : "/intranet/boardaction",
            type : "post",
            data : postdata,
            processData: false,
            contentType: false,
            success : function(result)
            {
                alert(result);
                if(mode != 'UPD'){
                    $(location).attr('href','/intranet/board/{{ $div ?? ''}}');
                }else{
                    boardView($('#board_no').val());
                }
                $("#modal01").modal('hide');
            },
            error : function(xhr)
            {
                alert(result);
            }
            });
        }
    
        //파일삭제
        function fileDel(no)
        {   
            const modifiedTitle = $("input[name='title']").val();
            const modifiedContents = $("textarea[name='contents']").val();

            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
            url  : "/intranet/board/filedel",
            type : "post",
            data : {
                // div         : "board",
                div         : $("input[name='board_div']").val(),
                no          : no,
                title       : $("input[name='title']").val(),
                contents    : $("textarea[name='contents']").val()
            },
            success : function(result)
            {
                alert(result);
                $('#tb_'+no).remove();

                $("input[name='title']").val(modifiedTitle);
                $("textarea[name='contents']").val(modifiedContents);
            },
            error:function(request, error) {
                alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error)
            }
            });
        }
    
        $('#expectedDate').datetimepicker({
            format: 'YYYY-MM-DD',
            locale: 'ko',
                useCurrent: false,
            });
    </script>