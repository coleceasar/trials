<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['tenant_username'])) {
    header('Location: tenantlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$tenants = [];
$errorMessage = '';

try {
    $query = "SELECT 
                t.name,  
                u.unit_name,
                maintenance.request_id,
                maintenance.description,
                maintenance.date,
                maintenance.status
            FROM tenants t
            LEFT JOIN units u ON t.unit_id = u.unit_id
            LEFT JOIN maintenance ON t.tenant_id = maintenance.tenant_id
            WHERE t.tenant_id = :tenant_id
            AND t.status = 'Recieved'
            ORDER BY t.name";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':tenant_id', $_SESSION['tenant_id'], PDO::PARAM_INT);
    $stmt->execute();
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Unable to load maintenance requests: ' . $e->getMessage();
}

$columns = ['name', 'unit_name', 'description', 'date', 'status'];
$columnLabels = [
    'name' => 'Name',
    'unit_name' => 'Unit Name',
    'description' => 'Description',
    'date' => 'Date',
    'status' => 'Status'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Maintenance Requests</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/mheader.php'; ?>
        <div class="dashboard-grid">
            <?php include '../components/msidebar.php'; ?>
            <main class="main-content">
                <section class="welcome-card">
                    <div>
                        <p class="eyebrow">Tenant tools</p>
                        <h1>View Maintenance Requests</h1>
                    </div>
                </section>

                <section class="action-card">
                    <div class="page-actions">
                        <a href="tdashboard.php" class="btn-primary">Back to dashboard</a>
                    </div>

                    <?php if ($errorMessage !== ''): ?>
                        <p style="color:#b91c1c; font-weight:700;"><?php echo htmlspecialchars($errorMessage); ?></p>
                    <?php endif; ?>

                    <?php if (empty($tenants)): ?>
                        <p>No maintenance requests were found.</p>
                    <?php else: ?>
                        <div style="overflow-x:auto;">
                            <table class="tenant-table">
                                <thead>
                                    <tr>
                                        <?php foreach ($columns as $column): ?>
                                            <th><?php echo htmlspecialchars($columnLabels[$column] ?? $column); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tenants as $tenant): ?>
                                        <tr>
                                            <?php foreach ($columns as $column): ?>
                                                <td><?php echo htmlspecialchars((string)($tenant[$column] ?? '')); ?></td>
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
