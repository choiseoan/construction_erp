@extends('errors.minimal')

@section('title', __('접근권한 없음'))
@section('code', '401')
@section('message', __('접근권한이 없습니다. 관리자에게 문의해 주세요.'))
