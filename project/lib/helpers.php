<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create
//I have provided two for you
function is_logged_in() {
    return isset($_SESSION["user"]);
}

function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] == $role) {
                return true;
            }
        }
    }
    return false;
}

function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function get_first_name()
{
  if (is_logged_in() && isset($_SESSION["user"]["first_name"])) {
    return $_SESSION["user"]["first_name"];
  }
  return -1;
}

function get_last_name()
{
  if (is_logged_in() && isset($_SESSION["user"]["last_name"])) {
    return $_SESSION["user"]["last_name"];
  }
  return -1;
}

function get_name()
{
  if (is_logged_in() && isset($_SESSION["user"]["id"])) {
    return $_SESSION["user"]["first_name"] . " " .$_SESSION["user"]["last_name"];
  }
  return -1;
}


function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}

//end flash

function getURL($path) {
    if(substr($path, 0, 1) == '/') {
      return $path;
    }
    return $_SERVER['CONTEXT_PREFIX'] . "/IT202/project/$path";
  }

function changeBalance($db, $src, $dest, $type, $balChange, $memo = '') {
    // Src Account Balance
    $stmt = $db->prepare("SELECT balance from Accounts WHERE id = :id");
    $stmt->execute([":id" => $src]);
    $srcAcct = $stmt->fetch(PDO::FETCH_ASSOC);
  
    // Dest Account Balance
    $stmt->execute([":id" => $dest]);
    $destAcct = $stmt->fetch(PDO::FETCH_ASSOC);
  
    // Insert Transaction
    $transactions = $db->prepare(
      "INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total)
      VALUES (:act_src_id, :act_dest_id, :amount, :action_type, :memo, :expected_total)"
    );
    $accounts = $db->prepare(
      "UPDATE Accounts SET balance = :balance WHERE id = :id"
    );
  
    // Calc
    // Force balChange positive
    $balChange = abs($balChange);
    $finalSrcBalace = $srcAcct['balance'] - $balChange;
    $finalDestBalace = $destAcct['balance'] + $balChange;
  
    // First action
    $transactions->execute([
      ":act_src_id" => $src,
      ":act_dest_id" => $dest,
      ":amount" => -$balChange,
      ":action_type" => $type,
      ":memo" => $memo,
      ":expected_total" => $finalSrcBalace
    ]);
  
    // Second action
    $transactions->execute([
      ":act_src_id" => $dest,
      ":act_dest_id" => $src,
      ":amount" => $balChange,
      ":action_type" => $type,
      ":memo" => $memo,
      ":expected_total" => $finalDestBalace
    ]);
  
    // Update balances of Accounts
    $accounts->execute([":balance" => $finalSrcBalace, ":id" => $src]);
    $accounts->execute([":balance" => $finalDestBalace, ":id" => $dest]);
  
    return $transactions;
  }
?>