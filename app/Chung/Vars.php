<?php
namespace App\Chung;

use Func;

/*
    공통변수(일반 공통 변수는 코드관리를 가져다 쓰고 여기는 시스템 변수만 넣을것)
*/

class Vars
{
    // 자동 로그아웃 이동 시간 (분)
    public static $refreshToLogoutTm = 65;

    /**
     * 메세지 타입
     *
     * @var array
     */
    public static $arrayMsgType = array(
        'M'=>'메세지',
		'N'=>'공지',
		'S'=>'알람',
    );

    public static $arrayMsgLevel = array(
        'gray'    => '일반',
		'success' => '성공',
		'info'    => '정보',
        'warning' => '경고',
        'error'   => '오류',
		
    );

    /**
     * 기능권한관리 ( 최상위 메뉴코드 => 해당메뉴의 기능권한 추가 )
     *
     * @var array
     */
    public static $arrayFuncPermit = array(
        // 인트라넷
        '001' => Array( 
            'I001' => '게시판 관리자',
        ),
        // 현장관리
        '002' => Array(
            'E001' => '전지점계약열람',
        ),
        // 인사관리     
        '003' => Array(
        ),
        // 시스템설정    
        '006' => Array(
        ),
        // Development
        'DEV' => Array(
            
        ),
        // Extras
        'EXT' => Array(
            
        ),
    );

    /**
     * 로그인 기록 로그인 성공 여부 코드
     *
     * @var array
     */
    public static $arrayLoginSuccess = Array(
        'Y' => '로그인 성공',
        'N' => '로그인 실패',
        'L' => '로그인 잠김',
        'I' => '아이피 차단',
        'P' => '비밀번호 미변경',
        'O' => '로그아웃',
        'A' => '권한없는페이지접근',
    );

    /**
     * 요일
     *
     * @var array
     */
    public static $arrayWeekDay = Array(
        '1' => '월',
        '2' => '화',
        '3' => '수',
        '4' => '목',
        '5' => '금',
        '6' => '토',
        '0' => '일'
    );

    /* 
        YES or NO
    */
    public static $arrayYN = Array(
        'Y'=>'예', 
        'N'=>'아니오', 
    );

    /*
        결재상태
    */
    public static $arrayConfirmStatus = Array(
        'A' => '결재요청',
        'B' => '1차결재',
        'C' => '2차결재',
        'Y' => '결재완료',
        'N' => '결재취소'
    );

    /*
        기타 문서
    */
    public static $arrayEtcDoc = Array(
        '9999999' => '기타 파일',
    );
 
    /**
     * 지역구분
     *
     * @var array
     */
    public static $arrayLocal = array(
        '서울' => '서울',
        '부산' => '부산',
        '대구' => '대구',
        '인천' => '인천',
        '광주' => '광주',
        '대전' => '대전',
        '울산' => '울산',
        '세종' => '세종',
        '경기' => '경기',
        '강원' => '강원',
        '충북' => '충북',
        '충남' => '충남',
        '전북' => '전북',
        '전남' => '전남',
        '경북' => '경북',
        '경남' => '경남',
        '제주' => '제주',
    );

    /**
     * 일괄처리로그 진행상태 
     */
    public static $arrayLumpLogStatus = array(
        "W" => "대기",
        "P" => "진행",
        "C" => "완료",
        "X" => "실패",
        "E" => "오류",
    );

    // 엑셀 다운로그 상태
    public static $arrExcelDownStatus = Array("S"=>"대기", "A"=>"진행", "D"=>"완료", "E"=>"바로실행", "F"=>"실패");

    /**
     * 전산요청 상태
     *
     * @var array
     */
    public static $arrComrequestBoardStatus = Array(
        'A' => '요청',
        'C' => '검수',
        'Y' => '완료',
    );

	/**
     * 현장관리 탭 상태
     *
     * @var array
     */
    public static $arrayFieldStaTab = array(
        'ALL'=>'전체',
        'N'=>'대기',
        'A'=>'진행중',
        'E'=>'완료',
    );
    
    /* 
        YES or NO
    */
    public static $arrayUseYn = Array(
        'Y'=>'사용', 
        'N'=>'미사용', 
    );
}