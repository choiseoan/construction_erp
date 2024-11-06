@if( auth()->check() )
<script>
    //alert('이미 로그인상태입니다.');
    location.href = '/intranet/main';
</script>
@elseif( session('error') )
<script>
    alert("{{ session('error') }}");
</script>
@endif

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.comp') }}</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="/css/font.css">
  <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="/dist/css/adminlte.css">
  <script src="/dist/js/script.js?ver=20211015091500"></script>
</head>
<body class="hold-transition login-page pb-5"">
<div class="login-box pb-5">
  <div class="login-logo">
    <a href="/"><img src=""></a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg font-weight-bold">{{ config('app.comp') }}</p>

      <form action="/auth/login" method="post" name="form">
      {{ csrf_field() }}

        <div class="input-group mb-3">
          <!--키보드보안 이슈로 인해 npkencrypt 주석처리 및 키이벤트 제외 
            <input type="text" class="form-control" id="id" name="id" placeholder="ID" npkencrypt="key" onKeyup="doEnter(event, 'id')">-->
          <input type="text" class="form-control" id="id" name="id" placeholder="ID" <?=(isset($_COOKIE['saved_id']) && $_COOKIE['saved_id']!='')?"value='".$_COOKIE['saved_id']."'":""?>>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" id="password" name="password" placeholder="Password" npkencrypt="key" onKeyup="doEnter(event, 'password')">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember" <?=(isset($_COOKIE['saved_id']) && $_COOKIE['saved_id']!='')?"checked":""?>>
              <label for="remember" class="text-sm">
                아이디저장
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="button" class="btn btn-primary btn-block text-sm" onClick="doLogin()">로그인</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
{{-- 키보드보안때문에 아래로 변경 <script src="/plugins/jquery/jquery.min.js"></script> --}}
<script src="/pluginfree/js/jquery-1.11.0.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/dist/js/adminlte.js"></script>

<script type="text/javascript">
  function doEnter(e, inputParam)
  {
    if(e.keyCode === 13)
    {
      doLogin();
    }
  }

  function doLogin()
  {
    if($('#id').val()=='')
    {
      alert('아이디를 입력해주세요'); 
      // $('#id').focus();
      // npPfsCtrl.doFocusOut();
      return false;
    }

    if($('#password').val()=='')
    {
      alert('비밀번호를 입력해주세요'); 
      // $('#password').focus();
      // npPfsCtrl.doFocusOut();
      return false;
    }
    
    // 아이디저장
    if($('#remember').is(':checked'))
    {
      setCookie('saved_id', $('#id').val(), 7);
    }
    else
    {
      setCookie('saved_id', '', 7);
    }
    
    form.submit();
  }

  </script>

</body>
</html>
