<?php
include('vendor/autoload.php');
include('db.php');

use Telegram\Bot\Api;

$telegram = new Api('1249473332:AAFdOS-wiFOox1_5YBFUIDNcILHPIh1-5Rw');

$result = $telegram -> getWebhookUpdates(); //Передаем в переменную $result полную информацию о сообщении пользователя
$textForSendingToChats = "";
$user_phone = $result["message"]["contact"]["phone_number"];//Номер телефона пользователя
$text = $result["message"]["text"]; //Текст сообщения
$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
$usernameChat = $result["message"]["chat"]["username"];
$user_id = $result["message"]["from"]["id"]; 
$first_name = $result["message"]["from"]["first_name"];
$last_name = $result["message"]["from"]["last_name"];
$chat_type = $result["message"]["chat"]["type"];
$name = $result["message"]["from"]["username"]; //Юзернейм пользователя
$keyboardConfirm = array(
    array(
         array(
               'text'=>"Подтвердить",
               'request_contact'=>true
         )
    )
);//Клавиатура с подтверждением
$keyboardStandart = [["Задать вопрос"]];//Клавиатура для клиента
$keyboardStandartClicked = [["Отправить вопрос"]];//Клавиатура для клиента после нажатия кнопки Задать вопрос



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

function getChatBranchByGroups($chat_id){
  	if(getToken() == ""){
      apiAuthorise();
	  getChatBranchByGroups($chat_id);
    }else{
      $branches = getAllBranches();
      foreach($branches as $item){
          $countPages = 0;
          for(;;){
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$item[branchId].'/group/index');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$countPages.'}');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = json_decode(curl_exec($ch), true);
            $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if($code == "403" || $code == "401"){
                 apiAuthorise();
                 getChatBranchByGroups($chat_id);
              }
              else{
              if($result[count] == 0){
                break;
              }
              else{
                $countPages++;
                if($code == "403" || $code == "401"){
                   apiAuthorise();
                   getChatBranchByGroups($chat_id);
                }
                else{
                      //print_r($result);
                  		foreach($result[items] as $group){
                          	if($group[custom_chat_id] == $chat_id){
                            	return $item[branchId];
                            }
                        }
                }
                }
              }
          }
      }
      
    }
}

function getBranchIdInCustomers($ch_id){
  	if(getToken() == ""){
      apiAuthorise();
	  getBranchIdInCustomers($ch_id);
    }else{
      $branches = getAllBranches();
      foreach($branches as $item){
        $countPages = 0;
        for(;;){
          $ch = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$item[branchId].'/customer/index');
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$countPages.'}');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          $result = json_decode(curl_exec($ch), true);
          $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          if($code == "403" || $code == "401"){
               apiAuthorise();
               getBranchIdInCustomers($ch_id);
            }
            else{
            if($result[count] == 0){
              break;
            }
            else{
              $countPages++;
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                 getBranchIdInCustomers($ch_id);
              }
              else{
                	foreach($result[items] as $learner){
                    	if($learner[custom_individual_chat_id] == $ch_id){
                          return $item[branchId];
                        }
                    }
                }
              }
            }
        }
      }
      return "bad";
    }
}

function getCustomer($ch_id, $phoneNumber, $userId){
  	if(getToken() == ""){
      apiAuthorise();
	  getCustomer($ch_id, $phoneNumber, $userId);
    }else{
      global $telegram;
      global $keyboardConfirm;
      global $keyboardStandart;
      $check = 0;
      $branches = getAllBranches();
      foreach($branches as $item){
        $countPages = 0;
        for(;;){
          $ch     = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$item[branchId].'/customer/index');
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$countPages.'}');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          $result = json_decode(curl_exec($ch), true);
          $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          if($code == "403"){
               apiAuthorise();
               getCustomer($ch_id, $phoneNumber, $userId);
            }
            else{
              if($result[count] == 0){
                break;
              }
              else{
                $countPages++;
                if($code == "403"){
                   apiAuthorise();
                   getCustomer($ch_id, $phoneNumber, $userId);
                }
                else{
                  foreach($result[items] as $learner) {
                      foreach($learner[phone] as $phone) {
                          if ($phoneNumber == str_replace(')','',str_replace('(','',str_replace('-', '', $phone))))
                          {
                              $check++;
                              addClient($ch_id, $phoneNumber, $learner[branch_ids][0],$userId, $learner[name], $learner[id], $learner[custom_group_chat_id]);
                          }
                      }
                    }
                }
              }
        	}
      	}
      }
      if($check == 0){
          	$reply = "Произошла ошибка при подтверждении!\nНажмите на кнопку 'Подтвердить' чтобы попробовать снова...";
            $reply_markup = $telegram->replyKeyboardMarkup([ 'keyboard' => $keyboardConfirm, 'resize_keyboard' => true, 'one_time_keyboard' => false ]);
            $telegram->sendMessage([ 'chat_id' =>$ch_id, 'text' => $reply, 'reply_markup' => $reply_markup ]);
      }else{
      	$reply = "Вы успешно подтвердили аккаунт!";
        $telegram->sendMessage([ 'chat_id' => $ch_id, 'text' => $reply, 'reply_markup' => $reply_markup ]);
      }
      
    }
}


if($chat_type == "group" || $chat_type == "supergroup"){
  	if(checkChatsTelegram($chat_id)){
      try{
      	//$telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => ":".getBranchId($chat_id), 'reply_markup' => $reply_markup ]);
      }catch(Exception $e){}
      	$chatBranch = getBranchIdInCustomers($chat_id);
      	if($chatBranch == "bad"){
        	$chatBranch = getChatBranchByGroups($chat_id);
        }
        addToChats($chat_id, $chatBranch);
    }
  	if(checkGroupsTelegram($chat_id)){//если в чат бота добавли первый раз
      $date = new DateTime();
	 $date->add(new DateInterval('P14D'));
    	addNewGroup($date->format('Y-m-d'), $chat_id);
    }
  	if($text){
      	if($text != "/whatChatId"){
          if(checkUsersWithoutQuestion($user_id) == "nice"){
              $managerChatsIdis = getAllChatsManager();
              $checkManager = 0;
              for($i=0;$i<count($managerChatsIdis);$i++){
                  if($chat_id == $managerChatsIdis[$i]){
                      $checkManager++;
                  }
              }

              if($checkManager == 0){
                  $questId = addQuestion($text, $chat_id, $user_id);

                  try{
                    if(checkQuestion($user_id) == true){
                      $branchId = getBranchIdInCustomers($chat_id);
                      if($branchId == "bad"){
                          $branchId = getChatBranchByGroups($chat_id);
                      }
                      if($branchId == "net" || $branchId == "bad"){
                        endQuestionByUserId($user_id);
                        
                      }else{
                        $supportChat = returnSupportChatIdByBranch($branchId);
                        if($supportChat == null || $supportChat == ""){
                          endQuestionByUserId($user_id);
                          
                        }else{
                          
                          $ch2     = curl_init();

                          curl_setopt($ch2, CURLOPT_URL, 'https://api.telegram.org/bot1249473332:AAFdOS-wiFOox1_5YBFUIDNcILHPIh1-5Rw/exportChatInviteLink?chat_id='.$chat_id);
                          curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'POST');
                          curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);

                          if(checkInviteLink($chat_id) != "bad"){
                            addLinkToChat($questId, checkInviteLink($chat_id));
                            
								$telegram->sendMessage([ 'chat_id' => $supportChat, 'text' => "!ВОПРОС!\nId вопроса: ".$questId."\nФИО: ".$first_name." ".$last_name."\nСсылка на чат: ".checkInviteLink($chat_id)."\nВопрос: ".returnQuestionMessage($questId), 'reply_markup' => $reply_markup ]);
                          }else{
                            $result = json_decode(curl_exec($ch2), true);
                            addLinkToChat($questId, $result[result]);
                            
                            $telegram->sendMessage([ 'chat_id' => $supportChat, 'text' => "!ВОПРОС!\nId вопроса: ".$questId."\nФИО: ".$first_name." ".$last_name."\nСсылка на чат: ".$result[result]."\nВопрос: ".returnQuestionMessage($questId), 'reply_markup' => $reply_markup ]);
                          }
                        }
                      }
                    }
                  }catch(Exception $ex){
                  }
              }
          }
        }
        if($text == "/whatChatId"){
        	$telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => "Id чата: ".$chat_id, 'reply_markup' => $reply_markup ]);
        }else if(strripos($text, "/Done") !== false){
          	$managerChatsIdis = getAllChatsManager();
        	$checkManager = 0;
            for($i=0;$i<count($managerChatsIdis);$i++){
                if($chat_id == $managerChatsIdis[$i]){
                    $checkManager++;
                }
            }
          
          	if($checkManager != 0){
              file_put_contents(__DIR__ . '/log10.txt', returnQuestionChatIdById(str_replace('/Done','',$text)) . PHP_EOL, FILE_APPEND);
              	try{
              	$telegram->sendMessage([ 'chat_id' => returnQuestionChatIdById(str_replace('/Done','',$text)), 'text' => returnTextPhraseById(8), 'reply_markup' => $reply_markup ]);
            		$telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => "Вопрос в чате ".returnQuestionChatLinkById(str_replace('/Done','',$text))." закрыт", 'reply_markup' => $reply_markup ]);
                  endQuestion(str_replace('/Done','',$text));
                }catch(Exception $ex){
                }
            }
        }
    }
}else{
  if($text){
        /*if(checkQuestion($chat_id) == "Nice"){
              if($text != "Отправить вопрос "){
                  addMessageToQuesiton($chat_id, $text);
              }
        }*/
        if ($text == "/start") {
            $reply = "Добро пожаловать в бота!\nНажмите на кнопку 'Подтвердить' чтобы продолжить...";
            $reply_markup = $telegram->replyKeyboardMarkup([ 'keyboard' => $keyboardConfirm, 'resize_keyboard' => true, 'one_time_keyboard' => false ]);
            $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup ]);
        }else if($text == "/myId"){
        	$telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => "Ваш id: ".$user_id, 'reply_markup' => $reply_markup ]);
        }/*else if($text == "Задать вопрос"){
            addQuestion($chat_id, 1);
            $reply = returnTextPhraseById(4);
            $reply_markup = $telegram->replyKeyboardMarkup([ 'keyboard' => $keyboardStandartClicked, 'resize_keyboard' => true, 'one_time_keyboard' => false ]);
            $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup ]);
        }else if($text == "Отправить вопрос"){
            endQuestion($chat_id);
            $reply = returnTextPhraseById(5);

            $reply_markup = $telegram->replyKeyboardMarkup([ 'keyboard' => $keyboardStandart, 'resize_keyboard' => true, 'one_time_keyboard' => false ]);
            $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup ]);
        }*/else{
            if(checkQuestion($chat_id) == "Bad"){
              $reply = "По запросу \"<b>".$text."</b>\" ничего не найдено.";
              $telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode'=> 'HTML', 'text' => $reply ]);
            }
        }
  }
  else if($user_phone){//если подтвердил аккаунт
      getCustomer($chat_id, $user_phone, $user_id);
    //$reply = "По запросу \"<b>".$text."</b>\" ничего не найдено.";
             //$telegram->sendMessage([ 'chat_id' => $chat_id, 'parse_mode'=> 'HTML', 'text' => $user_phone]);
  }
  else{
      $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => "Отправьте текстовое сообщение." ]);
      //$telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => "Id: ".$chat_id ]);
  }
}