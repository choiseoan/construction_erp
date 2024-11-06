@extends('layouts.master')
@section('content')




<!-- Main content -->
<section class="content">
<div class="container-fluid">




    <div class="row">
    <div class="col-md-4">
            <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title" style="width:100%">
                최상위메뉴 리스트
                <div class='float-right hand' onclick="setTopMenuForm('');">신규등록</div>
                </h3>
                </div>
                <div class="card-body" id="topMenuList" style="height: 490px;">
                </div>
            </div>
          </div>
    <div class="col-md-8">
    <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title" style="width:100%">
                서브메뉴 리스트
                <div class='float-right hand' onclick="setSubMenuForm('');">신규등록</div>                  
                </h3>
                </div>
                <div class="card-body" id="subMenuList" style="height: 490px;">
                </div>
            </div>
          </div>
    </div>    


    <div class="row">

    <div class="col-md-4">
            <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title">최상위메뉴 등록</h3>
                </div>

                <form class="form-horizontal">
                <div class="card-body" id="topMenuForm">

                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-sm btn-info float-right" id="top_menu_btn" onclick="topMenuAction();">저장</button>

                    <button type="button" class="btn btn-sm btn-danger float-right mr-2" id="top_menu_btn_del" onclick="topMenuActionDel();">삭제</button>
                </div>
                </form>

                
            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title">서브메뉴 등록</h3>
                </div>

                <form class="form-horizontal">
                <div class="card-body" id="subMenuForm">

                </div>
                <div class="card-footer">
                <button type="button" class="btn btn-sm btn-info float-right ml-2" id="sub_menu_btn" onclick="subMenuAction();">저장</button>

                <button type="button" class="btn btn-sm btn-danger float-right" id="sub_menu_btn_del" onclick="subMenuActionDel();">삭제</button>
                </div>
                </form>
            </div>
        </div>
        
    </div>





    


</div>
</section>
<!-- /.content -->



@endsection






@section('javascript')
<script>



function setTopMenuList(code)
{
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $.post("/config/menulist", {gubun:'TOP', menu_cd:code}, function(data) {
      $("#topMenuList").html(data);
      setTopMenuForm(code);
  });
}
function setTopMenuForm(code)
{
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $("#topMenuForm").html(loadingString);
  $.post("/config/menuform", {gubun:'TOP', menu_cd:code}, function(data) {
      $("#topMenuForm").html(data);
      enterClear();
      setSubMenuList(code);
  });
  //$("#topMenuList tbody tr").css("background", "#FFFFFF");
}



function setSubMenuList(code)
{
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $("#subMenuList").html(loadingString);
  $.post("/config/menulist", {gubun:'SUB', menu_cd:code}, function(data) {
      $("#subMenuList").html(data);
      if(code) setSubMenuForm('');
  });
}

function setSubMenuForm(code)
{
  var pcode = $("#top_menu_cd").val();
  if( pcode.length!=3 )
  {
    alert("최상위 메뉴가 선택되지 않았습니다.")
    return false;
  }

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $.post("/config/menuform", {gubun:'SUB', pcode:pcode, menu_cd:code}, function(data) {
      $("#subMenuForm").html(data);
      enterClear();

       $("#sub_menu_btn_del").attr("disabled", ( $("#sub_menu_mode").val()=="INS" ));
  });
}





setTopMenuList('001');





//최상위메뉴 등록,수정
function topMenuAction()
{
  if( $("#top_menu_cd").val().length!=3 )
  {
    alert("메뉴코드는 3자리로만 입력가능합니다.")
    return false;
  }
  if( $("#top_menu_nm").val()=="" )
  {
    alert("메뉴명을 입력해주세요.")
    return false;
  }

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  var gubun   = $("#top_menu_gubun").val();
  var mode    = $("#top_menu_mode").val();
  var menu_cd = $("#top_menu_cd").val();
  var menu_nm = $("#top_menu_nm").val();
  var menu_ic = $("#top_menu_icon").val();
  var menu_order = 0;
  var menu_uri   = "";
  var use_yn     = "Y";

  $.post("/config/menuaction", {gubun:gubun, mode:mode, menu_cd:menu_cd, menu_nm:menu_nm, menu_icon:menu_ic, menu_order:menu_order, menu_uri:menu_uri, use_yn:use_yn}, function(data) {
    setTopMenuList('');
    setTopMenuForm('');
    alert(data);
  });
}

//서브메뉴 등록,수정
function subMenuAction()
{
  var pcode = $("#top_menu_cd").val();

  if( $("#sub_menu_cd").val().length!=6 )
  {
    alert("서브메뉴코드는 6자리로만 입력가능합니다.")
    return false;
  }
  if( $("#sub_menu_cd").val().substring(0,3)!=pcode )
  {
    alert("서브메뉴 코드 앞 3자리는 최상위메뉴와 같아야합니다.")
    return false;
  }
  if( $("#sub_menu_nm").val()=="" )
  {
    alert("메뉴명을 입력해주세요.")
    return false;
  }
  if( $("#sub_menu_uri").val()=="" )
  {
    alert("링크주소를 입력해주세요.")
    return false;
  }

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  var gubun   = $("#sub_menu_gubun").val();
  var mode    = $("#sub_menu_mode").val();
  var menu_cd = $("#sub_menu_cd").val();
  var menu_nm = $("#sub_menu_nm").val();
  var menu_ic = $("#sub_menu_icon").val();
  var menu_order = $("#sub_menu_order").val();
  var menu_uri   = $("#sub_menu_uri").val();
  var use_yn     = $("input:checkbox[id='sub_use_yn']").is(":checked") ? "Y" : "N" ;
  var menu_all_view = $("input:checkbox[id='sub_menu_all_view']").is(":checked") ? "Y" : "N" ;
  var sub_share   = $("#sub_menu_share").val();

  $.post("/config/menuaction", {gubun:gubun, mode:mode, menu_cd:menu_cd, menu_nm:menu_nm, menu_icon:menu_ic, menu_order:menu_order, menu_uri:menu_uri, use_yn:use_yn, menu_all_view:menu_all_view, sub_share:sub_share}, function(data) {
    setSubMenuList(pcode);
    setSubMenuForm('');
    alert(data);
  });

}

function subMenuActionDel()
{
  var pcode = $("#top_menu_cd").val();  
  if( !confirm("서브메뉴를 삭제하시겠습니까?") ) return false;

  var gubun   = $("#sub_menu_gubun").val();
  var mode    = "DEL";
  var menu_cd = $("#sub_menu_cd").val();

  $.post("/config/menuaction", {gubun:gubun, mode:mode, menu_cd:menu_cd}, function(data) {
    setSubMenuList(pcode);
    setSubMenuForm('');
    alert(data);
  });
}


function topMenuActionDel()
{
  var pcode = $("#top_menu_cd").val();  
  if( !confirm("최상위메뉴를 삭제하시겠습니까?") ) return false;

  var gubun   = $("#top_menu_gubun").val();
  var mode    = "TOPDEL";
  var menu_cd = $("#top_menu_cd").val();  

  $.post("/config/menuaction", {gubun:gubun, mode:mode, menu_cd:menu_cd}, function(data) {
    if(data=='정상처리되었습니다.')
    {
      setTopMenuList('');
      setTopMenuForm('');
    }
    alert(data);
  });
}



// 엔터막기
function enterClear()
{
  $('input[type="text"]').keydown(function() {
    if (event.keyCode === 13)
    {
      event.preventDefault();
    };
  });

  $("input[data-bootstrap-switch]").each(function() {
  $(this).bootstrapSwitch('state', $(this).prop('checked'));
});

}

enterClear();






$(document).on("click","#topMenuList div table tbody tr", function() {
  $(this).closest('table').find('tr').removeClass('bg-click');
  $(this).addClass('bg-click');
});
$(document).on("click","#subMenuList div table tbody tr", function() {
  $(this).closest('table').find('tr').removeClass('bg-click');
  $(this).addClass('bg-click');
});




</script>
@endsection