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
      
        $countPages = 0;
        for(;;){
          $today =  "20".date("y")."-".date("m")."-".date("d");
          $ch = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/4/customer/index');
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
                foreach($result[items] as $learner) {
                    if($today == substr($learner[next_lesson_date], 0, 10)){ 
                      getLessonByClientId($learner[id], 4, $learner[custom_individual_chat_id], $learner[next_lesson_date], $learner[custom_zoom_link]);
                    }
                }
                }
              }
            }
      }
    }
}


function lessonReminder($groupChatId, $lessonDate){//Напоминание о занятии
  	global $telegram;
  	$lessonDate = substr($lessonDate, 11);
  	$lessonDate = substr($lessonDate, 0, -3);
  	$texttosend = returnTextPhraseById(1);
      	usleep(rand(500000, 800000));
      	try{
        $telegram->sendMessage([ 'chat_id' => $groupChatId, 'text' => "Сегодня у Вас состоится урок в Школе иностранных языков IEC\n\u{1F558} Приходите на занятия вовремя\n\nВремя проведения: ".$lessonDate, 'reply_markup' => $reply_markup ]);
        }catch (Exception $e) {
        }
}

function lessonReminderOnline($arrayPhones, $lessonDate, $zoomLink){//Напоминание о занятии
  	global $telegram;
  	$lessonDate = substr($lessonDate, 11);
  	$lessonDate = substr($lessonDate, 0, -3);
  	$texttosend = returnTextPhraseById(1);
      	usleep(rand(500000, 800000));
      	try{
        $telegram->sendMessage([ 'chat_id' =>$arrayPhones, 'text' => "Сегодня у Вас состоится урок в Школе иностранных языков IEC\n\u{1F558} Приходите на занятия вовремя\n\nВремя проведения: ".$lessonDate."\n\nСсылка на zoom: ".$zoomLink, 'reply_markup' => $reply_markup ]);
        }catch (Exception $e) {
            }
}

function getGroupChatIdByGroupTitle($branch, $groupTitle){
  	if(getToken() == ""){
      apiAuthorise();
	  getGroupChatIdByGroupTitle($branch, $groupTitle);
    }else{
        $countPages = 0;
        for(;;){
          $today =  "20".date("y")."-".date("m")."-".date("d");
          $ch = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$branch.'/group/index');
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$countPages.'}');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          $result = json_decode(curl_exec($ch), true);
          $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          if($code == "403" || $code == "401"){
               apiAuthorise();
               getGroupChatIdByGroupTitle($branch, $groupTitle);
            }
            else{
            if($result[count] == 0){
              break;
            }
            else{
              $countPages++;
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                 getGroupChatIdByGroupTitle($branch, $groupTitle);
              }
              else{
                	foreach($result[items] as $group){
                      	//echo "\t".$group[name]." |=| ".$groupTitle;
                		if(str_replace(' ', '', $group[name]) == str_replace(' ', '', $groupTitle)){
                        	return $group[custom_chat_id];
                        }
                	}
              }
              }
            }
        }
      return "bad";
    }
}


function getLessonByClientId($clientId, $branchId, $individualChatId, $nextLessonDate, $zoomLink){
  	if(getToken() == ""){
      apiAuthorise();
	 getLessonByClientId($clientId, $branchId, $individualChatId, $nextLessonDate, $zoomLink);
    }else{
          $today =  "20".date("y")."-".date("m")."-".date("d");
          $ch = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$branchId.'/calendar/customer?id='.$clientId.'&date1='.$today.'&date2='.$today);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          $result = json_decode(curl_exec($ch), true);
          $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          
          if($code == "403" || $code == "401"){
               apiAuthorise();
               getLessonByClientId($clientId, $branchId, $individualChatId, $nextLessonDate, $zoomLink);
            }
            else{
              
                //echo "Nice!";
                foreach($result as $lesson){
                  if($lesson[type] == 1 || $lesson[type] == 2){
                	if($lesson[status] == 1){
                      
                          //print_r($lesson);
                          if($lesson[type] == 1){//индивидуалный
                              //echo "Individual: ".$individualChatId." ";
                            if(checkMorningReminder($today, $individualChatId) == "nice"){
                                    lessonReminder($individualChatId, $nextLessonDate); 
                                    addMorningReminder($today, $individualChatId);
                            }
                          }else if($lesson[type] == 2){
                              //$chatIdGroup = getChatIdByGroupId(getGroupIdByLessonId($lesson[branch_id], $lesson[id]));
                              //echo "Group: ".$chatIdGroup;

                              $one  = substr($lesson[title], 0, strrpos( $lesson[title], '('));
                              $chatIdGroup = getGroupChatIdByGroupTitle($lesson[branch_id], $one);
                              
                            //echo $chatIdGroup." asdddddd ";
                               if($chatIdGroup != "bad" && $chatIdGroup != null){
                                 	if(checkMorningReminder($today, $chatIdGroup) == "nice"){
                                      
                                        lessonReminder($chatIdGroup, $nextLessonDate); 
                                        addMorningReminder($today, $chatIdGroup);
                                    }
                               }

                      }
                    }
                }
              }
            }
    }
}

getCustomer();
?>