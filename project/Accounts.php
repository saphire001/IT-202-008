<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$db = getDB();
$U_id =  get_user_id();

$stmt = $db -> prepare ("SELECT account_number, account_type, balance WHERE user_id = :user"); 
$stmt->execute([":user" => get_user_id()]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);

if($r)
{
    echo $r['account_number'];
    echo $r['accunt_type'];
    echo $r['balance']; 
}

?>
<?php require(__DIR__ . "/partials/flash.php");?>