<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_id'])) {
    header('Location: ../managerpages/managerlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$unitId = trim($_GET['unit_id'] ?? '');
$success = isset($_GET['updated']) && $_GET['updated'] === '1';
$errorMessage = '';
$unit = null;

$managerId = (int)($_SESSION['manager_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentUnitId = trim($_POST['current_unit_id'] ?? '');
    $newUnitId = trim($_POST['unit_id'] ?? '');
    $unitType = trim($_POST['unit_type'] ?? '');
    $unitName = trim($_POST['unit_name'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $unitDeposit = trim($_POST['unit_deposit'] ?? '');

    if (!$currentUnitId || !$newUnitId || !$unitName) {
        $errorMessage = 'Please provide the current unit ID, updated unit ID, and unit name.';
    } else {
        try {
            if ($managerId > 0) {
                $checkStmt = $db->prepare(" SELECT u.unit_id
                    FROM units u
                    INNER JOIN blocks b ON u.block_id = b.block_id
                    INNER JOIN manager_properties mp ON b.property_id = mp.property_id
                    WHERE u.unit_id = :current_unit_id
                      AND mp.manager_id = :manager_id
                    LIMIT 1
                ");
                $checkStmt->bindValue(':current_unit_id', $currentUnitId, PDO::PARAM_STR);
                $checkStmt->bindValue(':manager_id', $managerId, PDO::PARAM_INT);
                $checkStmt->execute();
                $existingUnit = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$existingUnit) {
                    throw new Exception('You do not have permission to update this unit.');
                }
            }

            $updateFields = [
                'unit_id'      => $newUnitId,
                'unit_type'    => $unitType,
                'unit_name'    => $unitName,
                'unit_deposit' => $unitDeposit !== '' ? $unitDeposit : null,
                'status'       => $status !== '' ? $status : null,
            ];

            $setClauses = [];
            foreach ($updateFields as $column => $value) {
                $setClauses[] = "$column = :$column";
            }

            $sql = 'UPDATE units SET ' . implode(', ', $setClauses) . ' WHERE unit_id = :current_unit_id';
            $stmt = $db->prepare($sql);

            foreach ($updateFields as $column => $value) {
                if (is_null($value)) {
                    $stmt->bindValue(':' . $column, null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':' . $column, $value, PDO::PARAM_STR);
                }
            }

            $stmt->bindValue(':current_unit_id', $currentUnitId, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Unit updated successfully.";
                header('Location: updateunit.php?unit_id=' . urlencode($newUnitId) . '&updated=1');
                exit;
            } else {
                $err = $stmt->errorInfo();
                $errorMessage = 'Unable to save unit updates. SQL error: ' . ($err[2] ?? 'unknown');
            }
        } catch (Exception $e) {
            $errorMessage = 'Error: ' . $e->getMessage();
        }
    }
}

try {
    if ($managerId > 0) {
        $unitsStmt = $db->prepare(" SELECT u.unit_id, u.unit_name
            FROM units u
            INNER JOIN blocks b ON u.block_id = b.block_id
            INNER JOIN manager_properties mp ON b.property_id = mp.property_id
            WHERE mp.manager_id = :manager_id
            GROUP BY u.unit_id, u.unit_name
            ORDER BY u.unit_name, u.unit_id
        ");
        $unitsStmt->bindValue(':manager_id', $managerId, PDO::PARAM_INT);
        $unitsStmt->execute();
    } else {
        $unitsStmt = $db->query('SELECT unit_id, unit_name FROM units ORDER BY unit_name, unit_id');
    }
    $unitOptions = $unitsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $unitOptions = [];
    $errorMessage = 'Unable to load units: ' . $e->getMessage();
}

if ($unitId !== '') {
    try {
        $stmt = $db->prepare(" SELECT u.*
            FROM units u
            INNER JOIN blocks b ON u.block_id = b.block_id
            INNER JOIN manager_properties mp ON b.property_id = mp.property_id
            WHERE u.unit_id = :unit_id
              AND mp.manager_id = :manager_id
            LIMIT 1
        ");
        $stmt->bindValue(':unit_id', $unitId, PDO::PARAM_STR);
        $stmt->bindValue(':manager_id', $managerId, PDO::PARAM_INT);
        $stmt->execute();
        $unit = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$unit) {
            $errorMessage = 'Selected unit not found or you do not have permission to edit it.';
        }
    } catch (PDOException $e) {
        $errorMessage = 'Unable to load unit: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Unit</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/mheader.php'; ?>
        <main class="main-content">
            <section class="welcome-card">
                <div>
                    <p class="eyebrow">Manage Units</p>
                    <h1>Update Unit Details</h1>
                    <p>Select a unit and update its values below.</p>
                </div>
            </section>

            <section class="action-card">
                <div class="page-actions">
                    <a href="unitsdata.php" class="btn-primary">View Units</a>
                </div>

                <?php if ($success): ?>
                    <p style="margin-bottom: 15px; color: #0f766e; font-weight: 600;">Unit updated successfully.</p>
                <?php endif; ?>
                <?php if ($errorMessage !== ''): ?>
                    <p style="margin-bottom: 15px; color: #dc2626; font-weight: 700;"><?php echo htmlspecialchars($errorMessage); ?></p>
                <?php endif; ?>

                <form method="get" class="widget-form" style="margin-bottom: 24px;">
                    <label>
                        Choose a unit to edit
                        <select name="unit_id" onchange="this.form.submit()">
                            <option value="">Select unit</option>
                            <?php foreach ($unitOptions as $option): ?>
                                <option value="<?php echo htmlspecialchars($option['unit_id']); ?>" <?php echo ($option['unit_id'] === $unitId) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($option['unit_name'] . ' (' . $option['unit_id'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </form>

                <?php if ($unit): ?>
                    <form method="post" action="" class="widget-form">
                        <input type="hidden" name="current_unit_id" value="<?php echo htmlspecialchars($unit['unit_id']); ?>">

                        <label>
                            Unit ID
                            <input type="text" name="unit_id" value="<?php echo htmlspecialchars($unit['unit_id'] ?? ''); ?>" required>
                        </label>

                        <label>
                            Unit Name
                            <input type="text" name="unit_name" value="<?php echo htmlspecialchars($unit['unit_name'] ?? ''); ?>" required>
                        </label>

                        <label>
                            Unit Type
                            <input type="text" name="unit_type" value="<?php echo htmlspecialchars($unit['unit_type'] ?? ''); ?>">
                        </label>

                        <label>
                            Unit Deposit (Ksh)
                            <input type="number" step="0.01" min="0" name="unit_deposit" value="<?php echo htmlspecialchars($unit['unit_deposit'] ?? '0.00'); ?>">
                        </label>

                        <label>
                            Status
                            <select name="status">
                                <option value="" <?php echo empty($unit['status']) ? 'selected' : ''; ?>>Select Status</option>
                                <option value="available" <?php echo ($unit['status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                                <option value="occupied" <?php echo ($unit['status'] === 'occupied') ? 'selected' : ''; ?>>Occupied</option>
                                <option value="maintenance" <?php echo ($unit['status'] === 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            </select>
                        </label>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Save Changes</button>
                            <a href="manageunits.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>