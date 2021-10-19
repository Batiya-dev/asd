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
function checkIndividual($ch_id){
	if(getToken() == ""){
      apiAuthorise();
	  checkIndividual($ch_id);
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
               checkIndividual($ch_id);
            }
            else{
            if($result[count] == 0){
              break;
            }
            else{
              $countPages++;
              if($code == "403" || $code == "401"){
                 apiAuthorise();
                 checkIndividual($ch_id);
              }
              else{
                	foreach($result[items] as $learner){
                    	if($learner[custom_individual_chat_id] == $ch_id){
                          return $learner[name];
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

function CheckAndSendMess(){
    $date = new DateTime();
  	$arrayIdis = checkDatesGroup($date->format('Y-m-d'));
  	global $telegram;
  	for($i=0;$i<count($arrayIdis);$i++){
      		$brcnhId = getBranchIdInCustomers($arrayIdis[$i]);
            if($brcnhId == "bad"){
                $brcnhId = getChatBranchByGroups($arrayIdis[$i]);
            }
      
          	if($brcnhId != "net" && $brcnhId != "bad"){
              	if(checkIndividual($arrayIdis[$i]) != "bad"){//индивидуальный
                  	$texttosend = returnTextPhraseById(7);
                  	$texttosend = str_replace("/Name", checkIndividual($arrayIdis[$i]), $texttosend);
                  	$texttosend = str_replace("/Link", searchGoogleLinkByBranchId($brcnhId), $texttosend);
              		try{
                		usleep(rand(500000, 800000));
          				$telegram->sendMessage([ 'chat_id' => $arrayIdis[$i], 'text' => $texttosend, 'reply_markup' => $reply_markup ]);
                    }catch(Exception $ex){}
                }else{//группа
                  	$texttosend = returnTextPhraseById(16);
                  	$texttosend = str_replace("/Link", searchGoogleLinkByBranchId($brcnhId), $texttosend);
                  	try{
                		usleep(rand(500000, 800000));
          				$telegram->sendMessage([ 'chat_id' => $arrayIdis[$i], 'text' => $texttosend, 'reply_markup' => $reply_markup ]);
                    }catch(Exception $ex){}
                }
            }
    }
  	if(sizeof($arrayIdis) != 0){
    	deleteGroupBd($arrayIdis);
    }
}


CheckAndSendMess();
?>