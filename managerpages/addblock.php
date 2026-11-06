<?php
session_start();
include_once '../config.php';

// Redirect if manager is not logged in
if (empty($_SESSION['manager_id'])) {
    header('Location: managerlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$manager_id = $_SESSION['manager_id'];
$message = '';
$selected_property_id = '';

// 1. Fetch properties assigned to this logged-in manager
$assigned_properties = [];
try {
    $prop_stmt = $db->prepare("SELECT p.property_id, p.property_name 
        FROM property p 
        INNER JOIN manager_properties mp ON p.property_id = mp.property_id 
        WHERE mp.manager_id = :manager_id
        ORDER BY p.property_name ASC
    ");
    $prop_stmt->execute([':manager_id' => $manager_id]);
    $assigned_properties = $prop_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "<div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// 2. Handle Form Submission for Multiple Blocks
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_property_id = $_POST['property_id'] ?? '';
    $block_names = $_POST['block_name'] ?? [];
    $number_of_floors = $_POST['number_of_floors'] ?? [];

    if (!empty($selected_property_id) && !empty($block_names)) {
        try {
            // Verify property authorization
            $check_stmt = $db->prepare("SELECT 1 FROM manager_properties WHERE manager_id = :manager_id AND property_id = :property_id");
            $check_stmt->execute([
                ':manager_id'  => $manager_id,
                ':property_id' => $selected_property_id
            ]);

            if ($check_stmt->rowCount() > 0) {
                $db->beginTransaction();

                $insert_stmt = $db->prepare("INSERT INTO blocks (property_id, block_name, number_of_floors) 
                    VALUES (:property_id, :block_name, :number_of_floors)");

                $inserted_count = 0;

                for ($i = 0; $i < count($block_names); $i++) {
                    $b_name = trim($block_names[$i]);
                    $b_floors = intval($number_of_floors[$i] ?? 1);

                    if (!empty($b_name)) {
                        $insert_stmt->execute([
                            ':property_id'      => $selected_property_id,
                            ':block_name'       => $b_name,
                            ':number_of_floors' => $b_floors
                        ]);
                        $inserted_count++;
                    }
                }

                $db->commit();

                if ($inserted_count > 0) {
                    $_SESSION['success_msg'] = "{$inserted_count} block(s) successfully added!";
                    header("Location: addblock.php");
                    exit();
                } else {
                    $message = "<div class='alert alert-danger'>Please fill in at least one block name.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Unauthorized: You do not manage this property.</div>";
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $message = "<div class='alert alert-danger'>Error adding blocks: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Please select a property and fill in the block details.</div>";
    }
}

// Display redirect session message if present
if (isset($_SESSION['success_msg'])) {
    $message = "<div class='alert alert-success'>" . $_SESSION['success_msg'] . "</div>";
    unset($_SESSION['success_msg']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property Blocks</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/mheader.php'; ?>
        
        <main class="main-content">
            <section class="welcome-card my-3">
                <div>
                    <p class="eyebrow text-muted mb-1">Property Setup</p>
                    <h1 class="heading">Add New Property Blocks</h1>
                </div>
            </section>

            <form action="" method="POST">
                <section class="action-card">
                    <div class="page-actions">
                        <a href="manageunits.php" class="btn btn-secondary">Back to Unit Management</a>
                    </div>

                    <?php if (!empty($message)) echo $message; ?>

                    <!-- Select Assigned Property -->
                    <div class="form-group mb-4">
                        <label for="property_id" class="form-label fw-bold">Select Property *</label>
                        <select id="property_id" name="property_id" class="form-select" required>
                            <option value="">-- Choose Assigned Property --</option>
                            <?php foreach ($assigned_properties as $prop): ?>
                                <option value="<?php echo $prop['property_id']; ?>" 
                                    <?php echo ($selected_property_id == $prop['property_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prop['property_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h5 class="fw-bold mb-3">Blocks Details</h5>

                    <!-- Container for Block Rows -->
                    <div id="blocks-container">
                        <div class="row align-items-end mb-3 block-row">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Block Name / Letter *</label>
                                <input type="text" name="block_name[]" class="form-control" placeholder="e.g. Block A" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Number of Floors *</label>
                                <input type="number" name="number_of_floors[]" class="form-control" placeholder="e.g. 5" min="1" value="1" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 remove-row" disabled>Remove</button>
                            </div>
                        </div>
                    </div>

                    <!-- Actions: Add Row & Save -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="button" id="add-more-btn" class="btn btn-outline-primary">+ Add Another Block</button>
                        <button type="submit" class="btn btn-success px-4">Save All Blocks</button>
                    </div>
                </section>
            </form>
        </main>
    </div>

    <!-- External JavaScript Libraries & Custom Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="block.js"></script>
</body>
</html>