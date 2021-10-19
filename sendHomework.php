<?php

include('vendor/autoload.php');
include('db.php');


use Telegram\Bot\Api;

$telegram = new Api('1249473332:AAFdOS-wiFOox1_5YBFUIDNcILHPIh1-5Rw');
$result = $telegram -> getWebhookUpdates();

$data = file_get_contents("php://input");
$events = json_decode($data, true);
//file_put_contents(__DIR__ . '/log11.txt', $events[entity_id]." ".$events[fields_new][homework]." ".$events[topic] . PHP_EOL, FILE_APPEND);
getIdCustomers($events[branch_id], $events[entity_id],$events[fields_new][homework], $events[topic]);

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

function getChatIdByCustomerId($branch, $customer_id){
	if(getToken() == ""){
      apiAuthorise();
	  getChatIdByCustomerId($branch, $customer_id);
    }else{
      global $telegram;
      global $keyboardConfirm;
      global $keyboardStandart;
      $check = 0;
        $countPages = 0;
        for(;;){
          $ch     = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$branch.'/customer/index');
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$countPages.'}');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          $result = json_decode(curl_exec($ch), true);
          $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          if($code == "403" || $code == "401"){
               apiAuthorise();
               getChatIdByCustomerId($branch, $customer_id);
            }
            else{
              if($result[count] == 0){
                break;
              }
              else{
                $countPages++;
                if($code == "403" || $code == "401"){
                   apiAuthorise();
                   getChadIdByCustomerId($ch_id);
                }
                else{
                  foreach($result[items] as $learner) {
                      if($learner[id] == $customer_id){
                          return $learner[custom_individual_chat_id];
                      }
                    }
                }
              }
        	}
      }
      return "net";
    }
}

function getLessonType($branchId, $lessonId){
  	if(getToken() == ""){
      apiAuthorise();
	   getLessonType($branchId, $lessonId);
    }else{
        for($i=0;$i<2;$i++){
          $today =  "20".date("y")."-".date("m")."-".date("d");
          $ch     = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$branchId.'/lesson/index');
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$i.'}');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          $result = json_decode(curl_exec($ch), true);
          $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                  getLessonType($branchId, $lessonId);
              }
              else{
                foreach($result[items] as $lesson){
                  if($lesson[id] == $lessonId){
                  		return $lesson[lesson_type_id];
                  }
                }
            }
      }
    }
}

function getLessons($branchId, $lessonId){
  	if(getToken() == ""){
      apiAuthorise();
	   getLessons($branchId, $lessonId);
    }else{
        for($i=0;$i<2;$i++){
          $today =  "20".date("y")."-".date("m")."-".date("d");
          $ch     = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$branchId.'/lesson/index');
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$i.'}');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          $result = json_decode(curl_exec($ch), true);
          $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                  getLessons($branchId, $lessonId);
              }
              else{
                foreach($result[items] as $lesson){
                  if($lesson[id] == $lessonId){
                  		foreach($lesson[group_ids] as $group){
                        	return $group;
                       }
                  }
                }
            }
      }
      return "bad";
    }
}

function getIdCustomers($brchid,$lessId, $homeWork){
  	if(getToken() == ""){
      apiAuthorise();
	  getIdCustomers($brchid,$lessId, $homeWork);
    }else{
      global $telegram;
      $branches = getAllBranches();
      foreach($branches as $item){
        for($i=0;$i<2;$i++){
          $today =  "20".date("y")."-".date("m")."-".date("d");
          $ch     = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$item[branchId].'/lesson/index');
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$i.'}');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          $result = json_decode(curl_exec($ch), true);
          $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          
              if($code == "403"){
                 apiAuthorise();
                 getIdCustomers($brchid,$lessId, $homeWork);
              }
              else{
                $massHomeworks = array();
                foreach($result[items] as $learner){
                  		if($learner[lesson_type_id] == 1 || $learner[lesson_type_id] == 2){
                          if($lessId == $learner[id]){
                              foreach($learner[details] as $detail){
                                
                                if(getLessonType($brchid,$lessId) != null){
                                  		if(getLessonType($brchid,$lessId) != 2){
                                          if(getChatIdByCustomerId($brchid, $detail[customer_id]) != "net"){

                                                if(checkSendHomework($today, returnGroupChatIdByCustomerId($detail[customer_id])) == "nice"){
                                                    try{

                                                      $textToSend = "Доброго времени суток!\n\n\u{1F469}\u{200D}\u{1F3EB}Сегодня на уроке мы проходили: ".$learner[topic]."\n\n\u{1F4DA}Домашнее задание на следующее занятие: ".$homeWork;
                                                      //file_put_contents(__DIR__ . '/log.txt', 'log:'.returnGroupChatIdByCustomerId($detail[customer_id]) . PHP_EOL, FILE_APPEND);
                                                      $telegram->sendMessage([ 'chat_id' => getChatIdByCustomerId($brchid, $detail[customer_id]), 'text' => $textToSend, 'reply_markup' => $reply_markup ]);
                                                    }catch (Exception $e) {
                                                      }
                                                    /*addSendHomework($today, returnGroupChatIdByCustomerId($detail[customer_id]));
                                                  $massHomeworks = returnGroupChatIdByCustomerId($detail[customer_id]);*/
                                                }
                                          }
                                        }else{
                                          	
                                        	$groupId = getLessons($brchid,$lessId);
                                            if($groupId != "bad"){
                                              	$groupChatId = getChatIdByGroupId($groupId);
												if($groupChatId != "bad"){
                                                  if(checkSendHomework($today, $groupChatId) == "nice"){
                                                	$textToSend = "Доброго времени суток!\n\n\u{1F469}\u{200D}\u{1F3EB}Сегодня на уроке мы проходили: ".$learner[topic]."\n\n\u{1F4DA}Домашнее задание на следующее занятие: ".$homeWork;
                                                      //file_put_contents(__DIR__ . '/log.txt', 'log:'.returnGroupChatIdByCustomerId($detail[customer_id]) . PHP_EOL, FILE_APPEND);
                                                      $telegram->sendMessage([ 'chat_id' => $groupChatId, 'text' => $textToSend, 'reply_markup' => $reply_markup ]);
                                                  	addSendHomework($today, $groupChatId);
                                        			$massHomeworks = $groupChatId;
                                                  }
                                                }
                                            }
                                        }
                                            
                                }
                                
                              }
                          }
                    	}
                      }
                
                if(count($massHomeworks) != 0){
                	for($i=0;$i<count($massHomeworks);$i++){
                    	deleteHomework($massHomeworks[$i]);
                    }
                }
            }
        }
      }
    }
}







function getChatIdByGroupId($groupId){
  	if(getToken() == ""){
      apiAuthorise();
	  getChatIdByGroupId($groupId);
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
               getChatIdByGroupId($groupId);
            }
            else{
            if($result[count] == 0){
              break;
            }
            else{
              $countPages++;
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                 getChatIdByGroupId($groupId);
              }
              else{
                	foreach($result[items] as $group){
                    	if($group[id] == $groupId){
                          	
                          	if($group[custom_chat_id] != null){
                            	return $group[custom_chat_id];
                            }else{
                            	return "bad";
                            }
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
?>