<?php include('db.php');?>
<?php
header('Content-Type: text/html; charset=utf-8');

updateChatLink($_POST['id'],$_POST['text']);
?>