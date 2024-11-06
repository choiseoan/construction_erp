@extends('layouts.master')
@section('content')

<link rel="stylesheet" href="/plugins/fullcalendar/main.css">

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-7">
                <div class="card card-primary" >
                    <div class="card-body p-0" >
                        <!-- THE CALENDAR -->
                        <div id="calendar" class="fc fc-media-screen fc-direction-ltr fc-theme-bootstrap">                      
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection



@section('lump')
일괄처리할거 입력
@endsection



@section('javascript')
<script>

setCalendar();


// 캘린더 출력
function setCalendar(){
   
    // 초기달력 세팅
    var ym_date;
    var holiday= [];
    var Calendar = FullCalendar.Calendar;
    var calendarEl = document.getElementById('calendar');

    var date  = (new Date()).toISOString().slice(0, 10);
    var today = date.split('-');
    var y     = today[0];
    var m     = today[1];

    // 초기월 기준 전월, 당월, 익월 주말 출력
    ym_date = getHoliday(y, m);

    // 주말 색상변경 events
    for (var key in ym_date) { 
        holiday.push({
            backgroundColor : "#ff0000",
            start : ym_date[key]['day'], 
            display : "background",
            id : ym_date[key]['day'],
        });
    }

    // calendar events
    var calendar = new Calendar(calendarEl, {
        height: 750,
        headerToolbar : {
            left      : 'prev',
            center    : 'title',
            right     : 'next'
        },
        themeSystem   : 'bootstrap',
        locale        : 'ko',
        events        : holiday,
        eventClassNames: function(args, $el) {
            for (var key in ym_date) { 
                $("#calendar").find(".fc-daygrid-day[data-date='"+ym_date[key]['day']+"']").css('color', '#FF0000');
            }
        },
        
        dateClick : function(date){
            
            // 클릭한 달력의 년월
            var click_date = date.dateStr.split('-');
            var click_y    = click_date[0];
            var click_m    = click_date[1];
            
            // 이전달, 다음달 버튼 클릭 후 년월 format
            var get_date = calendar.getDate();
            var convert_date = convertDate(get_date);
            var btn_date = convert_date.split('-');
            var btn_y    = btn_date[0];
            var btn_m    = btn_date[1];

            // 날짜클릭시 색상 변경
            var eventList = calendar.getEvents(); 
            
            for($i=0; $i<eventList.length; $i++)
            {
                if(eventList[$i]['_def']['publicId'] == date.dateStr)
                {
                    eventList[$i].remove();
                }
            }
            
            // 클릭한 날짜가 당월인지
            if(click_y+click_m === btn_y+btn_m)
            {   
                $param_holiday = insertHoliday(date.dateStr);

                if($param_holiday['type'] == 'Y')
                {
                    $backColor = '#FF0000';
                    $textColor = '#FF0000';
                }
                else
                {
                    $backColor = '#FFFFFF';
                    $textColor = '#000000';
                }
                
                // 배경색 변경
                calendar.addEvent({
                    backgroundColor : $backColor,
                    id : $param_holiday['holiday'],
                    start : $param_holiday['holiday'],
                    display : "background",
                    
                });
                
                // 날짜색 변경
                $("#calendar").find(".fc-daygrid-day[data-date='"+$param_holiday['holiday']+"']").css('color', $textColor);
            }  
        },        
    });

    calendar.render();

    // 이전달 버튼 클릭시 해당월 주말 출력
    $('#calendar').on('click', '.fc-prev-button', function(){
        
        prev_holiday = [];
        date = calendar.getDate()
        date_ym = convertDate(date);

        var prev_date = date_ym.split('-');
        var prev_y    = prev_date[0];
        var prev_m    = prev_date[1];

        // 이전버튼 클릭 후 해당월의 주말출력
        prev_ym = getHoliday(prev_y, prev_m)
        
        calendar.removeAllEvents();

        for (var key in prev_ym) { 
            calendar.addEvent({
                backgroundColor : "#ff0000",
                id : prev_ym[key]['day'],
                start : prev_ym[key]['day'],
                display : "background"
            });

            // 날짜색 변경
            $("#calendar").find(".fc-daygrid-day[data-date='"+prev_ym[key]['day']+"']").css('color', '#FF0000');
            
        }
    });

    // 다음달 버튼 클릭시 해당월 주말 출력
    $('#calendar').on('click', '.fc-next-button', function(){
        
        next_holiday = [];
        date = calendar.getDate()
        date_ym = convertDate(date);

        var next_date = date_ym.split('-');
        var next_y    = next_date[0];
        var next_m    = next_date[1];

        // 다음버튼 클릭 후 해당월의 주말출력
        next_ym = getHoliday(next_y, next_m)
        
        calendar.removeAllEvents();

        for (var key in next_ym) { 
            calendar.addEvent({
                backgroundColor : "#ff0000",
                id : next_ym[key]['day'],
                start : next_ym[key]['day'],
                display : "background"
            });

            // 날짜색 변경
            $("#calendar").find(".fc-daygrid-day[data-date='"+next_ym[key]['day']+"']").css('color', '#FF0000');
        }
    });
}

// 받은 날짜값을 date 형태로 형변환
function convertDate(date) {
    var date1 = new Date(date);
    return date.yyyymm();
}

// 받은 날짜값을 YYYY-MM 형태로 출력
Date.prototype.yyyymm = function() {
    var yyyy = this.getFullYear().toString();
    var mm = (this.getMonth() + 1).toString();
    return yyyy + "-" + (mm[1] ? mm : "0" + mm[0]);
}

// 주말출력 ajax통신
function getHoliday(y, m){

    var date; 

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $.ajax({
        url   : '/config/calendarholiday',
        type  : 'post',
        async : false,
        data  : { y:y, m:m },
            success : function(result)
            {
                date = result;
            },
            error : function(xhr)
            {
                alert('통신오류입니다.');
            }
    });

    return date;
}

// 날짜 클릭시 주말 insert,delete ajax통신
function insertHoliday(holiday){

    var date; 

    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
    });

    $.ajax({
        url   : '/config/calendarinsert',
        type  : 'post',
        async : false,
        data  : { holiday:holiday },
            success : function(result)
            {
                date = result;
            },
            error : function(xhr)
            {
                alert('통신오류입니다.');
            }
    });
    
    return date;
}


</script>

@endsection