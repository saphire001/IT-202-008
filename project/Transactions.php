<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
ob_start();
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

  $stmt = $db -> prepare("SELECT * FROM Accounts WHERE user_id = :id AND active = 1"); 
  $stmt -> execute([':id' => $user]); 
  $result = $stmt-> fetchAll(PDO::FETCH_ASSOC); 

  if (isset($_POST["save"])) {
    $balance = $_POST["balance"];
    $memo = $_POST["memo"];
    
    if($type == 'deposit') {
      $account = $_POST["account"];
      $r = changeBalance($db, 1, $account, 'deposit', $balance, $memo);
    }
    if($type == 'withdraw')  {
      $account = $_POST["account"];
      $stmt = $db->prepare('SELECT balance FROM Accounts WHERE id = :id');
      $stmt->execute([':id' => $account]);
      $acct = $stmt->fetch(PDO::FETCH_ASSOC);
      if($acct["balance"] < $balance) {
          flash("Not enough funds to withdraw!");
          die(header("Location: transaction.php?type=withdraw"));
      }
      $r = changeBalance($db, $account, 1, 'withdraw', $balance, $memo);
    }
    if($type == 'transfer')  {
      $account_src = $_POST["account_src"];
      $account_dest = $_POST["account_dest"];
      if($account_src == $account_dest){
        flash("Cannot transfer to same account!");
        die(header("Location: transaction.php?type=transfer"));
      }
      $stmt = $db->prepare('SELECT balance FROM Accounts WHERE id = :id');
      $stmt->execute([':id' => $account_src]);
      $acct = $stmt->fetch(PDO::FETCH_ASSOC);
      if($acct["balance"] < $balance) {
        flash("Not enough funds to transfer!");
        die(header("Location: transaction.php?type=transfer"));
      }
      $r = changeBalance($db, $account_src, $account_dest, 'transfer', $balance, $memo);
    }
    if ($r) {
      flash("Successfully executed transaction.");
    } 
    else {
      flash("Error doing transaction!");
    }
  }
  ob_end_flush();
?>

<ul class="nav nav-pills justify-content-center mt-4 mb-2">
  <li class="nav-item"><a class="nav-link <?php echo $type == 'deposit' ? 'active' : ''; ?>" href="?type=deposit">Deposit</a></li>
  <li class="nav-item"><a class="nav-link <?php echo $type == 'withdraw' ? 'active' : ''; ?>" href="?type=withdraw">Withdraw</a></li>
  <li class="nav-item"><a class="nav-link <?php echo $type == 'transfer' ? 'active' : ''; ?>" href="?type=transfer">Transfer</a></li>
</ul> 

<form method="POST">
  <?php if (count($results) > 0): ?>
  <div class="form-group">
    <label for="account"><?php echo $type == 'transfer' ? 'Account Source' : 'Account'; ?></label>
    <select class="form-control" id="account" name="<?php echo $type == 'transfer' ? 'account_src' : 'account'; ?>">
      <?php foreach ($results as $r): ?>
      <?php if ($r["account_type"] != "loan"): ?>
      <option value="<?php safer_echo($r["id"]); ?>">
        <?php safer_echo($r["account_number"]); ?> | <?php safer_echo($r["account_type"]); ?> | <?php safer_echo($r["balance"]); ?>
      </option>
      <?php endif; ?>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <?php if (count($results) > 0 && $type == 'transfer'): ?>
  <div class="form-group">
    <label for="account">Account Destination</label>
    <select class="form-control" id="account" name="account_dest">
      <?php foreach ($results as $r): ?>
      <option value="<?php safer_echo($r["id"]); ?>">
        <?php safer_echo($r["account_number"]); ?> | <?php safer_echo($r["account_type"]); ?> | <?php safer_echo($r["balance"]); ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <div class="form-group">
    <label for="deposit">Amount</label>
    <div class="input-group">
      <div class="input-group-prepend">
        <span class="input-group-text">$</span>
      </div>
      <input type="number" class="form-control" id="deposit" min="0.00" name="balance" step="0.01" placeholder="0.00"/>
    </div>
  </div>
  <div class="form-group">
    <label for="memo">Memo</label>
    <textarea class="form-control" id="memo" name="memo" maxlength="250"></textarea>
  </div>
  <button type="submit" name="save" value="Do Transaction" class="btn btn-success">Do Transaction</button>
</form>

<?php require(__DIR__ . "/partials/flash.php");?>