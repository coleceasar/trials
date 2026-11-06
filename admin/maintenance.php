<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $errorMessage = 'You do not have permission to modify request status from the admin view. This page is read-only.';
}

try {
    $query = "SELECT
                m.request_id,
                m.description,
                m.date,
                m.status,
                m.priority,
                t.name AS tenant_name,
                u.unit_type AS unit_type,
                u.unit_name AS unit_name,
                p.property_name
              FROM maintenance m
              LEFT JOIN tenants t ON m.tenant_id = t.tenant_id
              LEFT JOIN units u ON COALESCE(m.unit_id, t.unit_id) = u.unit_id
              LEFT JOIN blocks b ON u.block_id = b.block_id
              LEFT JOIN property p ON b.property_id = p.property_id
              ORDER BY m.request_id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Error fetching requests: " . $e->getMessage();
    $requests = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord - Maintenance Requests</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body class="dashboard-page">
        <main class="main-content">
            <section class="welcome-card">
                <div>
                    <p class="eyebrow">Admin Portal</p>
                    <h1 class="heading">Maintenance Requests</h1>
                </div>
            </section>
            
            <section class="action-card">
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
                <?php endif; ?>
                <div class="page-actions mb-3">
                    <a href="admindashboard.php" class="btn-primary">Back to Dashboard</a>
                </div>
                <?php if (empty($requests)): ?>
                    <p>No maintenance requests have been submitted yet.</p>
                <?php else: ?>

                    <div style="overflow-x:auto;">
                        <table class="tenant-table table table-striped">
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>Unit Name</th>
                                    <th>Unit Type</th>
                                    <th>Tenant</th>
                                    <th>Description</th>
                                    <th>Date Requested</th>
                                    <th>Current Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['property_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($request['unit_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($request['unit_type'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($request['tenant_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($request['description'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($request['date'] ?? ''); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $request['status'] ?? '')); ?>">
                                                <?php echo htmlspecialchars($request['status'] ?? 'Pending'); ?>
                                            </span>
                                        </td>
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