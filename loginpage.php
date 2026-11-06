<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">
  <form method="post" action="tenantslogin.php">
                <div class="container">
            <h2>Tenant Login</h2>
            <img src="loginpanel.png" alt="Login Panel">
            <p>Enter the Login Credentials:</p>
            <input type="text" placeholder="Username" name="username" required>
            <input type="password" placeholder="Password" name="password" required>
               <button type="submit">Login</button>
               <p class="small-note">Don't have an account? <a href="tregistration.php">Register here</a>.</p>
                </div>
 </form>
</body>
</html>