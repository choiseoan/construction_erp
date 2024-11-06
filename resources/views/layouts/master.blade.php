<?
$array_my_menu = Func::getMyMenu();
$array_side_menu = $array_my_menu['SIDE'];
$array_head_menu = $array_my_menu['HEAD'];
$array_curr_menu = $array_my_menu['CURR'];
if(!sizeof($array_curr_menu))
{
    echo view('errors.401');
    exit;
    //response(null, 403);
    // echo Redirect::withErrors(401);
    // exit;
}    
// RECENT MENUS
$recent_menus = [];
if( Cookie::has('recent_menus') && Cookie::get('recent_menus') )
{
    $recent_menus = json_decode(Cookie::get('recent_menus'), true);
}
if( isset($array_my_menu['CURR']['code']) && isset($recent_menus[$array_my_menu['CURR']['code']]) )
{
    unset($recent_menus[$array_my_menu['CURR']['code']]);
}
if( sizeof($recent_menus)>=5 )
{
    unset($recent_menus[array_key_first($recent_menus)]);;
}
if(isset($array_my_menu['CURR']['code']))
{
    $recent_menus[$array_my_menu['CURR']['code']] = $array_my_menu['CURR'];
}
$cookie = Cookie::queue('recent_menus', json_encode($recent_menus), 1440);
$recent_menus = array_reverse($recent_menus);


?>

@include('inc/header')
@include('inc/navbar')
@include('inc/sidebar')

@if( sizeof($array_curr_menu)>0 )


    <div class="content-wrapper">

    <section class="content-header @if($array_curr_menu['code']=='011001') p-0 @endif">
    <div class="container-fluid">
        <div class="row @if($array_curr_menu['code']!='011001') mb-2 @endif ">
        <div class="col-sm-12">
                <h1>
                <span id="masterTitle">{{ $array_curr_menu['name'] }}</span>
                <small>({{ $array_curr_menu['code'] }})</small>

                <button class="btn btn-navbar" type="button" id="BTN_ADD_HEAD_MENU">
                <i class="fas fa-star {{ isset($array_head_menu[$array_curr_menu['code']]) ? 'text-orange' : 'text-gray-light' }}"></i>
                </button>

                {{-- ipcc  --}}
                @if($array_curr_menu['code']=='011001')
                <span id="ipccCallStatus" class="text-sm ml-5"></span>
                @endif 

                </h1>
                
        </div>
        <!--
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">{{ $array_curr_menu['pmnm'] }}</li>
                <li class="breadcrumb-item active">{{ $array_curr_menu['name'] }}</li>
            </ol>
        </div>
        -->
        </div>
    </div>
    </section>

    @yield('content')

@else

    권한없음

@endif






<!-- 위로
<a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top"><i class="fas fa-chevron-up"></i></a>
-->

</div>




<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark position-fixed mt-5" >
<div class="p-3 text-center">
@if(View::hasSection('lump'))
<div class='lump'>
    <div class="lump-title" id="lump-title">일괄처리</div>
    <div class="lump-contents">
    @yield('lump')
    </div>
</div>
@else
    <div style='padding-top:80px;'>
    <div class="display-2"><i class="fas fa-ghost"></i></div>
    <div class="pt-4">실행 가능한 일괄처리가 없습니다.</div>
    </div>
@endif      
</div>
</aside>





@include('inc/footer')
@yield('javascript')
@yield('javascriptSms')


<script src="/plugins/toastr/toastr.min.js"></script>



<script>
    window.laravel_echo_port='6001';
</script>

<script>

var blinkMsgTot;
    function getMyMsg()
    {
      $.ajax({ 
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'POST',
        url : "/intranet/msgnav", 
        contentType: 'application/json; charset=utf-8', 
        dataType: 'json', 
        success : function(data) {

          if( typeof data.total !== "undefined" && data.total!="0" )
          {
            //$('#myMsgTot').css('display','block');
            clearInterval(blinkMsgTot);
            blinkMsgTot = setInterval( function(){ $("#myMsgTot").toggle(); }, 800 );
          }
          else
          {
            $('#myMsgTot').css('display','none');
            clearInterval(blinkMsgTot);
          }
          
          //success,info,warning,danger
          $('#myMsgTot').removeClass('badge-info');
          $('#myMsgTot').removeClass('badge-warning');
          $('#myMsgTot').removeClass('badge-error');
          $('#myMsgTot').removeClass('badge-success');

          if( typeof data.error !== "undefined" && data.error!="0" )
          {
            $('#myMsgTot').addClass('badge-error');
          }
          else if( typeof data.warning !== "undefined" && data.warning!="0" )
          {
            $('#myMsgTot').addClass('badge-warning');
          }
          else if( typeof data.success !== "undefined" && data.success!="0" )
          {
            $('#myMsgTot').addClass('badge-success');
          }
          else
          {
            $('#myMsgTot').addClass('badge-info');
          }

          $('#myMsgTot').html(data.total);
          $('#myMsg1').html(data.M);
          $('#myMsg2').html(data.N);
          $('#myMsg3').html(data.S);
        }
      });
    }

    getMyMsg();
    
</script>



{{-- 로컬 테스트시 추가. env('REDIS_HOST')=="172.18.2.58" ||  --}}
{{-- @if( env('REDIS_HOST')=="127.0.0.1" || env('REDIS_HOST')=="172.17.1.43" || Auth::user()->id=='truman' ) --}}
@php
    
    // 로컬 개발환경을 loc-로, 테스트계를  dev-로 설정해야 테스트계로 요청을 보낸다.
    if(stristr(Request::getHost(), 'loc-'))
    {
        $SOKET_HOST = 'http://'.str_replace('loc-', 'dev-', Request::getHost());
    }
    else 
    {
        $SOKET_HOST = '//'.Request::getHost();
    }

@endphp

@if(env('APP_ENV')!='local')
{{-- <script src="{{ $SOKET_HOST }}:6001/socket.io/socket.io.js"></script> --}}
<script src="{{ url('/js/laravel-echo-setup.js') }}" type="text/javascript"></script>
@endif

<style>
.toast-top-right-margin { 
  position: absolute;
  right: 0;
  top: 60px;
  z-index: 1040;
}
</style>
<script type="text/javascript">
    window.Echo.private('message.{{ Auth::user()->id }}').listen('SendMessage', (data) =>
    {

        var toastDiv = document.createElement("div");
        toastDiv.id = "toast_msg_"+data.no;


        // M 메세지(사용자간쪽지) - 메세지를 띄웠다가 5초후 사라짐
        if( data.msg_type=='M' )
        {
            if( data.msg_level=="error" ) data.msg_level = "danger";
            $(document).Toasts('create', {
                msgno: data.no,
                title: data.send_nm+'('+data.send_id+') - '+data.send_time,
                body: data.title,
                class: 'bg-'+data.msg_level,
                autohide: true,
                delay: 86400000,
                icon: 'fas fa-envelope',
                position: 'bottomRight',
            });
        }
        // N 공지
        else if( data.msg_type=='N' )
        {
            if( data.msg_level=="error" ) data.msg_level = "danger";
            $(document).Toasts('create', {
                msgno: data.no,
                title: data.send_nm+'('+data.send_id+') - '+data.send_time,
                body: data.title,
                class: 'bg-'+data.msg_level,
                icon: 'fas fa-bullhorn',
                position: 'bottomRight',
            });
        }
        // S 알람(시스템)
        else if( data.msg_type=='S' )
        {
            toastr.options = { onclick: function () { popupMsg(data.no); }, "class":"mt-5", "timeOut":10000, "extendedTimeOut":30000 };
            toastr.options.positionClass = "toast-top-right-margin";
            
            if( data.msg_level=='success' )
            {
                toastr.success(data.title);
            }
            else if( data.msg_level=='error' )
            {
                toastr.error(data.title);
            }
            else if( data.msg_level=='warning' )
            {
                toastr.warning(data.title);
            }
            else if( data.msg_level=='info' )
            {
                toastr.info(data.title);
            }
            else
            {
                toastr.info(data.title);
            }
        }

        getMyMsg();
        console.log(data);
    });

</script>

{{-- @endif --}}