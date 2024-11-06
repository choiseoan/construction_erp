<?php

// 부서관리
Route::get ('/config/branch',               'Config\BranchController@branch')               ->name('부서관리 메인');
Route::post('/config/branchlist',           'Config\BranchController@branchList')           ->name('부서관리 메인리스트');
Route::post('/config/branchform',           'Config\BranchController@branchForm')           ->name('부서관리 입력창');      
Route::post('/config/branchaction',         'Config\BranchController@branchAction')         ->name('부서관리 입력저장');

// 직원관리
Route::get ('/config/user',                 'Config\UserController@user')                   ->name('직원관리 메인');
Route::post('/config/userlist',             'Config\UserController@userList')               ->name('직원관리 메인리스트');
Route::post('/config/userexcel',            'Config\UserController@userExcel')              ->name('직원관리 엑셀 다운로드');
Route::post('/config/userform',             'Config\UserController@userForm')               ->name('직원관리 입력창');
Route::post('/config/useraction',           'Config\UserController@userAction')             ->name('직원관리 입력저장');

// 직원접속기록
Route::get ('/config/userloginlog',         'Config\UserController@userLoginLog')           ->name('직원관리 로그인기록'); 
Route::post('/config/userloginloglist',     'Config\UserController@userLoginLogList')       ->name('직원관리 로그인기록 리스트'); 
Route::post('/config/userlogexcel',         'Config\UserController@userLogExcel')           ->name('직원관리 로그인기록 엑셀 다운로드'); 
Route::post('/config/userbranchdiv',        'Config\UserController@userBranchDiv')          ->name('직원관리 부서별 직원출력'); 

// 권한관리(부서별)
Route::get ('/config/permitbranch',         'Config\PermitController@permitBranch')         ->name('메뉴권한관리(부서별) 부서메인');
Route::post('/config/permitbranchmenus',    'Config\PermitController@permitBranchMenus')    ->name('메뉴권한관리(부서별) 메뉴권한리스트');
Route::post('/config/permitbranchaction',   'Config\PermitController@permitBranchAction')   ->name('메뉴권한관리(부서별) 메뉴권한저장');

// 권한관리(직원별)
Route::get ('/config/permituser',           'Config\PermitController@permitUser')           ->name('메뉴권한관리(직원별) 직원메인');
Route::post('/config/permituserlist',       'Config\PermitController@permitUserList')       ->name('메뉴권한관리(직원별) 직원 리스트');
Route::post('/config/permitusermenus',      'Config\PermitController@permitUserMenus')      ->name('메뉴권한관리(직원별) 메뉴권한리스트');
Route::post('/config/permituseraction',     'Config\PermitController@permitUserAction')     ->name('메뉴권한관리(직원별) 메뉴권한저장');

// 기능권한관리(직원별)
Route::get ('/config/funcpermituser',       'Config\PermitController@funcPermitUser')       ->name('기능권한관리(직원별) 직원메인');
Route::post('/config/funcpermitusermenus',  'Config\PermitController@funcPermitUserMenus')  ->name('기능권한관리(직원별) 기능권한리스트');
Route::post('/config/funcpermituseraction', 'Config\PermitController@funcPermitUserAction') ->name('기능권한관리(직원별) 기능권한저장');

// 기능권한변경내역
Route::get ('/config/changepermitinfo',     'Config\ChangeController@changePermitInfo')     ->name('기능권한변경내역 메인');
Route::post('/config/changepermitinfolist', 'Config\ChangeController@changePermitInfoList') ->name('기능권한변경내역 메인 리스트');

// 직원정보변경내역
Route::get ('/config/changeuserinfo',      'Config\ChangeController@changeUserInfo')        ->name('직원정보변경내역 메인');
Route::post('/config/changeuserinfolist',  'Config\ChangeController@changeUserInfoList')    ->name('직원정보변경내역 메인 리스트');
Route::post('/config/changeusertarget',    'Config\ChangeController@changeUserTarget')      ->name('직원정보변경내역 직원 리스트');

// 코드관리
Route::get ('/config/code',                 'Config\CodeController@code')                   ->name('코드관리 메인');
Route::post('/config/codelist',             'Config\CodeController@codeList')               ->name('코드관리 메인 리스트');
Route::post('/config/codeform',             'Config\CodeController@codeForm')               ->name('코드관리 입력창');
Route::post('/config/codeaction',           'Config\CodeController@codeAction')             ->name('코드관리 입력저장');
Route::post('/config/cacheclear',           'Config\CodeController@cacheClear')             ->name('캐시 초기화');
Route::post('/config/subcodeform',          'Config\CodeController@subCodeForm')		    ->name('코드관리 하위코드 입력폼');
Route::post('/config/subcodeaction',        'Config\CodeController@subCodeAction')		    ->name('코드관리 하위코드 저장');

// 영업일관리
Route::get ('/config/calendar',             'Config\CalendarController@calendar')           ->name('영업일관리 메인');
Route::post('/config/calendarholiday',      'Config\CalendarController@calendarHoliday')    ->name('영업일관리 메인 휴일리스트');
Route::post('/config/calendarinsert',       'Config\CalendarController@calendarInsert')     ->name('영업일관리 메인 휴일리스트저장');

// 메뉴관리
Route::get ('/config/menu',                 'Config\MenuController@menu')                   ->name('메뉴관리 메인');
Route::post('/config/menulist',             'Config\MenuController@menuList')               ->name('메뉴관리 메인 리스트');
Route::post('/config/menuform',             'Config\MenuController@menuForm')               ->name('메뉴관리 메인 입력창');
Route::post('/config/menuaction',           'Config\MenuController@menuAction')             ->name('메뉴관리 메인 입력저장');

// 배치관리
Route::get ('/config/batch',                'Config\BatchController@batch')                 ->name('배치관리 메인');
Route::post('/config/batchlist',            'Config\BatchController@batchList')             ->name('배치관리 메인 리스트');
Route::get('/config/batchform',             'Config\BatchController@batchForm')             ->name('배치관리 메인 입력창');
Route::post('/config/batchformaction',      'Config\BatchController@batchFormAction')       ->name('배치관리 메인 입력저장');

// 배치로그
Route::get ('/config/batchlog',             'Config\BatchController@batchLog')              ->name('배치로그 메인');
Route::post('/config/batchloglist',         'Config\BatchController@batchLogList')          ->name('배치로그 메인 리스트');
Route::post('/config/batchlogexcel',        'Config\BatchController@batchLogExcel')         ->name('배치로그 엑셀 다운로드');

// 승인권한관리
Route::get ('/config/confirmpermit',         'Config\PermitController@confirmPermit')       ->name('승인권한관리 메인');
Route::post('/config/getconfirmpermit',      'Config\PermitController@getConfirmPermit')    ->name('승인권한 가져오기');
Route::post('/config/confirmpermitaction',   'Config\PermitController@ConfirmPermitAction') ->name('승인권한 저장');

// 엑셀다운명세
Route::get ('/config/excel',                 'Config\ExcelController@excel')                ->name('엑셀다운 메인');
Route::post('/config/excellist',             'Config\ExcelController@excelList')            ->name('엑셀다운 리스트');
Route::post('/config/excelexcel',            'Config\ExcelController@excelExcel')           ->name('엑셀파일생성');
Route::post('/config/exceldown',             'Config\ExcelController@excelDown')            ->name('엑셀다운 바로실행');

?>