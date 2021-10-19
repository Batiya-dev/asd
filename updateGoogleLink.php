<?php include('db.php');?>
<?php
header('Content-Type: text/html; charset=utf-8');

updateGoogleLink($_POST['id'],$_POST['text']);
?>