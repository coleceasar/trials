<?php
session_start();
include_once '../config.php';

// Ensure the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: adminlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Sanitize inputs
$name        = trim($_POST['name'] ?? '');
$phoneNumber = trim($_POST['phone_number'] ?? '');
$idNumber    = trim($_POST['id_number'] ?? '');
$username    = trim($_POST['username'] ?? '');
$password    = trim($_POST['password'] ?? '');
$unitId      = trim($_POST['unit_id'] ?? '');

if (!$name || !$phoneNumber || !$username || !$password || !$unitId) {
    echo 'All required fields must be completed.';
    exit;
}

try {
    $db->beginTransaction();

    // 1. Check if the username is already taken in the tenants table
    $checkStmt = $db->prepare("SELECT COUNT(*) FROM tenants WHERE username = :username");
    $checkStmt->execute([':username' => $username]);

    if ($checkStmt->fetchColumn() > 0) {
        $db->rollBack();
        echo 'This username is already taken. Please choose another one.';
        exit;
    }

    // 2. Insert into tenants table
    $tenantQuery = "INSERT INTO tenants (unit_id, username, name, `idnumber/passport`, phone_number, status) 
                    VALUES (:unit_id, :username, :name, :idnumber, :phone_number, 'active')";
    
    $tenantStmt = $db->prepare($tenantQuery);
    $tenantStmt->execute([
        ':unit_id'      => $unitId,
        ':username'     => $username,
        ':name'         => $name,
        ':idnumber'     => $idNumber,
        ':phone_number' => $phoneNumber
    ]);

    $tenantId = $db->lastInsertId();
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // 3. Insert into tenant_profile table
    $profileQuery = "INSERT INTO tenant_profile (username, tenant_id, password, lastlogin, ip_address) 
                     VALUES (:username, :tenant_id, :password, NOW(), INET_ATON(:ip_address))";
    
    $profileStmt = $db->prepare($profileQuery);
    $profileStmt->execute([
        ':username'   => $username,
        ':tenant_id'  => $tenantId,
        ':password'   => $password,
        ':ip_address' => $ipAddress
    ]);

    // 4. Update the unit status to 'occupied'
    $updateUnitStmt = $db->prepare("UPDATE units SET status = 'occupied' WHERE unit_id = :unit_id");
    $updateUnitStmt->execute([':unit_id' => $unitId]);

    // Commit all operations
    $db->commit();

    header('Location: admindashboard.php');
    exit;

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Registration Error: " . $e->getMessage());
    echo 'Registration failed: ' . $e->getMessage();
}