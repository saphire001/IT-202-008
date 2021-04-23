<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . "/partials/nav.php";
if (!is_logged_in()) {
  //this will redirect to login and kill the rest of this script (prevent it from executing)
  flash("You don't have permission to access this page");
  die(header("Location: login.php"));
}

$results = [];

if (isset($_GET["id"])) {
  $id = $_GET["id"];
  $user = get_user_id();
  if(isset($_GET["page"])){
    $page = (int)$_GET["page"];
  } else {
    $page = 1;
  }
  $db = getDB();

  $stmt = $db->prepare(
    "SELECT count(*) as total
    FROM Transactions
    JOIN Accounts AS Src ON Transactions.act_src_id = Src.id
    WHERE Transactions.act_src_id = :acct_id AND Src.user_id = :user
    ORDER BY Transactions.id DESC LIMIT 10"
  );
  $r = $stmt->execute([
    ":acct_id" => $id,
    ":user" => $user
  ]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  if($result){
    $total = (int)$result["total"];
  } else {
    $total = 0;
  }

  $per_page = 10;
  $total_pages = ceil($total / $per_page);
  $offset = ($page - 1) * $per_page;

  $stmt = $db->prepare(
    "SELECT amount, action_type, memo, expected_total, created, Dest.account_number AS dest, Src.account_number AS src
    FROM Transactions
    JOIN Accounts AS Src ON Transactions.act_src_id = Src.id
    JOIN Accounts AS Dest ON Transactions.act_dest_id = Dest.id
    WHERE Transactions.act_src_id = :acct_id AND Src.user_id = :user
    ORDER BY Transactions.id DESC LIMIT :offset,:count"
  );
  $stmt->bindValue(":acct_id", $id);
  $stmt->bindValue(":user", $user);
  $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
  $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
  $r = $stmt->execute();
  if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    $results = [];
    flash("There was a problem fetching the results");
  }
}
?>
    <h3 class="text-center mt-4 mb-4">Transaction History</h3>

<?php if (count($results) > 0): ?>
  <table class="table table-striped">
    <thead class="thead-dark">
      <tr>  
        <th scope="col">Account Number (Source)</th>
        <th scope="col">Account Number (Dest)</th>
        <th scope="col">Type</th>
        <th scope="col">Change</th>
        <th scope="col">Memo</th>
        <th scope="col">Balance</th>
      </tr>
    </thead>
    <tbody>
  <?php foreach ($results as $r): ?>
      <tr>
        <td><?php safer_echo($r["src"]); ?></td>
        <th scope="row"><?php safer_echo($r["dest"]); ?></th>
        <td><?php safer_echo(ucfirst($r["action_type"])); ?></td>
        <td>$<?php safer_echo($r["amount"]); ?></td>
        <td><?php safer_echo($r["memo"]); ?></td>
        <td>$<?php safer_echo($r["expected_total"]); ?></td>
      </tr>
  <?php endforeach; ?>
    </tbody>
  </table>

  <nav>
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($page - 1) < 1 ? "disabled" : ""; ?>">
            <a class="page-link" href="?id=<?php safer_echo($id); ?>&page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
        </li>
        <?php for($i = 0; $i < $total_pages; $i++): ?>
          <li class="page-item <?php echo ($page-1) == $i ? "active" : ""; ?>"><a class="page-link" href="?id=<?php safer_echo($id); ?>&page=<?php echo ($i + 1); ?>"><?php echo ($i + 1); ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page) >= $total_pages ? "disabled" : ""; ?>">
            <a class="page-link" href="?id=<?php safer_echo($id); ?>&page=<?php echo $page + 1; ?>">Next</a>
        </li>
    </ul>
  </nav>
<?php else: ?>
  <p>You don't have any accounts.</p>
<?php endif; ?>

<?php require __DIR__ . "/partials/flash.php"; ?>