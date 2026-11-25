<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "customer") {
    header("Location: ../login.php");
    exit();
}

include "../config/database.php";

$user_id = $_SESSION["user_id"];

$query = $conn->prepare(
    "SELECT account_number, balance FROM accounts WHERE user_id = ?"
);
$query->bind_param("i", $user_id);
$query->execute();
$account = $query->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?></h1>

    <h2>Account Details</h2>

    <p>
        Account Number:
        <?php echo htmlspecialchars($account["account_number"]); ?>
    </p>

    <p>
        Current Balance:
        ₹<?php echo number_format($account["balance"], 2); ?>
    </p>

    <h2>Customer Services</h2>
    <p><a href="deposit.php">Deposit Money</a></p>
    <p><a href="withdraw.php">Withdraw Money</a></p>
    <p><a href="transactions.php">Transaction History</a></p>
    <p><a href="../logout.php">Logout</a></p>
</body>
</html>