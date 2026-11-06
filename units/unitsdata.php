<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_id'])) {
    header('Location: managerlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$units = [];
$errorMessage = '';
$managerId = $_SESSION['manager_id'];

try {
    // Fetch all units across all properties assigned to this manager
    $query = "SELECT 
                u.unit_id,
                p.property_name,
                blocks.block_name,
                u.unit_name,
                u.unit_type,
                u.monthly_rent,
                u.unit_deposit,
                u.status
              FROM units u
              INNER JOIN blocks ON u.block_id = blocks.block_id
              INNER JOIN property p ON blocks.property_id = p.property_id
              INNER JOIN manager_properties mp ON p.property_id = mp.property_id
              WHERE mp.manager_id = :manager_id
              ORDER BY p.property_name, blocks.block_name, u.unit_name";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':manager_id', $managerId, PDO::PARAM_INT);
    $stmt->execute();
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($units)) {
        $errorMessage = 'No units found for your assigned properties.';
    }
} catch (PDOException $e) {
    $errorMessage = 'Unable to load units: ' . $e->getMessage();
}

$columns = ['property_name', 'block_name', 'unit_name', 'unit_type','unit_deposit', 'status', 'monthly_rent'];
$columnLabels = [
    'property_name' => 'Property',
    'block_name'    => 'Block Name',
    'unit_name'     => 'Unit Name',
    'unit_deposit'  => 'Unit Deposit',
    'unit_type'     => 'Unit Type',
    'status'        => 'Status',
    'monthly_rent'  => 'Monthly Rent'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Units</title>
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
                    <h1>All Units in Assigned Properties</h1>
                </div>
            </section>

            <section class="action-card">
                <div class="page-actions mb-3">
                    <a href="../managerpages/manageunits.php" class="btn-primary">Back to Dashboard</a>
                </div>

                <?php if (!empty($errorMessage) && empty($units)): ?>
                    <p style="color: #dc2626; font-weight: 700;"><?php echo htmlspecialchars($errorMessage); ?></p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="tenant-table table table-striped">
                            <thead>
                                <tr>
                                    <?php foreach ($columns as $column): ?>
                                        <th><?php echo htmlspecialchars($columnLabels[$column] ?? $column); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($units as $unit): ?>
                                    <tr>
                                        <?php foreach ($columns as $column): ?>
                                            <td><?php echo htmlspecialchars((string)($unit[$column] ?? '')); ?></td>
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