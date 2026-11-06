<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['tenant_username'])) {
    header('Location: tenantlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$username = $_SESSION['tenant_username'];
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $requestId = (int) $_POST['request_id'];
        $newStatus = trim($_POST['status']);

        $allowedStatuses = ['Received', 'Under Review', 'Completed'];
        if (!in_array($newStatus, $allowedStatuses)) {
            throw new Exception('Invalid status selected.');
        }

        $updateQuery = "UPDATE maintenance SET status = :status WHERE request_id = :request_id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':status', $newStatus);
        $updateStmt->bindParam(':request_id', $requestId, PDO::PARAM_INT);

        if ($updateStmt->execute()) {
            $successMessage = "Request status successfully updated to '{$newStatus}'.";
        } else {
            throw new Exception('Failed to update status.');
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

try {
    // 1. Fetch tenant_id and assigned unit_id for the logged-in tenant
    $tenantStmt = $db->prepare("SELECT tp.tenant_id, t.unit_id 
                                FROM tenant_profile tp
                                INNER JOIN tenants t ON tp.tenant_id = t.tenant_id
                                WHERE tp.username = :username 
                                LIMIT 1");
    $tenantStmt->execute([':username' => $username]);
    $tenantData = $tenantStmt->fetch(PDO::FETCH_ASSOC);

    $unitId = $tenantData['unit_id'] ?? null;

    if (!$unitId) {
        throw new Exception('Unable to locate an assigned unit for your account.');
    }

    // 2. Query maintenance requests strictly filtered by unit_id
    $query = "SELECT
        m.request_id,
        t.name AS tenant_name,
        u.unit_name,
        m.description,
        m.priority,
        m.date,
        m.status
    FROM maintenance m
    INNER JOIN tenants t ON m.tenant_id = t.tenant_id
    INNER JOIN units u ON m.unit_id = u.unit_id
    WHERE m.unit_id = :unit_id
    ORDER BY m.date DESC";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':unit_id', $unitId, PDO::PARAM_INT);
    $stmt->execute();

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMessage = "Error fetching requests: " . $e->getMessage();
    $requests = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant - Maintenance Requests</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/theader.php'; ?>
        <main class="main-content">
            <section class="welcome-card">
                <div>
                    <p class="eyebrow">Tenants Portal</p>
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

                <?php if (empty($requests)): ?>
                    <p>No maintenance requests have been submitted for your unit yet.</p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="tenant-table table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Tenant Name</th>
                                    <th>Unit</th>
                                    <th>Description</th>
                                    <th>Priority</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($request['tenant_name']) ?></td>
                                        <td><?= htmlspecialchars($request['unit_name']) ?></td>
                                        <td><?= htmlspecialchars($request['description']) ?></td>
                                        <td><?= htmlspecialchars($request['priority']) ?></td>
                                        <td><?= htmlspecialchars($request['date']) ?></td>
                                        <td>
                                            <span class="status-badge <?= strtolower(str_replace(' ', '-', $request['status'])) ?>">
                                                <?= htmlspecialchars($request['status']) ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../components/list-filter.js" defer></script>
</body>
</html>