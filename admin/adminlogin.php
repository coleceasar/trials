<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body class="auth-page">
  <form method="post" action="#">
        <div class="container">
            <h2>ADMIN LOGIN</h2>
            <p>Enter the Login Credentials:</p>
            <input type="text" placeholder="Username" name="username" required>
            <input type="password" placeholder="Password" name="password" required>
            <button type="submit">Login</button>
        </div>
 </form>
</body>
</html>
<?php
session_start();
include_once '../config.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        die("Please enter both username and password.");
    }

    try {

        // Get admin details
        $query = "SELECT * FROM admin WHERE username = :username LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            die("Invalid username or password.");
        }
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        $passwordValid = false;

        if (!empty($admin['password'])) {

            if (password_verify($password, $admin['password'])) {
                $passwordValid = true;
            } elseif ($password === $admin['password']) {
                // Remove this after all passwords are hashed
                $passwordValid = true;
            }
        }

        if (!$passwordValid) {
            die("Invalid username or password.");
        }

        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_username'] = $admin['username'];

        header("Location: admindashboard.php");
        exit();
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>