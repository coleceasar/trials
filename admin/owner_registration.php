<?php
session_start();
include_once '../config.php';

// Redirect if admin is not logged in
if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// --- AJAX ENDPOINT FOR UNASSIGNED PROPERTIES ---
if (isset($_GET['get_unassigned_properties'])) {
    header('Content-Type: application/json');
    try {
        $stmt = $db->query("SELECT property_id, property_name FROM property WHERE status = 'Unassigned' ORDER BY property_name ASC");
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($properties);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// --- AJAX ENDPOINT FOR BRANCHES ---
if (isset($_GET['get_branches']) && isset($_GET['property_id'])) {
    header('Content-Type: application/json');
    try {
        $propertyId = (int)$_GET['property_id'];
        $stmt = $db->prepare("SELECT branch_id, branch_name, location FROM branch WHERE property_id = :property_id");
        $stmt->bindParam(':property_id', $propertyId, PDO::PARAM_INT);
        $stmt->execute();
        $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($branches);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

$registered = isset($_GET['registered']) && $_GET['registered'] === '1';

// Fetch managers for dropdown
$managers = [];
try {
    $managerStmt = $db->query("SELECT manager_id, username, name, status FROM manager ORDER BY status = 'Inactive' DESC");
    $managers = $managerStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching managers: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Property & Register Landlord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="auth-page bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <?php if ($registered): ?>
                            <div class="text-center py-3">
                                <h2 class="h4 text-success fw-bold mb-3">Registration Successful</h2>
                                <div class="alert alert-success mb-4" role="alert">
                                    Landlord registered and property assigned successfully!
                                </div>
                                <a href="admindashboard.php" class="btn btn-primary">Return to Admin Dashboard</a>
                            </div>
                        <?php else: ?>
                            <h2 class="card-title fw-bold text-center mb-1">Assign Property &amp; Register Landlord</h2>
                            <p class="text-muted text-center mb-4">Select manager, assign unassigned property, and enter landlord details:</p>

                            <form method="post" action="ownerregister.php" class="needs-validation">
                                
                                <!-- Manager Selection -->
                                <div class="mb-3">
                                    <label for="manager_id" class="form-label">Assign Manager <span class="text-danger">*</span></label>
                                    <select id="manager_id" name="manager_id" class="form-select" required>
                                        <option value="">-- Select Manager --</option>
                                        <?php foreach ($managers as $manager): ?>
                                            <option value="<?php echo $manager['manager_id']; ?>">
                                                <?php echo htmlspecialchars($manager['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Unassigned Property Dropdown -->
                                <div class="mb-4">
                                    <label for="property_select" class="form-label">Select the Property <span class="text-danger">*</span></label>
                                    <select id="property_select" name="property_id" class="form-select" required disabled>
                                        <option value="">-- Select Manager First --</option>
                                    </select>
                                </div>

                                <!-- Branch Table -->
                                <div id="branch_container" class="mb-4">
                                    <h5 class="fw-semibold mb-3">Branches of Selected Property</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Branch Name</th>
                                                    <th>Location</th>
                                                </tr>
                                            </thead>
                                            <tbody id="branch_table_body">
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">Select a property above to load branches</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Landlord Registration Fields -->
                                <h5 class="fw-semibold mb-3">Landlord Details</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="landlord_name" class="form-label">Landlord Name <span class="text-danger">*</span></label>
                                        <input type="text" id="landlord_name" placeholder="Landlord Full Name" name="name" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="national_id" class="form-label">National ID / Passport</label>
                                        <input type="text" id="national_id" placeholder="National ID / Passport" name="national_id" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="phone_number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" id="phone_number" placeholder="Phone Number" name="phone_number" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" id="email" placeholder="Email Address" name="email" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                        <input type="text" id="username" placeholder="Username" name="username" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" id="password" placeholder="Password" name="password" class="form-control" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2">Register Landlord &amp; Assign Property</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="owner.js"></script>
<script src="../components/list-filter.js" defer></script>
</body>
</html>