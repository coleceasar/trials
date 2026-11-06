<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$adminId = $_SESSION['admin_id'];
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenantId = trim($_POST['tenant_id'] ?? '');

    if ($tenantId === '') {
        $message = 'Please select a tenant.';
        $messageType = 'error';
    } else {
        try {
            // 1. Verify tenant exists and fetch their assigned unit_id
            $checkStmt = $db->prepare("SELECT tenant_id, unit_id 
                                       FROM tenants 
                                       WHERE tenant_id = :tenant_id 
                                         AND status = 'active' 
                                       LIMIT 1");
            $checkStmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
            $checkStmt->execute();
            $tenant = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$tenant) {
                $message = 'No active tenant found with that ID.';
                $messageType = 'error';
            } else {
                // Begin transaction to ensure both updates complete successfully
                $db->beginTransaction();

                // 2. Set tenant status to 'inactive'
                $updateTenantStmt = $db->prepare("UPDATE tenants SET status = 'inactive' WHERE tenant_id = :tenant_id");
                $updateTenantStmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
                $updateTenantStmt->execute();

                // 3. Set unit status to 'vacant' (or 'available')
                if (!empty($tenant['unit_id'])) {
                    $updateUnitStmt = $db->prepare("UPDATE units SET status = 'available' WHERE unit_id = :unit_id");
                    $updateUnitStmt->bindValue(':unit_id', $tenant['unit_id'], PDO::PARAM_INT);
                    $updateUnitStmt->execute();
                }

                // Commit the transaction
                $db->commit();

                $message = 'Tenant deactivated and unit marked as available successfully.';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            // Roll back changes if an exception occurs
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $message = 'Unable to deactivate tenant: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$activeTenants = [];
try {
    // Fetch active tenants across all properties
    $listStmt = $db->query("SELECT 
                                t.tenant_id, 
                                t.name AS tenant_name, 
                                t.username,
                                u.unit_name,
                                p.property_name
                              FROM tenants t
                              INNER JOIN units u ON t.unit_id = u.unit_id
                              INNER JOIN blocks b ON u.block_id = b.block_id
                              INNER JOIN property p ON b.property_id = p.property_id
                              WHERE t.status = 'active'
                              ORDER BY p.property_name ASC, u.unit_name ASC, t.name ASC");
    $activeTenants = $listStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Error fetching tenant list: ' . $e->getMessage();
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deactivate Tenant</title>
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
                    <h1>Deactivate Tenant</h1>
                </div>
            </section>

            <section class="action-card">
                <div class="mb-3">
                    <a href="managetenant.php" class="btn-primary">Back to Tenant Management</a>
                </div>

                <form method="post" style="margin-top:20px;">
                    <label for="tenant_id" class="form-label font-weight-bold">Select Tenant to Deactivate</label>
                    <select id="tenant_id" name="tenant_id" required class="form-select mb-3">
                        <option value="">Select a tenant from the property</option>
                        <?php foreach ($activeTenants as $t): ?>
                            <option value="<?php echo htmlspecialchars($t['tenant_id']); ?>">
                                <?php
                                echo htmlspecialchars(
                                    $t['tenant_name'] .
                                    " (" . ($t['unit_name'] ?? 'N/A') . " - " . $t['property_name'] . ")"
                                );
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="btn-primary"
                        onclick="return confirm('Are you sure you want to set this tenant to inactive and free up the unit?');">Deactivate
                        Tenant</button>
                </form>

                <?php if ($message !== ''): ?>
                    <p
                        style="margin-top:15px; font-weight:700; color:<?php echo $messageType === 'error' ? '#b91c1c' : '#0f766e'; ?>;">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>