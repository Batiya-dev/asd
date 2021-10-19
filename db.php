<?php

$dbhost = "localhost";
$dbname = "cj06732_bot";//имя бд
$username = "cj06732_bot";//логин
$password = 'rootbot';//пароль

$db = new PDO("mysql:host=$dbhost; dbname=$dbname", $username, $password);

function addPaymentReminderIndividual($groupId){
	global $db;
  	$today = "20".date("y")."-".date("m");
  	$db->query("INSERT INTO paymentReminderIndividual (dateR,groupId) VALUES ('$today','$groupId')");
}

function checkPaymentReminderIndividual($groupId){
	global $db;
  	$payments = $db->query("SELECT * FROM paymentReminderIndividual WHERE groupId = '$groupId'");
  	foreach($payments as $item){
      	$pieces = explode("-", $item[dateR]);
    	if("20".date("y") == $pieces[0] && date("m") == $pieces[1]){
            return "bad";
        }
    }
  	return "nice";
}

function deleteAllBirthdays(){
	global $db;
  	$db->query("TRUNCATE TABLE happyBDayReminder");
}

function deletePaymentReminderIndividual($groupChatId){
	global $db;
  	$db->query("DELETE FROM paymentReminderIndividual WHERE groupId = '$groupChatId'");
}

function deletePaymentReminder($groupChatId){
	global $db;
  	$db->query("DELETE FROM paymentReminder WHERE groupId = '$groupChatId'");
}

function deleteHomework($hw){
	global $db;
  	$db->query("DELETE FROM sendHomework WHERE groupChatId = '$hw'");
}

function addUserId($user_id){
	global $db;
  	$db->query("INSERT INTO usersWithoutQuestion (user_id) VALUES ('$user_id')");
}

function checkUsersWithoutQuestion($user_id){
	global $db;
    $users = $db->query("SELECT * FROM usersWithoutQuestion");
  	foreach($users as $item){
    	if($item[user_id] == $user_id){
        	return "bad";
        }
    }
  	return "nice";
}

function addBranchToChat($id, $branchId){
	global $db;
    $db->query("UPDATE chats SET branchId = '$branchId' WHERE chatId = '$id'");
}

function returnBranchIdByChatId($chId){
     global $db;
    $chatBranch = $db->query("SELECT * FROM chats WHERE chatId = '$chId'");
  	foreach($chatBranch as $item){
    	if($item[branchId] == "net"){
        	return "net";
        }else{
        	return $item[branchId];
        }
    }
}

function returnChatIdByUserId($userId){
	global $db;
    $question = $db->query("SELECT * FROM question WHERE clientID = '$userId'");
  	foreach($question as $item){
    	return $item[chatID];
    }
  	return false;
}

function getChatsWithoutBranch(){
	global $db;
  	$chatsWithoutBranch = array();
  	$allChats = $db->query("SELECT * FROM chats");
  	foreach($allChats as $chat){
    	if($chat[branchId] == null || $chat[branchId] == "net"){
        	$chatsWithoutBranch[] = $chat;
        }
    }
  	return $chatsWithoutBranch;
}

function getAllChatsManager(){
	global $db;
  	$allChats = $db->query("SELECT * FROM branches");
  	$managerIdis = array();
  	foreach($allChats as $item){
    	$managerIdis[] = $item[chat];
    }
  	return $managerIdis;
}

function returnGroupChatIdByCustomerId($customer_id){
  	global $db;
  	$allChats = $db->query("SELECT * FROM clients");
  	foreach($allChats as $item){
    	if($item[customerId] == $customer_id){
        	return $item[groupChatId];
        }
    }
  	return false;//если customer_id не найден
}

function checkChatsTelegram($chat_id){
	global $db;
  	$allChats = $db->query("SELECT * FROM chats");
  	foreach($allChats as $item){
    	if($item[chatId] == $chat_id){
        	return false;
        }
    }
  return true;//если id не найден
}

function getAllChats(){
	global $db;
  	return $db->query("SELECT * FROM chats");
}

function addToChats($chat_id, $branchId){
	global $db;
  	$db->query("INSERT INTO chats (chatId, branchId) VALUES ('$chat_id', '$branchId')");
}

function getFIObyUserId($user_id){
	global $db;
	$allClients = $db->query("SELECT * FROM clients");
  	foreach($allClients as $item){
    	if($item[userId] == $user_id){
         	return $item[fio];
        }
    }
}

function getPhoneNumberByUserId($user_id){
  	global $db;
	$allClients = $db->query("SELECT * FROM clients");
  	foreach($allClients as $item){
    	if($item[userId] == $user_id){
         	return $item[phone_number];
        }
    }
}

function returnSupportChatIdByBranch($branchId){
	global $db;
  	$bran = $db->query("SELECT * FROM branches");
  	foreach($bran as $item){
      if($item[branchId] == $branchId){
    	return $item[chat];
      }
    }
}

function returnBranchUser($user_id){
	global $db;
  	$allClients = $db->query("SELECT * FROM clients");
  	
  	foreach($allClients as $item){
      	if($item[userId] == $user_id){
        	return $item[branch_id];
        }
    }
}

function checkClientByUserId($userId){
	global $db;
  	$allClients = $db->query("SELECT * FROM clients");
  	
  	foreach($allClients as $item){
      	if($item[userId] == $userId){
        	return true;
        }
    }
  return false;
}

function getAllBranches(){
	global $db;
  	return $db->query("SELECT * FROM branches");
}
function addOrCheckBranchLink($bId){
	global $db;
  	$branches = $db->query("SELECT * FROM googleLinks");
  	$count = 0;
  	foreach($branches as $item){
    	if($item[branch_id] == $bId){
        	$count++;
        }	
    }
  	if($count == 0){
  		$db->query("INSERT INTO googleLinks (branch_id) VALUES ('$bId')");
  	}
}
function addOrCheckBranch($bId, $bName){
	global $db;
  	$branches = $db->query("SELECT * FROM branches");
  	$count = 0;
  	foreach($branches as $item){
    	if($item[name] == $bName){
        	$count++;
        }	
    }
  	if($count == 0){
  		$db->query("INSERT INTO branches (branchId, name) VALUES ('$bId', '$bName')");
  	}
	
}

function deleteGroupBd($arrayIdis){
	global $db;
  	for($i=0;$i<count($arrayIdis);$i++){
    	$db->query("DELETE FROM after2WeeksChat WHERE groupId = '$arrayIdis[$i]'");
    }
}

function checkDatesGroup($date){
	global $db;
  	$arrayIdis = array();
  	$reminders = $db->query("SELECT * FROM after2WeeksChat");
  	foreach($reminders as $item){
         if($item[dateToSend] == $date){
              $arrayIdis[] = $item[groupId];
         }
    }
  return $arrayIdis;
}

function checkGroupsTelegram($groupId){
	global $db;
  	$reminders = $db->query("SELECT * FROM after2WeeksChat");
  	foreach($reminders as $item){
         if($item[groupId] == $groupId){
              return false;
         }
    }
  	return true;//true если не нашёл
}

function addNewGroup($dateToSend, $groupId){
	global $db;
	$db->query("INSERT INTO after2WeeksChat (dateToSend, groupId) VALUES ('$dateToSend', '$groupId')");
}


function addSendHomework($dateR, $groupId){
	global $db;
	$db->query("INSERT INTO sendHomework (dateR, groupChatId) VALUES ('$dateR', '$groupId')");
}

function checkSendHomework($dateR, $groupId){
	global $db;
  	$reminders = $db->query("SELECT * FROM sendHomework");
  	foreach($reminders as $item){
         if($item[dateR] == $dateR && $item[groupChatId] == $groupId){
              return "bad";
         }
    }
  	return "nice";
}

function addHappyBDayReminder($dateR, $groupId, $fio){
	global $db;
	$db->query("INSERT INTO happyBDayReminder (dateR, groupChatId, fio) VALUES ('$dateR', '$groupId', '$fio')");
}

function checkHappyBDayReminder($dateR, $groupId, $fio){
	global $db;
  	$reminders = $db->query("SELECT * FROM happyBDayReminder");
  	foreach($reminders as $item){
         if($item[dateR] == $dateR && $item[groupChatId] == $groupId && $fio == $item[fio]){
              return "bad";
         }
    }
  	return "nice";
}

function addPaymentReminder($dateR, $groupId){
	global $db;
	$db->query("INSERT INTO paymentReminder (dateR, groupId) VALUES ('$dateR', '$groupId')");
}

function checkPaymentReminder($dateR, $groupId){
	global $db;
  	$reminders = $db->query("SELECT * FROM paymentReminder");
  	foreach($reminders as $item){
         if($item[dateR] == $dateR && $item[groupId] == $groupId){
              return "bad";
         }
    }
  	return "nice";
}


function addMorningReminder($dateR, $groupId){
	global $db;
	$db->query("INSERT INTO morningReminder (dateR, groupChatId) VALUES ('$dateR', '$groupId')");
}

function checkMorningReminder($dateR, $groupId){
	global $db;
  	$reminders = $db->query("SELECT * FROM morningReminder");
  	foreach($reminders as $item){
         if($item[dateR] == $dateR && $item[groupChatId] == $groupId){
              return "bad";
         }
    }
  	return "nice";
}


function add15MinReminder($dateR, $phoneR){
  	global $db;
	$db->query("INSERT INTO 15minutesReminder (dateR, groupChatId) VALUES ('$dateR', '$phoneR')");
}

function check15MinReminder($dateR, $phoneR){
	global $db;
  	$reminders = $db->query("SELECT * FROM 15minutesReminder");
  	foreach($reminders as $item){
         if($item[dateR] == $dateR && $item[groupChatId] == $phoneR){
              return "bad";
         }
    }
  	return "nice";
}

function updateToken($token){
	global $db;
    $db->query("UPDATE tokenApi SET Token = '$token' WHERE Id = 1");
}

function getToken(){
	global $db;
  	$tokenId = "";
    $token = $db->query("SELECT * FROM tokenApi WHERE Id = 1");
  	foreach($token as $item){
    	$tokenId = $item[Token];
    }
  	return $tokenId;
}

function searchGoogleLinkByBranchId($id){
	global $db;
  	$allPhrases = $db->query("SELECT * FROM googleLinks WHERE branch_id = '$id'");
  	foreach($allPhrases as $item){
    	return $item[link];
    }
}

function searchChatLinkByBranchId($id){
	global $db;
  	$allPhrases = $db->query("SELECT * FROM branches WHERE branchId = '$id'");
  	foreach($allPhrases as $item){
    	return $item[chat];
    }
}

function searchPhraseById($id){
	global $db;
  	$allPhrases = $db->query("SELECT * FROM phrases WHERE id = '$id'");
  	foreach($allPhrases as $item){
    	return $item[text];
    }
}

function allPhrases(){
	global $db;
  	$allPhrases = $db->query("SELECT * FROM phrases");
  	return $allPhrases;
}

function allClientsIds(){
	global $db;
  	$allClients = $db->query("SELECT * FROM clients");
  	$allIds = array();
  	foreach($allClients as $item){
    	$allIds[] = $item[chat_id];
    }
  	return $allIds;
}

function searchClientByPhone($arrayPhones){
	global $db;
  	$allClients = $db->query("SELECT * FROM clients");
  	$arrayIdis = array();
  	foreach($allClients as $item){
          if($item[phone_number] == $arrayPhones){
              $arrayIdis[] = $item[chat_id];
          }
    }
  	return $arrayIdis;
}

function addClient($chat_id, $phone, $branchID, $userId, $fioUser, $customerId, $groupChatId){
  	global $db;
  	$check = 0;
  	$arrayClients = $db->query("SELECT * FROM clients");
  	foreach($arrayClients as $item){
    	if($item[phone_number] == $phone){
        	$check++;
        }
    }
  	if($check == 0){
    	$db->query("INSERT INTO clients (chat_id, phone_number, branch_id, userId, fio, customerId, groupChatId) VALUES ('$chat_id', '$phone', '$branchID', '$userId', '$fioUser', '$customerId', '$groupChatId')");
    }
}

function get_admin(){
    global $db;
    $singles = $db->query("SELECT * FROM admin");
    return $singles;
}

function updateGoogleLink($id, $text){
	global $db;
    $db->query("UPDATE googleLinks SET link = '$text' WHERE branch_id = '$id'");
}

function updateChatLink($id, $text){
	global $db;
    $db->query("UPDATE branches SET chat = '$text' WHERE branchId = '$id'");
}
function updatePhrase($id, $text){
	global $db;
    $db->query("UPDATE phrases SET text = '$text' WHERE id = '$id'");
}

function returnTextPhraseById($id){
	global $db;
  	$textPhrase = $db->query("SELECT * FROM phrases WHERE id = '$id'");
  	foreach($textPhrase as $item){
    	return $item['text'];
    }
}

function returnQuestionMessage($userID){
	global $db;
  	$question = $db->query("SELECT * FROM question WHERE id = '$userID'");
  	foreach($question as $item){
  		return $item[messages];
  	}
}

function returnQuestionChatIdById($id){
	global $db;
  	$question = $db->query("SELECT * FROM question WHERE id = '$id'");
  	foreach($question as $item){
  		return $item[chatID];
  	}
}

function returnQuestionChatLinkById($id){
	global $db;
  	$question = $db->query("SELECT * FROM question WHERE id = '$id'");
  	foreach($question as $item){
  		return $item[chatLink];
  	}
}

function returnQuestionId($userID){
	global $db;
   	$id = 0;
  	$question = $db->query("SELECT * FROM question WHERE clientID = '$userID'");
  	foreach($question as $item){
  		$id = $item[id];
  	}
  	return $id;
}

function checkInviteLink($id){
  	global $db;
	$question = $db->query("SELECT * FROM question WHERE chatID = '$id'");
  	foreach($question as $item){
      	if($item[chatLink] != null){
  			return $item[chatLink];
        }
  	}
  	return "bad";
}

function addQuestion($message, $chatId, $userID){
	global $db;
  	$db->query("INSERT INTO question (messages,chatID, clientID) VALUES ('$message','$chatId', '$userID')");
  	$quest = $db->query("SELECT * FROM question ORDER BY id DESC LIMIT 1");
  	foreach($quest as $item){
    	return $item[id];
    }
  	
}

function endQuestionByUserId($user_id){
	global $db;
  	$db->query("DELETE FROM question WHERE clientID = '$user_id'");
}

function endQuestion($Id){
	global $db;
  	$db->query("DELETE FROM question WHERE id = '$Id'");
}

function addLinkToChat($id, $link){
	global $db;
    	$db->query("UPDATE question SET chatLink = '$link' WHERE id = '$id'");
}

function checkQuestion($user_id){
	global $db;
  	$result = $db->query("SELECT * FROM question");
  	foreach($result as $item){
      	if($item[clientID] == $user_id){
        	return true;
        }
    }
  	return false;
}