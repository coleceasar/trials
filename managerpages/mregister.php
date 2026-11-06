<?php
session_start();
include_once '../config.php';

$database = new Database();
$db = $database->getConnection();

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$phoneNumber = trim($_POST['phone_number'] ?? '');
$id_number = trim($_POST['id_number'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');


if (!$firstName || !$lastName || !$phoneNumber || !$id_number || !$username || !$password) {
    echo 'All fields are required. Please complete the form and try again.';
    exit;
}

try {
    $checkQuery = "SELECT COUNT(*) FROM property_manager WHERE username = :username";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();

    if ($checkStmt->fetchColumn() > 0) {
        echo 'This username is already taken. Please choose another username.';
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO property_manager (first_name, last_name, phone_number, id_number, username, password, unit_id, block_id) VALUES (:first_name, :last_name, :phone_number, :id_number, :username, :password, :unit_id, :block_id)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':first_name', $firstName);
    $stmt->bindParam(':last_name', $lastName);
    $stmt->bindParam(':phone_number', $phoneNumber);
    $stmt->bindParam(':id_number', $id_number);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashedPassword);


    if ($stmt->execute()) {
        $_SESSION['manager_username'] = $username;
        header('Location: pages/mdashboard.php');
        exit;
    } else {
        echo 'Unable to save registration. Please try again.';
    }
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

