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


function getCustomer(){
  	if(getToken() == ""){
      apiAuthorise();
	  getCustomer();
    }else{
     $massPayments = array();
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
          if($code == "403"){
                 apiAuthorise();
                 getCustomer();
          }
          else{
            if($result[count] == 0){
              break;
            }
            else{
              $countPages++;
              if($code == "403"){
                 apiAuthorise();
                 getCustomer();
              }
              else{
                foreach ($result[items] as $learner) {
                  if($learner[paid_lesson_count] <= 1){
                    if($learner[custom_free_education] != 1){
                        if($learner[custom_individual_chat_id] != null && $learner[custom_individual_chat_id] != ""){
                          if(checkPaymentReminderIndividual($learner[custom_individual_chat_id]) == "nice"){
                            	addPaymentReminderIndividual($learner[custom_individual_chat_id]);
                       			paymentReminder($learner[name],$learner[custom_individual_chat_id], $learner[paid_lesson_count]);
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
    }
}

function paymentReminder($fio,$groupChatId, $paidCount){//Напоминание об оплате
  	
	global $telegram;
  	$brnchId= returnBranchIdByChatId($groupChatId);
  	
      		if($brnchId != "net" && $brnchId != 5){//офлайн
              if($brnchId == 1){
                try{
                    usleep(rand(500000, 800000));
                    $telegram->sendMessage([ 'chat_id' => $groupChatId, 'text' => str_replace("/PaidCount", $paidCount, returnTextPhraseById(13)), 'reply_markup' => $reply_markup ]);

                }catch (Exception $e) {
                }
              }else if($brnchId == 3){
              	try{
                    usleep(rand(500000, 800000));
                    $telegram->sendMessage([ 'chat_id' => $groupChatId, 'text' => str_replace("/PaidCount", $paidCount, returnTextPhraseById(14)), 'reply_markup' => $reply_markup ]);

                }catch (Exception $e) {
                }
              }
              else if($brnchId == 4){
              	try{
                    usleep(rand(500000, 800000));
                  	
                    $telegram->sendMessage([ 'chat_id' => $groupChatId, 'text' => str_replace("/PaidCount", $paidCount, returnTextPhraseById(10)), 'reply_markup' => $reply_markup ]);

                }catch (Exception $e) {
                }
              }
            }else if($brnchId != "net" && $brnchId == 5){//онлайн
            	try{
                  usleep(rand(500000, 800000));
                  $telegram->sendMessage([ 'chat_id' => $groupChatId, 'text' => str_replace("/PaidCount", $paidCount, returnTextPhraseById(11)), 'reply_markup' => $reply_markup ]);

                }catch (Exception $e) {
                }
            }
}

$forcheck = "20".date("y")."-".date("m");
if($forcheck != "2020-12"){
	getCustomer();
}
?>