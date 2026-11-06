<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['landlord_username'])) {
    header('Location: ownerlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$units = [];
$errorMessage = '';
$landlordUsername = trim($_SESSION['landlord_username'] ?? '');
$landlordId = null;
$propertyIds = [];

try {
    $landlordQuery = "SELECT owner_id FROM landlords WHERE username = :username LIMIT 1";
    $landlordStmt = $db->prepare($landlordQuery);
    $landlordStmt->bindParam(':username', $landlordUsername);
    $landlordStmt->execute();
    $landlord = $landlordStmt->fetch(PDO::FETCH_ASSOC);
    $landlordId = $landlord['owner_id'] ?? null;

    if (!empty($landlord['property_id'])) {
        $propertyIds[] = (int)$landlord['property_id'];
    }

    if ($landlordId !== null) {
        $propertyQuery = "SELECT property_id FROM landlord_properties WHERE owner_id = :owner_id";
        $propertyStmt = $db->prepare($propertyQuery);
        $propertyStmt->bindValue(':owner_id', $landlordId, PDO::PARAM_INT);
        $propertyStmt->execute();
        while ($propertyRow = $propertyStmt->fetch(PDO::FETCH_ASSOC)) {
            $propertyId = (int)($propertyRow['property_id'] ?? 0);
            if ($propertyId > 0 && !in_array($propertyId, $propertyIds, true)) {
                $propertyIds[] = $propertyId;
            }
        }
    }
} catch (PDOException $e) {
    $errorMessage = 'Unable to load landlord profile: ' . $e->getMessage();
    $landlordId = null;
}

try {
    if (!empty($propertyIds)) {
        $placeholders = implode(', ', array_fill(0, count($propertyIds), '?'));
        $query = "SELECT 
                    u.unit_id,
                    blocks.block_name,
                    u.unit_name,
                    u.unit_type,
                    u.monthly_rent,
                    u.status
                  FROM units u
                  LEFT JOIN blocks ON u.block_id = blocks.block_id
                  LEFT JOIN property p ON blocks.property_id = p.property_id
                  WHERE p.property_id IN ($placeholders)
                  ORDER BY blocks.block_name, u.unit_name";

        $stmt = $db->prepare($query);
        foreach ($propertyIds as $index => $propertyId) {
            $stmt->bindValue($index + 1, $propertyId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $errorMessage = "Error fetching units: " . $e->getMessage();
    $units = [];
}
$columns = ['block_name', 'unit_name', 'unit_type', 'status', 'monthly_rent'];
$columnLabels = [
    'unit_name' => 'Unit Name',
    'block_name' => 'Block Name',
    'unit_type' => 'Unit Type',
    'status' => 'Status',
    'monthly_rent' => 'Monthly Rent'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Units</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="dashboard-page">
    <form method="post" action="../udata.php">
    <div class="dashboard-shell">
        <?php include '../components/lheader.php'; ?>
        <div class="dashboard-grid">
            <?php include '../components/lsidebar.php'; ?>
            <main class="main-content">
                <section class="welcome-card">
                    <div>
                        <p class="eyebrow">Manage Units</p>
                        <h1>All units in the property</h1>
                    </div>
                </section>

                <section class="action-card">
                    <div class="page-actions">
                        <a href="../ownerpages/ownerdashboard.php" class="btn-primary">Back to dashboard</a>
                    </div>

                    <?php if (!empty($errorMessage)): ?>
                        <p style="color: #dc2626; font-weight: 700;"><?php echo htmlspecialchars($errorMessage); ?></p>
                    <?php endif; ?>

                    <?php if (empty($units)): ?>
                        <p>No units are registered.</p>
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
                                    <?php foreach ($units as $unit): ?>
                                        <tr>
                                            <?php foreach ($columns as $column): ?>
                                                <td><?php echo htmlspecialchars($unit[$column] ?? ''); ?></td>
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
