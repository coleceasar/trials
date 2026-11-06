<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_id'])) {
    header('Location: managerlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$tenants = [];

try {
    // Fetch active tenants for all properties assigned to this manager via manager_properties
    $query = "SELECT 
                t.name, 
                t.phone_number,
                t.national_id,
                u.unit_name,
                p.property_name,
                t.status
              FROM tenants t
              INNER JOIN units u ON t.unit_id = u.unit_id
              INNER JOIN blocks b ON u.block_id = b.block_id
              INNER JOIN property p ON b.property_id = p.property_id
              INNER JOIN manager_properties mp ON p.property_id = mp.property_id
              AND mp.manager_id = :manager_id
              ORDER BY p.property_name, u.unit_name, t.name";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':manager_id', $_SESSION['manager_id'], PDO::PARAM_INT);
    $stmt->execute();
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Unable to load tenants: ' . $e->getMessage();
}

$columns = ['property_name','name','phone_number','national_id',  'unit_name', 'status'];
$columnLabels = [
     'property_name' => 'Property',
    'name' => 'Tenant Name',
    'phone_number' => 'Phone Number',
    'national_id' => 'National ID/Passport No:',
    'unit_name' => 'Unit Name',
    'status'=> 'Status'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Active Tenants</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/mheader.php'; ?>
        <main class="main-content">
            <section class="welcome-card">
                <div>
                    <p class="eyebrow">Manager tools</p>
                    <h1>All Active Tenants</h1>
                </div>
            </section>

            <section class="action-card">
                <div class="page-actions mb-3">
                    <a href="addtenant.php" class="btn-primary">Back to Tenants Management</a>
                </div>

                <?php if ($message !== '' && empty($tenants)): ?>
                    <p style="color:#b91c1c; font-weight:700;"><?php echo htmlspecialchars($message); ?></p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="tenant-table table table-striped" data-default-status="active">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../components/list-filter.js" defer></script>
</body>
</html>