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
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/5/customer/index');
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POSTFIELDS, '{"page":'.$countPages.'}');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          $result = json_decode(curl_exec($ch), true);
          $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          //print_r($result);
          if($code == "403" || $code == "401"){
               apiAuthorise();
               getCustomer();
          }else{
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
                foreach($result[items] as $learner) {
                  if($today == substr($learner[next_lesson_date], 0, 10)){ 
                    $times = downcounter($learner[next_lesson_date]);
                        if($times[0] == 0 && $times[1] == 0 && $times[2] <= 15 && $times[2] >= 0){
                          	getLessonByClientId($learner[id], 5, $learner[custom_individual_chat_id], $learner[next_lesson_date], $learner[custom_zoom_link]);
                          
                                             
                        }
                  }
                }
              }
            }
          }
      }
    }
}

function lessonReminderOnline($groupChatId, $lessonDate, $linkZoom){//Напоминание о занятии
  	global $telegram;
  	$lessonDate = substr($lessonDate, 11);
  	$lessonDate = substr($lessonDate, 0, -3);
  	$texttosend = returnTextPhraseById(1);
      	usleep(rand(500000, 800000));
      	try{
        $telegram->sendMessage([ 'chat_id' => $groupChatId, 'text' => $texttosend."\nВремя проведения: ".$lessonDate."\n\nСсылка на zoom: ".$linkZoom, 'reply_markup' => $reply_markup ]);
        }catch (Exception $e) {
        }
}

getCustomer();

function downcounter($date){
  $check_time = strtotime($date) - time();
  if($check_time <= 0){
      return false;
  }

  $days = floor($check_time/86400);
  $hours = floor(($check_time%86400)/3600);
  $minutes = floor(($check_time%3600)/60);
  $seconds = $check_time%60; 

  $str = array();
  $str[] = $days;
  $str[] = $hours;
  $str[] = $minutes;
  return $str;
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
                        	return $group[custom_chat_id].'^'.$group[custom_zoom_link];
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
                      if($nextLessonDate == $lesson[start]){
                          //print_r($lesson);
                          if($lesson[type] == 1){//индивидуалный
                              //echo "Individual: ".$individualChatId." ";
                            if(check15MinReminder($today, $individualChatId) == "nice"){
                                  
                                    lessonReminderOnline($individualChatId, $nextLessonDate, $zoomLink); 
                                          add15MinReminder($today, $individualChatId);
                                  
                            }
                          }else if($lesson[type] == 2){
                              //$chatIdGroup = getChatIdByGroupId(getGroupIdByLessonId($lesson[branch_id], $lesson[id]));
                              //echo "Group: ".$chatIdGroup;

                              $one  = substr($lesson[title], 0, strrpos( $lesson[title], '('));
                              $chatIdGroup = getGroupChatIdByGroupTitle($lesson[branch_id], $one);
                              $pieces = explode("^",  $chatIdGroup);
                              
                            //echo $chatIdGroup." asdddddd ";
                               if($chatIdGroup != "bad" && $chatIdGroup != null){
                                 	if(check15MinReminder($today, $pieces[0]) == "nice"){
                                      
                                        lessonReminderOnline($pieces[0], $nextLessonDate, $pieces[1]); 
                                          add15MinReminder($today, $pieces[0]);
                                      
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
?>
