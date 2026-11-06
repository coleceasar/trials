<?php
session_start();
include_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$phoneNumber = trim($_POST['phone_number'] ?? '');
$id_number = trim($_POST['id_number'] ?? '');
$floorNo = trim($_POST['floor_no'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$unitId = trim($_POST['unit_id'] ?? '');
$blockId = trim($_POST['block_id'] ?? '');

if (!$firstName || !$lastName || !$phoneNumber || !$id_number || !$floorNo || !$username || !$password || !$unitId || !$blockId) {
    echo 'All fields are required. Please complete the form and try again.';
    exit;
}

try {
    $checkQuery = "SELECT COUNT(*) FROM tenants WHERE username = :username";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();

    if ($checkStmt->fetchColumn() > 0) {
        echo 'This username is already taken. Please choose another username.';
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO tenants (first_name, last_name, phone_number, id_number, floor_no, username, password, unit_id, block_id) VALUES (:first_name, :last_name, :phone_number, :id_number, :floor_no, :username, :password, :unit_id, :block_id)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':first_name', $firstName);
    $stmt->bindParam(':last_name', $lastName);
    $stmt->bindParam(':phone_number', $phoneNumber);
    $stmt->bindParam(':id_number', $id_number);
    $stmt->bindParam(':floor_no', $floorNo);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':unit_id', $unitId);
    $stmt->bindParam(':block_id', $blockId);

    if ($stmt->execute()) {
        $_SESSION['tenant_username'] = $username;
        header('Location: tenantsdashboard.php');
        exit;
    } else {
        echo 'Unable to save registration. Please try again.';
    }
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

