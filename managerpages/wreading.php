<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_username'])) {
    header('Location: managerlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$username = trim($_SESSION['manager_username']);
$manager = null;
$tenants = [];
$message = '';
$messageType = '';

// 1. Get logged-in manager details
try {
    $query = "SELECT * FROM manager WHERE username = :username LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $manager = null;
}

if (!$manager) {
    echo 'Manager record not found. Please log in again.';
    exit;
}

$managerId = $manager['manager_id'];

// 2. Fetch ONLY tenants assigned under this manager_id via manager_properties
try {
$tenantQuery = "SELECT
    t.tenant_id,
    t.unit_id,
    t.name AS tenant_name,
    u.unit_name,
    w.meter_number
FROM tenants t
INNER JOIN units u
ON t.unit_id = u.unit_id
INNER JOIN blocks b
ON u.block_id = b.block_id
INNER JOIN manager_properties mp
ON b.property_id = mp.property_id
LEFT JOIN water_reader w
ON w.tenant_id = t.tenant_id
WHERE mp.manager_id = :manager_id
ORDER BY u.unit_name
";

$tenantStmt = $db->prepare($tenantQuery);
$tenantStmt->bindValue(':manager_id', $managerId, PDO::PARAM_INT);
$tenantStmt->execute();
$tenants = $tenantStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tenants = [];
}
// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reading'])) {
    $tenantId = $_POST['tenant_id'] ?? null;
    $readingDate = $_POST['reading_date'] ?? null;
    $previousReading = $_POST['previous_reading'] ?? null;
    $currentReading = $_POST['current_reading'] ?? null;

    if (!$tenantId || !$readingDate || $previousReading === '' || $currentReading === '') {
        $message = "Please fill in all required fields.";
        $messageType = "error";
    } elseif ($currentReading < $previousReading) {
        $message = "Current reading cannot be less than the previous reading.";
        $messageType = "error";
    } else {
        try {
            $unitsConsumed = $currentReading - $previousReading;

            $insertQuery = "INSERT INTO water_reader 
                                (tenant_id, reading_date, previous_reading, current_reading, volume_used) 
                            VALUES 
                                (:tenant_id, :reading_date, :previous_reading, :current_reading, :volume_used)";
            
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
            $insertStmt->bindValue(':reading_date', $readingDate);
            $insertStmt->bindValue(':previous_reading', $previousReading);
            $insertStmt->bindValue(':current_reading', $currentReading);
            $insertStmt->bindValue(':volume_used', $unitsConsumed);

            if ($insertStmt->execute()) {
                $message = "Meter reading successfully recorded!";
                $messageType = "success";
            } else {
                $message = "Failed to save reading. Please try again.";
                $messageType = "error";
            }
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Meter Reading</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
           <aside class="sidebar">
                <div class="sidebar-heading">Manager Menu</div>
                <nav>
                    <a href="../managerpages/utilities.php">Go Back</a>
                </nav>
            </aside>
                 <main class="main-content">

                <div class="form-card">
                    <h2>Record Utility Meter Reading</h2>
                    <p class="form-subtitle">
                        Select a tenant assigned to your properties to fetch their unit and meter details.
                    </p>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="tenant_id">Select Tenant / Unit *</label>
                            <select name="tenant_id" id="tenant_id" required onchange="updateMeterNumber()">
                                <option value="">-- Choose Tenant --</option>
                                <?php if (!empty($tenants)): ?>
                                    <?php foreach ($tenants as $tenant): ?>
                                        <option value="<?php echo $tenant['tenant_id']; ?>" 
                                                data-meter="<?php echo htmlspecialchars($tenant['meter_number'] ?? 'N/A'); ?>">
                                            <?php echo htmlspecialchars($tenant['unit_name'] . ' - ' . $tenant['tenant_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No tenants found under your assigned properties</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Read-only Meter Number field -->
                        <div class="form-group">
                            <label for="meter_number">Meter Number</label>
                            <input type="text" id="meter_number" readonly placeholder="Select a tenant first">
                        </div>

                        <!-- Reading Date -->
                        <div class="form-group">
                            <label for="reading_date">Reading Date *</label>
                            <input type="date" name="reading_date" id="reading_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <!-- Previous Reading -->
                        <div class="form-group">
                            <label for="previous_reading">Previous Reading *</label>
                            <input type="number" step="0.01" name="previous_reading" id="previous_reading" placeholder="0.00" required>
                        </div>

                        <!-- Current Reading -->
                        <div class="form-group">
                            <label for="current_reading">Current Reading *</label>
                            <input type="number" step="0.01" name="current_reading" id="current_reading" placeholder="0.00" required>
                        </div>

                        <button type="submit" name="submit_reading" class="btn-submit">Save Meter Reading</button>
                    </form>
                </div>

            </main>
        </div>
    </div>
    <!-- JS to update meter number automatically -->
    <script>
function updateMeterNumber() {
    const tenant = document.getElementById("tenant_id");
    const option = tenant.options[tenant.selectedIndex];
    document.getElementById("meter_number").value =
        option.dataset.meter || "";
    document.getElementById("unit_id").value =
        option.dataset.unit || "";
}
    </script>
</body>
</html>