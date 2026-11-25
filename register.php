<?php
include "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($full_name) || empty($email) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "This email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $account_number = "ACC" . random_int(10000000, 99999999);

            $conn->begin_transaction();

            try {
                $user_query = $conn->prepare(
                    "INSERT INTO users (full_name, email, password, role)
                     VALUES (?, ?, ?, 'customer')"
                );
                $user_query->bind_param("sss", $full_name, $email, $hashed_password);
                $user_query->execute();

                $user_id = $conn->insert_id;

                $account_query = $conn->prepare(
                    "INSERT INTO accounts (user_id, account_number, balance)
                     VALUES (?, ?, 0.00)"
                );
                $account_query->bind_param("is", $user_id, $account_number);
                $account_query->execute();

                $conn->commit();
                $message = "Registration successful. Your account number is: " . $account_number;
            } catch (Exception $e) {
                $conn->rollback();
                $message = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Registration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Banking Management System</h1>
    <h2>Customer Registration</h2>

    <?php if (!empty($message)) { ?>
        <p><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST">
        <label>Full Name:</label><br>
        <input type="text" name="full_name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already registered? <a href="login.php">Login here</a></p>
</body>
</html>