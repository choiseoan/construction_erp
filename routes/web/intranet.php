<?php

// 메인
Route::get ('/intranet/main',                   'Intranet\IntranetController@mainContent')          ->name('메인페이지');
Route::post('/intranet/setheadmenu',            'Intranet\IntranetController@setHeadMenu')          ->name('메인 즐겨찾기메뉴 세팅');
Route::post('/intranet/getheadmenu',            'Intranet\IntranetController@getHeadMenu')          ->name('메인 즐겨찾기메뉴 가져오기');
Route::get('/intranet/dashboard',               'Intranet\IntranetController@mainDashBoard')         ->name('메인페이지 추가작업');


#쪽지함
Route::get ('/intranet/msg',                    'Intranet\MessageController@msg')                   ->name('쪽지함');
Route::post('/intranet/msglist',                'Intranet\MessageController@msgList')               ->name('쪽지함리스트');
Route::post('/intranet/msgnav',                 'Intranet\MessageController@msgNav')                ->name('쪽지알림');
Route::any('/intranet/msgpop',                  'Intranet\MessageController@msgPop')                ->name('쪽지보기 및 저장');
Route::post('/intranet/msgaction',              'Intranet\MessageController@msgAction')             ->name('쪽지보내기'); 
Route::post('/intranet/lumpmsgaction',          'Intranet\MessageController@lumpMsgAction')         ->name('쪽지삭제'); 


#게시판
Route::get ('/intranet/board/{div}',            'Intranet\BoardController@board')                   ->name('공지사항 메인'); 
Route::post('/intranet/board/{div}/list',       'Intranet\BoardController@boardList')               ->name('공지사항 메인리스트'); 
Route::post('/intranet/boardform',              'Intranet\BoardController@boardForm')               ->name('공지사항 등록폼'); 
Route::post('/intranet/boardaction',            'Intranet\BoardController@boardAction')             ->name('공지사항 등록 저장'); 
Route::post('/intranet/board/detail',           'Intranet\BoardController@boardDetail')             ->name('공지사항 보기'); 
Route::post('/intranet/boardcomment',           'Intranet\BoardController@boardComment')            ->name('공지사항 댓글보기'); 
Route::get ('/intranet/board/filedown/{no}',    'Intranet\BoardController@boardFileDown')           ->name('공지사항 파일다운로드'); 
Route::post('/intranet/board/filedel',          'Intranet\BoardController@boardFileDelete')         ->name('공지사항 파일삭제'); 
Route::post('/intranet/board/saveworker',       'Intranet\BoardController@saveWorker')              ->name('공지사항 작업자저장'); 



# 내정보관리
Route::get ('/intranet/myinfo',                 'Config\UserController@myInfo')                     ->name('내정보관리'); 
Route::post('/intranet/myinfoaction',           'Config\UserController@myInfoAction')               ->name('내정보관리 저장'); 
Route::post('/intranet/myinfopwdaction',        'Config\UserController@myInfoPwdAction')            ->name('내정보관리 비밀번호저장'); 
Route::get ('/intranet/myloginlog',             'Config\UserController@myLoginLog')                 ->name('내정보관리 로그인기록'); 
Route::post('/intranet/myloginloglist',         'Config\UserController@myLoginLogList')             ->name('내정보관리 로그인기록 리스트'); 

# 데이터 공유
Route::get ('/intranet/datashare',               'Intranet\DataShareController@dataShare')          ->name('데이터공유 메인'); 
Route::post('/intranet/datasharedownload',       'Intranet\DataShareController@dataShareDownload')  ->name('데이터공유 다운로드'); 

// 예약내역
Route::get ('/intranet/reservationdetail',        'Intranet\ReservationDetailController@reservationDetail')     ->name('예약내역 메인');
Route::post ('/intranet/reservationdetaillist',   'Intranet\ReservationDetailController@reservationList')       ->name('예약내역 리스트');
