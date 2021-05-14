<?php
require_once __DIR__ . "/partials/nav.php";
if (!is_logged_in()) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: login.php"));
}

$user = get_user_id();
$db = getDB();

$stmt = $db->prepare("SELECT id, account_number, account_type, balance FROM Accounts WHERE user_id = :id AND active = 1");
$stmt->execute([':id' => $user]);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["save"])) {
  $id = $_POST["account"];

  $stmt = $db->prepare('SELECT balance, account_number FROM Accounts WHERE id = :q');
  $stmt->execute([ ":q" => $id ]);
  $account = $stmt->fetch(PDO::FETCH_ASSOC);
  if($account["balance"] != 0) {
    flash("Balance has to be $0 before closing.");
    die(header("Location: close_account.php"));
  }

  $user = get_user_id();
  $stmt = $db->prepare("UPDATE Accounts SET active = 0 WHERE id = :id");
  $r = $stmt->execute([ ":id" => $id ]);
  if ($r) {
    flash("Account ".$account["account_number"]." successfully closed.");
    die(header("Location: Accounts.php"));
  } else {
    flash("Error closing account!");
  }
}
?>

<form method="POST">
<label for="account_dest">Account</label>
    <select class="form-control" id="account" name="account">
      <?php foreach ($accounts as $r): ?>
      <option value="<?php safer_echo($r["id"]); ?>">
        <?php safer_echo($r["account_number"]); ?> | <?php safer_echo($r["account_type"]); ?> | <?php safer_echo($r["balance"]); ?>
      </option>
      <?php endforeach; ?>
    </select>
 <small id="accountHelp" class="form-text text-muted">Account Balance has to be $0 in order to be closed.</small>
 <button type="submit" name="save" value="close"> Close </button> 
</form> 

<?php require __DIR__ . "/partials/flash.php"; ?>