<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

$transactions = $conn->query(
    "SELECT transactions.id, transactions.transaction_type,
            transactions.amount, transactions.transaction_date,
            users.full_name, accounts.account_number
     FROM transactions
     INNER JOIN accounts ON transactions.account_id = accounts.id
     INNER JOIN users ON accounts.user_id = users.id
     ORDER BY transactions.transaction_date DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Transactions</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>All Transactions</h1>

    <table border="1" cellpadding="10">
        <tr>
            <th>Transaction ID</th>
            <th>Customer Name</th>
            <th>Account Number</th>
            <th>Transaction Type</th>
            <th>Amount</th>
            <th>Date and Time</th>
        </tr>

        <?php while ($transaction = $transactions->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $transaction["id"]; ?></td>
                <td><?php echo htmlspecialchars($transaction["full_name"]); ?></td>
                <td><?php echo htmlspecialchars($transaction["account_number"]); ?></td>
                <td><?php echo ucfirst($transaction["transaction_type"]); ?></td>
                <td>₹<?php echo number_format($transaction["amount"], 2); ?></td>
                <td><?php echo $transaction["transaction_date"]; ?></td>
            </tr>
        <?php } ?>
    </table>

    <br>
    <a href="dashboard.php">Back to Admin Dashboard</a>
</body>
</html>