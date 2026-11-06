<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_id'])) {
    header('Location: managerlogin.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$managerId = $_SESSION['manager_id'];
$database = new Database();
$db = $database->getConnection();

$assignedProperties = [];
$availableBranches   = [];
$availableBlocks     = [];
$availableUnits      = [];
$errorMessage        = '';

try {
    $sqlProperty = "SELECT p.property_id, p.property_name 
                    FROM manager_properties mp
                    INNER JOIN property p ON mp.property_id = p.property_id
                    WHERE mp.manager_id = :manager_id";
    $stmtProp = $db->prepare($sqlProperty);
    $stmtProp->bindValue(':manager_id', $managerId, PDO::PARAM_INT);
    $stmtProp->execute();
    $assignedProperties = $stmtProp->fetchAll(PDO::FETCH_ASSOC);

    $propertyIds = array_column($assignedProperties, 'property_id');

    if (!empty($propertyIds)) {
        $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));

        $sqlBranches = "SELECT branch_id, branch_name, property_id 
                        FROM branch
                        WHERE property_id IN ($placeholders) 
                        ORDER BY branch_name ASC";
        $stmtBranches = $db->prepare($sqlBranches);
        $stmtBranches->execute($propertyIds);
        $availableBranches = $stmtBranches->fetchAll(PDO::FETCH_ASSOC);

        $sqlBlocks = "SELECT block_id, block_name, property_id 
                      FROM blocks 
                      WHERE property_id IN ($placeholders) 
                      ORDER BY block_name ASC";
        $stmtBlocks = $db->prepare($sqlBlocks);
        $stmtBlocks->execute($propertyIds);
        $availableBlocks = $stmtBlocks->fetchAll(PDO::FETCH_ASSOC);

        $sqlUnits = "SELECT u.unit_id, 
                            u.unit_name, 
                            u.unit_type,
                            u.block_id,
                            b.block_name, 
                            b.property_id
                     FROM units u
                     INNER JOIN blocks b ON u.block_id = b.block_id
                     WHERE b.property_id IN ($placeholders) 
                       AND u.status = 'available'
                     ORDER BY b.block_name ASC, u.unit_name ASC";

        $stmtUnits = $db->prepare($sqlUnits);
        $stmtUnits->execute($propertyIds);
        $availableUnits = $stmtUnits->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $errorMessage = 'Database error: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token execution.');
    }

    $propertyId = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
    $branchId   = filter_input(INPUT_POST, 'branch_id', FILTER_VALIDATE_INT);
    $blockId    = filter_input(INPUT_POST, 'block_id', FILTER_VALIDATE_INT);
    $unitId     = filter_input(INPUT_POST, 'unit_id', FILTER_VALIDATE_INT);

    if (!$propertyId || !$branchId || !$blockId || !$unitId) {
        $errorMessage = 'Please complete all required location choices.';
    } else {
        $selectedPropertyName = '';
        foreach ($assignedProperties as $p) {
            if ($p['property_id'] == $propertyId) {
                $selectedPropertyName = $p['property_name'];
                break;
            }
        }

        $_SESSION['registration'] = [
            'property_id'   => $propertyId,
            'property_name' => $selectedPropertyName,
            'branch_id'     => $branchId,
            'block_id'      => $blockId,
            'unit_id'       => $unitId
        ];

        header('Location: viewservices.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Step 1: Select Property, Branch, Block & Unit</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container" style="max-width:600px; margin:40px auto;">
        <h2>Step 1: Select Property, Branch, Block & Unit</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" style="color:red; margin-bottom:15px;"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group" style="margin-bottom:15px;">
                <label for="property_id">Property</label>
                <select name="property_id" id="property_id" class="form-control" required>
                    <option value="">-- Choose Property --</option>
                    <?php foreach ($assignedProperties as $prop): ?>
                        <option value="<?php echo (int)$prop['property_id']; ?>">
                            <?php echo htmlspecialchars($prop['property_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label for="branch_id">Branch</label>
                <select name="branch_id" id="branch_id" class="form-control" required disabled>
                    <option value="">Choose The Branch</option>
                    <?php foreach ($availableBranches as $branch): ?>
                        <option value="<?php echo (int)$branch['branch_id']; ?>" data-property-id="<?php echo (int)$branch['property_id']; ?>">
                            <?php echo htmlspecialchars($branch['branch_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label for="block_id">Block</label>
                <select name="block_id" id="block_id" class="form-control" required disabled>
                    <option value="">Select The Block</option>
                    <?php foreach ($availableBlocks as $block): ?>
                        <option value="<?php echo (int)$block['block_id']; ?>" data-property-id="<?php echo (int)$block['property_id']; ?>">
                            <?php echo htmlspecialchars($block['block_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label for="unit_id">Available Unit</label>
                <select name="unit_id" id="unit_id" class="form-control" required disabled>
                    <option value="">Select Vacant Unit</option>
                    <?php foreach ($availableUnits as $unit): ?>
                        <option value="<?php echo (int)$unit['unit_id']; ?>" data-block-id="<?php echo (int)$unit['block_id']; ?>" data-unit-type="<?php echo htmlspecialchars($unit['unit_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($unit['unit_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <a href="managersdashboard.php" class="btn-primary" style="background-color:#6c757d; text-decoration:none; display:inline-block; text-align:center;">&larr; Back to Dashboard</a>
                <button type="submit" class="btn-primary">Next: Select Services &rarr;</button>
            </div>
        </form>
    </div>
    <script src="property.js"></script>
</body>
</html>