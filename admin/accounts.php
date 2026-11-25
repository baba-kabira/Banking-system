<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

$accounts = $conn->query(
    "SELECT accounts.id, accounts.account_number, accounts.balance,
            accounts.created_at, users.full_name, users.email
     FROM accounts
     INNER JOIN users ON accounts.user_id = users.id
     ORDER BY accounts.id DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Accounts</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Customer Accounts
        
    </h1>

    <table border="1" cellpadding="10">
        <tr>
            <th>Account ID</th>
            <th>Customer Name</th>
            <th>Email</th>
            <th>Account Number</th>
            <th>Current Balance</th>
            <th>Created On</th>
        </tr>

        <?php while ($account = $accounts->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $account["id"]; ?></td>
                <td><?php echo htmlspecialchars($account["full_name"]); ?></td>
                <td><?php echo htmlspecialchars($account["email"]); ?></td>
                <td><?php echo htmlspecialchars($account["account_number"]); ?></td>
                <td>₹<?php echo number_format($account["balance"], 2); ?></td>
                <td><?php echo $account["created_at"]; ?></td>
            </tr>
        <?php } ?>
    </table>

    <br>
    <a href="dashboard.php">Back to Admin Dashboard</a>
</body>
</html>