<?php
include('db.php');
header('Content-Type: text/html; charset=utf-8');

echo searchChatLinkByBranchId($_POST['id']);
?>