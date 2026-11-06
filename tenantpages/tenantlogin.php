<?php
session_start();
include_once '../config.php';

if (!empty($_SESSION['tenant_id'])) {
    header('Location: tdashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $query = "SELECT 
                        tp.username,
                        tp.password,
                        t.tenant_id,
                        t.name,
                        t.status
                      FROM tenant_profile tp
                      INNER JOIN tenants t ON tp.tenant_id = t.tenant_id
                      WHERE tp.username = :username
                      LIMIT 1";

            $stmt = $db->prepare($query);
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && strtolower(trim($user['status'])) === 'active') {
                $passwordValid = false;

                if (password_verify($password, $user['password'])) {
                    $passwordValid = true;
                } elseif ($password === $user['password']) {
                    $passwordValid = true;
                }

                if ($passwordValid) {
                    session_regenerate_id(true);

                    $_SESSION['tenant_id'] = (int)$user['tenant_id'];
                    $_SESSION['tenant_username'] = $user['username'];
                    $_SESSION['tenant_name'] = $user['name'];

                    header('Location: tdashboard.php');
                    exit;
                }
            }

            $error = 'Invalid username or password,try gain  or account is inactive.';
        } catch (PDOException $e) {
            $error = 'Database connection error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Login</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="auth-page">
    <form method="POST" action="">
        <div class="container">
            <h2>Tenant Login</h2>
            <p>Enter your credentials to continue:</p>

            <?php if ($error !== ''): ?>
                <div class="error-message" style="color:#d9534f;background:#f2dede;padding:10px;border-radius:4px;margin-bottom:15px;">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </div>
    </form>
</body>
</html>