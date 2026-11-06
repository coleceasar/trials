<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_username']) && empty($_SESSION['admin_username'])) {
    header('Location: managerlogin.php');
    exit;
}

$registered = isset($_GET['registered']) && $_GET['registered'] === '1';

$database = new Database();
$db = $database->getConnection();

$properties = [];
$blocks = [];
$availableUnits = [];
$managersByProperty = [];

try {
    $propertiesStmt = $db->prepare("SELECT property_id, property_name FROM property ORDER BY property_name ASC");
    $propertiesStmt->execute();
    $properties = $propertiesStmt->fetchAll(PDO::FETCH_ASSOC);

    $blocksStmt = $db->prepare("SELECT block_id, block_name, property_id FROM blocks ORDER BY block_name ASC");
    $blocksStmt->execute();
    $blocks = $blocksStmt->fetchAll(PDO::FETCH_ASSOC);

    $unitsStmt = $db->prepare("SELECT u.unit_id, u.unit_name, u.block_id FROM units u INNER JOIN blocks b ON u.block_id = b.block_id WHERE u.status = 'available' ORDER BY u.unit_name ASC");
    $unitsStmt->execute();
    $availableUnits = $unitsStmt->fetchAll(PDO::FETCH_ASSOC);

    $managerStmt = $db->prepare("SELECT mp.property_id, m.name AS manager_name FROM manager_properties mp INNER JOIN manager m ON mp.manager_id = m.manager_id WHERE m.status = 'active'");
    $managerStmt->execute();
    while ($row = $managerStmt->fetch(PDO::FETCH_ASSOC)) {
        $propertyId = (string)($row['property_id'] ?? '');
        if ($propertyId !== '') {
            $managersByProperty[$propertyId] = $row['manager_name'];
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching registration options: " . $e->getMessage());
}

$blocksByProperty = [];
foreach ($blocks as $block) {
    $propertyId = (string)($block['property_id'] ?? '');
    $blocksByProperty[$propertyId][] = [
        'id' => (int)($block['block_id'] ?? 0),
        'name' => $block['block_name'] ?? ''
    ];
}

$unitsByBlock = [];
foreach ($availableUnits as $unit) {
    $blockId = (string)($unit['block_id'] ?? '');
    $unitLabel = trim((string)($unit['unit_name'] ?? '')) !== '' ? $unit['unit_name'] : ('Unit ' . ($unit['unit_number'] ?? $unit['unit_id']));
    $unitsByBlock[$blockId][] = [
        'id' => (int)$unit['unit_id'],
        'label' => $unitLabel
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Property Management System</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="auth-page">
    <div class="container">
        <?php if ($registered): ?>
            <h2>Tenant Registered</h2>
            <p class="success-message">TENANT HAS BEEN SUCCESSFULLY REGISTERED</p>
            <p><a href="admindashboard.php" class="btn-primary">Return to Tenant Dashboard</a></p>
        <?php else: ?>
            <form method="post" action="tregister.php">
                <h2>MAKAO Tenant Registration Portal</h2>
                <p>Enter the tenant information:</p>
                    <select name="property_id" id="property_id" required onchange="updateBlocks()">
                        <option value="">-- Select Property --</option>
                        <?php foreach ($properties as $property): ?>
                            <option value="<?php echo htmlspecialchars($property['property_id']); ?>"><?php echo htmlspecialchars($property['property_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="assigned_manager" name="assigned_manager" readonly placeholder="Managername will appear here" />

                    <select name="block_id" id="block_id" required onchange="updateUnits()" disabled>
                        <option value="">-- Select Block --</option>
                    </select>

                    <select name="unit_id" id="unit_id" required disabled>
                        <option value="">-- Select Available Unit --</option>
                    </select>

<div id="unit_details_section" style="display: none; border: 1px solid #ccc; padding: 10px; margin: 10px 0;">
    <p><strong>Unit Type:</strong> <span id="unit_type_display"></span></p>
    <p><strong>Rate:</strong> <span id="charge_per_sqft_display"></span></p>
    <p><strong>Monthly Rent:</strong> <span id="monthly_rent_display"></span></p>
</div>

<input type="text" placeholder="Name*" name="name" required>
<input type="text" placeholder="ID Number(optional)" name="id_number">
<input type="text" placeholder="Phone Number(*)" name="phone_number" required>
<input type="text" placeholder="Username*" name="username" required>
<input type="password" placeholder="Password*" name="password" required>

                <button type="submit">Register</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Inject data for external JS file -->
    <script>
        const blocksByProperty = <?php echo json_encode($blocksByProperty); ?>;
        const unitsByBlock = <?php echo json_encode($unitsByBlock); ?>;
        const managersByProperty = <?php echo json_encode($managersByProperty); ?>;
    </script>
    <script src="registration.js"></script>
</body>
</html>