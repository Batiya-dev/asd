<?php include('db.php');?>
<?php
header('Content-Type: text/html; charset=utf-8');

updatePhrase($_POST['id']+1,$_POST['text']);
?>