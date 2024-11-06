
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light" id="idNavbar">





    <!-- Favorites -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>

      <div id="HEAD_MENUS_ZONE">
      
      @foreach( $array_head_menu as $value ) 

      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ $value['link'] }}" class="nav-link">{{ $value['name'] }}</a>
      </li>

      @endforeach

      </div>

    </ul>


    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      @if(env('APP_ENV')=='local')
        <h2 style="color:red;">로컬</h2>
      @elseif(env('APP_ENV')=='dev')
        <h2 style="color:red;">개발</h2>
      @endif
      <!-- SEARCH FORM -->
      <!-- <form class="form-inline ml-2" onsubmit="window.open('/erp/bondsearch/'+$('#search_bond').val(),'bondsearch','left=0,top=0,width=1350,height=800,scrollbars=yes'); return false;">
        <div class="input-group input-group-sm">
          <input class="form-control form-control-navbar" type="search" placeholder="현장 검색" aria-label="Search" name="search_bond" id="search_bond" style="width:120px">
            <div class="input-group-append">
            <button class="btn btn-navbar" type="submit">
                <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
      </form>

      <form class="form-inline ml-3" onsubmit="window.open('/erp/search/'+$('#search_string').val(),'custsearch','left=0,top=0,width=1350,height=800,scrollbars=yes'); return false;">
      <div class="input-group input-group-sm">
        <input class="form-control form-control-navbar" type="search" placeholder="발주 검색" aria-label="Search" name="search_string"  id="search_string">
        <div class="input-group-append">
        <button class="btn btn-navbar" type="submit">
            <i class="fas fa-search"></i>
        </button>
        </div>
      </div>
      </form> -->

      <li class="nav-item" style="width:30px">
        <a class="nav-link" href="#" onClick='window.open("/intranet/msgpop", "msgInfo", "width=600, height=800, scrollbars=no");' role="button">
          <i class="fas fa-paper-plane"></i>
        </a>
      </li>

      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown" style="width:30px">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="fas fa-bell pt-1"></i>
          <span class="badge badge-warning navbar-badge" id="myMsgTot" style="display:none;">0</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <div class="dropdown-divider"></div>
          <a href="/intranet/msg?mtype=M" target="_blank" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i><span id="myMsg1">0</span> 새로운 메세지
          </a>
          <div class="dropdown-divider"></div>
          <a href="/intranet/msg?mtype=N" target="_blank" class="dropdown-item">
            <i class="fas fa-bullhorn mr-2"></i><span id="myMsg2">0</span> 새로운 공지
          </a>
          <div class="dropdown-divider"></div>
          <a href="/intranet/msg?mtype=S" target="_blank" class="dropdown-item">
            <i class="fas fa-bell mr-2"></i><span id="myMsg3">0</span> 새로운 시스템알림
          </a>
          <div class="dropdown-divider"></div>
          <a href="/intranet/msg?mdiv=recv" target="_blank" class="dropdown-item dropdown-footer">See All Messages</a>
        </div>
      </li>      

      <li class="nav-item" style="display:none;">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button" id="lump_btn">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>



  </nav>
  <!-- /.navbar -->

<script>
  function searchNoAndOpenWindow() {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    alert('존재하지 않는 번호 입니다');
  }
</script>
