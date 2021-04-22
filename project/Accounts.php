<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<?php
$query = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
    $db = getDB(); 
    $stmt = $db -> prepare("SELECT account_number, account_type, balance from Accounts Where user_id like :q LIMIT 5"); 
    $r = $stmt -> execute([":q" => "%$query%"]); 
    if($r){
        $results = $stmt -> fetchAll(POD::FETCH_ASSOC); 
    } 
    else{
        flash("Issue with fetching data");
    }
}
?>

<form method = "POST">
    <input name = "query" placeholder = "Search" value = "<?php safer_echo($query);?>"/>
    <input type = "submit" value = "Search" name = "search"/>
</form>

<div class = "results"> 
    <?php if(count($results) > 0): ?>
     <div class = "list-account"> 
        <?php foreach ($results as $r):?>
            <div class = "list-account-balance">
                <div>
                    <div> Account Number </div>
                    <div><?php safer_echo($r["account_number"]);?></div>
                </div>
                <div> 
                    <div>Account Type:</div>
                    <div><?php safer_echo($r["account_type"]);?></div> 
                </div>
                <div> 
                    <div>Balance:</div>
                    <div><?php safer_echo($r["balance"]);?></div> 
                </div> 
            </div>
        <?php endforeach; ?>
     </div>
    <?php else:?>
        <p>No results</p>
    <?php endif;?> 
</div>
<?php require(__DIR__ . "/partials/flash.php");?>