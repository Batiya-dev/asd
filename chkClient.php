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

function getSda(){
if(getToken() == ""){
      apiAuthorise();
	  getCustomer();
    }else{
          $today =  "20".date("y")."-".date("m")."-".date("d");
          $ch = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/1/calendar/customer?id=14490&date1=2020-12-19&date2=2020-12-19');
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
              $countPages++;
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                 getCustomer();
              }
              else{
                	/*foreach($result[items] as $learner){
                    	//if($learner[custom_individual_chat])
                    }
*/                	print_r($result);
                }
      }
    }
}
getCustomer();
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
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/1/customer/index');
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
                	/*foreach($result[items] as $learner){
                    	//if($learner[custom_individual_chat])
                    }
*/                	print_r($result);
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
                        	print_r($lesson);
                       }
                  }
                }
            }
      }
      return "bad";
    }
}

/*
function findClientByGroupId($idGroup, $branchId){
  	if(getToken() == ""){
      apiAuthorise();
	  getCustomer();
    }else{
        $countPages = 0;
        for(;;){
          $today =  "20".date("y")."-".date("m")."-".date("d");
          $ch = curl_init();

          curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-ALFACRM-TOKEN: '.getToken(), 'Accept: application/json', 'Content-Type: application/json']);
          curl_setopt($ch, CURLOPT_URL, 'https://iecenglish.s20.online/v2api/'.$branchId.'/cgi/index?group_id='.$idGroup);
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
                	print_r($result);
              }
              }
            }
        }
    }
  
}*/

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
//getGroup();
//echo getLessons(1,14578);
//findClientByGroupId(70,1);
//getLessons(1, 7);
//getCustomer();



//НАПОМИНАНИЕ ОБ УРОКАХ ------------------------------------
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

function getLessonByClientId($clientId, $branchId, $individualChatId){
  	if(getToken() == ""){
      apiAuthorise();
	  getLessonByClientId($clientId, $branchId, $individualChatId);
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
               getLessonByClientId($clientId, $branchId, $individualChatId);
            }
            else{
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                 getLessonByClientId($clientId, $branchId, $individualChatId);
              }
              else{
                //echo "Nice!";
                foreach($result as $lesson){
                	if($lesson[status] == 1){
                      	//print_r($lesson);
                      	if($lesson[type] == 1){//индивидуалный
                        	//echo "Individual: ".$individualChatId." ";
                        }else if($lesson[type] == 2){
                          	//$chatIdGroup = getChatIdByGroupId(getGroupIdByLessonId($lesson[branch_id], $lesson[id]));
                          	//echo "Group: ".$chatIdGroup;
                          
                          	$one  = substr($lesson[title], 0, strrpos( $lesson[title], '('));
                            $chatIdGroup = getGroupChatIdByGroupTitle($lesson[branch_id], $one);
                          //echo $chatIdGroup." asdddddd ";
                             if($chatIdGroup != "bad" && $chatIdGroup != null){
                             	echo $chatIdGroup;
                             }
                          	
                        }
                    }
                }
              }
            }
    }
}
//getLessonByClientId(14408,1, 34152);

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
                          echo "Nice!)";
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



//поздравление с др
function getChatIdByCustomerId($customerId){
  	if(getToken() == ""){
      apiAuthorise();
	  getGroup();
    }else{
      $branches = getAllBranches();
      $massBranches = array();
      $massGroups = array();
      
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
                	foreach($result[items] as $group){
                    	foreach($group[branch_ids] as $branch){
                          	
                          	$massBranches[] = $branch;
                          	$massGroups[] = $group[id];
                          	break;
                        }
                    }
              }
              }
            }
        }
      }
      for($i=0;$i<count($massBranches); $i++){
      	getLessons($massBranches[$i], $massGroups[$i]);
      }
    }
}
/*
function getChatIdByGroupId($branchId, $groupId){
  	if(getToken() == ""){
      apiAuthorise();
	  getChatIdByGroupId($branchId, $groupId);
    }else{
      
        $countPages = 0;
        for(;;){
          $today =  "20".date("y")."-".date("m")."-".date("d");
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
               getChatIdByGroupId($branchId, $groupId);
            }
            else{
            if($result[count] == 0){
              break;
            }
            else{
              $countPages++;
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                 getChatIdByGroupId($branchId, $groupId);
              }
              else{
                print_r($result);
                	foreach($result as $group){
                    	if($group[id] == $groupId){
                          print_r($group);
                          	if($group[custom_chat_id] != null){
                            	return $group[custom_chat_id];
                            }else{
                            	return "bad";
                            }
                        }
                    }
                return "bad";
              }
              }
            }
        }
    }
}*/

//echo getChatIdByGroupId(1, 461);

//getGroupChatIdByCustomerIdAndBranch(23,1);
?>