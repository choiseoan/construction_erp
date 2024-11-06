<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
      @if(env('APP_ENV')!='production')
      {{ ucfirst(explode(".",request()->getHost())[0]) }}
      -
      @endif
      @if (isset($userTitle)) {{ $userTitle }} @elseif (isset($array_curr_menu['name'])) {{ $array_curr_menu['name'] }} @else {{ config('app.comp') }} @endif 
    </title>
  
  <link rel="preconnect" href="https://fonts.gstatic.com">

  <link rel="stylesheet" href="/css/font.css"> 
  <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="/css/ionicons.min.css">
  <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="/css/icheck/icheck.css">
  <link rel="stylesheet" href="/plugins/select2/css/select2.min.css?ver=20210914165000">
  <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css?ver=20210914165000">
  <link rel="stylesheet" href="/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <link rel="stylesheet" href="/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
  <link rel="stylesheet" href="/plugins/bs-stepper/css/bs-stepper.min.css">
  <link rel="stylesheet" href="/plugins/dropzone/min/dropzone.min.css">
  <link rel="stylesheet" href="/plugins/summernote/summernote-bs4.min.css">
  <link rel="stylesheet" href="/dist/css/adminlte.css">
  <link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
  <link rel="stylesheet" href="/plugins/bootstrap-select/css/bootstrap-select.min.css?ver=20210914165000">
  <!-- datatable -->
  <link rel="stylesheet" href="/plugins/datatables/jquery.dataTables.min.css">
  <!-- 부트스트랩 관련 오버라이드 -->
  <link rel="stylesheet" href="/css/public.css">
  <!-- 기타 -->
  <link rel="stylesheet" href="/css/custom.css">
  

</head>
@if (isset($_COOKIE['hide_sidebar']) && $_COOKIE['hide_sidebar'] == 'Y')
<body id="sidebar" class="hold-transition sidebar-mini sidebar-collapse layout-navbar-fixed">
@else
<body id="sidebar" class="hold-transition sidebar-mini layout-navbar-fixed">
@endif


<div class="wrapper">