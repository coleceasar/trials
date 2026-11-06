<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['landlord_username'])) {
    header('Location: ownerlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$propertyId = $_SESSION['property_id'] ?? null;
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $requestId = (int) $_POST['request_id'];
        $newStatus = trim($_POST['status'] ?? '');

        $allowedStatuses = ['Under Review', 'Approved', 'Not Approved'];
        if (!in_array($newStatus, $allowedStatuses, true)) {
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
    if ($propertyId === null || $propertyId === '') {
        throw new Exception('Unable to determine the assigned property for this landlord.');
    }
    $query = "SELECT
                m.request_id,
                m.description,
                m.date,
                m.status,
                m.priority,
                t.tenant_id,
                t.name AS name,
                u.unit_type as unit_type,
                u.unit_name AS unit_name
              FROM maintenance m
              LEFT JOIN tenants t ON m.tenant_id = t.tenant_id
              LEFT JOIN units u ON COALESCE(m.unit_id, t.unit_id) = u.unit_id
              LEFT JOIN blocks b ON u.block_id = b.block_id
              INNER JOIN property p ON b.property_id = p.property_id
              WHERE p.property_id = :property_id
              ORDER BY m.request_id DESC";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':property_id', $propertyId, PDO::PARAM_INT);
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
    <title>Landlord - Maintenance Requests</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body class="dashboard-page">
        <main class="main-content">
            <section class="welcome-card">
                <div>
                    <p class="eyebrow">Owner's Portal</p>
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
                    <p>No maintenance requests have been submitted yet.</p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="tenant-table">
                            <thead>
                                <tr>
                                    <th>Unit Name</th>
                                    <th>Unit Type</th>
                                    <th>Description</th>
                                    <th>Date Requested</th>
                                    <th>Current Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['unit_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($request['unit_type'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($request['description'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($request['date'] ?? ''); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $request['status'] ?? '')); ?>">
                                                <?php echo htmlspecialchars($request['status'] ?? 'Pending'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                                                <input type="hidden" name="request_id" value="<?php echo (int)$request['request_id']; ?>">
                                                <select name="status" class="status-select">
                                                    <option value="Under Review" <?php echo (($request['status'] ?? '') === 'Under Review') ? 'selected' : ''; ?>>Under Review</option>
                                                    <option value="Approved" <?php echo (($request['status'] ?? '') === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Not Approved" <?php echo (($request['status'] ?? '') === 'Not Approved') ? 'selected' : ''; ?>>Not Approved</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn-update">Update</button>
                                            </form>
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