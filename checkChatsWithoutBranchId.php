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

function getBranchId(){
  	if(getToken() == ""){
      apiAuthorise();
	  getBranchId($ch_id);
    }else{
      global $telegram;
      global $keyboardConfirm;
      global $keyboardStandart;
      $check = 0;
      $branches = getAllBranches();
      
      $massChats = array();
      $massBranches = array();
      
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
               getBranchId($ch_id);
            }
            else{
              if($result[count] == 0){
                break;
              }
              else{
                $countPages++;
                if($code == "403"){
                   apiAuthorise();
                   getBranchId($ch_id);
                }
                else{
                  foreach($result[items] as $learner) {
                      if($learner[custom_group_chat_id] != null){
                          if(!in_array($learner[custom_group_chat_id], $massChats)){
                              $massChats[] = $learner[custom_group_chat_id];
                              foreach($learner[branch_ids] as $item2){
                                $massBranches[] = $item2;
                              }
                          }
                      }
                    }
                }
              }
        	}
      	}
      }
      print_r($massChats);
      print_r($massBranches);
      for($i=0;$i<count($massChats);$i++){
      	addBranchToChat($massChats[$i], $massBranches[$i]);
      }
    }
}

//$arrayChats = getChatsWithoutBranch();
//print_r($arrayChats);
//print_r($arrayChats);
getBranchId();
/*foreach($arrayChats as $item){
  	echo $item[chatId];
  	
	if(getBranchId($item[chatId]) != "net"){
    	echo "Hi!";
    	addBranchToChat($item[id], getBranchId($item[chatId]));
    }
}*/

?>