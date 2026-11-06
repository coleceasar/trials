<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: complaints.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    $_SESSION['communication_message'] = ['type' => 'error', 'text' => 'Database connection failed.'];
    header('Location: complaints.php');
    exit;
}

$unitId = filter_input(INPUT_POST, 'unit_id', FILTER_VALIDATE_INT);
$tenantId = filter_input(INPUT_POST, 'tenant_id', FILTER_VALIDATE_INT);
$complainttype = trim(filter_input(INPUT_POST, 'complaint_type', FILTER_SANITIZE_SPECIAL_CHARS));
$priority = trim(filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_SPECIAL_CHARS));
$subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS));
$description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS));

if (!$unitId || !$tenantId || !$complainttype || !$priority || !$subject || !$description) {
    $_SESSION['communication_message'] = ['type' => 'error', 'text' => 'Please fill all required fields.'];
    header('Location: complaints.php');
    exit;
}

$managerId = null;
try {
    $managerLookup = $db->prepare(
        "SELECT mp.manager_id
         FROM tenants t
         JOIN units u ON t.unit_id = u.unit_id
         JOIN blocks b ON u.block_id = b.block_id
         JOIN manager_properties mp ON b.property_id = mp.property_id
         WHERE t.tenant_id = :tenant_id
         LIMIT 1"
    );
    $managerLookup->execute([':tenant_id' => $tenantId]);
    $managerRow = $managerLookup->fetch(PDO::FETCH_ASSOC);
    $managerId = $managerRow['manager_id'] ?? null;
} catch (PDOException $e) {
    // manager_id remains null if lookup fails
}

try {
    $stmt = $db->prepare(
        'INSERT INTO complaints (unit_id, tenant_id, manager_id, complaint_type, priority, subject, description, created_at) VALUES (:unit_id, :tenant_id, :manager_id, :complaint_type, :priority, :subject, :description, NOW())'
    );

    $stmt->execute([
        ':unit_id' => $unitId,
        ':tenant_id' => $tenantId,
        ':manager_id' => $managerId,
        ':complaint_type' => $complainttype,
        ':priority' => $priority,
        ':subject' => $subject,
        ':description' => $description,
    ]);

    $_SESSION['communication_message'] = ['type' => 'success', 'text' => 'Your message was saved successfully.'];
} catch (PDOException $e) {
    $_SESSION['communication_message'] = ['type' => 'error', 'text' => 'Unable to save data: ' . $e->getMessage()];
}

header('Location: complaints.php');
exit;
