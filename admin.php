<?php
include('vendor/autoload.php');
include('db.php');



//------------------------------------------------------
function apiAuthorise(){
  $ch = curl_init();
  $data    = ['email' => 'dev@atp-24.ru', 'api_key' => 'e9fdb6c0-81a6-11eb-abf7-0cc47a6ca50e'];

  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json']);
  curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/auth/login');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = json_decode(curl_exec($ch), true);
  $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  updateToken($result['token']);
}
//------------------------------------------------------

function getCustomer(){
  	if(getToken() == ""){
      apiAuthorise();
	  getCustomer();
    }else{
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
      curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/branch/index');
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
      curl_setopt($ch, CURLOPT_POSTFIELDS, '{"is_active":1,"page":0}');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $result = json_decode(curl_exec($ch), true);
      $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if($code == "403"){
         apiAuthorise();
	  	 getCustomer();
      }
      else{
        foreach($result as $item){
          foreach($item as $value){
            addOrCheckBranch($value[id], $value[name]);
            addOrCheckBranchLink($value[id]);
          }
        }
      }
    }
}

getCustomer();

function sendTextToIndividualChats($texttosend, $branchToSend){
	if(getToken() == ""){
      apiAuthorise();
	  sendTextToIndividualChats($texttosend, $branchToSend);
    }else{
      	global $telegram;
        $countPages = 0;
        for(;;){
          $ch = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$branchToSend.'/customer/index');
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$countPages.'}');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          $result = json_decode(curl_exec($ch), true);
          $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          if($code == "403" || $code == "401"){
               apiAuthorise();
               sendTextToIndividualChats($texttosend, $branchToSend);
            }
            else{
            if($result[count] == 0){
              break;
            }
            else{
              $countPages++;
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                 sendTextToIndividualChats($texttosend, $branchToSend);
              }
              else{
                	foreach($result[items] as $learner){
                    	if($learner[custom_individual_chat_id] != null){
                        	$telegram->sendMessage([ 'chat_id' => $learner[custom_individual_chat_id], 'text' => $texttosend, 'reply_markup' => $reply_markup ]);
                        }
                    }
                }
              }
            }
        }
    }
}

function sendTextToGroupChats($texttosend, $branchToSend){
	if(getToken() == ""){
      apiAuthorise();
	  sendTextToGroupChats($texttosend, $branchToSend);
    }else{
     	  global $telegram;
          $countPages = 0;
          for(;;){
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$branchToSend.'/group/index');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$countPages.'}');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = json_decode(curl_exec($ch), true);
            $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if($code == "403" || $code == "401"){
                 apiAuthorise();
                 sendTextToGroupChats($texttosend, $branchToSend);
              }
              else{
              if($result[count] == 0){
                break;
              }
              else{
                $countPages++;
                if($code == "403" || $code == "401"){
                   apiAuthorise();
                   sendTextToGroupChats($texttosend, $branchToSend);
                }
                else{
                      //print_r($result);
                  		foreach($result[items] as $group){
                          	if($group[custom_chat_id] != null){
                              	$telegram->sendMessage([ 'chat_id' => $group[custom_chat_id], 'text' => $texttosend, 'reply_markup' => $reply_markup ]);
                            }
                        }
                }
                }
              }
          }
      }
}

//----- Реализация бота -------
use Telegram\Bot\Api;

$telegram = new Api('1249473332:AAFdOS-wiFOox1_5YBFUIDNcILHPIh1-5Rw');

$result = $telegram -> getWebhookUpdates();
//- - - - - - - - - - - - - - -


if (!isset($_COOKIE['adminLogin']))   
{   
    header("Location: /admin_login.php");
}
if (!empty($_GET['exit'])){
    setcookie("adminLogin", "", time() - 3600);
    header("Location: /admin_login.php");
}

if(isset($_POST['btnIdUserSend'])){
	addUserId($_POST['textIduser']);
  	header("Location: /admin.php");
}

if(isset($_POST['btnMailingSend'])){//Нажатие на кнопку: Рассылка сообщений во все чаты
  	$texttosend = $_POST['textMailing'];
  	$branchToSend = $_POST['inputGroupSelect03'];
  	
  	sendTextToGroupChats($texttosend, $branchToSend);
  	sendTextToIndividualChats($texttosend, $branchToSend);
  	
  
  	header("Location: /admin.php");
}




// = - = - = - = - = - = - = - = - = - = - = 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panel</title>
    <!-- BOOTSTRAP LINK -->
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script
  src="https://code.jquery.com/jquery-3.5.1.js"
  integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc="
  crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    <!-- ADMIN CSS -->
    <link rel="stylesheet" href="css/admin.css" type="text/css" />
    <style>
        .otherItem{
            border-radius: 25px;
        }
        .otherItem:hover{
            background-color: #eeeeee;
            transition: 0.2s;
        }
        .otherItem a{
            color: black;
        }
        .otherItem a:hover{
            text-decoration: underline !important;
        }
    </style>
</head>
<body>
    <div class="container">
      
        <div class="row">
          <div class="offset-lg-9 col-lg-3">
              <a class="nav-link" href="?exit=14">Выход<img class="logout-img" src="img/logout.png" alt="Выйти"></a>
          </div>
        </div>

        <div class="row border" style="padding: 15px">
            <div class="col-xl-12">
                <h1 class="text-center titleCenter">Смена фраз бота</h1>
            </div>
            <form class="col-xl-12 text-center" method="POST" enctype='multipart/form-data'>
              <select style="margin-bottom: 15px" class="custom-select" id="inputGroupSelect01">
                <?php $allPhrases = allPhrases(); foreach($allPhrases as $item):?>
                <option value="<?=$item['id']?>"><?=$item['phrase']?></option>
                <?php endforeach; ?>
              </select>
                   <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Текст:</span>
                      </div>
                      <textarea class="form-control" id="textPhrases" name="textPhrases" aria-label="Текст:"></textarea>
                    </div>
              		<button style="margin-top: 20px" id="btnPhrases" name="btnPhrases" type="submit" class="btn btn-success">Сохранить</button>
            </form>
        </div>
      	
      	<div class="row border" style="padding: 15px; margin-top: 15px">
      		<div class="col-xl-12">
                <h1 class="text-center titleCenter">Ссылки на гугл-форму</h1>
            </div>
          	
          	<form class="col-xl-12 text-center" method="POST" enctype='multipart/form-data'>
          		<select style="margin-bottom: 15px" class="custom-select" id="inputGroupSelect04">
                  <?php $allBranches = getAllBranches(); foreach($allBranches as $item):?>
                  	<option value="<?=$item['branchId']?>"><?=$item['name']?></option>
                  <?php endforeach; ?>
                </select>
              
              	<div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Id чата:</span>
                      </div>
                      <textarea class="form-control" id="textGoogleLink" name="textGoogleLink" aria-label="Ссылка на гугл-форму:"></textarea>
                </div>
              		<button style="margin-top: 20px" id="btnGoogleLink" name="btnGoogleLink" type="submit" class="btn btn-success">Сохранить</button>
          	</form>
        </div>
      
        <div class="row border" style="padding: 15px; margin-top: 15px">
      		<div class="col-xl-12">
                <h1 class="text-center titleCenter">Чат поддержки филиала</h1>
            </div>
          	
          	<form class="col-xl-12 text-center" method="POST" enctype='multipart/form-data'>
          		<select style="margin-bottom: 15px" class="custom-select" id="inputGroupSelect02">
                  <?php $allBranches = getAllBranches(); foreach($allBranches as $item):?>
                  	<option value="<?=$item['branchId']?>"><?=$item['name']?></option>
                  <?php endforeach; ?>
                </select>
              
              	<div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Id чата:</span>
                      </div>
                      <textarea class="form-control" id="textChatLink" name="textChatLink" aria-label="Ссылка на чат:"></textarea>
                </div>
              		<button style="margin-top: 20px" id="btnChatLink" name="btnChatLink" type="submit" class="btn btn-success">Сохранить</button>
          	</form>
        </div>

        <div class="row border" style="padding: 15px; margin-top: 15px">
            <div class="col-xl-12">
                <h1 class="text-center titleCenter">Рассылка сообщений во все чаты</h1>
            </div>
            <form class="col-xl-12 text-center" method="POST" enctype='multipart/form-data'>
                   <select style="margin-bottom: 15px" class="custom-select" id="inputGroupSelect03" name="inputGroupSelect03">
                        <?php $allBranches = getAllBranches(); foreach($allBranches as $item):?>
                          <option value="<?=$item['branchId']?>"><?=$item['name']?></option>
                        <?php endforeach; ?>
                   </select>
                   <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Текст:</span>
                      </div>
                      <textarea class="form-control" name="textMailing" aria-label="Текст:"></textarea>
                    </div>
              		<button style="margin-top: 20px" name="btnMailingSend" type="submit" class="btn btn-success">Отправить</button>
            </form>
        </div>
      
      	<div class="row border" style="padding: 15px; margin-top: 15px">
            <div class="col-xl-12">
                <h2 class="text-center titleCenter">Добавление ID-человека который не будет отправлять вопросы</h2>
            </div>
            <form class="col-xl-12 text-center" method="POST" enctype='multipart/form-data'>
                   <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">Id:</span>
                      </div>
                      <textarea class="form-control" name="textIduser" aria-label="Id:"></textarea>
                    </div>
              		<button style="margin-top: 20px" name="btnIdUserSend" type="submit" class="btn btn-success">Отправить</button>
            </form>
        </div>
    </div>
  	<script>
      	window.onload = function () { 
          	$.ajax({
                url: "getTextPhrase.php",
                type: "POST",
                data: ({ id:  document.getElementById("inputGroupSelect01").options.selectedIndex}),
                success: function(data) {
                    $('#textPhrases').val( data );    // здесь задаете новое значение для инпута
                }
            });
          	$.ajax({
                url: "getChatLink.php",
                type: "POST",
                data: ({ id:  document.getElementById("inputGroupSelect02").value}),
                success: function(data) {
                    $('#textChatLink').val( data );    // здесь задаете новое значение для инпута
                }
            });
          	$.ajax({
                url: "getGoogleLink.php",
                type: "POST",
                data: ({ id:  document.getElementById("inputGroupSelect04").value}),
                success: function(data) {
                    $('#textGoogleLink').val( data );    // здесь задаете новое значение для инпута
                }
            });
        }
      	
      	$('#btnPhrases').click(function(){
          	$.post(
              "/updateTextPhrase.php",
              {
                id: document.getElementById("inputGroupSelect01").options.selectedIndex,
                text: $('#textPhrases').val()
              },
              onAjaxSuccess
            );
        });
      	$('#btnChatLink').click(function(){
          	$.post(
              "/updateChatLink.php",
              {
                id: document.getElementById("inputGroupSelect02").value,
                text: $('#textChatLink').val()
              },
              onAjaxSuccessChatLink
            );
        });
      	$('#btnGoogleLink').click(function(){
          	$.post(
              "/updateGoogleLink.php",
              {
                id: document.getElementById("inputGroupSelect04").value,
                text: $('#textGoogleLink').val()
              },
              onAjaxSuccessGoogleLink
            );
        });
      	function onAjaxSuccessGoogleLink(data){
        	alert("Ссылка на гугл-форму успешно отредактирована!");
        }
      	function onAjaxSuccessChatLink(data){
        	alert("Ссылка на чат успешно отредактирована!");
        }
      	function onAjaxSuccess(data)
        {
          alert("Фраза успешно отредактирована!");
        }
      
  		$('#inputGroupSelect01').change(function(){
             $.ajax({
                url: "getTextPhrase.php",
                type: "POST",
                data: ({ id:  document.getElementById("inputGroupSelect01").options.selectedIndex}),
                success: function(data) {
                    $('#textPhrases').val( data );    // здесь задаете новое значение для инпута
                }
            });
        });
      	$('#inputGroupSelect02').change(function(){
             $.ajax({
                url: "getChatLink.php",
                type: "POST",
                data: ({ id:  document.getElementById("inputGroupSelect02").value}),
                success: function(data) {
                    $('#textChatLink').val( data );    // здесь задаете новое значение для инпута
                }
            });
        });
      
      	$('#inputGroupSelect04').change(function(){
             $.ajax({
                url: "getGoogleLink.php",
                type: "POST",
                data: ({ id:  document.getElementById("inputGroupSelect04").value}),
                success: function(data) {
                    $('#textGoogleLink').val( data );    // здесь задаете новое значение для инпута
                }
            });
        });
  	</script>
</body>
</html>