<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<form method="POST">
<label>Account Number</label>
    <input type = "number" name = "ANum"  required maxlength="12" value = "<?php echo $result ["ANum"]?>"/>
    <label>Account Type</label>
    <input type = "text" name = "ATyp"  required maxlength="20" value = "<?php echo $result ["ATyp"]?>"/>
    <label>Balance</label>
    <input type = "number" name = "Bal" min = "0.00" value = "<?php echo $result ["Bal"]?>"/>
	<input type="submit" name="save" value="Update"/>
</form>

<?php
if(isset($_GET["id"])){
	$id = $_GET["id"];
}
?>

<?php
if(isset($_POST["save"])){
    $AN = $_POST["ANum"];
    $ATy = $_POST["ATyp"]; 
    $BA = $_POST["Bal"]; 
    $user = get_user_id();
    $db = getDB();

    if(isset($id)){
        $stmt = $db->prepare ("UPDATE Accounts set ANun=:AN, ATyp=:ATy, Bal=:BA where id = :id"); 
        $r = $stmt->execute([":AN" => $AN, ":ATy" => $ATy, ":BA" => $BA, ":id" => $id]); 

        if($r){
			flash("Updated successfully with id: " . $id);
		}
		else{
			$e = $stmt->errorInfo();
			flash("Error updating: " . var_export($e, true));
		}
    }

    else{
		flash("ID isn't set, we need an ID in order to update");
	}
}
?>

<?php
$result = [];
if(isset($id)){
	$id = $_GET["id"];
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Accounts where id = :id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<?php require(__DIR__ . "/partials/flash.php");