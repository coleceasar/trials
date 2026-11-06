<?php
$registered = isset($_GET['registered']) && $_GET['registered'] === '1';

include_once '../config.php';
$database = new Database();
$db = $database->getConnection();

$properties = [];
$blocks = [];
$availableUnits = [];

try {
    $propertiesStmt = $db->query("SELECT property_id, property_name 
                                 FROM property 
                                 ORDER BY property_name");
    $properties = $propertiesStmt->fetchAll(PDO::FETCH_ASSOC);

    $blocksStmt = $db->query("SELECT block_id, block_name, property_id 
                            FROM blocks 
                            ORDER BY block_name");
    $blocks = $blocksStmt->fetchAll(PDO::FETCH_ASSOC);

    $unitsStmt = $db->query("SELECT unit_id, unit_name,block_id 
                            FROM units 
                            WHERE status = 'available' 
                            ORDER BY unit_name");
    $availableUnits = $unitsStmt->fetchAll(PDO::FETCH_ASSOC);

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
    $unitLabel = trim((string)($unit['unit_name'] ?? '')) !== ''
        ? $unit['unit_name']
        : ('Unit ' . ($unit['unit_number'] ?? $unit['unit_id']));

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
                <p style="color:#0f766e; font-weight:700;">TENANT HAS BEEN SUCCESSFULLY REGISTERED</p>
                <p><a href="../managerpages/managersdashboard.php" class="btn-primary">Return to Tenant Dashboard</a></p>
            <?php else: ?>
                <form method="post" action="../managerpages/managersdashboard.php">
                    <h2>MAKAO Tenant Registration Portal</h2>
                    <p>Enter the tenant information:</p>

                    <select name="property_id" id="property_id" required onchange="updateBlocks()" style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">-- Select Property --</option>
                        <?php foreach ($properties as $property): ?>
                            <option value="<?php echo (int)$property['property_id']; ?>">
                                <?php echo htmlspecialchars($property['property_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="block_id" id="block_id" required onchange="updateUnits()" disabled style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">-- Select Block --</option>
                    </select>

                    <select name="unit_id" id="unit_id" required disabled style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">-- Select Available Unit --</option>
                    </select>
                    <input type="text" placeholder="First Name" name="first_name" required>
                    <input type="text" placeholder="Last Name" name="last_name" required>
                    <input type="text" placeholder="ID Number" name="id_number" required>
                    <input type="text" placeholder="Phone Number" name="phone_number" required>
                    <input type="text" placeholder="Username" name="username" required>
                    <input type="password" placeholder="Password" name="password" required>

                    <button type="submit">Register</button>
                </form>
            <?php endif; ?>
        </div>

        <script>
        const blocksByProperty = <?php echo json_encode($blocksByProperty); ?>;
        const unitsByBlock = <?php echo json_encode($unitsByBlock); ?>;

        function updateBlocks() {
            const propertyId = document.getElementById('property_id').value;
            const blockSelect = document.getElementById('block_id');
            const unitSelect = document.getElementById('unit_id');

            blockSelect.innerHTML = '<option value="">-- Select Block --</option>';
            unitSelect.innerHTML = '<option value="">-- Select Available Unit --</option>';
            blockSelect.disabled = true;
            unitSelect.disabled = true;

            if (!propertyId) {
                return;
            }

            const blocks = blocksByProperty[propertyId] || [];
            if (blocks.length) {
                blocks.forEach(function(block) {
                    const option = document.createElement('option');
                    option.value = block.id;
                    option.textContent = block.name;
                    blockSelect.appendChild(option);
                });
                blockSelect.disabled = false;
            }
        }

        function updateUnits() {
            const blockId = document.getElementById('block_id').value;
            const unitSelect = document.getElementById('unit_id');

            unitSelect.innerHTML = '<option value="">-- Select Available Unit --</option>';
            unitSelect.disabled = true;

            if (!blockId) {
                return;
            }

            const units = unitsByBlock[blockId] || [];
            if (units.length) {
                units.forEach(function(unit) {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.label;
                    unitSelect.appendChild(option);
                });
                unitSelect.disabled = false;
            }
        }
        </script>
    </body>
</html>