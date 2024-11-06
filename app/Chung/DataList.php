<?php
namespace App\Chung;

use App\Models\User;
use DB;
use DBD;
use Auth;
use Log;
use Storage;
use Sum;
use Excel;
use Func;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Shared\Date;

//리스트 데이터 셋팅 class
class DataList
{
    private $listArr;
    private $searchDetailLikeOption = "";

    //생성자 
    //기존 생성된 list 삽입 가능
    public function __construct($listArr=array()) {
        $this->listArr                  = $listArr;

        // 기본값 세팅
        $this->listArr['viewNum'] = true;
        $this->listArr['refresh'] = '';
        $this->listArr['resultOrder'] = false;
    }

    // 리스트 데이터 임의 지정
    public function setCustomList($col,$val){
        $this->listArr[$col] = $val;
    }

    // listName : 리스트 이름 (표시 x)
    public function setListName($listName) {
        $this->listArr['listName']        = $listName;
    }

    // listAction : 리스트 url - ajax 요청주소 ( '/'.$request->path() )
    public function setListAction($listAction) {
        $this->listArr['listAction']      = $listAction;
    }

    /**
     *  리스트 맨앞에 번호 표시 여부. 단순 순번
     * @param string $bl     true / false
     */
    public function setViewNum($bl=true){
        $this->listArr['viewNum']          = $bl;
    }

    /**
     *  새로고침 버튼 custom 세팅 여부. 기본은 해당페이지를 다시 불러온다.
     * @param string $bl     true / false
     */
    public function setRefresh($link=''){
        $this->listArr['refresh']          = $link;
    }

    /**
     *  리스트 앞에 체크박스 세팅 유무 
     * @param string $checkboxNm     체크박스 NAME
     */
    public function setCheckBox($checkboxNm){
        $this->listArr['checkbox']          = $checkboxNm;
    }


    // listAction : 리스트 url - ajax 요청주소 ( '/'.$request->path() )
    public function setSearchDetailLikeOption($opt) {
        $this->searchDetailLikeOption      = $opt;
    }

    // resultOrder : 결과내검색
    public function setResultOrder($bl) {
        $this->listArr['resultOrder']        = $bl;
    }
    
    /**
     * 서류함(탭) 설정
     *
     * @param array $tabsArray        탭 배열 (이차원배열)
     * @param string $tabsSelect     선택한 탭
     */
    public function setTabs($tabsArray,$tabsSelect){
        $this->listArr['Tabs']['tabsArray']     = $tabsArray;
        $this->listArr['Tabs']['tabsSelect']    = $tabsSelect;
    }

    /**
     * 서류함(탭) 상태 가져오기
     *
     */
    public function getTabs(){
        return $this->listArr['Tabs']['tabsSelect'];
    }

    /**
     * 버튼 설정 (여러개가능)
     *
     * @param string $buttonName         버튼 이름
     * @param string $buttonAction       버튼 동작
     * @param string $buttonClass        버튼 클래스 추가 'btn-danger', 'btn-info','btn-success','btn-primary','btn-secondary','btn-light','btn-dark','btn-link'
     */
    public function setButtonArray($buttonName,$buttonAction,$buttonClass='btn-info',$buttonId=''){
        $this->listArr['buttonArray'][]  = Array(
            'buttonArrayNm'     => $buttonName,
            'buttonArrayClass'  => $buttonClass,
            'buttonArrayAction' => $buttonAction,
            'buttonArrayId'     => $buttonId,
        );
    }

    /**
     * 일자 검색 (여러개가능)
     *
     * @param string $searchDateTitle         표시할 항목 (option 첫번째 이름)
     * @param array $searchDateArray          표시할 option 태그 배열 [name 속성 => 표시할 이름]
     * @param string $searchDateNm            검색 input name 값 - select 태그 name, text는 자동으로 뒤에 String이 붙음.
     * @param string $searchDatePair          Y or N 일자검색 시작날짜, 종료날짜 검색 여부 - 두번째 날짜 input은 name에 End가 붙는다.
     * @param string $searchDateNoBtn         (N == 표시, Y == 미표시, YESTERDAY == 전날도 사용, YEAR == 당해도 사용) 오늘, 이번주, 한달 버튼 여부
     * @param string $searchDateTxt           datepicker 시작일 기본 셋팅 날짜   
     * @param string $searchDateTxtEnd        datepicker 종료일 기본 셋팅 날짜   
     * @param string $searchDateSelect        표시할 option select 값
     * @param string $searchDateFunc          onchange 적용할 함수 이름
     */
    public function setSearchDate($searchDateTitle,$searchDateArray,$searchDateNm,$searchDatePair='N',$searchDateNoBtn='N',$searchDateTxt='',$searchDateTxtEnd='',$searchDateSelect='',$searchDateFunc=''){
        $this->listArr['searchDate'][]  = Array(
            'searchDateTitle'   => $searchDateTitle,
            'searchDateArray'   => $searchDateArray,
            'searchDateNm'      => $searchDateNm,
            'searchDatePair'    => $searchDatePair,
            'searchDateNoBtn'   => $searchDateNoBtn,
            'searchDateTxt'     => $searchDateTxt,
            'searchDateTxtEnd'  => $searchDateTxtEnd,
            'searchDateFunc'    => $searchDateFunc,
            'searchDateSelect'  => $searchDateSelect
        );
        if($searchDateSelect!='') $this->listArr['searchDateSelect'] = $searchDateSelect;
    }

    /**
     * 월 검색 (여러개가능)
     *
     * @param string $searchWolTitle         표시할 항목 (option 첫번째 이름)
     * @param array $searchWolArray          표시할 option 태그 배열 [name 속성 => 표시할 이름]
     * @param string $searchWolNm            검색 input name 값 - select 태그 name, text는 자동으로 뒤에 String이 붙음.
     * @param string $searchWolTxt           datepicker 시작일 기본 셋팅 날짜   
     * @param string $searchWolSelect        표시할 option select 값
     * @param string $searchWolFunc          onchange 적용할 함수 이름
     */
    public function setSearchWol($searchWolTitle,$searchWolArray,$searchWolNm,$searchWolTxt='',$searchWolSelect='',$searchWolFunc=''){
        $this->listArr['searchWol'][]  = Array(
            'searchWolTitle'  => $searchWolTitle,
            'searchWolArray'  => $searchWolArray,
            'searchWolNm'     => $searchWolNm,
            'searchWolTxt'    => $searchWolTxt,
            'searchWolFunc'   => $searchWolFunc
        );
        if($searchWolSelect!='') $this->listArr['searchWolSelect'] = $searchWolSelect;
    }

    
    /**
     * 구간검색 
     * @param array  $rangeSearchDetailArray :  구간검색 select 태그 option 값
     * @param string $sRangeSearchStringSet :   검색 input value 로 넣을 값
     * @param string $eRangeSearchStringSet :   검색 input value 로 넣을 값
     * @param string $rangePlHolder             placeholder 표시 text
     * @param string $rangeSearchDetailSet      select box 선택값
     * 
     */
    public function setRangeSearchDetail($rangeSearchDetailArray,$sRangeSearchStringSet,$eRangeSearchStringSet,$rangePlHolder='',$rangeSearchDetailSet=''){
        $this->listArr['rangeSearchDetail']  = Array(
            'rangeSearchDetailArray'    => $rangeSearchDetailArray,
            'rangePlHolder'             => $rangePlHolder,
            'sRangeSearchStringSet'     => $sRangeSearchStringSet,
            'eRangeSearchStringSet'     => $eRangeSearchStringSet,
        );
        if($rangeSearchDetailSet!='') $this->listArr['rangeSearchDetailSet'] = $rangeSearchDetailSet;
        
    }

    /**
     * 구간검색 
     * @param array  $rangeSearchDetailArray :  구간검색 select 태그 option 값
     * @param string $sRangeSearchStringSet :   검색 input value 로 넣을 값
     * @param string $eRangeSearchStringSet :   검색 input value 로 넣을 값
     * @param string $rangePlHolder             placeholder 표시 text
     * @param string $rangeSearchDetailSet      select box 선택값
     * 
     */
    public function setRangeSearchDetail2($rangeSearchDetailArray,$sRangeSearchStringSet,$eRangeSearchStringSet,$rangePlHolder='',$rangeSearchDetailSet=''){
        $this->listArr['rangeSearchDetail2']  = Array(
            'rangeSearchDetailArray'    => $rangeSearchDetailArray,
            'rangePlHolder'             => $rangePlHolder,
            'sRangeSearchStringSet'     => $sRangeSearchStringSet,
            'eRangeSearchStringSet'     => $eRangeSearchStringSet,
        );
        if($rangeSearchDetailSet!='') $this->listArr['rangeSearchDetailSet'] = $rangeSearchDetailSet;
        
    }

    /**
     * select box 검색 조건 추가  (여러개가능)
     * @param string $searchTypeNm : select 태그 name 속성 값
     * @param array  $searchTypeArray :  option name, value 값
     * @param string $searchTypeTitle : option 첫번째 칸 설정
     * @param string $searchTypeAction : select 속성 추가 (ex: onchange, onclick, style) 'onclick="console.log(\'test\');"'
     * @param string $searchTypeSubject : 검색창 왼쪽에 표시할 텍스트
     * @param string $searchTypeVal      select box 선택값
     * @param string $searchMultiple       다중검색    
     * @param boolean $searchCustom       검색쿼리 pass   
     * @param boolean $searchLive       항목 검색 가능 여부
     * 
     */
    public function setSearchType($searchTypeNm,$searchTypeArray,$searchTypeTitle,$searchTypeAction='',$searchTypeSubject='',$searchTypeVal='',$searchLike='',$searchMultiple='N' ,$searchCustom=false, $searchLive=false){
        $this->listArr['searchType'][]  = Array(
            'searchTypeNm'      => $searchTypeNm,
            'searchTypeArray'   => $searchTypeArray,
            'searchTypeTitle'   => $searchTypeTitle,
            'searchTypeAction'  => $searchTypeAction,
            'searchTypeSubject' => $searchTypeSubject,
            'searchTypeVal'     => $searchTypeVal,
            'searchLike'        => $searchLike,
            'searchMultiple'    => $searchMultiple,
            'searchCustom'      => $searchCustom,
            'searchLive'        => $searchLive 
        );
    }

    /**
     * select box 검색 조건 추가  (여러개가능)
     * @param string $searchTypeNm : select 태그 name 속성 값
     * @param string $searchTypeSubNm : select 태그 두번째 name 속성 값
     * @param array  $searchTypeArray :  option name, value 값 2차배열
     * @param string $searchTypeTitle : option 첫번째 칸 설정
     * @param string $searchTypeAction : select 속성 추가 두번째에 적용됨.(ex: onchange, onclick, style) 'onclick="console.log(\'test\');"'
     * @param string $searchTypeSubject : 검색창 왼쪽에 표시할 텍스트
     * @param string $searchTypeVal      select box 선택값1
     * @param string $searchTypeSubVal   select box 선택값2
     * 
     */
    public function setSearchTypeChain($searchTypeNm, $searchTypeSubNm, $searchTypeArray, $searchTypeTitle, $searchTypeAction='',$searchTypeSubject='',$searchTypeVal='',$searchTypeSubVal=''){
        $this->listArr['searchTypeChain'][]  = Array(
            'searchTypeNm'      => $searchTypeNm,
            'searchTypeSubNm'   => $searchTypeSubNm,            
            'searchTypeArray'   => $searchTypeArray,
            'searchTypeTitle'   => $searchTypeTitle,
            'searchTypeAction'  => $searchTypeAction,
            'searchTypeSubject' => $searchTypeSubject,
            'searchTypeVal'     => $searchTypeVal,
            'searchTypeSubVal'  => $searchTypeSubVal,
        );
    }

    /**
     * select box 검색 조건 추가  (여러개가능)
     * @param string $searchTypeNm : select 태그 name 속성 값
     * @param string $searchTypeSubNm : select 태그 두번째 name 속성 값
     * @param array  $searchTypeArray :  option name, value 값 2차배열
     * @param string $searchTypeTitle : option 첫번째 칸 설정
     * @param string $searchTypeAction : select 속성 추가 두번째에 적용됨.(ex: onchange, onclick, style) 'onclick="console.log(\'test\');"'
     * @param string $searchTypeSubject : 검색창 왼쪽에 표시할 텍스트
     * @param string $searchTypeVal      select box 선택값1
     * @param string $searchTypeSubVal   select box 선택값2
     * @param string $searchTypeSubTitle  : 서브 select의 표시 텍스트
     * @param boolean $searchLive :       항목 검색 가능 여부
     * 
     */
    public function setSearchTypeMultiChain($searchTypeNm, $searchTypeSubNm, $searchTypeArray, $searchTypeTitle, $searchTypeAction='',$searchTypeSubject='',$searchTypeVal='',$searchTypeSubVal='', $searchTypeSubTitle='', $searchLive=true){
        $this->listArr['searchTypeMultiChain'][]  = Array(
            'searchTypeNm'      => $searchTypeNm,
            'searchTypeSubNm'   => $searchTypeSubNm,            
            'searchTypeArray'   => $searchTypeArray,
            'searchTypeTitle'   => $searchTypeTitle,
            'searchTypeAction'  => $searchTypeAction,
            'searchTypeSubject' => $searchTypeSubject,
            'searchTypeVal'     => $searchTypeVal,
            'searchTypeSubVal'  => $searchTypeSubVal,
            'searchTypeSubTitle'=> $searchTypeSubTitle,
            'searchLive'        => $searchLive,
        );
    }


    /**
     * select box 검색상세기준
     * @param string $searchTypeNm : select 태그 name 속성 값
     * @param array  $searchTypeArray :  option name, value 값
     * @param string $searchTypeTitle : option 첫번째 칸 설정
     * @param string $searchTypeAction : select 속성 추가 (ex: onchange, onclick, style) 'onclick="console.log(\'test\');"'
     * @param string $searchTypeSubject : 검색창 왼쪽에 표시할 텍스트
     * @param string $searchTypeVal      select box 선택값
     * @param string $searchMultiple       다중검색    
     * @param boolean $searchCustom       검색쿼리 pass   
     * @param boolean $searchLive       항목 검색 가능 여부
     * 
     */
    public function setSearchTypeDetail($searchTypeNm,$searchTypeArray,$searchTypeTitle,$searchTypeAction='',$searchTypeSubject='',$searchTypeVal='',$searchLike='',$searchMultiple='N' ,$searchCustom=false, $searchLive=false){
        $this->listArr['searchTypeDetail'][]  = Array(
            'searchTypeNm'      => $searchTypeNm,
            'searchTypeArray'   => $searchTypeArray,
            'searchTypeTitle'   => $searchTypeTitle,
            'searchTypeAction'  => $searchTypeAction,
            'searchTypeSubject' => $searchTypeSubject,
            'searchTypeVal'     => $searchTypeVal,
            'searchLike'        => $searchLike,
            'searchMultiple'    => $searchMultiple,
            'searchCustom'      => $searchCustom,
            'searchLive'        => $searchLive 
        );
    }

    /**
     * searchDetail select + string 상세 검색 조건 추가 
     * @param array $searchDetailArray : 검색 select 태그 option 값
     * @param string  $searchDetailSet :  selected 할 option 키값
     * @param string $searchStringSet : input value 값
     * @param string $searchStringReadOnly :readonly 사용유무
     * 
     */
    public function setSearchDetail($searchDetailArray,$searchDetailSet='',$searchStringSet='',$searchStringReadOnly=''){
        $this->listArr['searchDetail']         = $searchDetailArray;
        $this->listArr['searchDetailSet']      = $searchDetailSet;
        $this->listArr['searchStringSet']      = $searchStringSet;
        $this->listArr['searchStringReadOnly'] = $searchStringReadOnly;
    }

    /**
     * 멀티검색 입력창 추가
     * @param array  $multiArray : 멀티검색 option 값
     * @param string  $multiButtonAction : 멀티검색 버튼 onclick 동작
     */
    public function setMultiButton($multiArray, $multiButtonAction='javascript:multi_view()'){
        $this->listArr['multiArray']    = $multiArray;
        $this->listArr['multiButton']   = $multiButtonAction;
    }

    /**
     * 모달창 사용여부 modalAction 함수로 호출
     * @param string  $modalTitle : 모달창 타이틀
     * @param string $modalAction : 모달액션 url
     * @param array $modalParams : 모달창 전달 파라미터
     * @param string $modalSize : 모당창 사이즈 (modal-sm, modal-lg 등등)
     * @param string $modalOption : 모달창 옵션 (style, 부트스트랩 속성 등) 'style="background-color: red;"'
     * 
     */
    public function setIsModal($modalTitle,$modalAction,$modalParams,$modalSize='modal-sm',$modalOption=''){
        $this->listArr['isModal']  = Array(
            'modalTitle'        => $modalTitle,
            'modalAction'       => $modalAction,
            'modalParams'       => $modalParams,
            'modalSize'         => $modalSize,
            'modalOption'       => $modalOption,
        );
    }

    /**
     *  등록 버튼 추가
     * @param string  $plusButtonAction : 등록 버튼 onclick 동작
     */
    public function setPlusButton($plusButtonAction){
        $this->listArr['plusButton'] = $plusButtonAction;
    }
    
    /**
     *  체크 버튼 추가
     * @param string  $plusButtonAction : 체크 버튼 onclick 동작
     */
    public function setStatusCheckBox($setStatusCheckBoxYn){
        $this->listArr['statusCheckBox'] = $setStatusCheckBoxYn;
    }

    /**
     *  일괄처리 form 추가
     * @param string  $lumpCode : 일괄처리 분류 code
     * @param array  $lumpType : 일괄처리btn 속성 Array('BTN_NAME'=>'문자메세지','BTN_ACTION'=>'','BTN_ICON'=>'','BTN_COLOR'=>'');
     * 
     */
    public function setLumpForm($lumpCode,$lumpType){
        $this->listArr['lumpForm'][$lumpCode] = $lumpType;
    }

    /**
     *  일괄처리 오른쪽 form 추가
     * @param string  $lumpCode : 일괄처리 분류 code
     * @param array  $lumpType : 일괄처리btn 속성 Array('BTN_NAME'=>'문자메세지','BTN_ACTION'=>'','BTN_ICON'=>'','BTN_COLOR'=>'');
     * 
     */
    public function setrightLumpForm($lumpCode,$lumpType){
        $this->listArr['rightlumpForm'][$lumpCode] = $lumpType;
    }
    
    /**
     *  list 표시할 컬럼 (공통)
     * @param array   listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 
     *                  리스트 세팅  
     */
    public function setlistTitleCommon($listTitle){
        $this->listArr['listTitle']['common'] = $listTitle;
    }
        
    /**
     *  list 표시할 컬럼 (공통뒷부분)
     * @param array   listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 
     *                  리스트 세팅  
     */
    public function setlistTitleCommonEnd($listTitle){
        $this->listArr['listTitle']['commonEnd'] = $listTitle;
    }

    /**
     *  list 표시할 컬럼 (탭별로)
     * @param string  $tabs : tab
     * @param array   listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 
     *                  리스트 세팅([0] key=>타이틀,[1]사용X ,[2] 넓이 - % 또는 px,[3] text 정렬,[4] rightline 여부,[5] data정렬,
     *                              [6] 한칸에 여러데이터 중첩표시 array([컬럼]=>array(text,data 정렬,txet앞에 표시될html( / , <br> ..) )))
     */
    public function setlistTitleTabs($tabs,$listTitle){
        $this->listArr['listTitle'][$tabs] = $listTitle;
    }

    /**
     *  input hidden 타입
     * @param array  $array_hidden : 추가할 input 정보, form태그 안 hidden으로 추가된다. (name => value)
     * 
     */
    public function setHidden($array_hidden){
        $this->listArr['hidden'] = $array_hidden;
    }

    /**
     *  팝업창 여부 설정
     * @param boolean
     * 
     */
    public function setIsPopup($flag){
        $this->listArr['isPopup'] = $flag;
    }

    /**
     * 리스트 컬럼 체크박스 추가
     * @param array  $checkboxListInfo : 리스트에 체크박스  (name => value)
     * 
     */
    public function setCheckboxListAdd($checkboxListInfo){
        $this->listArr['checkboxListAdd'] = $checkboxListInfo;
    }
    
    /**
     * 세팅된 dataList get 
     * @param array  $col 해당 컬럼에 대한 data return col 없으면 전체 리턴 col에 대한 리스트가없으면 false 리턴
     * 
     */
    public function getList($col=''){
        if($col =='' ) return $this->listArr;
        if($col!='' && isset($this->listArr[$col])) return $this->listArr[$col];
        else return false;
    }

    
    /**
     * 셋팅된 dataList 를 unset 한다. - 아주 가끔 controller에서 하드코딩하여 조건절부여가 필요할때가 있는데, 그때 사용할 필요가 있음 ex) tradeDetailContoller.php
     * @param String 입력된 키로 listArr 데이터 삭제처리
     */
    public function unsetList($col)
    {
        if( isset($this->listArr[$col]) )
        {
            unset($this->listArr[$col]);
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * 공통 검색 쿼리 추출
     * @param string  $mainTable : 테이블이름
     * @param string  $div    구분 main 검색 쿼리 or order 정렬 쿼리
     * @param string  $query : query;
     * @param string  $param : request->all();
    
     */
    public function getListQuery($mainTable, $div, $query, $param)
    {
        if($div=='order')
		{
            echo "해당 메소드 옵션 사용금지";
            Log::debug("해당 메소드 옵션 사용금지");
            exit;

			// 정렬
			if(isset($param['listOrder']))
			{
				if(strstr($param['listOrder'], ','))
				{
					$orders = explode(',', $param['listOrder']);
					foreach($orders as $col)
					{
						$result = $query->ORDERBY($col, $param['listOrderAsc']);
					}
				}
				else
				{
					$result = $query->ORDERBY( $param['listOrder'],  $param['listOrderAsc']);
				}
			}
			else
			{
				$result = $query->ORDERBY($mainTable.'.no', 'desc');
			}
			return $result;     
		}
		else if($div=='main') 
		{
            // 정렬
			if(isset($param['listOrder']))
			{
                // 리스트 오더를 쓰지 않음.
                if($param['listOrder']=='NONE')
                {

                }
                // 암호화 확인 - addr11은 정렬이 안되고 있다. 이유를 모르겠음.
                else if(substr($param['listOrder'], 0, 4)=='ENC-')
				{
                    $order = substr($param['listOrder'], 4);

                    // , 구분해서 여러컬럼일 경우
                    if(strstr($order, ','))
                    {
                        $orders = explode(',', $order);
                        foreach($orders as $col)
                        {
                            $query = Func::encOrderBy($query, $col, $param['listOrderAsc']);
                        }

                    }
                    else 
                    {
                        $query = Func::encOrderBy($query, $order, $param['listOrderAsc']);
                    }
				}
                else if(stripos($param['listOrder'], 'coalesce') !== false) // null 데이터 때문에 추가 ~ 
				{
                    $query = $query->ORDERBY($param['listOrder'], $param['listOrderAsc']);
				}
                else if(strstr($param['listOrder'], ',') && strstr($param['listOrderAsc'], ','))
                {
                    $i=0;
                    $orders = explode(',', $param['listOrder']);
                    $ordersAsc = explode(',', $param['listOrderAsc']);
					foreach($orders as $col)
					{
                        $query = $query->ORDERBY($col, $ordersAsc[$i]);
                        $i++;
					}
				}
				else if(strstr($param['listOrder'], ','))
				{
					$orders = explode(',', $param['listOrder']);
					foreach($orders as $col)
					{
						$query = $query->ORDERBY($col, $param['listOrderAsc']);
					}
				}
				else
				{
					$query = $query->ORDERBY( $param['listOrder'],  $param['listOrderAsc']);
				}
			}
			else
			{
				$query = $query->ORDERBY($mainTable.'.no', 'desc');
			}

			// tab  
            if(strpos(strtolower($mainTable), "excel_down_log") === false && strpos(strtolower($mainTable), "lump_master_log") === false)
            {
                if( isset($param['tabsSelect']) && ( is_array($param['tabsSelect']) ||( strtolower($param['tabsSelect'])!='all' && strtolower($param['tabsSelect'])!='del' ) ) )
                {
                    if( isset($param['tabSelectNm']) )
                    {
                        if(is_array($param['tabsSelect']))
                        {
                            $query = $query->WHEREIN($param['tabSelectNm'], $param['tabsSelect']);
                        }
                        else
                        {
                            $query = $query->WHERE($param['tabSelectNm'], '=', $param['tabsSelect']);
                        }
                    } 
                    else
                    {
                        $query = $query->WHERE($mainTable.'.STATUS','=',$param['tabsSelect']);
                    }
                }
            }


			// 상세 검색
			if(isset( $param['searchDetail']) && isset($param['searchString']) )
			{
				// 암호화 컬럼. , 암호화 해제
				/*
                $arrayEnc = [$joinTable.'.name', $joinTable.'.biz_ssn', $joinTable.'.ssn11'];
				
                if(in_array($request->searchDetail, $arrayEnc))
					$query = $query->where($request->searchDetail, '=', Func::encrypt($request->searchString));
				else
                */
                /**
                 * 2022-09-29
                 * 암호화 대상 컬럼은 따로 분류 후 검색 필요
                 * 해당 컬럼들은 like검색 불가능
                 */

                // 차입자번호에 특정문서가 있을 수 있음.
                if(stristr($param['searchDetail'], 'cust_info_no'))
                {
                    $param['searchString'] = Func::stripCi($param['searchString']);
                }

                // 암호화 대상 컬럼 리스트 추출
                $arrayAllCol = array();
                $obj = new Decrypter();
		        $arrayAllCol_list = $obj->arrayEncCol;
                foreach($arrayAllCol_list as $key => $val)
                {
                    foreach($val as $value)
                    {
                        array_push($arrayAllCol, $value);
                    }
                }
                if(strpos($param['searchDetail'], '.'))
                {
                    $searchDetail = explode(".", $param['searchDetail']);
                    $searchDetail = $searchDetail[1];
                }
                else
                    $searchDetail = $param['searchDetail'];
                // 암호화 대상 조건 추가 - 2022-09-29
                if( $this->searchDetailLikeOption=="" && in_array(strtolower($searchDetail), $arrayAllCol) )
                {
                    if(strpos(strtolower($mainTable), "mydata") !== false)
                    {
                        LOG::debug('마이데이터 암호화 비대상 : '.$param['searchDetail']);
                        $query = $query->WHERE($param['searchDetail'], '=', $param['searchString']);     // 일반 검색
                    }
                    else
                    {
                        LOG::debug('암호화 대상 : '.$param['searchDetail']);
                        //Func::encLikeSearch($query, $param['searchDetail'], $param['searchString']);
                        $query = $query->WHERE($param['searchDetail'], '=', Func::encrypt($param['searchString'], 'ENC_KEY_SOL'));     // 암호화 대상 검색
                    }
                }
                else if( $this->searchDetailLikeOption=="" )
                {
                    LOG::debug('암호화 비대상 : '.$param['searchDetail']);
                    $query = $query->WHERE($param['searchDetail'], '=', $param['searchString']);     // 일반 검색
                }
                else if( $this->searchDetailLikeOption=="% " )
                {
                    $query = $query->WHERE($param['searchDetail'], 'like', '%'.$param['searchString']);     // 앞으로 % 검색
                }
                else if( $this->searchDetailLikeOption==" %" )
                {
                    $query = $query->WHERE($param['searchDetail'], 'like', $param['searchString'].'%');     // 뒤로 % 검색
                }
                else
                {
				    $query = $query->WHERE($param['searchDetail'], 'like', '%'.$param['searchString'].'%');     // 앞뒤로 % 검색
                }

			}
            
			// 일자 검색
            if($this->getList('searchDate'))
            {
                foreach($this->getList('searchDate') as $i => $search)
                {
                    if(isset($param[$search['searchDateNm']]))
                    {
                        $colname = $param[$search['searchDateNm']];
                        
                        /*
                        if( strtolower(substr($colname,-5))=="_time" )
                        {
                            $colname = "substr(".$colname.",1,8)";
                        }
                        */
                        if(isset($param[$search['searchDateNm'].'String']))
                        {
                            if( strtolower(substr($colname,-5))=="_time" )
                            {
                                $param[$search['searchDateNm'].'String'].= "000000";
                            }
                            
                            $query = $query->WHERE($colname, '>=', str_replace('-', '', $param[$search['searchDateNm'].'String']) );
                        }
                        if(isset($param[$search['searchDateNm'].'StringEnd']))
                        {
                            if( strtolower(substr($colname,-5))=="_time" )
                            {
                                $param[$search['searchDateNm'].'StringEnd'].= "235959";
                            }
                            $query = $query->WHERE($colname, '<=', str_replace('-', '', $param[$search['searchDateNm'].'StringEnd']) );
                        }
                    }
                }
            }

            // 월 검색
            if($this->getList('searchWol'))
            {
                foreach($this->getList('searchWol') as $i => $search)
                {
                    if(isset($param[$search['searchWolNm']]))
                    {
                        $colname = $param[$search['searchWolNm']];

                        if(isset($param[$search['searchWolNm'].'String']))
                        {
                            if( strtolower(substr($colname,-5))=="_time" )
                            {
                                $param[$search['searchWolNm'].'String'].= "000000";
                            }
                            $query = $query->WHERE($colname, '=', str_replace('-', '', $param[$search['searchWolNm'].'String']) );
                        }
                    }
                }
            }

			// select box 검색
			if($this->getList('searchType'))
			{
                foreach($this->getList('searchType') as $i => $search)
                {
                    $searchTypeNm = str_replace(".","_",$search['searchTypeNm']);
                    // 조회할 테이블 지정하는 경우. 테이블과컬럼을 -로 구분 (ex : users-id)
                    if(strstr($search['searchTypeNm'], '-') && isset($param[$search['searchTypeNm']]))
                    {                        
                        $searchTypeNm = str_replace("-",".",$search['searchTypeNm']);
                        $param[$searchTypeNm] = $param[$search['searchTypeNm']];
                    }

                    if(isset($param[$searchTypeNm]))
                    {
                        // Like 검색 여부
                        if($search['searchLike']=='Y')   
                        {
                            $query = $query->WHERE($searchTypeNm, 'like', '%"'.$param[$searchTypeNm].'"%');
                        }
                        else if(is_array($param[$searchTypeNm]))
                        {
                            if(isset($search['searchCustom']) && $search['searchCustom'] == true)
                            {
                                continue;
                            }

                            $searchTypeNm = strToLower($searchTypeNm);
                            
                            $query = $query->WHEREIN($searchTypeNm,$param[$searchTypeNm]);
                        }
                        else
                        {
                            if(isset($search['searchCustom']) && $search['searchCustom'] == true)
                            {
                                continue;
                            }
                            $query = $query->WHERE($searchTypeNm, '=', $param[$searchTypeNm]);
                        }
                    }
                }
			}

            // select box chain 검색
			if($this->getList('searchTypeChain'))
			{
                foreach($this->getList('searchTypeChain') as $i => $search)
                {
                    $searchTypeNm = str_replace(".","_",$search['searchTypeNm']);
                    // 조회할 테이블 지정하는 경우. 테이블과컬럼을 -로 구분 (ex : users-id)

                    if(strstr($search['searchTypeNm'], '-') && isset($param[$search['searchTypeNm']]))
                    {                        
                        $searchTypeNm = str_replace("-",".",$search['searchTypeNm']);
                        $param[$searchTypeNm] = $param[$search['searchTypeNm']];
                    }

                    if(isset($param[$searchTypeNm]))
                    {                        
                        if(is_array($param[$searchTypeNm]))
                        {
                            $query = $query->WHEREIN($searchTypeNm,$param[$searchTypeNm]);
                        }
                        else
                        {
                            if(isset($search['searchCustom']) && $search['searchCustom'] == true)
                            {
                                continue;
                            }
                            $query = $query->WHERE($searchTypeNm, '=', $param[$searchTypeNm]);
                        }
                    }

                    $searchTypeSubNm = str_replace(".","_",$search['searchTypeSubNm']);
                    if(strstr($search['searchTypeSubNm'], '-') && isset($param[$search['searchTypeSubNm']]))
                    {                        
                        $searchTypeSubNm = str_replace("-",".",$search['searchTypeSubNm']);
                        $param[$searchTypeSubNm] = $param[$search['searchTypeSubNm']];
                    }

                    if(isset($param[$searchTypeSubNm]))
                    {                        
                        if(is_array($param[$searchTypeSubNm]))
                        {
                            $query = $query->whereIn($searchTypeSubNm,$param[$searchTypeSubNm]);
                        }
                        else
                        {
                            if(isset($search['searchCustom']) && $search['searchCustom'] == true)
                            {
                                continue;
                            }
                            $query = $query->WHERE($searchTypeSubNm, '=', $param[$searchTypeSubNm]);
                        }
                    }
                }
			}

            // select box chain 검색
			if($this->getList('searchTypeMultiChain'))
			{
                foreach($this->getList('searchTypeMultiChain') as $i => $search)
                {
                    $searchTypeNm = str_replace(".","_",$search['searchTypeNm']);
                    // 조회할 테이블 지정하는 경우. 테이블과컬럼을 -로 구분 (ex : users-id)

                    if(strstr($search['searchTypeNm'], '-') && isset($param[$search['searchTypeNm']]))
                    {                        
                        $searchTypeNm = str_replace("-",".",$search['searchTypeNm']);
                        $param[$searchTypeNm] = $param[$search['searchTypeNm']];
                    }

                    if(isset($param[$searchTypeNm]))
                    {               
                        Log::debug($param[$searchTypeNm]);         
                        if(is_array($param[$searchTypeNm]))
                        {
                            $query = $query->whereIn($searchTypeNm,$param[$searchTypeNm]);
                        }
                        else
                        {
                            if(isset($search['searchCustom']) && $search['searchCustom'] == true)
                            {
                                continue;
                            }
                            $query = $query->where($searchTypeNm, '=', $param[$searchTypeNm]);
                        }
                    }

                    $searchTypeSubNm = str_replace(".","_",$search['searchTypeSubNm']);
                    if(strstr($search['searchTypeSubNm'], '-') && isset($param[$search['searchTypeSubNm']]))
                    {                        
                        $searchTypeSubNm = str_replace("-",".",$search['searchTypeSubNm']);
                        $param[$searchTypeSubNm] = $param[$search['searchTypeSubNm']];
                    }

                    if(isset($param[$searchTypeSubNm]))
                    {                        
                        if(is_array($param[$searchTypeSubNm]))
                        {
                            $query = $query->whereIn($searchTypeSubNm,$param[$searchTypeSubNm]);
                        }
                        else
                        {
                            if(isset($search['searchCustom']) && $search['searchCustom'] == true)
                            {
                                continue;
                            }
                            $query = $query->where($searchTypeSubNm, '=', $param[$searchTypeSubNm]);
                        }
                    }
                }
			}

            // 구간검색
            if($this->getList('rangeSearchDetail') || (isset($param['rangeSearchDetail']) && !empty($param['rangeSearchDetail'])))
            {   
                if (isset($param['rangeSearchDetail']) && !empty($param['rangeSearchDetail'])) {
                    if(isset($param['sRangeSearchString']))
                    {
                        $s_range = $param['sRangeSearchString'];
                        $query = $query->WHERE($param['rangeSearchDetail'], '>=', $s_range);
                    }
                    if(isset($param['eRangeSearchString']))
                    {
                        $e_range = $param['eRangeSearchString'];
                        $query = $query->WHERE($param['rangeSearchDetail'], '<=', $e_range);
                    }
                }
            }

            if($this->getList('rangeSearchDetail2') || (isset($param['rangeSearchDetail2']) && !empty($param['rangeSearchDetail2'])))
            {   
                if (isset($param['rangeSearchDetail2']) && !empty($param['rangeSearchDetail2'])) {
                    if(isset($param['sRangeSearchString2']))
                    {
                        $s_range = $param['sRangeSearchString2'];
                        $query = $query->WHERE($param['rangeSearchDetail2'], '>=', $s_range);
                    }
                    if(isset($param['eRangeSearchString2']))
                    {
                        $e_range = $param['eRangeSearchString2'];
                        $query = $query->WHERE($param['rangeSearchDetail2'], '<=', $e_range);
                    }
                }
            }

            
            
            // 멀티 검색
			if($this->getList('multiButton'))
			{
                if($param['multi_detail'] != null && $param['multi_content'] != null)
                {
                    $array_content = Func::multiContents($param['multi_content'], $param['multi_detail']);

                    $query = $query->whereRaw($param['multi_detail']." in (".Func::multiArr($param['multi_detail'],array_filter($array_content)).") ");
                }
			}

			return $query;
		}
    }


}