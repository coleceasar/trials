<?php
session_start();
include_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$name          = trim($_POST['name'] ?? '');
$phoneNumber   = trim($_POST['phone_number'] ?? '');
$email         = trim($_POST['email'] ?? '');
$username      = trim($_POST['username'] ?? '');
$password      = trim($_POST['password'] ?? '');
$percentagepay = trim($_POST['percentage_pay'] ?? '');

// Validation check
if (empty($name) || empty($phoneNumber) || empty($username) || empty($password) || $percentagepay === '') {
    die('All fields are required.');
}

try {
    $db->beginTransaction();

    // Check if username exists
    $checkUser = $db->prepare("SELECT COUNT(*) FROM manager WHERE username = :username");
    $checkUser->execute([':username' => $username]);
    if ($checkUser->fetchColumn() > 0) {
        $db->rollBack();
        die('This username is already taken.');
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("INSERT INTO manager (name, email, phone_number, username, password, status, percentage_pay) 
                          VALUES (:name, :email, :phone_number, :username, :password, 'inactive', :percentage_pay)");
    
    $stmt->execute([
        ':name'          => $name,
        ':email'         => $email,
        ':phone_number'  => $phoneNumber,
        ':username'      => $username,
        ':password'      => $hashedPassword,
        ':percentage_pay'=> (float)$percentagepay,
    ]);

    $db->commit();
    header('Location: manager_registration.php?registered=1');
    exit;

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    error_log("Manager Registration Error: " . $e->getMessage());
    echo "Database Error: " . htmlspecialchars($e->getMessage());
}