<?php
ob_start();
require_once __DIR__ . "/partials/nav.php";
if (!is_logged_in()) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: login.php"));
}

if (isset($_GET["type"])) {
  $type = $_GET["type"];
} else {
  $type = 'deposit';
}

$user = get_user_id();
$db = getDB();

$stmt = $db->prepare("SELECT id, account_number, account_type, balance,  FROM Accounts WHERE user_id = :id AND account_type NOT LIKE 'loan' AND active = 1");
$stmt->execute([':id' => $user]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["save"])) {
  $account_src = $_POST["account_src"];
  $balance = $_POST["balance"];
  $memo = $_POST["memo"];

  $last_name = $_POST["last_name"];
  $last_four = $_POST["last_four"];

  if(strlen($last_four) != 4){
    flash("Enter last 4 digits of the destination account.");
    die(header("Location: transaction_out.php"));
  }

  $stmt = $db->prepare('SELECT Accounts.id, Users.username, account_type FROM Accounts JOIN Users ON Accounts.user_id = Users.id 
  WHERE Users.last_name = :last_name AND Accounts.account_number LIKE :last_four AND active = 1');
  $stmt->execute([':last_name' => $last_name, ':last_four' => "%$last_four"]);
  $account_dest = $stmt->fetch(PDO::FETCH_ASSOC);

  flash(var_export($last_four, true));
  flash(var_export($last_name, true));
  flash(var_export($account_dest, true));
  flash(var_export($account_src, true));
  flash(var_export($results, true));

  if($account_src == $account_dest["id"] || $account_dest["username"] == get_username()) {
    flash("Cannot transfer to the same user!");
    die(header("Location: transaction_out.php"));
  }
  if($account_dest["account_type"] == "loan") {
    flash("Cannot transfer to a loan account!");
    die(header("Location: transaction_out.php"));
  }

  $stmt = $db->prepare('SELECT balance FROM Accounts WHERE id = :id');
  $stmt->execute([':id' => $account_src]);
  $acct = $stmt->fetch(PDO::FETCH_ASSOC);
  if($acct["balance"] < $balance) {
    flash("Not enough funds to transfer!");
    die(header("Location: transaction_out.php"));
  }
  $r = changeBalance($db, $account_src, $account_dest["id"], 'ext-transfer', $balance, $memo);
  
  if ($r) {
    flash("Successfully executed transaction.");
  } else {
    flash("Error doing transaction!");
  }
}
ob_end_flush();
?>

<form method="POST"> 
<?php if(count($results) > 0): ?>
  <label for="account"> Account Source</label>
  <select id="account" name="account_src"> 
    <?php foreach($results as $r): ?>
    <option value="<?php safer_echo($r["id"]); ?>">
      <?php safer_echo($r["account_number"]); ?> | <?php safer_echo($r["account_type"]); ?> | <?php safer_echo($r["balance"]); ?>
    </option>
    <?php endforeach; ?>
  </select>
<?php endif; ?>
<div class="row">
 <div class="col-sm">
   <label for="last_name"> Dest Last Name </label>
   <input type="text" id="last_name" name="last_name" maxlength="60">
  </div>
  <div class="col-sm"> 
   <label for="last_four"> Dest Last Four Digits </label> 
   <input type="number" id="last_four" name="last_four" min="0" max="9999"> 
  </div>
</div>
<label for="deposit"> Amount </label> 
<input type="number" id="deposit" min="0.00" name="balance" step="0.01"> 
<label for="memo"> Memo </label>
<textarea id="memo" name="memo" maxlength="250"></textarea>
<button type="submit" name="save" value="Do transaction"> Do Transaction</button>
</form> 

<?php require __DIR__ . "/partials/flash.php"; ?>