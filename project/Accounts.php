<?php
ob_start();
require_once __DIR__ . "/partials/nav.php";
if (!is_logged_in()) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: login.php"));
}

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
        $results = $stmt -> fetchAll(PDO::FETCH_ASSOC); 
    } 
    else{
        flash("Issue with fetching data");
    }
}
ob_end_flush();
?>

<form method = "POST">
    <input name = "query" placeholder = "Search" value = "<?php safer_echo($query);?>"/>
    <input type = "submit" value = "Search" name = "search"/>
</form>

    <h3 class="text-center mt-4 mb-4">Accounts</h3>

    <?php if (count($results) > 0): ?>
      <table class="table table-striped">
        <thead class="thead-dark">
          <tr>  
            <th scope="col">Account Number</th>
            <th scope="col">Account Type</th>
            <th scope="col">Balance</th>
            <th scope="col">History</th>
          </tr>
        </thead>
        <tbody>
      <?php foreach ($results as $r): ?>
          <tr>
            <th scope="row"><?php safer_echo($r["account_number"]); ?></th>
            <td><?php safer_echo(ucfirst($r["account_type"])); ?>
            </td>
            <td>$<?php safer_echo(abs($r["balance"])); ?></td>
            <td><a href="view_transactions.php?id=<?php safer_echo($r["id"]); ?>" class="btn btn-success">Transactions</a></td>
          </tr>
      <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>You don't have any accounts.</p>
    <?php endif; ?>

<?php require __DIR__ . "/partials/flash.php"; ?>