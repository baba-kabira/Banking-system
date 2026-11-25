<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

include "../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["customer_id"])) {
    $customer_id = (int) $_POST["customer_id"];

    try {
        $conn->begin_transaction();

        $check_customer = $conn->prepare(
            "SELECT id FROM users WHERE id = ? AND role = 'customer'"
        );
        $check_customer->bind_param("i", $customer_id);
        $check_customer->execute();

        if ($check_customer->get_result()->num_rows == 0) {
            throw new Exception("Customer not found.");
        }

        $delete_transactions = $conn->prepare(
            "DELETE transactions
             FROM transactions
             INNER JOIN accounts ON transactions.account_id = accounts.id
             WHERE accounts.user_id = ?"
        );
        $delete_transactions->bind_param("i", $customer_id);
        $delete_transactions->execute();

        $delete_accounts = $conn->prepare(
            "DELETE FROM accounts WHERE user_id = ?"
        );
        $delete_accounts->bind_param("i", $customer_id);
        $delete_accounts->execute();

        $delete_customer = $conn->prepare(
            "DELETE FROM users WHERE id = ? AND role = 'customer'"
        );
        $delete_customer->bind_param("i", $customer_id);
        $delete_customer->execute();

        $conn->commit();
        $message = "Customer and associated account records removed successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Customer removal failed.";
    }
}

$customers = $conn->query(
    "SELECT users.id, users.full_name, users.email, users.created_at,
            accounts.account_number, accounts.balance
     FROM users
     LEFT JOIN accounts ON users.id = accounts.user_id
     WHERE users.role = 'customer'
     ORDER BY users.id DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Customers</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Manage Customers</h1>

    <?php if (!empty($message)) { ?>
        <p><?php echo $message; ?></p>
    <?php } ?>

    <p><a href="../register.php">Register New Customer</a></p>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Customer Name</th>
            <th>Email</th>
            <th>Account Number</th>
            <th>Balance</th>
            <th>Registered On</th>
            <th>Action</th>
        </tr>

        <?php while ($customer = $customers->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $customer["id"]; ?></td>
                <td><?php echo htmlspecialchars($customer["full_name"]); ?></td>
                <td><?php echo htmlspecialchars($customer["email"]); ?></td>
                <td><?php echo htmlspecialchars($customer["account_number"]); ?></td>
                <td>₹<?php echo number_format($customer["balance"], 2); ?></td>
                <td><?php echo $customer["created_at"]; ?></td>
                <td>
                    <form method="POST"
                          onsubmit="return confirm('Remove this customer and all associated records?');">
                        <input type="hidden" name="customer_id"
                               value="<?php echo $customer["id"]; ?>">
                        <button type="submit">Remove</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

    <br>
    <a href="dashboard.php">Back to Admin Dashboard</a>
</body>
</html>