		var vHost = "127.0.0.1";
		var vPort = "8443";
		var vContent = "Pace";
		var vURL = "wss://" + vHost + ":" + vPort + "/" + vContent;
		var ListStartNum = 0;
		var vConn = "N";

		var start = function () 
		{
  		try
			{		
				var wsImpl = window.WebSocket || window.MozWebSocket;
				
				window.ws = new wsImpl(vURL);

				ws.onopen = function () 
				{
					vConn = "Y";
				};
				
				ws.onclose = function () 
				{
					vConn = "N";
				}
				
			}
			catch (err) 
			{
				console.log(err);
				alert("WebSocket 기능을 지원하지 않는 브라우저입니다.");
			}
		}
		
		
		function P_onLoad() 
		{			
			P_UIInit();
			P_ServerConn();
		}		
		
		function P_UIInit() 
		{
			// var ViewMode = document.getElementById('ViewMode');
			// var SendMode = document.getElementById('SendMode');
			// ViewMode.checked = true;
			// SendMode.checked = false;
		}
		
		
		function P_ServerConn() 
		{
			if (vConn == "Y")
			{
				return;
			}
			
			start();
			//vConn == "Y"
		}
		
		function P_ServerDisConn() 
		{
			if (vConn == "N")
			{
				return;
			}
				
			ws.close();
			//vConn == "N"
		}
		
		function P_ServerConnFail()
		{
			// alert("WebSocket 서버에 연결되지 않았습니다.");
			if (confirm("EDMS Client 프로그램이 설치되어 있지 않습니다.\n프로그램을 다운로드 하시겠습니까?\n(※다운로드 후 설치 파일을 실행해 주세요.)")) {
				window.open('https://edms.leadcorp.co.kr:8080/download/EDMS_Client.exe', '_blank');
			}
		}
		
		function P_SendMessage(MSG) 
		{
			ws.send(MSG);
		}
		
		
		function P_SendMSG(MSG) 
		{
			console.log(MSG);
			
			if (vConn == "N")
			{
				P_ServerConnFail();
				return vConn;
			}
			
			//신 소비자금융시스템에 작업시에는 아래와 같이 'MSG|' 값을 제외한 JSON 전체 정보를 전송해 주세요.
			//위에 'MSG|' 값은 화면에서 메시지 확인 용도입니다.
			P_SendMessage(MSG);
		}