<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Login</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body class="auth-page">
  <form method="post" action="#">
                <div class="container">
            <h2>Landlord Login</h2>
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

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$username || !$password) {
    exit;
}
// query the database for the landlord with the provided username
try {
    $query = "SELECT * FROM landlords WHERE username = :username LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo 'Invalid username';
        exit;
    }

    $storedPassword = $row['password'] ?? null;
    $passwordIsValid = false;

    if ($storedPassword) {
        if (password_verify($password, $storedPassword)) {
            $passwordIsValid = true;
        } elseif ($storedPassword === $password) {
            $passwordIsValid = true;
        }
    }

    if (!$passwordIsValid) {
        echo 'Invalid username or password.';
        exit;
    }
    $_SESSION['landlord_username'] = $row['username'];

    $propertyStmt = $db->prepare("SELECT property_id FROM landlord_properties WHERE owner_id = :owner_id LIMIT 1");
    $propertyStmt->execute([':owner_id' => $row['owner_id']]);
    $propertyRow = $propertyStmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($propertyRow['property_id'])) {
        $_SESSION['property_id'] = $propertyRow['property_id'];
    }

    header('Location:ownerdashboard.php');
    exit;
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

