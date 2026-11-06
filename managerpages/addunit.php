<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_id'])) {
    header('Location: managerlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$manager_id = $_SESSION['manager_id'];
$message = '';

// Handle Form Submission BEFORE reading list data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {    
    // Explicitly set block_id to null if empty or not selected
    $block_id = !empty($_POST['block_id']) ? $_POST['block_id'] : null;
    $unit_name = trim($_POST['unit_name'] ?? '');
    $unit_type = $_POST['unit_type'] ?? '';
    $selected_size = $_POST['unit_size'] ?? '';
    $charge_per_sqft = floatval($_POST['charge_per_sqft'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    $unitDeposit = floatval($_POST['unit_deposit'] ?? 0);

    $office_size = NULL;
    $commercial_size = NULL;
    $number_of_rooms = NULL;
    $sqft = 0;
    $monthly_rent = 0;

    if ($unit_type === 'offices') {
        $office_size = $selected_size;
        $sqft = floatval($selected_size);
        $monthly_rent = $sqft * $charge_per_sqft;
    } elseif ($unit_type === 'commercial') {
        $commercial_size = $selected_size;
        $sqft = floatval($selected_size);
        $monthly_rent = $sqft * $charge_per_sqft;
    } elseif ($unit_type === 'residential') {
        $number_of_rooms = !empty($_POST['number_of_rooms']) ? intval($_POST['number_of_rooms']) : NULL;
        $monthly_rent = floatval($_POST['residential_rent'] ?? 0);
        $charge_per_sqft = 0; // Not applicable for residential
    }

    if (!empty($unit_name) && !empty($unit_type)) {
        try {
            $stmt = $db->prepare("INSERT INTO units (block_id, unit_name, unit_type, office_size, number_of_rooms, commercial_size, charge_per_sqft, monthly_rent, unit_deposit, status) 
                VALUES (:block_id, :unit_name, :unit_type, :office_size, :number_of_rooms, :commercial_size, :charge_per_sqft, :monthly_rent, :unit_deposit, :status)
            ");
            
            // PDO automatically binds PHP null as SQL NULL
            $stmt->execute([
                ':block_id'        => $block_id, 
                ':unit_name'       => $unit_name,
                ':unit_type'       => $unit_type,
                ':office_size'     => $office_size,
                ':number_of_rooms' => $number_of_rooms,
                ':commercial_size' => $commercial_size,
                ':charge_per_sqft' => $charge_per_sqft,
                ':monthly_rent'    => $monthly_rent,
                ':unit_deposit'    => $unitDeposit,
                ':status'          => $status
            ]);

            $_SESSION['success_msg'] = "Unit '{$unit_name}' added successfully!";
            header("Location: addunit.php");
            exit();

        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Please fill in all required fields (Property, Unit Name, Unit Type).</div>";
    }
}

// Display redirect session message if present
if (isset($_SESSION['success_msg'])) {
    $message = "<div class='alert alert-success'>" . $_SESSION['success_msg'] . "</div>";
    unset($_SESSION['success_msg']);
}

// 1. Fetch properties assigned to this logged-in manager
$assigned_properties = [];
try {
    $prop_stmt = $db->prepare("SELECT p.property_id, p.property_name 
        FROM property p 
        INNER JOIN manager_properties mp ON p.property_id = mp.property_id 
        WHERE mp.manager_id = :manager_id
    ");
    $prop_stmt->execute([':manager_id' => $manager_id]);
    $assigned_properties = $prop_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "<div class='alert alert-danger'>Error fetching properties: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// 2. Fetch all blocks linked to the manager's assigned properties
$property_blocks = [];
if (!empty($assigned_properties)) {
    $property_ids = array_column($assigned_properties, 'property_id');
    $in_clause = implode(',', array_fill(0, count($property_ids), '?'));

    try {
        $block_stmt = $db->prepare("SELECT block_id, property_id, block_name 
            FROM blocks 
            WHERE property_id IN ($in_clause) 
            ORDER BY block_name ASC
        ");
        $block_stmt->execute($property_ids);
        $blocks = $block_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($blocks as $block) {
            $property_blocks[$block['property_id']][] = $block;
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Error fetching blocks: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Unit</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hidden { display: none; }
    </style>
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/mheader.php'; ?>
        
        <main class="main-content">
            <section class="welcome-card">
                <div>
                    <p class="eyebrow">New Units</p>
                    <h1 class="heading">Add New Unit</h1>
                </div>
            </section>

            <form action=" " method="POST">
                <section class="action-card">
                    <div class="page-actions mb-3">
                        <a href="manageunits.php" class="btn-primary">Back to Unit Management</a>
                    </div>

                    <?php if (!empty($message)) echo $message; ?>

                    <!-- Property Dropdown -->
                    <div class="form-group mb-3">
                        <label for="property_id">Property *:</label>
                        <select id="property_id" name="property_id" class="form-select" required>
                            <option value="">-- Select Property --</option>
                            <?php foreach ($assigned_properties as $prop): ?>
                                <option value="<?php echo $prop['property_id']; ?>">
                                    <?php echo htmlspecialchars($prop['property_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filtered Block Dropdown (Optional) -->
                    <div class="form-group mb-3">
                        <label for="block_id">Block Name (Optional):</label>
                        <select id="block_id" name="block_id" class="form-select" disabled>
                            <option value="">-- None / Select Property First --</option>
                        </select>
                    </div>

                    <!-- Unit Name -->
                    <div class="form-group mb-3">
                        <label for="unit_name">Unit Name *:</label>
                        <input type="text" id="unit_name" name="unit_name" class="form-control" placeholder="e.g. A-101" required>
                    </div>

                    <!-- Unit Type -->
                    <div class="form-group mb-3">
                        <label for="unit_type">Unit Type *:</label>
                        <select id="unit_type" name="unit_type" class="form-select" required>
                            <option value="">-- Select Unit Type --</option>
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="offices">Offices</option>
                        </select>
                    </div>
                    
                    <!-- Unit Deposit -->
                    <div class="form-group mb-3 hidden" id="unit_deposit_wrapper">
                        <label for="unit_deposit">Unit Deposit (Ksh) *:</label>
                        <input type="number" step="0.01" min="0" id="unit_deposit" name="unit_deposit" class="form-control" placeholder="Enter fixed deposit per unit">
                    </div>

                    <!-- Dynamic Size Dropdown (Commercial/Offices) -->
                    <div class="form-group mb-3 hidden" id="size_wrapper">
                        <label for="unit_size">Size (Sq Ft):</label>
                        <select id="unit_size" name="unit_size" class="form-select">
                            <option value="">-- Select Size --</option>
                        </select>
                    </div>

                    <!-- Residential Rooms -->
                    <div class="form-group mb-3 hidden" id="rooms_wrapper">
                        <label for="number_of_rooms">Number of Rooms:</label>
                        <input type="number" id="number_of_rooms" name="number_of_rooms" class="form-control" placeholder="e.g. 3">
                    </div>

                    <!-- Direct Residential Monthly Rent -->
                    <div class="form-group mb-3 hidden" id="residential_rent_wrapper">
                        <label for="residential_rent">Monthly Rent (Ksh) *:</label>
                        <input type="number" step="0.01" id="residential_rent" name="residential_rent" class="form-control" placeholder="Enter fixed monthly rent">
                    </div>

                    <!-- Charge Per SqFt (Commercial/Offices) -->
                    <div class="form-group mb-3 hidden" id="sqft_charge_wrapper">
                        <label for="charge_per_sqft">Charge Per Sq Ft (Ksh):</label>
                        <input type="number" step="0.01" id="charge_per_sqft" name="charge_per_sqft" class="form-control">
                    </div>

                    <!-- Monthly Rent Preview (Commercial/Offices) -->
                    <div class="form-group mb-3 hidden" id="auto_rent_wrapper">
                        <label for="monthly_rent">Auto Calculated Monthly Rent (Ksh):</label>
                        <input type="number" step="0.01" id="monthly_rent" class="form-control" readonly placeholder="Auto-calculated from size & rate">
                    </div>

                    <!-- Status -->
                    <div class="form-group mb-3">
                        <label for="status">Status:</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success mt-2">Save Unit</button>
                </section>
            </form>
        </main>
    </div>
    <script>
        window.blocksData = <?php echo json_encode($property_blocks); ?>;
    </script>
    <script src="addunit.js"></script>
</body>
</html>