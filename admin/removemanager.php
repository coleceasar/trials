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
    $managerId = trim($_POST['manager_id'] ?? '');
    $propertyId = trim($_POST['property_id'] ?? '');

    if ($managerId === '' || $propertyId === '') {
        $message = 'Please select both a property and a manager.';
        $messageType = 'error';
    } else {
        try {
            // Start transaction to ensure both operations execute safely together
            $db->beginTransaction();

            // 1. Verify that the manager is currently assigned to this property
            $checkStmt = $db->prepare("SELECT mp.manager_id 
                                       FROM manager_properties mp
                                       WHERE mp.manager_id = :manager_id 
                                         AND mp.property_id = :property_id
                                       LIMIT 1");
            $checkStmt->bindValue(':manager_id', $managerId, PDO::PARAM_INT);
            $checkStmt->bindValue(':property_id', $propertyId, PDO::PARAM_INT);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                $db->rollBack();
                $message = 'This manager is not currently assigned to the selected property.';
                $messageType = 'error';
            } else {
                // 2. Delete the property assignment
                $deleteStmt = $db->prepare("DELETE FROM manager_properties 
                                           WHERE manager_id = :manager_id 
                                             AND property_id = :property_id");
                $deleteStmt->bindValue(':manager_id', $managerId, PDO::PARAM_INT);
                $deleteStmt->bindValue(':property_id', $propertyId, PDO::PARAM_INT);
                $deleteStmt->execute();

                // 3. Check if the manager has any remaining property assignments left
                $remainingStmt = $db->prepare("SELECT COUNT(*) FROM manager_properties WHERE manager_id = :manager_id");
                $remainingStmt->bindValue(':manager_id', $managerId, PDO::PARAM_INT);
                $remainingStmt->execute();
                $remainingCount = (int) $remainingStmt->fetchColumn();

                // 4. If 0 properties remain, update manager status to 'inactive'
                if ($remainingCount === 0) {
                    $statusStmt = $db->prepare("UPDATE manager SET status = 'inactive' WHERE manager_id = :manager_id");
                    $statusStmt->bindValue(':manager_id', $managerId, PDO::PARAM_INT);
                    $statusStmt->execute();

                    $message = 'Manager removed from the property.';
                } else {
                    $message = "Manager removed from property successfully. ({$remainingCount} active property assignment(s) remaining).";
                }

                $db->commit();
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $message = 'Unable to complete deactivation: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Fetch properties and their assigned managers
$assignedProperties = [];
$assignedManagers = [];

try {
    // Get all properties that currently have managers assigned
    $propStmt = $db->query("SELECT DISTINCT p.property_id, p.property_name 
                            FROM property p
                            INNER JOIN manager_properties mp ON p.property_id = mp.property_id
                            ORDER BY p.property_name ASC");
    $assignedProperties = $propStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get active manager-property assignments
    $assignStmt = $db->query("SELECT 
                                mp.property_id,
                                mp.manager_id,
                                m.name AS manager_name,
                                m.username,
                                p.property_name
                              FROM manager_properties mp
                              INNER JOIN manager m ON mp.manager_id = m.manager_id
                              INNER JOIN property p ON mp.property_id = p.property_id
                              WHERE m.status = 'active'
                              ORDER BY p.property_name ASC, m.name ASC");
    $assignedManagers = $assignStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = 'Error fetching assignment data: ' . $e->getMessage();
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deactivate Manager Property Access</title>
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
                    <h1>Deactivate Property Access</h1>
                </div>
            </section>

            <section class="action-card">
                <div class="mb-3">
                    <a href="managerscontrol.php" class="btn-primary">Back to Manager Management</a>
                </div>
                
                <form method="post" style="margin-top:20px;">
                    <div class="mb-3">
                        <label for="property_id" class="form-label font-weight-bold">1. Select Property</label>
                        <select id="property_id" name="property_id" required class="form-select">
                            <option value="">-- Select a Property --</option>
                            <?php foreach ($assignedProperties as $prop): ?>
                                <option value="<?php echo htmlspecialchars($prop['property_id']); ?>">
                                    <?php echo htmlspecialchars($prop['property_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="manager_id" class="form-label font-weight-bold">2. Select Manager to Deactivate</label>
                        <select id="manager_id" name="manager_id" required class="form-select">
                            <option value="">-- Select a Manager --</option>
                            <?php foreach ($assignedManagers as $m): ?>
                                <option value="<?php echo htmlspecialchars($m['manager_id']); ?>" data-property="<?php echo htmlspecialchars($m['property_id']); ?>">
                                    <?php echo htmlspecialchars($m['manager_name'] . ' (' . $m['property_name'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-primary" onclick="return confirm('Deactivating this property assignment may mark the manager as inactive if they have no other properties left. Continue?');">
                        Deactivate Control
                    </button>
                </form>

                <?php if ($message !== ''): ?>
                    <p style="margin-top:15px; font-weight:700; color:<?php echo $messageType === 'error' ? '#b91c1c' : '#0f766e'; ?>;">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script>
        document.getElementById('property_id').addEventListener('change', function () {
            const selectedPropertyId = this.value;
            const managerSelect = document.getElementById('manager_id');
            const options = managerSelect.querySelectorAll('option');

            options.forEach(option => {
                if (!option.value) return; 
                const propId = option.getAttribute('data-property');
                
                if (selectedPropertyId === '' || propId === selectedPropertyId) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });

            managerSelect.value = '';
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>