<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

$customer_count = $conn->query(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'customer'"
)->fetch_assoc()["total"];

$account_count = $conn->query(
    "SELECT COUNT(*) AS total FROM accounts"
)->fetch_assoc()["total"];

$transaction_count = $conn->query(
    "SELECT COUNT(*) AS total FROM transactions"
)->fetch_assoc()["total"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?></h1>
    <h2>Admin Dashboard</h2>

    <p>Total Customers: <?php echo $customer_count; ?></p>
    <p>Total Accounts: <?php echo $account_count; ?></p>
    <p>Total Transactions: <?php echo $transaction_count; ?></p>

    <h2>Admin Services</h2>
    <p><a href="customers.php">Manage Customers</a></p>
    <p><a href="accounts.php">View Accounts</a></p>
    <p><a href="transactions.php">View Transactions</a></p>
    <p><a href="../logout.php">Logout</a></p>
</body>
</html>