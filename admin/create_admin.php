<?php
include "../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        $message = "This email already exists.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = $conn->prepare(
            "INSERT INTO users (full_name, email, password, role)
             VALUES (?, ?, ?, 'admin')"
        );
        $query->bind_param("sss", $full_name, $email, $hashed_password);
        $query->execute();

        $message = "Admin account created successfully. You can now log in.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Admin Account</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>Create Admin Account</h1>

    <?php if (!empty($message)) { ?>
        <p><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST">
        <label>Admin Name:</label><br>
        <input type="text" name="full_name" required><br><br>

        <label>Admin Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Admin Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Create Admin</button>
    </form>
</body>
</html>