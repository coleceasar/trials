<?php
session_start();
include_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Form inputs
$managerId   = (int)($_POST['manager_id'] ?? 0);
$propertyId  = (int)($_POST['property_id'] ?? 0);
$name        = trim($_POST['name'] ?? '');
$phoneNumber = trim($_POST['phone_number'] ?? '');
$nationalId  = trim($_POST['national_id'] ?? '');
$username    = trim($_POST['username'] ?? '');
$password    = trim($_POST['password'] ?? '');

if (!$managerId || !$propertyId || !$name || !$phoneNumber || !$username || !$password) {
    die('Please fill in all required fields.');
}

try {
    $db->beginTransaction();

    // 1. Check if landlord username exists
    $checkUser = $db->prepare("SELECT COUNT(*) FROM landlords WHERE username = :username");
    $checkUser->execute([':username' => $username]);
    if ($checkUser->fetchColumn() > 0) {
        $db->rollBack();
        die('This username is already taken.');
    }

    // 2. Verify Property exists and is unassigned
    $checkProp = $db->prepare("SELECT status FROM property WHERE property_id = :property_id LIMIT 1");
    $checkProp->execute([':property_id' => $propertyId]);
    $propStatus = $checkProp->fetchColumn();

    if (!$propStatus || $propStatus !== 'Unassigned') {
        $db->rollBack();
        die('Selected property is either not found or already assigned.');
    }

    // 3. Register Landlord
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $landlordSql = "INSERT INTO landlords (`name`, phone_number, national_id, username, password)
                    VALUES (:name, :phone_number, :idnumber, :username, :password)";
    $stmt = $db->prepare($landlordSql);
    $stmt->execute([
        ':name'         => $name,
        ':phone_number' => $phoneNumber,
        ':idnumber'     => $nationalId,
        ':username'     => $username,
        ':password'     => $hashedPassword,
    ]);
    $landlordId = (int)$db->lastInsertId();

    // 4. Link Landlord to Property
    $assignLandlord = $db->prepare("INSERT INTO landlord_properties (owner_id, property_id, status)
                                   VALUES (:owner_id, :property_id, 'active')");
    $assignLandlord->execute([
        ':owner_id'    => $landlordId,
        ':property_id' => $propertyId,
    ]);

    // 5. Link Manager to Property
    $assignManager = $db->prepare("INSERT INTO manager_properties (manager_id, property_id) VALUES (:manager_id, :property_id)");
    $assignManager->execute([
        ':manager_id'  => $managerId,
        ':property_id' => $propertyId
    ]);

    // 6. Update Property Status to Assigned
    $updatePropStatus = $db->prepare("UPDATE property SET status = 'Assigned' WHERE property_id = :property_id");
    $updatePropStatus->execute([':property_id' => $propertyId]);

    // 7. Update Manager Status to active if necessary
    $activateManager = $db->prepare("UPDATE manager SET status = 'active' WHERE manager_id = :manager_id AND status = 'inactive'");
    $activateManager->execute([':manager_id' => $managerId]);

    $db->commit();
    header('Location: owner_registration.php?registered=1');
    exit;

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Registration Error: " . $e->getMessage());
    echo "Database Error: " . htmlspecialchars($e->getMessage());
}