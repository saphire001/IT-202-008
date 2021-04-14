<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
if(isset($_POST["create"])){
    $AN = $_POST["ANum"];
    $ATy = $_POST["ATyp"]; 
    $BA = $_POST["Bal"]; 
    $user = get_user_id();
    $db = getDB();
    
    $stmt = $db -> prepare ("INSERT INTO Accounts (ANum, ATyp, Bal, user_id) VALUES (:AN, :ATy, :BA, :user)"); 
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
    <label>Account Number</label>
    <input type = "number" name = "ANum"  required maxlength="12"/>
    <label>Account Type</label>
    <input type = "text" name = "ATyp"  required maxlength="20"/>
    <label>Balance</label>
    <input type = "number" name = "Bal" min = "0.00"/>
    <input type="submit" name="create" value="Create"/>
</form>

<?php require(__DIR__ . "/partials/flash.php");