<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @font-face {
            font-family: 'NanumGothic-Regular';
            src: url("{{ storage_path('fonts/NanumGothic-Regular.ttf') }}") format('truetype');
        }
        
        @font-face {
            font-family: 'NanumGothic-Bold';
            src: url("{{ storage_path('fonts/NanumGothic-Bold.ttf') }}") format('truetype');
        }

        body {
            font-size: 11px;
        }

        table {
            border-collapse: collapse;
            margin-bottom: 0;
            width: 100%;
        }

        th, td {
            border: 1px solid black;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .right-align {
            text-align: right;
            height:20px;
            padding-top: 5px;
            padding-left: 5px; 
            padding-right: 10px;
            padding-bottom: 5px;
        }

        /* 비고 */
        .break-word {
            word-wrap: break-word; /* 줄바꿈 설정 */
            word-break: break-all;  /* 긴 단어도 자동 줄바꿈 */
        }

        /* 특이사항 */
        .top-left-align {
            text-align: left;
            vertical-align: top;
            padding: 5px;
        }

        .footer {
            width: 100%;
            text-align: right;
            font-size: 10px;
            color: #666666;
        }

    </style>
</head>
<body>
<div class="header-container" style="display: flex; justify-content: space-between;">
    <table class="title-table">
        <tr>
            <td rowspan="2" colspan="5" style="width: 89.5%; font-size: 20px; font-weight: bold; border-bottom: 0;">
                <img src="{{ asset('dist/img/logo.png') }}" style="width: auto; max-width: 80px; height: auto; float: left; padding-top: 13px; padding-left: 18px;">
                발 주 서 (협력사 발송용)
            </td>
            <td rowspan="3" style="width: 3.33%;">결 재</td>
            <td style="width: 6.66%;">기안</td>
            <td style="width: 6.66%;">심사</td>
            <td style="width: 6.66%;">결정</td>
        </tr>
        <tr>
            <td rowspan="2" style="width: 6.66%;"></td>
            <td rowspan="2" style="width: 6.66%;"></td>
            <td rowspan="2" style="width: 6.66%;"></td>
        </tr>
        <tr>
            <td colspan="5" style="border-top: 0px; padding-left:90px; padding-bottom:10px; font-size: 12px; font-weight: bold;">㈜휴이스&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TEL 031-974-5979&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;FAX 031-974-5978</td>
        </tr>
        <tr>
            <td style="width:17.8%; height:20px; padding: 5px;">현장명</td>
            <td colspan="3" style="width:53.4%; height:20px; padding: 5px;">{{ $info['field_name'] ?? '' }}</td>
            <td style="width:17.8%; height:20px; padding: 5px;">청구일자</td>
            <td colspan="4" style="width:28.6%; height:20px; padding: 5px;">{{ Func::dateFormat($info['order_date'] ?? '') }}</td>
        </tr>
        <tr>
            <td style="width:17.8%; height:20px; padding: 5px;">현장위치</td>
            <td colspan="3" style="padding: 5px; height:20px;">{{ $info['field_addr'] ?? '' }}</td>
            <td style="padding: 5px; height:20px;">반입일자</td>
            <td colspan="4" style="padding: 5px; height:20px;">{{ Func::dateFormat($info['import_date'] ?? '') }}</td>            
        </tr>
        <tr>
            <td style="padding: 5px; height:20px;">협력사명</td>
            <td colspan="3" style="padding: 5px; height:20px;">{{ Func::getArrayName(Func::getArrayPartner(), $info['partner_name'] ?? '') }}</td>
            <td style="padding: 5px; height:20px;">현장담당</td>
            <td colspan="4" style="padding: 5px; height:20px;"></td>
        </tr>
        <tr>
            <td style="padding: 5px; height:20px;">TEL</td>
            <td colspan="3" style="padding: 5px; height:20px;"></td>
            <td style="padding: 5px; height:20px;">담당자 C/P</td>
            <td colspan="4" style="padding: 5px; height:20px;"></td>
        </tr>
        <tr>
            <td style="padding: 5px; height:20px;">FAX</td>
            <td colspan="3" style="padding: 5px; height:20px;"></td>
            <td style="padding: 5px; height:20px;">반입담당</td>
            <td colspan="4" style="padding: 5px; height:20px;">{{ $info['receiver_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td style="padding: 5px; height:20px;">담 당</td>
            <td colspan="3" style="padding: 5px; height:20px;"></td>
            <td style="padding: 5px; height:20px;">담당자 C/P</td>
            <td colspan="4" style="padding: 5px; height:20px;">{{ $info['receiver_ph'] ?? '' }}</td>
        </tr>
        
        <tr>
            <td colspan="9" style="height:5px; border-left: 0; border-right: 0;"></td>
        </tr>

        <tr>
            <th style="width:17.8%; height:20px;">품목</th>
            <th style="width:17.8%; height:20px;">규격(1)</th>
            <th style="width:17.8%; height:20px;">규격(2)</th>
            <th style="width:5%; min-width:37px; height:20px;">단위</th>
            <th style="width:17.8%; height:20px;">발주수량</th>
            <th colspan="4" style="width:17%; height:20px;">비고(특기사항/약도)</th>
        </tr>
        @for ($i = 0; $i < 17; $i++)
            <tr>
                <td style="padding: 5px; height:20px;">{{ $extra[$i]['name'] ?? '' }}</td>
                <td style="padding: 5px; height:20px;">{{ $extra[$i]['standard1'] ?? '' }}</td>
                <td style="padding: 5px; height:20px;">{{ $extra[$i]['standard2'] ?? '' }}</td>
                <td style="padding: 5px; height:20px;">{{ $extra[$i]['type'] ?? '' }}</td>
                <td class="right-align">{{ $extra[$i]['volume'] ?? '' }}</td>
                <td colspan="3" class="break-word" style="padding: 5px; height:20px;">{{ $extra[$i]['etc'] ?? '' }}</td>
                <td colspan="1" style="padding: 5px; height:20px;"></td>
            </tr>
        @endfor
        <tr>
            <td style="padding: 10px; height: 100px;">특이사항</td>
            <td colspan="8" class="top-left-align">{{ $info['order_memo'] ?? '' }}</td>
        </tr>
    </table>
</div>
<div class="footer">
        (주)휴이스
    </div>
</body>
</html>
