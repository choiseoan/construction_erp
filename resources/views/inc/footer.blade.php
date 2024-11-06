



  <footer class="main-footer">
    <strong>Copyright &copy; 2024 <a href="http://{{ env('CORP_DOMAIN') }}/" target="_blank">{{ env('CORP_DOMAIN') }}</a></strong> All rights reserved.
  </footer>



</div>
<!-- ./wrapper -->

<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/moment/locales.js"></script>
<script src="/plugins/inputmask/jquery.inputmask.min.js"></script>
<script src="/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.js"></script>
<script src="/plugins/select2/js/select2.min.js?ver=20210914165000"></script>
<script src="/js/icheck.min.js"></script>
<script src="/js/jquery.tmpl.min.js"></script>
<script src="/js/jquery.number.js"></script>
<script src="/js/bs-custom-file-input.min.js"></script>
<script src="/dist/js/adminlte.js"></script>
<!-- <script src="../dist/js/demo.js"></script> -->
<script src="/plugins/bootstrap-switch/js/bootstrap-switch.js"></script>
<script src="https://ssl.daumcdn.net/dmaps/map_js_init/postcode.v2.js"></script>
<script src="/plugins/fullcalendar/main.js"></script>
<script src="/dist/js/script.js?ver=20211015091500"></script>
<script src="/plugins/summernote/summernote-bs4.min.js"></script>
<script src="/plugins/bootstrap-select/js/bootstrap-select.min.js?ver=20210914165000"></script>
<script src="/plugins/bootstrap-select/js/i18n/defaults-ko_KR.min.js?ver=20210914165000"></script>
<script src="/dist/js/PSWebSocket_Client.js"></script>
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/js/jquery.print.min.js"></script>

</body>
<!-- <meta http-equiv="refresh" content="{{ (Vars::$refreshToLogoutTm * 60) }}; url=/intranet/main"></meta> -->
</html>


<script>
    $(document).ready(function() {
      // 리스트
      @if (isset($result) && gettype($result) == 'array' && isset($result['listAction']))
        // 진입시 데이터 가져오기
        getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize(), 'FIRST');

        // 라인 선택시 백그라운드
        $(document).on("click","#tb_{{ $result['listName'] }} tr", function() {
          $("#tb_{{ $result['listName'] }}").find('tr').removeClass('bg-click');
          $(this).addClass('bg-click');          
        });
        
        // 테이블 쓰레드 플로팅
        $(function () {
          $('table.floating-thead>thead').each(function() {
            var ftop = 57;//$('#idNavbar').height() + 20;
            $(this).after( $(this).clone().hide().css('top', ftop).css('position','fixed') );

            // 체크박스 없애기
            var clones = $('table.floating-thead').find('thead:last');
            clones.html(clones.html().replace('<input type="checkbox" name="check-all" id="check-all" class="check-all">', ''));
            
            // 배경색
            clones.css('background', '#d2d2d2');
          });
          
          $(window).scroll(function() {
            $('table.floating-thead').each(function(i) 
            {
              var table = $(this),
                  thead = table.find('thead:first'),
                  clone = table.find('thead:last'),
                  top = table.offset().top,
                  bottom = top + table.height() - thead.height(),
                  left = $('#dataTable').left;//table.position().left,
                  border = 0;//parseInt(thead.find('th:first').css('border-width')),
                  scroll = $(window).scrollTop();
              
              if( scroll < top || scroll > bottom ) 
              {
                clone.hide();
                return true;
              }

              if( clone.is('visible') ) return true;

              clone.css('left',left).show().find('th').each(function(i) {
                $(this).width( thead.find('th').eq(i).width() + border );
              });     

            });
          });
        });

      @endif

      afterAjax();

    });

    $('.brand-link').addClass('navbar-gray-dark');

    //$('.nav-sidebar').addClass('nav-flat');
    $('.nav-sidebar').addClass('nav-compact');
    $('.nav-sidebar').addClass('nav-child-indent');
    $('.main-sidebar').addClass('sidebar-dark-lightblue');

    //$('.main-header').addClass('navbar-dark');
    //$('.main-header').addClass('navbar-gray');

    function lump_btn_click(lumpid, lumpname)
    {
      var lump_open_flag = ( $('.control-sidebar').css("right")=="0px" );

      if( lump_open_flag )
      {
        $('#lump_btn').trigger('click');
      }
      else
      {
        
        if(lumpname!='')
            $('#lump-title').html(lumpname + '<a href="#" onClick="closeLump();" class="close mt-1" style="position: relative;">&times;</a>');

        $('.lump-forms').css("display","none");
        if( $('#LUMP_FORM_'+lumpid).length )
        {
          $('#LUMP_FORM_'+lumpid).css("display","block");
        }
        else
        {
          $('#LUMP_FORM_NONE').css("display","block");
        }
        $('#lump_btn').trigger('click');
      }
    }


    $("#BTN_ADD_HEAD_MENU").on("click", function() {

      var code = "{{ $array_curr_menu['code'] ?? "" }}";
      var mode = ( $("#BTN_ADD_HEAD_MENU i").hasClass('text-gray-light') ) ? "ADD" : "DEL";

      $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
      });

      
      $.post("/intranet/setheadmenu", {code:code, mode:mode}, function(data) {
        if( data=="Y" )
        {
          if( mode=="ADD" )
          {
            $("#BTN_ADD_HEAD_MENU i").removeClass('text-gray-light');
            $("#BTN_ADD_HEAD_MENU i").addClass('text-orange');
          }
          else
          {
            $("#BTN_ADD_HEAD_MENU i").removeClass('text-orange');
            $("#BTN_ADD_HEAD_MENU i").addClass('text-gray-light');
          }
          $.post("/intranet/getheadmenu", {}, function(ddd) {
            $("#HEAD_MENUS_ZONE").fadeOut('fast', function() {
              $("#HEAD_MENUS_ZONE").html(ddd);
              $("#HEAD_MENUS_ZONE").fadeIn('fast');
            });
          });
        }
      });
    });


// EDMS 스캔프로그램
function edmsScanStart(div)
{
  // EDMS 웹소켓 연결
  P_onLoad();
  
  $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var data = 'div='+div;

    $.ajax({
        url  : '/config/socket',
		    data : data,
        type : 'post',
            success : function(result)
            {
              P_SendMSG(result);
            },
            error : function(xhr)
            {
                alert('에러가 발생하였습니다.');
            }
    });
}

// selectpicker
$('.selectpicker').selectpicker({
  width: 'auto',
  style: 'btn-default form-control-sm bg-white',
  display: 'static'
});

</script>