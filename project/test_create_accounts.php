<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
  }
?>
<?php
if(isset($_POST["create"])){
    $ATy = $_POST["account_type"]; 
    $BA = $_POST["balance"]; 
    $user = get_user_id();
    $db = getDB();
    
    $stmt = $db -> prepare ("INSERT INTO Accounts (account_number, account_type, balance, user_id) VALUES (:AN, :ATy, :BA, :user)"); 
    $AN = rand(000000000001, 999999999999);
    $r = $stmt -> execute ([":AN"=>$AN, ":ATy"=>$ATy, ":BA"=>$BA, ":user"=>$user]);  

    if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>

<form method="POST">
    <label>Account Type</label>
    <select class="form-control" id="account_type" name="account_type">
      <option value="checking">Checking</option>
      <option value="savings">Savings</option>
    </select>
    <label>Balance</label>
    <input type = "number" name = "balance" min = "0.00"/>
    <input type="submit" name="create" value="Create"/>
</form>

<?php require(__DIR__ . "/partials/flash.php");
