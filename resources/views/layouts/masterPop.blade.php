@include('inc/header')

@yield('content')

<!-- ./wrapper -->

<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/moment/locales.js"></script>
<script src="/plugins/inputmask/jquery.inputmask.min.js"></script>
<script src="/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.js"></script>
<script src="/plugins/select2/js/select2.min.js"></script>
<script src="/js/icheck.min.js"></script>
<script src="/js/jquery.tmpl.min.js"></script>
<script src="/js/jquery.number.js"></script>
<script src="/js/bs-custom-file-input.min.js"></script>
<script src="/dist/js/adminlte.js"></script>
<!-- <script src="../dist/js/demo.js"></script> -->
<script src="/plugins/bootstrap-switch/js/bootstrap-switch.js"></script>
<script src="https://ssl.daumcdn.net/dmaps/map_js_init/postcode.v2.js"></script>
<script src="/plugins/fullcalendar/main.js"></script>
<script src="/plugins/summernote/summernote-bs4.min.js"></script>
<script src="/plugins/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="/plugins/bootstrap-select/js/i18n/defaults-ko_KR.min.js"></script>
<script src="/dist/js/script.js?up=202110261000"></script>
<script src="/dist/js/PSWebSocket_Client.js"></script>
<script src="/dist/js/sha512.js"></script>
<script src="/dist/js/socket_frame_pop.js"></script>
<script src="/dist/js/socket.io.js"></script>
<script src="/js/jquery.print.min.js"></script>
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
@yield('javascript')
@yield('javascriptTemp')
</body>
</html>


<script>
   afterAjax();
</script>