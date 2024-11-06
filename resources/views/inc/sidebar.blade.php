  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/intranet/main" class="brand-link">
      <img src="/dist/img/AdminLTELogo.png" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text">{{ config('app.comp') }}</span>
    </a>



    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-2 mb-3 d-flex">
        <div class="image">
          <img src="{{ Func::echoProfileImg(Auth::id()) }}" class="img-circle elevation-2" alt="User Image" style="width: 34px; height: 34px;">
        </div>
        <div class="info w-100">
          <div class="row">
            <div class="col-md-8">
              <a href="#collapseExample" class="" data-toggle="collapse" aria-expanded="false" aria-controls="collapseExample">
              {{ Func::chungDecOne(Auth::user()->name) }}  <i class="fa fa-circle text-success"></i>
              </a>
            </div>
            <div class="col-md-4 text-right">
              <button type="button" class="btn btn-sm btn-secondary" title="새창열기" onclick="window.open('/intranet/main');"><i class="fas fa-window-restore"></i></button>
            </div>
          </div>        
        <div class="collapse p-0" id="collapseExample">
          <div class="card mt-2 mb-0 p-0 dark-mode">
            <div class="card-body p-2 m-0 text-center">
              Login : {{ Func::dateFormat(Auth::user()->last_login) }}<br>

              <div class="row mt-1">
              <div class="col-md-12 text-right">                
                <button type="button" class="btn btn-sm btn-secondary mr-1" title="내정보" onclick="location.href='/intranet/myinfo';"><i class="fas fa-user-check"></i></button>
                <button type="button" class="btn btn-sm btn-secondary" title="로그아웃" onclick="location.href='/auth/logout';"><i class="fas fa-sign-out-alt"></i></button>
                </div>
                </div>

            </div>
          </div>
        </div>
        </div>
      </div>

      


      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group dark-mode" data-widget="sidebar-search">
          <input class="form-control form-control-sm " type="search" placeholder="메뉴 검색" aria-label="Mover">
          <div class="input-group-append">
            <button class="btn btn-sm btn-sidebar">
              <i class="fas fa-arrow-right fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column " data-widget="treeview" role="menu" data-accordion="false">

          <li class="nav-item ml-3 text-white">
            <div class="icheck-primary">
              <input id="hide_sidebar" type="checkbox" @if (isset($_COOKIE['hide_sidebar']) && $_COOKIE['hide_sidebar'] == 'Y') checked @endif onClick="chkSidebar(this.checked)">
              <label for="hide_sidebar">
                &nbsp; 메뉴자동숨김
              </label>
            </div>
          </li>


          <? foreach( $array_side_menu as $pmenucd => $pmenuinfo ) { ?>
          <?
            if( !is_numeric($pmenucd) || sizeof($pmenuinfo['sub'])==0 )
            {
              continue;
            }
          ?>
            <li class="nav-item <?=($pmenuinfo['open']) ? 'menu-open' : '';?>">

            <a href="#" class="nav-link <?=($pmenuinfo['open']) ? 'active' : '';?>">
              <i class="nav-icon fas fa-<?=($pmenuinfo['icon']) ? $pmenuinfo['icon'] : 'tachometer-alt'; ?>"></i>
              <p>
                <?=$pmenuinfo['name']?>
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>

            <? if( sizeof($pmenuinfo['sub'])>0 ) { ?>
            <ul class="nav nav-treeview">
            <? foreach( $pmenuinfo['sub'] as $smenucd => $smenuinfo ) { ?>
              <li class="nav-item">
                <a href="<?=$smenuinfo['link']?>" class="nav-link <?=($smenuinfo['open']) ? 'active' : '';?>" mncd="<?=$smenuinfo['code']?>">
                  <i class="nav-icon far fa-<?=($smenuinfo['icon']) ? $smenuinfo['icon'] : 'circle'; ?>"></i>
                  <p><?=$smenuinfo['name']?></p>
                </a>
              </li>
            <? } ?>
            </ul>
            <? } ?>
            </li>

          <? } ?>

          

          <? foreach( $array_side_menu as $pmenucd => $pmenuinfo ) { ?>
          <?
            if( is_numeric($pmenucd) || sizeof($pmenuinfo['sub'])==0 )
            {
              continue;
            }
          ?>

          <li class="nav-item <?=($pmenuinfo['open']) ? 'menu-open' : '';?>">

            <a href="#" class="nav-link <?=($pmenuinfo['open']) ? 'active' : '';?>">
              <i class="nav-icon fas fa-<?=($pmenuinfo['icon']) ? $pmenuinfo['icon'] : 'tachometer-alt'; ?>"></i>
              <p>
                <?=$pmenuinfo['name']?>
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
          {{-- <li class="nav-header"><?=$pmenuinfo['name']?></li> --}}
          <ul class="nav nav-treeview">
          <? foreach( $pmenuinfo['sub'] as $smenucd => $smenuinfo ) { ?>
          <li class="nav-item">
            <a href="<?=$smenuinfo['link']?>" class="nav-link <?=($smenuinfo['open']) ? 'active' : '';?>" mncd="<?=$smenuinfo['code']?>" <?=(substr($smenuinfo['link'],0,4)=="http") ? "target='_blank'" : ""; ?>>
              <i class="nav-icon far fa-<?=($smenuinfo['icon']) ? $smenuinfo['icon'] : 'plus-square'; ?>"></i>
              <p>
                <?=$smenuinfo['name']?>
                <!-- <span class="badge badge-info right">2</span> -->
              </p>
            </a>
          </li>
          <? } ?>
        </ul>

          <? } ?>






          <li class="nav-header">Recent</li>

          <? foreach( $recent_menus as $recent_menu ) { ?>
          <li class="nav-item">
            <a href="<?=$recent_menu['link']?>" class="nav-link">
              <i class="nav-icon far fa-dot-circle"></i>
              <p><?=$recent_menu['name']?></p>
            </a>
          </li>
          <? } ?>
                    
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>



  <div class="modal fade" id="userModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" id="userModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->
