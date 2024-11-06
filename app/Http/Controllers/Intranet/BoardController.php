<?php
 
namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Vars;
use Auth;
use Log; 
use DataList;
use App\Chung\Paging;
use Illuminate\Support\Facades\Storage;


class BoardController extends Controller
{
    private $board_permit = "I001";

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    { 
        //
    }
    
    private $arrayEmergency = [1=>'긴급', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9'];
    
    /**
     * 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataList(Request $request){

        $list   = new DataList(Array("listName"=>"board","listAction"=>'/intranet/board/'.$request->div.'/'));
 
        
        $list->setSearchDetail(Array('save_id' => '작성자ID', 'title' => '제목')); 
        
        if($request->div == 'comrequest')
        {
            $list->setSearchDate('날짜검색',Array('save_time' => '작성일', 'expected_date' => '완료예정일', 'due_date' => '완료일',),'searchDt','Y','N');
            $list->setSearchType('emergency_yn', $this->arrayEmergency, '개발 우선 순위','','','','','Y');
        }
        else 
        {
            $list->setSearchDate('날짜검색',Array('save_time' => '작성일',),'searchDt','Y','N');
        }
        
        
       
        return $list;
    }
    
     /**
     * 게시판
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function board(Request $request)
    {   
        //게시판 구분 {notice:공지사항 }
        if(isset($request->div)) $div = $request->div;
        else $div = '';

        $select_no = isset($request->no) ? $request->no : 0;
        $select_status = isset($request->status) ? $request->status : '';

        $list   = $this->setDataList($request);

		// log::debug("teswtsssss".print_r($list, true));

		if(!isset($request->tabs)) $request->tabs = 'A';

		if(isset($request->div) && $request->div == 'comrequest')
		{
			$list->setTabs(array_merge(Array('ALL'=>'전체'), Vars::$arrComrequestBoardStatus), $request->tabs);
		}

        $list->setIsModal($div.' 게시판','/intranet/boardform',Array('no','div','status', 'emergency_yn'),
        'modal-lg','data-backdrop=static  data-keyboard=false'); 
        
        $list->setPlusButton("modalAction('N','".$div."');");
        
        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        if($request->div == 'comrequest')
        {
            $titleArray = Array(
                'no'                =>     Array('글번호', 0, '', 'center', '', 'no'),
                'status'            =>     Array('상태', 0, '', 'center', '', 'status'),
                'emergency_yn'      =>     Array('우선순위', 0, '', 'center', '', 'emergency_yn'),
                'title'             =>     Array('제목', 1, '40%', 'left', '', 'title'),
                'save_id'           =>     Array('작성자', 0, '', 'center', '', 'save_id'),
                'save_time'         =>     Array('작성일', 0, '', 'center', '', 'save_time'),
                'expected_date'     =>     Array('완료예정일', 0, '', 'center', '', 'expected_date'),
                'due_date'          =>     Array('완료일', 0, '', 'center', '', 'due_date'),
                'click'             =>     Array('조회수', 1, '', 'center', '', 'click'),
            );
        }
        else 
        {
            $titleArray = Array(
                'no'                =>     Array('글번호', 0, '', 'center', '', 'no'),
                'title'             =>     Array('제목', 1, '40%', 'center', '', 'title'),
                'save_id'           =>     Array('작성자', 0, '', 'center', '', 'save_id'),
                'save_time'         =>     Array('작성일', 0, '', 'center', '', 'save_time'),
                'click'             =>     Array('조회수', 1, '', 'center', '', 'click'),
            );

        }
		
		if( $request->div == 'comrequest' && Auth::user()->branch_code == config('app.dev_branch')  ) 
		{
			$titleArray['worker'] = Array('작업자', 1, '', 'center', '', 'worker');
		}
        $list->setlistTitleCommon($titleArray);

        return view('intranet.board')->with("result", $list->getList())->with("select_no",$select_no ?? '')->with("select_status",$select_status ?? '');
    }

    /**
     * 게시판 list
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function boardList(Request $request)
    {
        //게시판 구분
        $div    = $request->div;
        
        $list   = $this->setDataList($request);

        $param  = $request->all();

        $chung_arr = Array();

        $chung_users = DB::TABLE("users")->SELECT("id", "name")->WHERE("save_status","Y")->WHERE("branch_code", config('app.dev_branch'))->GET();
        $chung_users = Func::chungDec(["users"], $chung_users);	// CHUNG DATABASE DECRYPT

        $co = 0;
        
        foreach($chung_users as $w)
        {

            $chung_arr[$w->id] = $w->name;

            $chung_h[$co] = $chung_arr;

            $co++;
        }

        $chung_h = end($chung_h);


		// Tab count 
		if($request->isFirst=='1')
		{
            $countDb = DB::TABLE("board")->SELECT('status as item', DB::RAW('count(no) as cnt'))->WHERE("save_status",'Y')->WHERE("div", 'comrequest');

            $count = $countDb->GROUPBY('status')->get();
            
			$r['tabCount'] = Func::getTabsCnt($count, Vars::$arrComrequestBoardStatus);
		}

        // 기본쿼리
        $board  = DB::TABLE("board")->SELECT("no","title","save_time","save_id","click","status","div", "due_date","expected_date", "emergency_yn", "worker")->WHERE("save_status","Y")->WHERE("div",$div);

        if(is_null($request->listOrder))
        {
            if( $request->tabsSelect=="A" )
            {	
                $board->WHERE('status','A')->ORDERBYRAW("COALESCE(emergency_yn , '') asc, save_time desc");
            }
            else if( $request->tabsSelect=="C" )
            {	
                $board->WHERE('status','C')->ORDERBYRAW("COALESCE(emergency_yn , '') asc, save_time desc");
            }
            else if( $request->tabsSelect=="Y" )
            {	
                $board->WHERE('status','Y')->ORDERBY("save_time", 'DESC');
            }
        }

        
        $board = $list->getListQuery("board",'main',$board,$param);

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging         = new Paging($board, $request->page, $request->listLimit, 10, $request->listName);

		$result         = $board->get();
        //직원정보
        $userList       = Func::getUserList('');
        $cnt            = 0;
		// 뷰단 데이터 정리.
		foreach ($result as $v)
		{   
            $cmt       = DB::TABLE("board_cmt")->SELECT("*")->WHERE("save_status","Y")->WHERE("board_no",$v->no)->count();
            $v->status = Func::getArrayName(Vars::$arrComrequestBoardStatus, $v->status);

            if( $v->title=="" )
            {
                $v->title = "제목없음";
            }
			
			$link     = '<a onclick="boardView(\''.$v->no.'\', \''.$v->status.'\', \''.$v->emergency_yn.'\');" style="cursor: pointer;" class="text-primary">';
            $worker   = $v->worker;

            // 전산요청 게시판 해야할거.
			if($v->div=='comrequest')
			{
                // 긴급
                if($v->emergency_yn=='1')
                {
				    $link	   .= '<i class="fas fa-star"></i>&nbsp;';
                }

                $v->expected_date   = Func::dateFormat($v->expected_date);
                $v->due_date        = Func::dateFormat($v->due_date);
                
                $v->emergency_yn    = Func::getArrayName($this->arrayEmergency, $v->emergency_yn);
                $v->worker          = "<select class='btn dropdown-toggle btn-default form-control-sm bg-white' onchange='workerChg(this,".$v->no.")' name='worker' id='worker'>
                                        <option value=''>작업자</option>
                                        ".Func::printOption($chung_h, $worker, false)."
                                        </select>";
			}

            $v->title       = $link.$v->title.'</a>';

            // 댓글수
            if($cmt > 0)
			{       
				$v->title  .= '<span title="3 New Messages" class="badge bg-info" style="margin-left:10px;">'.$cmt.'</span>';
			}

            $v->save_id         = (isset($userList[$v->save_id])) ? $userList[$v->save_id]->name." (".$v->save_id.")" : "-";
            $v->save_time       = Func::dateFormat($v->save_time);
            
			$r['v'][]       = $v;
			$cnt ++;
        }
		// 페이징
        $r['pageList']      = $paging->getPagingHtml($request->path());
		$r['result']        = 1;
        $r['txt']           = $cnt;
		return json_encode($r);
    }

	public function saveWorker(Request $request)
	{
		$_DATA = $request->all();

        $chung_users = DB::TABLE("users")->SELECT("id", "name")->WHERE("save_status","Y")->WHERE("branch_code", config('app.dev_branch'))->GET();

        $chung_usr = Array();


        foreach($chung_users as $w => $val)
        {
            $chung_usr[] = ((array)$val)['id'];
        }
        Log::debug("chung_usr");
        Log::debug($_DATA['target']);

		return DB::dataProcess("UPD", "board", ["worker"=>$_DATA['target']], ["no"=>$_DATA['no']]);

	}

    
    /**
     * 게시판 글 작성 및 수정
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function boardForm(Request $request)
    {
        $no                     = $request->input('no');
        $rslt['div']            = $request->input('div');
        $rslt['no']             = $no ;
        $rslt['status']         = $request->input('status');
        $rslt['emergency_yn']   = $request->input('emergency_yn');
        $rslt['arrayEmergency'] = $this->arrayEmergency;

        if(isset($no) && is_numeric($no))
        {
            //게시글
            $rslt['board']          = DB::TABLE("board")->SELECT("*")->WHERE('no', $no)->WHERE("save_status","Y")->FIRST();
            $rslt['status']	        = $rslt['board']->status;
		    $rslt['emergency_yn']	= $rslt['board']->emergency_yn;

            //첨부파일
            $rslt['file']       = DB::TABLE("board_file")->SELECT("no","file_origin","file_ext")->WHERE('board_no', $no)->WHERE("save_status","Y")->ORDERBY('no')->GET();
        }
        return view('intranet.boardForm')->with($rslt);
    }

    /**
     * 게시판 글 작성 및 view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function boardAction(Request $request)
    {
        
        $param    = $request->input();
        $disk     = $param['board_div']; // notice
        $insertId = 0;

        if($param['board_mode']=="DEL")
        {
            $DEL['del_id']       = Auth::user()->id;
            $DEL['del_time']     = date("YmdHis");
            $DEL['save_status']  = "N";
            $rslt                = DB::dataProcess('UPD', 'board', $DEL, array("no"=>$request->board_no));
            
            //첨부파일 삭제
            $file  = DB::TABLE("board_file")->SELECT("*")->WHERE("save_status","Y")->WHERE("board_no",$request->board_no);
            $result         = $file->get();
            foreach ($result as $v)
            {
                $fileurl    = $v->file_src;
                //파일 삭제
                Storage::disk('board')->delete($fileurl);
            }
            DB::dataProcess('UPD',"board_file", $DEL, array("board_no"=>$request->input('board_no')));    

        }
        else
        {
            // 게시판 내용 수정
            if(is_numeric($request->input('board_no'))&&$param['board_mode']=="UPD"){
                $insertId               = $request->board_no;
                $upd['title']           = $request->input('title');
                $upd['contents']        = $request->input('contents');
                $upd['status']          = $request->input('status');
                $upd['emergency_yn']    = $request->input('emergency_yn');
                $upd['update_time']     = date("YmdHis");
                $upd['update_id']       = Auth::user()->id;
                $upd['expected_date']   = str_replace("-", "", $request->input('expected_date'));

                // 완료일 찍는다.
                if($upd['status']=='Y')
                {
                    $upd['due_date']     = date("Ymd");
                }

                $rslt                   = DB::dataProcess('UPD', 'board', $upd, array("no"=>$request->board_no));
            }else{  
                //게시판 등록
                $param['save_id']       = Auth::user()->id;
                $param['save_time']     = date("YmdHis");
                $param['save_status']   = "Y";
                $param['click']         = 0;
                $param['div']           = $param['board_div'];
				$param['status']		= 'A';
				$param['emergency_yn']	= $param['emergency_yn'] ?? '9';
                $param['expected_date'] = (isset($param['expected_date'])) ? str_replace("-", "", $param['expected_date']):'';

                $rslt = DB::dataProcess('INS', 'board', $param,null,$insertId);
            }
            
            // 게시판 첨부 파일
            $file = $request->file('board_data');
            
            if(isset($file) && !empty($file)){
                foreach($file as $key => $val)
                {
                    $origin_filename        = $val->getClientOriginalName();
                    $extension              = $val->getClientOriginalExtension();
                    $filedir                = date('Ymd');
                    $filename               = date('YmdHis').'_'.rand(1000, 9999).'_'.$insertId;
                    Storage::disk('board')->putfileAs('', $val, $disk.'/'.$filedir.'/'.$filename.'.'.$extension);

                    $_DATA = [
                        'board_no'      => $insertId,
                        'save_id'       => Auth::user()->id,
                        'save_time'     => date("YmdHis"),
                        'save_status'   => "Y",
                        'file_name'     => $filename,
                        'file_origin'   => $origin_filename,
                        'file_ext'      => $extension,
                        'file_dir'      => $filedir,
                        // 'file_src'      => $filedir.'/'.$filename.'.'.$extension
                        'file_src'      => $disk.'/'.$filedir.'/'.$filename.'.'.$extension
                    ];
                    $query = DB::dataProcess('INS', 'board_file', $_DATA);
                }
            }
        }
         //결과 메세지 구분
         switch ($rslt) {
            case "Y":
                $msg = "정상처리되었습니다.";
                break;
            case "N":
                $msg = "처리에 실패하였습니다.";
                break;
            case "E":
                $msg = "등록정보가 올바르지 않습니다.";
                break;
            default:
                $msg = "기타오류";
        }

        return $msg;
    }
    

    /**
     * 게시글 view
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function boardDetail(Request $request)
    {
        $no          = $request->input('no');
        $rslt['no']  = $no ;
		$rslt['status'] = $request->input('status');
		$rslt['emergency_yn'] = $request->input('emergency_yn');

        //게시글
        $rslt['board']      = DB::TABLE("board")->JOIN('users', 'id', '=', 'board.save_id')->SELECT("board.*","users.name")->WHERE('board.no', $no)->WHERE("board.save_status","Y")->FIRST();
        $rslt['board']      = Func::chungDec(["BOARD", "USERS"], $rslt['board']);	

        //댓글
        $rslt['board_cmt']  = DB::TABLE("board_cmt")->JOIN('users', 'id', '=', 'board_cmt.save_id')->SELECT("board_cmt.*","users.name")->WHERE('board_cmt.board_no', $no)->WHERE("board_cmt.save_status","Y")->ORDERBY('board_cmt.no')->GET();
        $rslt['board_cmt']  = Func::chungDec(["BOARD_CMT", "USERS"], $rslt['board_cmt']);	// CHUNG DATABASE DECRYPT

        //첨부파일
        $rslt['board_file'] = DB::TABLE("board_file")->SELECT("*")->WHERE('board_no', $no)->WHERE("save_status","Y")->ORDERBY('no')->GET();
        $rslt['board_file'] = Func::chungDec(["BOARD_FILE"], $rslt['board_file']);	// CHUNG DATABASE DECRYPT

        //조회수 UP
        $v = DB::dataProcess('UPD', 'board',Array('click'=>($rslt['board']->click+1)),["no"=>$no]);   
        
        $rslt['sta'] = Func::getArrayName(Vars::$arrComrequestBoardStatus, $rslt['board']->status);

        
        //권한체크
        if(Func::funcCheckPermit($this->board_permit)) $rslt['board_admin'] = true;
        else  $rslt['board_admin'] = false;

        return view('intranet.boardDetail')->with($rslt);
    }


    /**
    * 게시판 댓글 action 
    *
    * @param  string
    * @return file
    */
    public function boardComment(Request $request)
    {
        $param    = $request->input();

        $comRequestArr = ['A'=>'요청','C'=>'검수','Y'=>'완료'];
        //댓글등록
        if(!is_numeric($request->input('no'))){
            $mode                   = "INS";
            $cmt['save_id']         = Auth::user()->id;
            $cmt['save_time']       = date("YmdHis");
            $cmt['save_status']     = "Y";
            $cmt['comment']         = $request->input('comment');
            $cmt['board_no']        = $request->input('board_no');    
            $where                  = null; 
            $board_no = $request->input('board_no');
            $main_board = DB::TABLE('board')->SELECT('worker','save_id','update_id','status')->WHERE('no',$board_no)->first();
            $cmtList = DB::TABLE('board_cmt')->SELECT('save_id')->WHERE('board_no',$board_no)->WHERE('save_status','Y')->pluck('save_id')->toArray();
            $idList = array_merge([$main_board->worker,$main_board->update_id,$main_board->save_id],$cmtList);
            $idList = array_unique(array_filter($idList));
            
            $msg = [
                'msg_type' => 'S',
                'msg_level'=> 'info',
                'title'    => '[전산요청 '.$request->input('board_no').'번] 댓글이 달렸습니다.',
                'contents' => $cmt['save_id'].': '.$cmt['comment'],
                'msg_link' => "/intranet/board/comrequest?no=".$request->input('board_no'),
            ];
            
            foreach($idList as $id)
            {
                $msg['recv_id'] = $id;

                Func::sendMessage($msg);
            } 
             
           
        }else{  //댓글삭제
            $mode                   = "UPD";
            $cmt['del_id']          = Auth::user()->id;
            $cmt['del_time']        = date("YmdHis");
            $cmt['save_status']     = "N";
            $where['no']            = $request->input('no');     
        }
        $rslt = DB::dataProcess($mode, 'board_cmt', $cmt,$where);
        
        return "Y";
    }

    /**
    * 첨부파일 다운로드
    *
    * @param  string
    * @return file
    */
    public function boardFileDown(Request $request,$no)
    {
        $mainTable  = 'board_file';
		$disk       = "board";

        $v = DB::table($mainTable)->select('*')->where("no", $no)->first();
		$filename   = $v->file_name;
		$extension  = $v->file_ext;
		$fileurl    = $v->file_src;
        $originname = $v->file_origin;
        $header     = array("Content-Disposition: attachment;filename=$filename;Content-Type"=>Storage::disk('board')->mimeType($fileurl));

		return Storage::disk('board')->download($fileurl, $originname);
    }

    /**
    * 첨부파일 삭제
    *
    * @param  string
    * @return file
    */
    public function boardFileDelete(Request $request)
    {
        //div로 받아서 처리 가능
        $param          = $request->input();
        $mainTable      = 'board_file';
		$disk       = $request->div;
        $v          = DB::table($mainTable)->select('*')->where("no", $request->input('no'))->first();
        $fileurl    = $v->file_src;
        
        //파일 삭제
        Storage::disk('board')->delete($fileurl);

        $_UPD['del_time']       = date('YmdHis');
        // $_UPD['del_id']         = Auth::user()->user_id;
        $_UPD['del_id']         = Auth::id();
        $_UPD['save_status']    = 'N'; // 저장 상태
        $rslt = DB::dataProcess('UPD', $mainTable, $_UPD, array("no"=>$request->input('no')));

        if($rslt=="Y") $msg = "파일 삭제 완료";
        else $msg = "파일 삭제 실패";

        return $msg;

    }
}