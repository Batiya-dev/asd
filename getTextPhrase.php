<?php
include('db.php');
header('Content-Type: text/html; charset=utf-8');

echo searchPhraseById($_POST['id']+1);
?>