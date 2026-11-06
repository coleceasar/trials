<?php
session_start();
include_once 'config.php';

if (empty($_SESSION['tenant_username'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$username = trim($_SESSION['tenant_username']);
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$paymentMethod = trim($input['payment_method'] ?? '');
$amount = trim($input['amount'] ?? '');
$transactionReference = trim($input['transaction_reference_number'] ?? $input['transaction_reference'] ?? '');
$phoneNumber = trim($input['phone_number'] ?? '');

if (!$paymentMethod || !$amount || !$transactionReference || !$phoneNumber) {
    echo json_encode(['success' => false, 'message' => 'Missing payment details.']);
    exit;
}

try {
    // Select both possible primary key column names to support different schemas
    $tenantStmt = $db->prepare("SELECT  tenant_id FROM tenants WHERE username = :username LIMIT 1");
    $tenantStmt->bindParam(':username', $username);
    $tenantStmt->execute();
    $tenant = $tenantStmt->fetch(PDO::FETCH_ASSOC);

    if (!$tenant) {
        echo json_encode(['success' => false, 'message' => 'Tenant record not found.']);
        exit;
    }

    $tenantIdValue = $tenant['id'] ?? $tenant['tenant_id'] ?? null;
    if ($tenantIdValue === null) {
        echo json_encode(['success' => false, 'message' => 'Tenant primary key not found in tenants table.']);
        exit;
    }

    $insertStmt = $db->prepare("INSERT INTO payments (tenant_id, payment_method, amount, transaction_reference_number, status, phone_number) VALUES (:tenant_id, :payment_method, :amount, :transaction_reference, 'paid', :phone_number)");
    $insertStmt->bindValue(':tenant_id', $tenantIdValue, PDO::PARAM_INT);
    $insertStmt->bindValue(':payment_method', $paymentMethod);
    $insertStmt->bindValue(':amount', $amount);
    $insertStmt->bindValue(':transaction_reference', $transactionReference);
    $insertStmt->bindValue(':phone_number', $phoneNumber, PDO::PARAM_INT);
    $insertStmt->execute();

    $paymentId = $db->lastInsertId();
    $paymentStmt = $db->prepare("SELECT * FROM payments WHERE payments_id = :payment_id LIMIT 1");
    $paymentStmt->bindValue(':payment_id', $paymentId, PDO::PARAM_INT);
    $paymentStmt->execute();
    $savedPayment = $paymentStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "message" => "Payment recorded successfully.", "payment" => $savedPayment]);
} catch (PDOException $e) {
    // Log detailed debug information to file for diagnosis
    $log = [
        'time' => date('c'),
        'error' => $e->getMessage(),
        'input' => $input,
        'tenant' => $tenant ?? null,
    ];
    @file_put_contents(__DIR__ . '/payments_error.log', json_encode($log, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

    echo json_encode(['success' => false, 'message' => 'Unable to save payment: ' . $e->getMessage()]);
}
