<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['landlord_username'])) {
    header('Location: ownerlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$tenants = [];
$errorMessage = '';

try {
    $query = "SELECT 
                t.name, 
                u.unit_name 
              FROM tenants t
              LEFT JOIN units u ON t.unit_id = u.unit_id
              WHERE t.status = 'active'
              ORDER BY t.name, t.tenant_id";

    $stmt = $db->query($query);
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Unable to load tenants: ' . $e->getMessage();
}

$columns = ['name', 'unit_name'];
$columnLabels = [
    'name' => 'Tenant Name',
    'unit_name' => 'Unit Name'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tenants</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/lheader.php'; ?>
        <div class="dashboard-grid">
            <?php include '../components/lsidebar.php'; ?>
            <main class="main-content">
                <section class="welcome-card">
                    <div>
                        <p class="eyebrow">Landlord tools</p>
                        <h1>All tenants in the property</h1>
                    </div>
                </section>

                <section class="action-card">
                    <div class="page-actions">
                        <a href="ownerdashboard.php" class="btn-primary">Back to Dashboard</a>
                    </div>

                    <?php if ($errorMessage !== ''): ?>
                        <p style="color:#b91c1c; font-weight:700;"><?php echo htmlspecialchars($errorMessage); ?></p>
                    <?php endif; ?>

                    <?php if (empty($tenants)): ?>
                        <p>No active tenants were found in the database.</p>
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