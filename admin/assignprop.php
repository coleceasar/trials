<?php
session_start();
require_once __DIR__ . '/../config.php';

// Instantiate Database class and establish connection
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed. Please check your configuration.");
}

// Redirect if admin is not logged in
if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

$message = '';
$messageType = '';

// Process Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manager_id = $_POST['manager_id'] ?? null;
    $property_ids = $_POST['property_ids'] ?? [];

    if (!empty($manager_id) && !empty($property_ids)) {
        try {
            $db->beginTransaction();

            // 1. Verify manager exists and is currently INACTIVE
            $managerCheck = $db->prepare("SELECT manager_id, name, username, status FROM manager WHERE manager_id = :manager_id AND status = 'inactive' LIMIT 1");
            $managerCheck->execute([':manager_id' => $manager_id]);
            $selectedManager = $managerCheck->fetch(PDO::FETCH_ASSOC);

            if (!$selectedManager) {
                throw new Exception("Selected manager does not exist or is already active.");
            }

            // 2. Check and assign properties
            $checkStmt = $db->prepare("SELECT mp_id FROM manager_properties WHERE property_id = :property_id LIMIT 1");
            $updateStmt = $db->prepare("UPDATE manager_properties SET manager_id = :manager_id WHERE property_id = :property_id");
            $insertStmt = $db->prepare("INSERT INTO manager_properties (property_id, manager_id) VALUES (:property_id, :manager_id)");

            $assignedCount = 0;
            foreach ($property_ids as $property_id) {
                $checkStmt->execute([':property_id' => $property_id]);
                $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    $updateStmt->execute([
                        ':manager_id' => $manager_id,
                        ':property_id' => $property_id
                    ]);
                } else {
                    $insertStmt->execute([
                        ':property_id' => $property_id,
                        ':manager_id' => $manager_id
                    ]);
                }
                $assignedCount++;
            }

            // 3. Reactivate manager status to 'active'
            $activateStmt = $db->prepare("UPDATE manager SET status = 'active' WHERE manager_id = :manager_id");
            $activateStmt->execute([':manager_id' => $manager_id]);

            $db->commit();

            $message = "The manager has successfully been assigned a a property";
            $messageType = 'success';

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Error assigning properties: ' . $e->getMessage());
            $message = "Error assigning properties: " . $e->getMessage();
            $messageType = 'danger';
        }
    } else {
        $message = "Please select both an inactive manager and at least one property.";
        $messageType = 'warning';
    }
}

// Fetch Data for Form Rendering
$inactiveManagers = [];
$properties = [];

try {
    // Fetch ONLY INACTIVE managers
    $managerStmt = $db->query("SELECT manager_id, name, username, status FROM manager WHERE status = 'inactive' ORDER BY name ASC");
    $inactiveManagers = $managerStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch properties
    $propertyStmt = $db->query("SELECT property_id, property_name FROM property ORDER BY property_name ASC");
    $properties = $propertyStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching dropdown/checkbox data: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Properties to Inactive Manager</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 mx-auto">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="card-title mb-0">Reactivate & Assign Properties</h4>
                    </div>
                    <div class="card-body p-4">

                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <!-- Inactive Manager Selection -->
                            <div class="mb-4">
                                <label for="manager_id" class="form-label fw-bold">Select Inactive Manager <span class="text-danger">*</span></label>
                                <select id="manager_id" name="manager_id" class="form-select" required>
                                    <?php if (!empty($inactiveManagers)): ?>
                                        <option value="">Select the manager</option>
                                        <?php foreach ($inactiveManagers as $manager): ?>
                                            <option value="<?php echo htmlspecialchars($manager['manager_id']); ?>"
                                                <?php echo (isset($_POST['manager_id']) && $_POST['manager_id'] == $manager['manager_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($manager['name'] . ' (' . $manager['username'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">No inactive managers found</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <!-- Properties Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Properties <span class="text-danger">*</span></label>
                                <?php if (!empty($properties)): ?>
                                    <div class="border rounded p-3 bg-white property-list-container" style="max-height: 250px; overflow-y: auto;">
                                        <?php foreach ($properties as $property): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="property_ids[]"
                                                    value="<?php echo htmlspecialchars($property['property_id']); ?>"
                                                    id="prop_<?php echo htmlspecialchars($property['property_id']); ?>">
                                                <label class="form-check-label" for="prop_<?php echo htmlspecialchars($property['property_id']); ?>">
                                                    <?php echo htmlspecialchars($property['property_name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No properties available to assign.</p>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" <?php echo empty($inactiveManagers) ? 'disabled' : ''; ?>>
                                    Reactivate & Assign Selected Properties
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>