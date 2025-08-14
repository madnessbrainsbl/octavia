<?php
//Patched vnc
session_start();

if (!isset($_SESSION['username'])) {
    header("Location:.");
    exit();
}

$port = 6080;
$password = $_SESSION['usersbyname'][$_SESSION['username']]["passw"];
//$host = $_SERVER['SERVER_ADDR'];
$host = 'control.octava.online';
$ovpn_ip= get_local_ip('tun');
function get_local_ip($interface){
    $out = explode(PHP_EOL, shell_exec("/usr/sbin/ip a"));
    foreach($out as $str){
        $arr=explode(' ', $str);
        $ip=false;
        $tun=false;
        foreach($arr as $key=>$value){
            if($value == 'inet'){
                $ip=$arr[$key+1];
            }
            if(substr( $value, 0, 3 ) === $interface){
                $iface=true;
            }
            if($ip && $iface){
                return explode('/',$ip)[0];
            }
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
   <head>
      <title>Segnetics VNC</title>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <style>
         body {
         width: auto;
         margin: 0;            
         height: 100%;
         display: flex;
         flex-direction: column;
         }
         html {
         height: 100%;
         }
         #top_bar {           
         color: #858AA0; 
         padding: 6px 5px 0px 5px;     
         margin-right: 0;       
         }        
         #status {
         text-align: left;
         box-sizing: none;
         }
         #screen {
         flex: 1; /* fill remaining space */
         overflow: hidden;
         
         }

         #screen:focus {
         flex: 1; /* fill remaining space */
         overflow: hidden;
         border: 2px blue solid;
         
         }

         canvas:focus {
            box-shadow: 0 0 20px 10px rgb(255 255 255 / 25%);
           /*  border: 2px red solid;*/
         }
      </style>
      <!-- Promise polyfill for IE11 -->
      <script src="vendor/promise.js"></script>
      <link href="../pages/styles/switches.css" rel="stylesheet">
      <script src="../pages/js/common.js"></script>
      <link rel="stylesheet" href="../pages/styles/theme.css" crossorigin="anonymous" rel="stylesheet">
      <link href="../pages/styles/fonts.css" rel="stylesheet">
      <link href="../pages/styles/styles.css" rel="stylesheet">
      <script src="../jquery.min.js"></script>
      <script src="../pages/js/popper.min.js" crossorigin="anonymous"></script>
      <script src="../pages/js/bootstrap.min.js" crossorigin="anonymous"></script>
     <link rel="stylesheet" href="../pages/styles/fontawesome.min.css">
      <!-- ES2015/ES6 modules polyfill -->
      <script nomodule src="vendor/browser-es-module-loader/dist/browser-es-module-loader.js"></script>    
      <script type="module" crossorigin="anonymous">
         // RFB holds the API to connect and communicate with a VNC server
         import RFB from './core/rfb.js';
         
         var rfb;
         let desktopName;
         
         // When this function is called we have
         // successfully connected to a server
         function connectedToServer(e) {
             status(langsGet("Поключено к ") + desktopName + " ["+host+"]");
             $("#refresh").hide();
             setTimeout(function() {              
                $("canvas").focus();
                }, 
             1000 );             
         }
         
         // This function is called when we are disconnected
         function disconnectedFromServer(e) {
             if (e.detail.clean) {
                 status(langsGet("Отключено"));
             } else {
                 status(langsGet("Ошибка. Соединение разорвано."));
             }

             $("#refresh").show();
         }
         
         // When this function is called, the server requires
         // credentials to authenticate
         function credentialsAreRequired(e) {
             const password = prompt(langsGet("Необходим пароль:"));
             rfb.sendCredentials({ password: password });
         }
         
         // When this function is called we have received
         // a desktop name from the server
         function updateDesktopName(e) {
             desktopName = e.detail.name;
         
         }
         
         // Since most operating systems will catch Ctrl+Alt+Del
         // before they get a chance to be intercepted by the browser,
         // we provide a way to emulate this key sequence.
         function sendCtrlAltDel() {
             rfb.sendCtrlAltDel();
             return false;
         }
         
         // Show a status text in the top bar
         function status(text) {
             document.getElementById('status').textContent = text;
         }
         
         function scaleScreen() {
             rfb.scaleViewport = document.getElementById("scale").checked;
         }
         
         const host = "<?php echo $host?>";
         let port =  <?php echo $port?>;
         let ovpn_ip =  "<?php echo $ovpn_ip?>";
         const password =  "<?php echo $password?>";
         const path = "/var/www";
         status("Подключение");
         
         document.getElementById("scale").addEventListener("click", scaleScreen);
         
         // Build the websocket URL used to connect
         let url;
         if (window.location.protocol === "https:") {
             url = 'wss';             
         } else {
             url = 'ws';
             port = port+1;
         }
         url += '://' + host;
         
         url += ':' + port;             
         
         url += '/' + path;
         url += '/?ip=' + ovpn_ip;
         
         
//let wsProtocol = window.location.protocol === "https:" ? "wss://" : "ws://";
//let wsHost = window.location.hostname;
//let wsPort = window.location.port ? ":" + window.location.port : "";
//let m = window.location.pathname.match(/^\/controller\.php\/(\d+)/);
//let ctrlSegment = m ? `/controller.php/${m[1]}/` : '';
//url = wsProtocol + wsHost + wsPort + ctrlSegment;
         
         // Creating a new RFB object will start a new connection
         rfb = new RFB(document.getElementById('screen'), url,
                       { credentials: { password: password }, preferHextile: true });
         
         // Add listeners to important events from the RFB module
         rfb.addEventListener("connect",  connectedToServer);
         rfb.addEventListener("disconnect", disconnectedFromServer);
         rfb.addEventListener("credentialsrequired", credentialsAreRequired);
         rfb.addEventListener("desktopname", updateDesktopName);         
         rfb.qualityLevel = 8;         
          
      </script>
      <script>
          function reload() {
            $("#refresh").hide();
            window.location.reload(true);            
          }

	function takeScreenshot(event) {	
		 $('canvas')[0].toBlob((blob)=>{
			let URLObj = window.URL || window.webkitURL;
			let a = document.createElement("a");  
			a.href = URLObj.createObjectURL(blob);
			a.download = "screenshot.png";
			document.body.appendChild(a);
			a.click();
			document.body.removeChild(a);
		  });		
	}
      </script>
   </head>
   <body>
      <div id="screen">
        <button class="btn btn-primary ml-5 mt-5" style="display:none" id="refresh" onclick="reload()">Повторить подключение</button>
      </div>
     <div id="top_bar" class="row text-center">
    	<button class="navbar-button btn ml-2 p-0 text-secondary" onclick="takeScreenshot(event)"><i class="fa fa-camera"></i></button>
         <div class="row ml-5 pt-2">
            <div class="">
               <label class="switch switch-mini">
               <input type="checkbox" id="scale" class="primary">
               <span class="slider slider-mini round"></span>
               </label>
            </div>
            <label for="scale" data-i18n="Масштабировать">Масштабировать</label>
         </div>
	
         <div id="status" class="text-right col" data-i18n="Загрузка">Поключено к Segnetics VNC [192.168.0.128]</div>
      </div>
   </body>
</html>