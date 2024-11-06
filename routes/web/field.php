<?php

// 현장관리

// 현장정보창
Route::get ('/field/management',                    'Field\ManagementController@management')                    ->name('현장관리 메인');
Route::post('/field/managementlist',                'Field\ManagementController@managementList')                ->name('현장관리 리스트');
Route::post('/field/managementexcel',               'Field\ManagementController@managementExcel')               ->name('현장관리 엑셀다운');
Route::post('/field/managementform',                'Field\ManagementController@managementForm')                ->name('현장관리 입력 form');
Route::post('/field/managementformaction',          'Field\ManagementController@managementFormAction')          ->name('현장관리 입력 form 저장');
Route::post('/field/managementlumpdelete',          'Field\ManagementController@managementLumpDelete')          ->name('현장관리 일괄삭제');

Route::get ('/field/managementpop',                 'Field\ManagementController@managementPop')                 ->name('현장관리 팝업창');

Route::post('/field/managementinfo',                'Field\ManagementController@managementInfo')                ->name('현장관리 정보');
Route::post('/field/managementinfoaction',          'Field\ManagementController@managementInfoAction')          ->name('현장관리 저장');

Route::post('/field/managementhistory',             'Field\ManagementController@managementHistory')             ->name('현장내역 정보');
Route::post('/field/managementhistoryaction',       'Field\ManagementController@managementHistoryAction')       ->name('현장내역 저장');

Route::post('/field/managementhistorylist',         'Field\ManagementController@managementHistoryList')         ->name('현장내역 코드리스트');
Route::post('/field/managementhistorysearch',       'Field\ManagementController@managementHistorySearch')       ->name('현장내역 일위대가 찾기');
Route::post('/field/managementcostcode',            'Field\ManagementController@managementCostCode')            ->name('현장내역 일위대가 코드 찾기');
Route::get ('/field/managementhistoryexcelsample',  'Field\ManagementController@managementHistoryExcelSample')	->name('현장내역 엑셀업로드샘플파일다운로드');
Route::post('/field/managementhistoryexcelform',    'Field\ManagementController@managementHistoryExcelForm')    ->name('현장내역 엑셀업로드');
Route::post('/field/managementhistoryexcelaction',  'Field\ManagementController@managementHistoryExcelAction')  ->name('현장내역 엑셀업로드액션');
Route::post('/field/managementhistoryexcelremove',  'Field\ManagementController@managementHistoryExcelRemove')  ->name('현장내역 엑셀업로드삭제');
Route::post('/field/managementhistoryexcel',        'Field\ManagementController@managementHistoryExcel')        ->name('현장내역 엑셀다운');

Route::post('/field/managementcost',                'Field\ManagementController@managementCost')                ->name('일위대가 정보');
Route::post('/field/managementcostlist',            'Field\ManagementController@managementCostList')            ->name('일위대가 리스트');
Route::post('/field/managementcostexcel',           'Field\ManagementController@managementCostExcel')           ->name('일위대가 엑셀다운');
Route::post('/field/managementcostallclear',        'Field\ManagementController@managementCostAllClear')        ->name('일위대가 일괄삭제');
Route::post('/field/managementcostaction',          'Field\ManagementController@managementCostAction')          ->name('일위대가 저장');

Route::post('/field/managementcostform',            'Field\ManagementController@managementCostForm')            ->name('일위대가 form');
Route::post('/field/managementcostformaction',      'Field\ManagementController@managementCostFormAction')      ->name('일위대가 form 저장');
Route::post('/field/managementmaterialsearch',      'Field\ManagementController@managementMaterialSearch')      ->name('일위대가 자재단가표 찾기');
Route::get ('/field/managementcostpop',             'Field\ManagementController@managementCostPop')             ->name('일위대가 pop');
Route::post('/field/managementcostpopaction',       'Field\ManagementController@managementCostPopAction')       ->name('일위대가 pop 저장');

Route::get ('/field/managementcostexcelsample',     'Field\ManagementController@managementCostExcelSample') 	->name('일위대가 엑셀업로드샘플파일다운로드');
Route::post('/field/managementcostexcelform',       'Field\ManagementController@managementCostExcelForm')       ->name('일위대가 엑셀업로드');
Route::post('/field/managementcostexcelaction',     'Field\ManagementController@managementCostExcelAction')     ->name('일위대가 엑셀업로드액션');

Route::post('/field/managementmaterial',            'Field\ManagementController@managementMaterial')            ->name('자재관리 정보');
Route::post('/field/managementmateriallist',        'Field\ManagementController@managementMaterialList')        ->name('자재관리 리스트');
Route::post('/field/managementmaterialexcel',       'Field\ManagementController@managementMaterialExcel')       ->name('자재관리 엑셀다운');
Route::post('/field/managementmaterialallclear',    'Field\ManagementController@managementMaterialAllClear')    ->name('자재관리 일괄삭제');

Route::post('/field/managementmaterialform',        'Field\ManagementController@managementMaterialForm')        ->name('자재관리 form');
Route::post('/field/managementmaterialformaction',  'Field\ManagementController@managementMaterialFormAction')  ->name('자재관리 form 저장');

Route::get ('/field/managementmaterialexcelsample', 'Field\ManagementController@managementMaterialExcelSample')	->name('자재관리 엑셀업로드샘플파일다운로드');
Route::post('/field/managementmaterialexcelform',   'Field\ManagementController@managementMaterialExcelForm')   ->name('자재관리 엑셀업로드');
Route::post('/field/managementmaterialexcelaction', 'Field\ManagementController@managementMaterialExcelAction') ->name('자재관리 엑셀업로드액션');





// 발주관리
Route::get ('/field/order',                         'Field\OrderController@order')                              ->name('발주관리 메인');
Route::post('/field/orderlist',                     'Field\OrderController@orderList')                          ->name('발주관리 리스트');
Route::post('/field/orderform',                     'Field\OrderController@orderForm')                          ->name('발주관리 입력 form');
Route::post('/field/orderformaction',               'Field\OrderController@orderFormAction')                    ->name('발주관리 입력 form 저장');

Route::get ('/field/orderpop',                      'Field\OrderController@orderPop')                           ->name('발주관리 팝업창');

Route::post('/field/orderinfo',                     'Field\OrderController@orderInfo')                          ->name('발주관리 정보');
Route::post('/field/ordercontractsearch',           'Field\OrderController@ordercontractsearch')                ->name('발주관리 계약수량 찾기');
Route::post('/field/orderinfoaction',               'Field\OrderController@orderInfoAction')                    ->name('발주관리 저장');
Route::post('/field/orderinfopdf',                  'Field\OrderController@orderInfoPdf')                       ->name('발주관리 PDF 저장');
Route::post('/field/orderinfoexcel',                'Field\OrderController@orderInfoExcel')                     ->name('발주관리 엑셀다운');
Route::post('/field/orderextraallclear',            'Field\OrderController@orderExtraAllClear')                 ->name('발주관리 일괄삭제');

Route::post('/field/orderstore',                    'Field\OrderController@orderStore')                         ->name('입고수량 정보');
Route::post('/field/orderstorelist',                'Field\OrderController@orderStoreList')                     ->name('입고수량 리스트');
Route::post('/field/orderstoreexcel',               'Field\OrderController@orderStoreExcel')                    ->name('입고수량 엑셀다운');
Route::post('/field/orderstoreaction',              'Field\OrderController@orderStoreAction')                   ->name('입고수량 저장');
Route::post('/field/orderstoreallclear',            'Field\OrderController@orderStoreAllClear')                 ->name('입고수량 일괄삭제');

Route::get ('/field/orderstoreexcelsample',         'Field\OrderController@orderStoreExcelSample')	            ->name('입고수량 엑셀업로드샘플파일다운로드');
Route::post('/field/orderstoreexcelform',           'Field\OrderController@orderStoreExcelForm')                ->name('입고수량 엑셀업로드');
Route::post('/field/orderstoreexcelaction',         'Field\OrderController@orderStoreExcelAction')              ->name('입고수량 엑셀업로드액션');

Route::post('/field/orderstoreform',                'Field\OrderController@orderStoreForm')                     ->name('입고수량 form');
Route::post('/field/orderstoreformaction',          'Field\OrderController@orderStoreFormAction')               ->name('입고수량 form 저장');

Route::post('/field/ordercompare',                  'Field\OrderController@orderCompare')                       ->name('수량비교 정보');
Route::post('/field/ordercomparelist',              'Field\OrderController@orderCompareList')                   ->name('수량비교 리스트');
Route::post('/field/ordercompareexcel',             'Field\OrderController@orderCompareExcel')                  ->name('수량비교 엑셀다운');


Route::post('/field/ordercontract',                 'Field\OrderController@orderContract')                      ->name('계약수량 정보');
Route::post('/field/ordercontractlist',             'Field\OrderController@orderContractList')                  ->name('계약수량 리스트');
Route::post('/field/ordercontractexcel',            'Field\OrderController@orderContractExcel')                 ->name('계약수량 엑셀다운');
Route::post('/field/ordercontractallclear',         'Field\OrderController@orderContractAllClear')              ->name('계약수량 일괄삭제');

Route::post('/field/ordercontractform',             'Field\OrderController@orderContractForm')                  ->name('계약수량 form');
Route::post('/field/ordercontractformaction',       'Field\OrderController@orderContractFormAction')            ->name('계약수량 form 저장');

Route::get ('/field/ordercontractexcelsample',      'Field\OrderController@orderContractExcelSample')	        ->name('계약수량 엑셀업로드샘플파일다운로드');
Route::post('/field/ordercontractexcelform',        'Field\OrderController@orderContractExcelForm')             ->name('계약수량 엑셀업로드');
Route::post('/field/ordercontractexcelaction',      'Field\OrderController@orderContractExcelAction')           ->name('계약수량 엑셀업로드액션');

Route::get ('/field/store',                         'Field\StoreController@store')                              ->name('입고수량명세 정보');
Route::post('/field/storelist',                     'Field\StoreController@storeList')                          ->name('입고수량명세 리스트');
Route::post('/field/storeexcel',                    'Field\StoreController@storeExcel')                         ->name('입고수량명세 엑셀다운');

Route::get ('/field/partner',                       'Field\PartnerController@partner')                          ->name('협력사 정보');
Route::post('/field/partnerlist',                   'Field\PartnerController@partnerList')                      ->name('협력사 리스트');
Route::post('/field/partnerform',                   'Field\PartnerController@partnerForm')                      ->name('협력사 상세보기');
Route::post('/field/partnerformaction',             'Field\PartnerController@partnerFormAction')                ->name('협력사 저장');
Route::post('/field/partnerexcel',                  'Field\PartnerController@partnerExcel')                     ->name('협력사 엑셀다운');

?>