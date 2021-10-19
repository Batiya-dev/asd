<?php
	include('vendor/autoload.php');
include('db.php');


use Telegram\Bot\Api;
$telegram = new Api('1249473332:AAFdOS-wiFOox1_5YBFUIDNcILHPIh1-5Rw');
$result = $telegram -> getWebhookUpdates();
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


function getGroup(){
  	if(getToken() == ""){
      apiAuthorise();
	  getGroup();
    }else{
      $branches = getAllBranches();
      foreach($branches as $item){
          $countPages = 0;
          for(;;){
            $today =  "20".date("y")."-".date("m")."-".date("d");
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
                 getGroup();
              }
              else{
              if($result[count] == 0){
                break;
              }
              else{
                $countPages++;
                if($code == "403" || $code == "401"){
                   apiAuthorise();
                   getGroup();
                }
                else{
                      //print_r($result);
                  		foreach($result[items] as $group){
                          	if($group[custom_chat_id] != null){
                              	if(checkPaymentReminder($today, $group[custom_chat_id]) == "nice"){
                              		addPaymentReminder($today, $group[custom_chat_id]);
                            		paymentReminder($group[custom_chat_id],$item[branchId]);
                                }
                            }
                        }
                }
                }
              }
          }
      }
    }
}

function paymentReminder($groupChatId, $brnchId){//Напоминание об оплате
  	
	global $telegram;
    
          	if($brnchId != "net" && $brnchId != 5){//офлайн
              if($brnchId == 1){
                try{
                    usleep(rand(500000, 800000));
                    $telegram->sendMessage([ 'chat_id' => $groupChatId, 'text' => returnTextPhraseById(12), 'reply_markup' => $reply_markup ]);

                }catch (Exception $e) {
                }
              }else if($brnchId == 3){
              	try{
                    usleep(rand(500000, 800000));
                    $telegram->sendMessage([ 'chat_id' => $groupChatId, 'text' => returnTextPhraseById(15), 'reply_markup' => $reply_markup ]);

                }catch (Exception $e) {
                }
              }
              else if($brnchId == 4){
              	try{
                    usleep(rand(500000, 800000));
                    $telegram->sendMessage([ 'chat_id' => $groupChatId, 'text' => returnTextPhraseById(5), 'reply_markup' => $reply_markup ]);

                }catch (Exception $e) {
                }
              }
            }else if($brnchId != "net" && $brnchId == 5){//онлайн
            	try{
                  usleep(rand(500000, 800000));
                  $telegram->sendMessage([ 'chat_id' => $groupChatId, 'text' => returnTextPhraseById(9), 'reply_markup' => $reply_markup ]);

                }catch (Exception $e) {
                }
           	}
    
}

if(date("d") == 18 || date("d") == 19 || date("d") == 20){
    if(date("d") == 18 && date("l") != "Saturday" && date("l") != "Sunday"){
        getGroup();
    }else if(date("d") == 19 && date("l") == "Monday"){
        getGroup();
    }else if(date("d") == 20 && date("l") == "Monday"){
        getGroup();
    }
}else if(date("d") == 23 || date("d") == 24 || date("d") == 25){
    if(date("d") == 23 && date("l") != "Saturday" && date("l") != "Sunday"){
        getGroup();
    }else if(date("d") == 24 && date("l") == "Monday"){
        getGroup();
    }else if(date("d") == 25 && date("l") == "Monday"){
        getGroup();
    }
}else if(date("d") == 26 || date("d") == 27 || date("d") == 28){
    if(date("d") == 26 && date("l") != "Saturday" && date("l") != "Sunday"){
        getGroup();
    }else if(date("d") == 27 && date("l") == "Monday"){
        getGroup();
    }else if(date("d") == 28 && date("l") == "Monday"){
        getGroup();
    }
}

?>