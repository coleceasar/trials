<?php
session_start();
include_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$username || !$password) {
    echo 'Username and password are required.';
    exit;
}

$tableName = 'tenants';
$usernameField = 'username';
$passwordField = 'password';

try {
    $query = "SELECT * FROM {$tableName} WHERE {$usernameField} = :username LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo 'Invalid username or password.';
        exit;
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $storedPassword = $row[$passwordField] ?? null;

    if (!$storedPassword || !password_verify($password, $storedPassword)) {
        echo 'Invalid username or password.';
        exit;
    }

    $_SESSION['tenant_username'] = $username;
    header('Location: tenantsdashboard.php');
    exit;
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}
