<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "customer") {
    header("Location: ../login.php");
    exit();
}

include "../config/database.php";

$user_id = $_SESSION["user_id"];

$account_query = $conn->prepare(
    "SELECT id, account_number FROM accounts WHERE user_id = ?"
);
$account_query->bind_param("i", $user_id);
$account_query->execute();
$account = $account_query->get_result()->fetch_assoc();

$transaction_query = $conn->prepare(
    "SELECT transaction_type, amount, transaction_date
     FROM transactions
     WHERE account_id = ?
     ORDER BY transaction_date DESC"
);
$transaction_query->bind_param("i", $account["id"]);
$transaction_query->execute();
$transactions = $transaction_query->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction History</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Transaction History</h1>

    <p>Account Number: <?php echo htmlspecialchars($account["account_number"]); ?></p>

    <table border="1" cellpadding="10">
        <tr>
            <th>Transaction Type</th>
            <th>Amount</th>
            <th>Date and Time</th>
        </tr>

        <?php if ($transactions->num_rows > 0) { ?>
            <?php while ($transaction = $transactions->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo ucfirst($transaction["transaction_type"]); ?></td>
                    <td>₹<?php echo number_format($transaction["amount"], 2); ?></td>
                    <td><?php echo $transaction["transaction_date"]; ?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="3">No transactions found.</td>
            </tr>
        <?php } ?>
    </table>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>