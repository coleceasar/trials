<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$properties = [];
$errorMessage = '';

try {
    // Fetch all properties
    $query = "SELECT 
                p.property_id,
                p.property_name,
                p.property_type,
                p.location,
                p.numberofblocks
              FROM property p
              ORDER BY p.property_name";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($properties)) {
        $errorMessage = 'No properties found.';
    }
} catch (PDOException $e) {
    $errorMessage = 'Unable to load properties: ' . $e->getMessage();
}
$columns = ['property_name', 'property_type', 'location', 'numberofblocks'];
$columnLabels = [
    'property_name' => 'Property Name',
    'property_type' => 'Property Type',
    'location' => 'Location',
    'numberofblocks' => 'Number of Blocks'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Properties</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/adminheader.php'; ?>
        <main class="main-content">
            <section class="welcome-card">
                <div>
                    <p class="eyebrow">Admin's Menu</p>
                    <h1>All Properties</h1>
                </div>
            </section>

            <section class="action-card">
                <div class="page-actions mb-3">
                    <a href="admindashboard.php" class="btn-primary">Back to Dashboard</a>
                </div>

                <?php if (!empty($errorMessage) && empty($properties)): ?>
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
                                <?php foreach ($properties as $property): ?>
                                    <tr>
                                        <?php foreach ($columns as $column): ?>
                                            <td><?php echo htmlspecialchars((string)($property[$column] ?? '')); ?></td>
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