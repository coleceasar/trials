<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$tenants = [];
$properties = [];

// Get filter/search params from GET request
$searchName = isset($_GET['search']) ? trim($_GET['search']) : '';
$propertyId = isset($_GET['property_id']) ? trim($_GET['property_id']) : '';
$statusFilter = strtolower(trim($_GET['status'] ?? 'active'));
if (!in_array($statusFilter, ['active', 'inactive', 'all'], true)) {
    $statusFilter = 'active';
}

try {
    // 1. Fetch properties for dropdown filter
    $propQuery = "SELECT property_id, property_name FROM property ORDER BY property_name";
    $propStmt = $db->prepare($propQuery);
    $propStmt->execute();
    $properties = $propStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Build dynamic SQL for tenant search & filter
    $query = "SELECT 
                t.name,  
                u.unit_name,
                p.property_name,
                t.status
              FROM tenants t
              INNER JOIN units u ON t.unit_id = u.unit_id
              INNER JOIN blocks b ON u.block_id = b.block_id
              INNER JOIN property p ON b.property_id = p.property_id
              WHERE 1=1";

    $params = [];

    if ($statusFilter !== 'all') {
        $query .= " AND LOWER(t.status) = :status";
        $params[':status'] = $statusFilter;
    }

    if ($searchName !== '') {
        $query .= " AND t.name LIKE :search";
        $params[':search'] = '%' . $searchName . '%';
    }

    if ($propertyId !== '') {
        $query .= " AND p.property_id = :property_id";
        $params[':property_id'] = $propertyId;
    }

    $query .= " ORDER BY p.property_name, u.unit_name, t.name";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($tenants)) {
        $message = 'No matching tenants found.';
    }
} catch (PDOException $e) {
    $message = 'Unable to load tenants: ' . $e->getMessage();
}

$columns = ['name', 'property_name', 'unit_name', 'status'];
$columnLabels = [
    'name' => 'Tenant Name',
    'property_name' => 'Property',
    'unit_name' => 'Unit Name',
    'status' => 'Status'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant List</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/adminheader.php'; ?>
        <main class="main-content">
            <section class="welcome-card">
                <div>
                    <p class="eyebrow">Admin Actions</p>
                    <h1>Tenant List</h1>
                </div>
            </section>

            <section class="action-card">
                <div class="page-actions mb-3 d-flex justify-content-between align-items-center">
                    <a href="managetenant.php" class="btn btn-secondary">Back to Tenants Management</a>
                </div>

                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search tenant name..." value="<?php echo htmlspecialchars($searchName); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="property_id" class="form-select">
                            <option value="">All Properties</option>
                            <?php foreach ($properties as $prop): ?>
                                <option value="<?php echo htmlspecialchars($prop['property_id']); ?>" <?php echo ($propertyId == $prop['property_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prop['property_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select" aria-label="Filter tenants by status">
                            <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All statuses</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                        <a href="?status=active" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>

                <?php if ($message !== '' && empty($tenants)): ?>
                    <p style="color:#b91c1c; font-weight:700;"><?php echo htmlspecialchars($message); ?></p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="tenant-table table table-striped" data-list-filter="off">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../components/list-filter.js" defer></script>
</body>
</html>