<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$db = getDB();
$U_id =  get_user_id();

$stmt = $db -> prepare ("SELECT account_number, account_type, balance WHERE user_id = :user"); 
$stmt->execute([":user" => get_user_id()]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);

if($r)
{
    $AN = $r['account_number'];
     $ATy = $r['accunt_type'];
     $BA = $r['balance']; 
}
else
{
    echo E;
}

echo $AN;
echo $ATy;
echo $BA;

?>
<?php require(__DIR__ . "/partials/flash.php");?>