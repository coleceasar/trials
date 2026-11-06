<?php
// 1. PHP Logic: Handle the form submission
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Hardcoded credentials for demonstration (Beginner Level)
    if ($username === "admin" && $password === "password123") {
        $message = "<p class='success'>Login successful! Welcome back.</p>";
    } else {
        $message = "<p class='error'>Invalid username or password.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Login</title>
</head>
<body>

    <div class="login-container">
        <h2>Account Login</h2>
        
        <?php echo $message; ?>

        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter username">
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter password">
            </div>
            
            <button type="submit">Sign In</button>
        </form>
    </div>

</body>
</html>