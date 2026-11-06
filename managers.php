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
// query the database for the tenant with the provided username
try {
    $query = "SELECT * FROM property_manager WHERE username = :username LIMIT 1";
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
// if the username and password are valid, set the session variable and redirect to the tenant dashboard
    $_SESSION['manager_username'] = $row['username'];
    header('Location: managersdashboard.php');
    exit;
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}
