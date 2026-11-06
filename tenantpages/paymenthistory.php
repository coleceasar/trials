<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['tenant_username'])) {
    header('Location: tenantlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$paymentHistory = [];
$errorMessage = '';
$columns = ['payment_date', 'phone_number', 'payment_method', 'amount', 'transaction_reference_number'];
$columnLabels = [
    'payment_date' => 'Payment Date',
    'phone_number' => 'Phone Number',
    'payment_method' => 'Payment Method',
    'amount' => 'Amount',
    'transaction_reference_number' => 'Transaction Reference Number',
];

try {
    $username = trim($_SESSION['tenant_username']);

    $tenantQuery = 'SELECT tenant_id FROM tenants WHERE username = :username LIMIT 1';
    $tenantStmt = $db->prepare($tenantQuery);
    $tenantStmt->bindParam(':username', $username);
    $tenantStmt->execute();
    $tenant = $tenantStmt->fetch(PDO::FETCH_ASSOC);

    if (!$tenant) {
        throw new Exception('Tenant record not found.');
    }

    $tenantId = $tenant['tenant_id'] ?? null;
    if ($tenantId === null) {
        throw new Exception('Unable to determine tenant ID.');
    }

    $query = 'SELECT * FROM payments WHERE tenant_id = :tenant_id ORDER BY tenant_id DESC LIMIT 5';
    $stmt = $db->prepare($query);
    $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
    $stmt->execute();
    $paymentHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($paymentHistory)) {
        $columns = array_values(array_filter($columns, function ($column) use ($paymentHistory) {
            return array_key_exists($column, $paymentHistory[0]);
        }));
    }
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/theader.php'; ?>
        <div class="dashboard-grid">
            <?php include '../components/tsidebar.php'; ?>
            <main class="main-content">
                <section class="welcome-card">
                    <div>
                        <p class="eyebrow">Payment History</p>
                    </div>
                </section>

                <section class="action-card">
                    <div class="page-actions">
                        <a href="../tenantpages/tdashboard.php" class="btn-primary">Back to dashboard</a>
                    </div>

                    <?php if (!empty($errorMessage)): ?>
                        <p style="color: #dc2626; font-weight: 700;"> <?php echo htmlspecialchars($errorMessage); ?></p>
                    <?php endif; ?>

                    <?php if (empty($paymentHistory)): ?>
                        <p>No payment history available.</p>
                    <?php else: ?>
                        <div style="overflow-x:auto;">
                            <table class="tenant-table">
                                <thead>
                                    <tr>
                                        <?php foreach ($columns as $column): ?>
                                            <th><?php echo htmlspecialchars($columnLabels[$column] ?? ucwords(str_replace('_', ' ', $column))); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paymentHistory as $payment): ?>
                                        <tr>
                                            <?php foreach ($columns as $column): ?>
                                                <td>
                                                    <?php if ($column === 'amount'): ?>
                                                        <?php echo 'KES ' . htmlspecialchars(number_format((float) ($payment[$column] ?? 0), 2)); ?>
                                                    <?php else: ?>
                                                        <?php echo htmlspecialchars($payment[$column] ?? ''); ?>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>
<script src="../components/list-filter.js" defer></script>
</body>
</html>