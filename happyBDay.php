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

function checkClientInGroup($customerId, $groupId, $branch){
	if(getToken() == ""){
      apiAuthorise();
	  checkClientInGroup($customerId, $groupId, $branch);
    }else{
        $countPages = 0;
        for(;;){
          $today =  "20".date("y")."-".date("m")."-".date("d");
          $ch = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$branch.'/cgi/index?group_id='.$groupId);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$countPages.'}');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          $result = json_decode(curl_exec($ch), true);
          $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          if($code == "403" || $code == "401"){
               apiAuthorise();
               checkClientInGroup($customerId, $groupId, $branch);
            }
            else{
            if($result[count] == 0){
              break;
            }
            else{
              $countPages++;
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                 checkClientInGroup($customerId, $groupId, $branch);
              }
              else{
                	foreach($result[items] as $learner){
                      	if($learner[customer_id] == $customerId){
                        	return true;
                        }
                    }
              }
              }
            }
        }
      	return false;
    }
}

function getGroupChatIdByCustomerIdAndBranch($customerId, $branchId){
  	if(getToken() == ""){
      apiAuthorise();
	  getGroupChatIdByCustomerIdAndBranch($customerId, $branchId);
    }else{
        $countPages = 0;
        for(;;){
          $ch = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$branchId.'/group/index');
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$countPages.'}');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          $result = json_decode(curl_exec($ch), true);
          $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          if($code == "403" || $code == "401"){
               apiAuthorise();
               getGroupChatIdByCustomerIdAndBranch($customerId, $branchId);
            }
            else{
            if($result[count] == 0){
              break;
            }
            else{
              $countPages++;
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                 getGroupChatIdByCustomerIdAndBranch($customerId, $branchId);
              }
              else{
                	foreach($result[items] as $group){
                        if(checkClientInGroup($customerId,$group[id], $branchId)){
                          //чат найден
                          	return $group[custom_chat_id];
                        }
                    }
              }
              }
            }
        }
      return false;
      }
}


function getCustomer(){
  	if(getToken() == ""){
      apiAuthorise();
	  getCustomer();
    }else{
      $branches = getAllBranches();
      foreach($branches as $item){
        $countPages = 0;
        for(;;){
          $today =  date("d").".".date("m");
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
             getCustomer();
          }
          else{
            if($result[count] == 0){
              break;
            }
            else{
              $countPages++;
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                 getCustomer();
              }
              else{
                //print_r($result);
                foreach($result[items] as $learner){
                    if($today == substr($learner[dob], 0, 5)){
                      if($learner[custom_individual_chat_id] != null){//если есть индивидуальный чат
                        happyBirthday($learner[name],$learner[custom_individual_chat_id]);
                        foreach($learner[branch_ids] as $branch){
                          	$chId = getGroupChatIdByCustomerIdAndBranch($learner[id], $branch);
                          	if($chId != false && $chId != null){
                            	happyBirthday($learner[name],$chId);
                          		break;
                            }
                        }
                      }else{
                        foreach($learner[branch_ids] as $branch){
                          	$chId = getGroupChatIdByCustomerIdAndBranch($learner[id], $branch);
                          	if($chId != false && $chId != null){
                            	happyBirthday($learner[name],$chId);
                          		break;
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
      deleteAllBirthdays();
    }
}

function happyBirthday($fio,$groupChatId){//Поздравление с днём рождения
	global $telegram;
  	$today2 =  "20".date("y")."-".date("m")."-".date("d");
  	$texttosend = returnTextPhraseById(2);
  	$texttosend = str_replace("/Name", $fio, $texttosend);
  	if(checkHappyBDayReminder($today2, $groupChatId, $fio) == "nice"){
      	usleep(rand(500000, 800000));
      	try{
        $telegram->sendMessage([ 'chat_id' => $groupChatId, 'text' => $texttosend, 'reply_markup' => $reply_markup ]);
          addHappyBDayReminder($today2, $groupChatId, $fio);
          }catch (Exception $e) {
            }
    }
}

getCustomer();
?>