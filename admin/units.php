<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$units = [];
$properties = [];
$propertyFilter = $_GET['property_id'] ?? '';
$selectedPropertyId = $propertyFilter === '' ? null : filter_var($propertyFilter, FILTER_VALIDATE_INT);
$invalidPropertyFilter = $propertyFilter !== ''
    && ($selectedPropertyId === false || $selectedPropertyId < 1);
$errorMessage = '';

try {
    $propertyQuery = "SELECT DISTINCT p.property_id, p.property_name
                      FROM property p
                      INNER JOIN blocks ON p.property_id = blocks.property_id
                      INNER JOIN units u ON blocks.block_id = u.block_id
                      ORDER BY p.property_name";
    $propertyStmt = $db->prepare($propertyQuery);
    $propertyStmt->execute();
    $properties = $propertyStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($invalidPropertyFilter) {
        $errorMessage = 'Invalid property selected.';
    } else {
        $query = "SELECT 
                    u.unit_id,
                    p.property_name,
                    blocks.block_name,
                    u.unit_name,
                    u.monthly_rent,
                    u.status
                  FROM units u
                  INNER JOIN blocks ON u.block_id = blocks.block_id
                  INNER JOIN property p ON blocks.property_id = p.property_id
                  " . ($selectedPropertyId !== null ? "WHERE p.property_id = :property_id " : "") . "
                  ORDER BY p.property_name, blocks.block_name, u.unit_name";

        $stmt = $db->prepare($query);
        if ($selectedPropertyId !== null) {
            $stmt->bindValue(':property_id', $selectedPropertyId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $units = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($units)) {
            $errorMessage = $selectedPropertyId !== null
                ? 'No units found for the selected property.'
                : 'No units found.';
        }
    }
} catch (PDOException $e) {
    $errorMessage = 'Unable to load units: ' . $e->getMessage();
}

$columns = ['property_name', 'block_name', 'unit_name', 'status', 'monthly_rent'];
$columnLabels = [
    'property_name' => 'Property',
    'block_name'    => 'Block Name',
    'unit_name'     => 'Unit Name',
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
        <?php include '../components/adminheader.php'; ?>
        <main class="main-content">
            <section class="welcome-card">
                <div>
                    <p class="eyebrow">Manager tools</p>
                    <h1>All Units in Assigned Properties</h1>
                </div>
            </section>

            <section class="action-card">
                <div class="page-actions mb-3">
                    <a href="admindashboard.php" class="btn-primary">Back to Dashboard</a>
                </div>

                <form method="get" class="d-flex flex-wrap align-items-end gap-2 mb-3">
                    <div>
                        <label for="property_id" class="form-label">Filter by property</label>
                        <select name="property_id" id="property_id" class="form-select">
                            <option value="">All properties</option>
                            <?php foreach ($properties as $property): ?>
                                <option value="<?php echo (int)$property['property_id']; ?>"
                                    <?php echo $selectedPropertyId === (int)$property['property_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($property['property_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <?php if ($selectedPropertyId): ?>
                        <a href="units.php" class="btn btn-outline-secondary">Clear</a>
                    <?php endif; ?>
                </form>

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