<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$db = getDB();
$U_id =  get_user_id();

$stmt = $db -> prepare ("SELECT account_number, account_type, balance WHERE user_id = :user"); 
echo $stmt

?>
<?php require(__DIR__ . "/partials/flash.php");?>