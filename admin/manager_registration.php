<?php
session_start();
include_once '../config.php';

// Redirect if admin is not logged in
if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

$registered = isset($_GET['registered']) && $_GET['registered'] === '1';

$database = new Database();
$db = $database->getConnection();
$managers = [];
try {
    $managerStmt = $db->query("SELECT manager_id, username, name FROM manager WHERE status = 'inactive' ORDER BY name");
    $managers = $managerStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching managers for dropdown: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management System</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-page bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <?php if ($registered): ?>
                            <div class="text-center py-3">
                                <h2 class="h4 text-success fw-bold mb-3">Manager Registered</h2>
                                <div class="alert alert-success mb-4" role="alert">
                                    MANAGER SUCCESSFULLY REGISTERED
                                </div>
                                <a href="admindashboard.php" class="btn btn-primary w-100">Return to Admin Dashboard</a>
                            </div>
                        <?php else: ?>
                            <h2 class="card-title fw-bold text-center mb-1">Register Manager</h2>
                            <p class="text-muted text-center mb-4">Enter details for the new manager:</p>

                            <form method="post" action="managerregister.php" class="needs-validation">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Manager Name <span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control" placeholder="Manager Name" required>
                                </div>
                                <div class="mb-3">
                                     <label for="percentagepay" class="form-label">Percentage Pay(%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" max="100" id="percentage_pay" name="percentage_pay" class="form-control" required>                                
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" id="phone_number" name="phone_number" class="form-control" placeholder="Phone Number" required>
                                </div>

                                <div class="mb-3">
                                    <label for="national_id" class="form-label">National ID Number</label>
                                    <input type="text" id="national_id" name="national_id" class="form-control" placeholder="National ID Number">
                                </div>

                                <div class="mb-3">
                                    <label for="location" class="form-label">Currently Located <span class="text-danger">*</span></label>
                                    <input type="text" id="location" name="location" class="form-control" placeholder="Currently Located" required>
                                </div>

                                <div class="mb-4">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" placeholder="Email Address">
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2">Register Manager</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>