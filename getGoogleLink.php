<?php
include('db.php');
header('Content-Type: text/html; charset=utf-8');

echo searchGoogleLinkByBranchId($_POST['id']);
?>