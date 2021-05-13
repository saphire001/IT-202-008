<?php
ob_start();
require_once __DIR__ . "/partials/nav.php";
if (!is_logged_in()) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: login.php"));
}

$user = get_user_id();
$db = getDB();

$stmt = $db->prepare("SELECT id, account_number, account_type, balance FROM Accounts WHERE user_id = :id AND account_type NOT LIKE 'loan' AND active = 1");
$stmt->execute([':id' => $user]);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["save"])) {
  $check = $db->prepare('SELECT account_number FROM Accounts WHERE account_number = :q');
  do {
    $account_number = rand(100000000000, 999999999999);
    $check->execute([':q' => $account_number]);
  } while ( $check->rowCount() > 0 );

  $account_dest = $_POST["account_dest"];
  $apy = $_POST["apy"];

  $balance = $_POST["balance"];
  if($balance < 500) {
    flash("Minimum balance not entered.");
    die(header("Location: create_loan.php"));
  }

  $user = get_user_id();
  $stmt = $db->prepare(
    "INSERT INTO Accounts (account_number, user_id, account_type, balance, APY) VALUES (:account_number, :user, :account_type, :balance, :apy)"
  );
  $r = $stmt->execute([":account_number" => $account_number,":user" => $user,":account_type" => 'loan',":balance" => -($balance * ($apy / 100)), ":apy" => $apy]);
  if ($r) {
    changeBalance($db, $db->lastInsertId(), $account_dest, 'deposit', $balance, 'New account deposit');
    flash("Account created successfully with Number: " . $account_number);
    die(header("Location: accounts.php"));
  } else {
    flash("Error creating account!");
  }
}
ob_end_flush();
?>

<from metod="POST"> 
 <label for="deposit"> Loan Principal </label>
 <input type="number" id="deposit" min="50.00" name="balance" step="0.01"/>
 <label for="account_dest">Deposit to Account</label>
    <select class="form-control" id="account_dest" name="account_dest">
      <?php foreach ($accounts as $r): ?>
      <option value="<?php safer_echo($r["id"]); ?>">
        <?php safer_echo($r["account_number"]); ?> | <?php safer_echo($r["account_type"]); ?> | <?php safer_echo($r["balance"]); ?>
      </option>
      <?php endforeach; ?>
    </select>
<label for="apy"> APY </label>
<input type="number" class="form-control" id="apy" min="2.00" name="apy" step="0.0001"/>
<button type="submit" name="save" value="create" class="btn btn-primary">Create</button>
</form>

<?php require __DIR__ . "/partials/flash.php"; ?>