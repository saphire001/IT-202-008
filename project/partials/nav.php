<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
<link rel="stylesheet" href="static/css/styles.css">
<nav>
<ul class="nav">
    <li><a href="home.php">Home</a></li>
    <?php if (!is_logged_in()): ?>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
    <?php endif; ?>
    <?php if (is_logged_in()): ?>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="test_create_accounts.php">Create Account</a></li>
        <li><a href="Close_Account.php">Close Account</a></li>
        <li><a href="Accounts.php">Accounts</a></li> 
        <li><a href="Transactions.php">Transactions</a></li>
        <li><a href="transaction_out.php">Transfers</a></li>
        <li><a href="Create_Loan.php">Create Loans</a></li>
        <li><a href="logout.php">Logout</a></li>
        
    <?php endif; ?>
</ul>
</nav>