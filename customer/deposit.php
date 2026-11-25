<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "customer") {
    header("Location: ../login.php");
    exit();
}

include "../config/database.php";

$user_id = $_SESSION["user_id"];
$message = "";

$account_query = $conn->prepare(
    "SELECT id, account_number, balance FROM accounts WHERE user_id = ?"
);
$account_query->bind_param("i", $user_id);
$account_query->execute();
$account = $account_query->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = (float) $_POST["amount"];

    if ($amount <= 0) {
        $message = "Please enter an amount greater than zero.";
    } else {
        try {
            $conn->begin_transaction();

            $update_balance = $conn->prepare(
                "UPDATE accounts SET balance = balance + ? WHERE id = ?"
            );
            $update_balance->bind_param("di", $amount, $account["id"]);
            $update_balance->execute();

            $save_transaction = $conn->prepare(
                "INSERT INTO transactions (account_id, transaction_type, amount)
                 VALUES (?, 'deposit', ?)"
            );
            $save_transaction->bind_param("id", $account["id"], $amount);
            $save_transaction->execute();

            $conn->commit();

            $message = "₹" . number_format($amount, 2) . " deposited successfully.";

            $account_query->execute();
            $account = $account_query->get_result()->fetch_assoc();
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Deposit failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Deposit Money</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Deposit Money</h1>

    <p>Account Number: <?php echo htmlspecialchars($account["account_number"]); ?></p>
    <p>Current Balance: ₹<?php echo number_format($account["balance"], 2); ?></p>

    <?php if (!empty($message)) { ?>
        <p><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST">
        <label>Amount to Deposit:</label><br>
        <input type="number" name="amount" min="0.01" step="0.01" required><br><br>

        <button type="submit">Deposit</button>
    </form>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>