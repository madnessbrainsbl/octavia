<?php
$tgcheck= shell_exec('dpkg -l|grep bottele');
if (strpos($tgcheck, 'bottelegram') === false) {    
    shell_exec('sudo dpkg -i bottelegram.deb');    
}
?>

<!DOCTYPE html>

<!-- // (c) ООО "Сегнетикс" https://segnetics.com/ru/  https://forum.segnetics.com/ -->
<html>
<head>
    <title>Телеграмм бот</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache" />
    
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,700,900" rel="stylesheet">    
    <link rel="stylesheet" href="css/font-awesome.min.css">
    
    <script src="../../jquery.min.js"></script>
    
    <link href="../../pages/styles/styles.css" rel="stylesheet">
    <link href="../../pages/styles/switches.css" rel="stylesheet">
    <script src="../../pages/js/common.js"></script>
    <link rel="stylesheet" href="../../pages/styles/theme.css" crossorigin="anonymous">
    <link href="../../pages/styles/fonts.css" rel="stylesheet">
    <script src="../../pages/js/popper.min.js" crossorigin="anonymous"></script>
    <script src="../../pages/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <link href="../../pages/styles/component-chosen.min.css" rel="stylesheet" />
    <script src="../../pages/js/chosen.jquery.min.js"></script>
    <script type="text/javascript" src="../../pages/js/moment-with-locales.min.js"></script>
    <script type="text/javascript" src="../../pages/js/moment-timezone-with-data-10-year-range.min.js"></script>
    <script type="text/javascript" src="../../pages/js/tempusdominus-bootstrap-4.min.js"></script>
    <link rel="stylesheet" href="../../pages/styles/tempusdominus-bootstrap-4.min.css" />
    <link rel="stylesheet" href="../../pages/styles/fontawesome.min.css">
    <link href="../../pages/styles/styles.css" rel="stylesheet">
    
          
    
    <!-- Стиль подложки -->
    <style type="text/css">
        .panel{
            background-color: white;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0px;
        }
        
        .logo{
            height: 70px;
        }
        
        .col-md-3{width:25%}
        
        .tab-content {
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding: 10px;
        }
        
        .nav-tabs {
            margin-bottom: 0;
        }
        
        @media screen and (max-width: 992px) {
            .card-body {
                margin-left: 0;
            }
            .form {
                margin-right: 5%;
            }
            .nav {
                padding-left: 2px;
                padding-right: 2px;
            }
            .nav li {
                display: block !important;
                width: 100%;
                margin: 0px;
                border: none;
            }
            .nav li .active {
                font-weight: bold;
                border: none;
                margin: 2px;
            }
        }
        nav a {
            display: block;
            text-decoration: none;
            color: white;
            font-weight: 900;
            margin: 10px 5px;
            border-radius: 5px;
            background-color: rgb(26, 40, 122);
            padding: 10px;
        }

       .StyleReplist {     
        padding: 10px;
        border: none;
        margin-right: 86px;
        margin-left: 86px;
        border-radius: 10px;
        background-color: white;
        min-width: 300px;
        padding-left: 86px;
        padding-right: 86px;     
        }

        .tab { margin-left: 20px;
        }
        .styleUnVis { display:none;
        }
        .styleVis { display:block;
        }

        #fileInput {
            display:none;
        }
    </style>

</head>


<body style="background-color: #e7eaf1" >
    <div class="header panel" style="margin-top: 0px;">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-4">
                <img src="img/logo.png" alt="logotype" height="70px">
            </div>
          </div>
        </div>
      </div>
    
    
        

    <script>        

        function wait() { 

                    document.getElementById('message_del').value = "Ждите...";
                    document.getElementById('return_db').style.display = "none";

                    setTimeout(
                        function(){
                            document.getElementById('message_del').value = "Удалить всех пользователей";
                            document.getElementById('return_db').style.display = "inline-block";
                        }, 1000*10)



                    
            
        };


        //перезагрузка страницы с задержкой
        function reload_interval(time){
            setTimeout(function(){
                location.reload();
            }, time);
        }

        //читаем токен
        $(document).ready(function(){
         $.get("token.php?cmd=get-token", ).done(
                function(data) {
                    let token = data;
                    $("#bottoken").append(token);

                    //console.log(token)
                
                });

        });

        //читаем имя
        $(document).ready(function(){
         $.get("name.php?cmd=get-name", ).done(
                function(data) {
                    let name = data;
                    $("#botname").append(name);
                    //console.log(name)
                
                });

        });

        //читаем номер
        $(document).ready(function(){
         $.get("number.php?cmd=get-number", ).done(
                function(data) {
                    let number = data;
                    $("#botnumber").append(number);
                   // console.log(number)
                
                });
        });

        //читаем пароль
        $(document).ready(function(){
         $.get("password.php?cmd=get-password", ).done(
                function(data) {
                    let password = data;
                    $("#botpassword").append(password);
                   // console.log(number)
                
                });
        });

        //очищаем пользователей
        function user_del() {
                $.get( "clearDB.php?" ).done(
                    function ( data ) {

                        console.log("База пользователей очищена, пользователей " + data);



                    } );
  
        }

        //восстанавливаем пользователей
        function user_return() {
                $.get( "returnDB.php?" ).done(
                    function ( data ) {
                        console.log("База пользователей восстановлена " + data);
                    } );
                setTimeout(function(){document.getElementById('return_db').style.display = "none"},1000*5);
  
        }        

        
        //считаем пользователей
        $( document ).ready( function () {
            localStorage.removeItem( 'area_user' );
            getList();
            setInterval( function () {
                getList();
            }, 1000 * 5);
     
            function getList() {
                $.get( "user.php?cmd=user", ).done(
                    function ( data ) {
                        let area_user = document.querySelector('#area_user');
                        area_user.innerHTML = data;
                        //console.log(data);
                        let count_user = data.split(' ')
                        //console.log(count_user[2]);
                        if (count_user[2]>0){
                            document.getElementById('return_db').style.display = "none";
                        }
                    } );
            }
        } );

        //считаем сообщения
        $( document ).ready( function () {
            localStorage.removeItem( 'message' );
            getList();
            setInterval( function () {
                getList();
            }, 1000 * 5);
     
            function getList() {
                $.get( "message.php?cmd=get-count", ).done(
                    function ( data ) {
                        let message = document.querySelector('#message');
                        message.innerHTML = data;
                        //console.log(message);
                    } );
            }
        } );

        //посылает PHP запрос на удаление отчетов
        function message_del() {
                $.get( "message_del.php?del=message_del" ).done(
                    function ( data ) {
                        console.log("Кэш очищен");

                    } );
  
        }

        //посылает  запрос на перезагрузку бота по изменению паролю и формирует смс
        function restart_password() {
                $.get( "restart.php?restart=password" ).done(
                    function ( data ) {
                        console.log("Подана команда на перезапуск");
                        console.log(data)

                    } );
  
        }
        //посылает  запрос на перезагрузку бота по изменению паролю и формирует смс
        function restart_token() {
                $.get( "restart.php?restart=token" ).done(
                    function ( data ) {
                        console.log("Подана команда на перезапуск");
                        console.log(data)

                    } );
  
        }

        //читаем статус бота
        $( document ).ready( function () {
            localStorage.removeItem( 'botstatus' );
            getList();
            gett_int();
            gett_sd();
            gett_usb();
            setInterval( function () {
                getList();
                gett_int();
                gett_sd();
                gett_usb();
            }, 1000 * 5);
     
            function getList() {
                $.get( "status.php?status=work", ).done(
                    function ( data ) {
                        var status = document.querySelector('#botstatus');

                        console.log(data);

                        if(data.includes('php tg_bot.php') == false){

                            status.innerHTML = "Остановлен";
                            document.getElementById('bot_on').value = "Пуск"
                           
                        }
                        else {status.innerHTML = "Запущен"
                            document.getElementById('bot_on').value = "Стоп"                            
                        }



                    } );
            }


            //статусы хранилищ
            function gett_int(){
            $(document).ready(function(){
             $.get("status.php?status=redirectreports_int").done(
                    function(data) {
                        let int = data;
                        //console.log(int);
                        if (int =='On'){
                            //console.log('Ok');
                            document.getElementById('tgreportint').checked = true; 

                        }

                        else {document.getElementById('tgreportint').checked = false; }

                    
                    });

                })
            };

            function gett_sd(){
            $(document).ready(function(){
             $.get("status.php?status=redirectreports_sd").done(
                    function(data) {
                        let sd = data;
                        //console.log(int);
                        if (sd =='On'){
                            //console.log('Ok');
                            document.getElementById('tgreportsd').checked = true; 

                        }

                        else {document.getElementById('tgreportsd').checked = false; }

                    
                    });

                })
            };

            function gett_usb(){
            $(document).ready(function(){
             $.get("status.php?status=redirectreports_usb").done(
                    function(data) {
                        let usb = data;
                        //console.log(int);
                        if (usb =='On'){
                            //console.log('Ok');
                            document.getElementById('tgreportusb').checked = true; 

                        }

                        else {document.getElementById('tgreportusb').checked = false; }

                    
                    });

                })
            };


        } );

        //посылаем запрос на пуск/стоп бота
        function bot_run() {

            let stat = document.getElementById("bot_on").value;
            console.log(stat);


                $.get( "run.php?run=" + stat ).done(
                    function ( data ) {
                        //console.log("Изменение в режиме работы");
                        //console.log(data)

                    } );

        }

        //восстанавливаем заводские настройки
        function reset_config(){
                $.get( "reset_config.php?" ).done(
                    function ( data ) {

                        //console.log("Конфигурация сброшена по умолчанию" + data);

                    } );

        }

        //сохраняем конфиг
        function save_config() {
            $.get( "user.php?cmd=db", ).done(
                function ( data ) {

                    let user_save = data   
                    let token_save = document.getElementById ('bottoken').innerHTML.trim();
                    let password_save = document.getElementById ('botpassword').innerHTML.trim();
                    let number_save = document.getElementById ('botnumber').innerHTML.trim();

                    const bot_conf = { token: token_save, password: password_save, number: number_save, user: user_save };
                    const a = document.createElement("a");

                    a.href = URL.createObjectURL(new Blob([JSON.stringify(bot_conf, null, 2)], {
                            type: "text/plain"
                          }));
                    a.setAttribute("download", "bot_config.json");
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);

                    //console.log(user_save)
                    //console.log(token_save)
                    //console.log(password_save)
                    //console.log(number_save)

                }
            );
        }

        //загружаем конфиг
        function processFiles(files) {
            var file = files[0];
            var reader = new FileReader();
            reader.onload = function (e) {
                // Когда это событие активируется, данные готовы.
                // Вставляем их в страницу в элемент <div>
                var output = document.getElementById("fileOutput");   
                output.textContent = e.target.result;
               // console.log(output.textContent)
                let arr = JSON.parse(output.textContent)
                //парсим и извлекаем конфиг
                let token_load = arr.token.trim()
                let password_load = arr.password
                let number_load = arr.number
                let user_load = arr.user
                console.log(token_load)
                console.log(password_load)
                console.log(number_load)
                console.log(user_load)
                        
                //
   
                $.get( "loadconf.php?cmd=token&rngval=" + token_load, ).done(
                    function ( data ) {
                        console.log(data);
                    } );
                    
                $.get( "loadconf.php?cmd=password&rngval=" + password_load, ).done(
                    function ( data ) {
                        console.log(data);
                    } );       

                $.get( "loadconf.php?cmd=number&rngval=" + number_load, ).done(
                    function ( data ) {
                        console.log(data);
                    } );  
            
                $.get( "loadconf.php?cmd=user&rngval=" + user_load, ).done(
                    function ( data ) {
                        console.log(data);
                    } );
 
            };
            reader.readAsText(file);

        };
        //загружаем конфиг
        function showFileInput() {
            var fileInput = document.getElementById("fileInput");
            fileInput.click();
        }

        //Пересылка отчетов в ТГ
        //int
        function tgreportint() {
                if (document.getElementById('tgreportint').checked) {
                    //console.log("Пересылка в ТГ включена");
                    $.get( "settings.php?redirectreports=intOn").done(
                        function ( data ) {
                    //console.log("Пересылка в ТГ включена");

                        } );  

                } else {
                    //console.log("Пересылка в ТГ выключена");

                    $.get( "settings.php?redirectreports=intOff").done(
                        function ( data ) {
                    //console.log("Пересылка в ТГ выключена");

                        } );  
                }
            }
        //sd
        function tgreportsd() {
                if (document.getElementById('tgreportsd').checked) {
                    console.log("Пересылка в ТГ включена");
                    $.get( "settings.php?redirectreports=sdOn").done(
                        function ( data ) {
                    //console.log("Пересылка в ТГ включена");

                        } );  

                } else {
                    console.log("Пересылка в ТГ выключена");

                    $.get( "settings.php?redirectreports=sdOff").done(
                        function ( data ) {
                    //console.log("Пересылка в ТГ выключена");

                        } );  
                }
            }
        //usb
        function tgreportusb() {
                if (document.getElementById('tgreportusb').checked) {
                    console.log("Пересылка в ТГ включена");
                    $.get( "settings.php?redirectreports=usbOn").done(
                        function ( data ) {
                    //console.log("Пересылка в ТГ включена");

                        } );  

                } else {
                    //console.log("Пересылка в ТГ выключена");

                    $.get( "settings.php?redirectreports=usbOff").done(
                        function ( data ) {
                    //console.log("Пересылка в ТГ выключена");

                        } );  
                }
            }






    </script>
<div class="container-fluid">
      <div class="row">
        <div class="col-md-3">
            <div class="panel">
                <h4 class="text-center" style="font-size: 18px;">Навигация</h4>
                <nav>
                    <a href="index.php?page=hygro">Гидравлические системы</a>
                    <a href="index.php?page=electric">Электропитание</a>
                    <a href="index.php?page=settings">Настройки</a>
                    <a href="telegram.php">Телеграм</a>
                    <a href="index.php?page=monitor">Мониторинг</a>
                    <a href="index.php?page=contact">Контакты</a>
                </nav>
            </div>
        </div>
        
          <div class="col">
              
             


    <!-- модальное окно задания конфигурации бота -->

    <div class="modal fade" id="eventsDialog_name" tabindex="-1" role="dialog" aria-labelledby="linkEventSettingsTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="linkEventSettingsTitle" data-i18n='Настройки имени'>Настройки имени</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                
                <div class="form-group w-100">
                    <label for="name_bot" data-i18n="Новое имя" class="col-form-label">Задайте имя для отображения</label>


                    <form action="name_w.php" method="post">
                    <input type="text" class="form-control rounded " id="name_bot" name ="name_bot" value="">                           
                                                                                          
                </div>

                    <div class="modal-footer">
                        <input type="submit" id ="submitButton_name" class="btn btn-danger" value="OK">
                                               
                        <button type="button" class="btn btn-primary" data-dismiss="modal" data-i18n='Отмена'>Отмена</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- модальное окно задания конфигурации бота -->

    <div class="modal fade" id="eventsDialog_token" tabindex="-1" role="dialog" aria-labelledby="linkEventSettingsTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="linkEventSettingsTitle" data-i18n='Настройки имени'>Настройки токена</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">

                <div class="form-group w-100">
                    <label for="token_bot" data-i18n="Новое имя" class="col-form-label">Задайте токен, полученный у @BotFather</label>

                    <br><span class="col-form-label">Если у вас нет бота, его нужно создать:</span>
                    <ol class="col-form-label">
                    <li> Если вы работаете с компьютера, авторизируйтесь в <a href="https://web.telegram.org/">веб-версии телеграмм</a> 
                    <li> Нажмите кнопку "Получить токен" - откроется новое окно с доступом в телеграмм
                    <li> Авторизируйтесь Телеграмм, если это требуется, откроется диалог с ботом @Botfather
                    <li> Напишите /start или нажмите кнопку Start
                    <li> Напишите /newbot
                    <li> Задайте любое имя для бота
                    <li> Придумайте уникальное имя с суффиксом _bot
                    <li> Скопируйте токен и введите его в этом окне
                    <li> Нажмите "Ок", дождитесь перезапуска бота. Готово!
                    </ol>


                    <form action="token_w.php" method="post">
                    <input type="text" class="form-control rounded " id="token_bot" name ="token_bot" value="">                           
                                                                                          
                </div>

                    <div class="modal-footer">
                        <button type="button" onclick="window.open('https://t.me/botfather')"  class="btn btn-danger" data-dismiss="modal" data-i18n='Получить токен'>Получить токен</button>

                        <input type="submit" id ="submitButton_token" class="btn btn-primary" value="OK" onclick = "restart_token(this)">
                        
                                                                       
                        <button type="button" class="btn btn-primary" data-dismiss="modal" data-i18n='Отмена'>Отмена</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- модальное окно задания конфигурации бота -->

    <div class="modal fade" id="eventsDialog_number" tabindex="-1" role="dialog" aria-labelledby="linkEventSettingsTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="linkEventSettingsTitle" data-i18n='Настройки имени'>Настройки номера</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">

                <div class="form-group w-100">
                    <label for="name_bot" data-i18n="Новое имя" class="col-form-label">Задайте короткий номер вашего бота (до трёх знаков)</label>

                    <form action="number_w.php" method="post">

                        <script>
                            $('body').on('input', 'input[type="number"][maxlength]', function(){
                                    if (this.value.length > this.maxLength){
                                        this.value = this.value.slice(0, this.maxLength);
                                    }
                            });
                        </script>
                    <input type="number" maxlength="3" class="form-control rounded " id="number_bot" name ="number_bot" value="" min ="0" max = "999">                           
                                                                                          
                </div>

                    <div class="modal-footer">
                        <input type="submit" id ="submitButton_number" class="btn btn-primary" value="OK">
                                               
                        <button type="button" class="btn btn-primary" data-dismiss="modal" data-i18n='Отмена'>Отмена</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- модальное окно задания конфигурации бота -->

    <div class="modal fade" id="eventsDialog_password" tabindex="-1" role="dialog" aria-labelledby="linkEventSettingsTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="linkEventSettingsTitle" data-i18n='Настройки имени'>Настройки пароля</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">

                <div class="form-group w-100">
                    <label for="password_bot" data-i18n="Новое имя" class="col-form-label">Задайте пароль для доступа к боту</label>
                    <br><span class="col-form-label">Внимание! После смены пароля бот будет перезапущен!</span>
                    <br>

                    <form action="password_w.php" method="post">
                    <input type="text" class="form-control rounded " id="password_bot" name ="password_bot" value="">                           
                                                                                          
                </div>

                    <div class="modal-footer">
                        <input type="submit" id ="submitButton" class="btn btn-primary" value="OK" onclick = "restart_password(this)">                     
                        <button type="button" class="btn btn-primary" data-dismiss="modal" data-i18n='Отмена'>Отмена</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>








    <div id="content" class="content">
    
    <div class="page">
        <div class="accordion" id="accordion">
            

            <div class="collapsed-form">

                <div class="collapsed-form-caption collapsed" data-toggle="collapse" data-target="#collapseMasterSettings" aria-expanded="false" aria-controls="collapseMasterSettings">
                    <span data-i18n="Настройки Master">Настройки телеграм бота</span><span class="collapsed-form-caption-chevron ml-2"></span>
                </div>

                <div id="collapseMasterSettings" class="collapse show" aria-labelledby="headerMasterSettings" data-parent="#accordion">
                    <div class="card-body">

                        <div class="row" style="margin-bottom: 10px;">

                            <div class="col-sm-2 col-form-label" data-i18n="Состояние" style = "flex-flow:wrap;">Состояние</div>

                            <div class="col-sm-2 col-form-label" id="botstatus" style="color:#343a40; font-weight:bold; flex-flow:wrap;"></div>

                            <div class="col-sm-2" style = "flex-flow:wrap;">
                                <input type = "button" class="btn btn-danger" style="padding-left: 60px;padding-right: 60px;"  onclick="bot_run(this)" value = "" id="bot_on" name="bot_on"/>
                            </div>

                        </div>

                        <div class="row" style="margin-bottom: 10px;">

                            <div class="col-sm-2 col-form-label" data-i18n="Токен">Токен</div>

                            <div class="col-sm-2 col-form-label" id="bottoken" style="word-wrap:break-word; color:#343a40; font-weight:bold; background: linear-gradient(90deg, rgba(255, 255, 255, 0) 61.9%, #FFFFFF 95.07%);"></div>

                            <div class="col-sm-2">
                                <button type="button" class=" mb-2 btn btn-outline-primary mr-5" data-toggle="modal" data-target="#eventsDialog_token" data-i18n="Изменить">Изменить</button>
                            </div>

                        </div>

                        <div class="row" style="margin-bottom: 10px;">

                            <div class="col-sm-2 col-form-label" data-i18n="Пароль">Пароль</div>

                            <div class="col-sm-2 col-form-label" id="botpassword" style="color:#343a40; font-weight:bold;" type="password"></div>

                            <div class="col-sm-2">
                                <button type="button" class=" mb-2 btn btn-outline-primary mr-5" data-toggle="modal" data-target="#eventsDialog_password" data-i18n="Изменить">Изменить</button>
                            </div>

                        </div>                     

                        <div class="row" style="margin-bottom: 46px;">

                            <div class="col-sm-2 col-form-label" data-i18n="Номер">Номер</div>

                            <div class="col-sm-2 col-form-label" id="botnumber" style="color:#343a40; font-weight:bold;"></div>

                            <div class="col-sm-2">
                                
                            </div>

                        </div>


                        <div class="row" >
                            <div class="col-sm-2" style="margin-bottom: 10px;">    
                                <input type = "button" class="btn btn-primary"  style=" position: relative;padding-left: 35px;padding-right: 35px;" onclick="save_config(this)" value = "Сохранить " id="save_config_btn"/>
                            </div>
                            <div class="col-sm-2" style="margin-bottom: 10px;">    
                                <input type = "button" class="btn btn-primary" style="position: relative; padding-left: 40px;padding-right: 40px;"  onclick="showFileInput();reload_interval(1000*3)" value = "Загрузить" id="load_config"/>
                                <input id="fileInput" type="file" size="50" onchange="processFiles(this.files)">
                                <div id="fileOutput" class = "styleUnVis"></div> 
                                <!--эта строка нужна для работы скриптов загрузки конфига, визуальной функции нет-->
                            </div>
                            <div class="col-sm-2" style="margin-bottom: 46px;">
                                <input type = "button" class="btn btn-danger" style=" position: relative;padding-left: 42px;padding-right: 42px;"  onclick="reset_config(this);reload_interval(1000*2)" value = "Сбросить" id="defailt_config"/>
                            </div>
                        </div>      

                    </div>
                </div>
            </div>


            <div class="collapsed-form">
                <div class="collapsed-form-caption collapsed" data-toggle="collapse" data-target="#collapseMasterSettings1" aria-expanded="false" aria-controls="collapseMasterSettings1">
                    <span data-i18n="Настройки Master">Настройки пользователей </span><span class="collapsed-form-caption-chevron ml-2"></span>
                </div>
                <div id="collapseMasterSettings1" class="collapse" aria-labelledby="headerMasterSettings1" data-parent="#accordion">
                    <div class="card-body">
                        <div class="row" style="margin-bottom: 16px;">
                            <label for="portType" class="col-sm-3 col-form-label" data-i18n="message" id="area_user"></label>
                        </div>
                                                    <!-- Кнопка очистки -->
                        <input type = "button" class="btn btn-danger"   onclick="user_del(this);wait(this)" value = "Удалить всех" id=""/>
                        <input type = "button" class="btn btn-primary styleUnVis"   onclick="user_return(this)" value = "Восстановить" id="return_db"/>
                    </div>
                </div>
            </div>


        <div class="collapsed-form">
            <div class="collapsed-form-caption searchable" id="headingDiag" data-toggle="collapse" data-target="#collapseDiag" aria-expanded="true" aria-controls="collapseDiag">
                <span data-i18n="Диагностика">Настройки сообщений</span><span class="collapsed-form-caption-chevron ml-2"></span>
            </div>
            <div id="collapseDiag" class="collapse" aria-labelledby="headingDiag" data-parent="#accordion" style="">
                    <div class="card-body">
                        <div class="row" style="margin-bottom: 16px;">                            
                            <label for="portType" class="col-sm-3 col-form-label" data-i18n="message" id="message"></label>
                        </div>                        
                            <!-- Кнопка  -->
                        <input type = "button" class="btn btn-danger"   onclick="message_del(this);reload_interval(1000*2)" value = "Очистить кэш" id="message_del"/>
                    </div>
            </div>
        </div>


        <div class="collapsed-form">
            <div class="collapsed-form-caption searchable" id="headingDiag" data-toggle="collapse" data-target="#collapseDiag" aria-expanded="true" aria-controls="collapseDiag">
                <span data-i18n="Диагностика">Автоматическая пересылка отчётов</span><span class="collapsed-form-caption-chevron ml-2"></span>
            </div>
            <div id="collapseDiag" class="collapse" aria-labelledby="headingDiag" data-parent="#accordion" style="">
                    <div class="card-body">
                        <div class="row" style="margin-bottom: 16px;">                            
                            <label for="portType" class="col-sm-3 col-form-label" data-i18n="message" id="message"></label>
                        </div> 

                        <div class="form-group row">
                            <div class="col-sm-3 searchable col-form-label" data-i18n="Включить запись логов">C внутреннего хранилища</div>
                            <div class="input-group col-sm-4">
                                    <label class="switch">
                                    <input type="checkbox" id="tgreportint" class="primary" onclick="tgreportint()" >
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-3 searchable col-form-label" data-i18n="Включить запись логов">C SD-карты</div>
                            <div class="input-group col-sm-4">
                                    <label class="switch">
                                    <input type="checkbox" id="tgreportsd" class="primary" onclick="tgreportsd()">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-3 searchable col-form-label" data-i18n="Включить запись логов">C USB-носителя</div>
                            <div class="input-group col-sm-4">
                                    <label class="switch">
                                    <input type="checkbox" id="tgreportusb" class="primary" onclick="tgreportusb()">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                    </div>
            </div>
        </div>
        </div>
    </div>
    </div>
      </div>
</div>


</body>
</html>