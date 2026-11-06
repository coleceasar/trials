<?php
// Handle form submission
$message_sent = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone   = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($name && $email && $message) {
        // Form processing logic (e.g., save to DB or send email)
        $message_sent = true;
    } else {
        $error_message = 'Please fill out all required fields with valid information.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Makao Property Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="landingpage.css">
</head>
<body class="bg-light">

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4 text-primary" href="#">
                <i class="bi bi-building-gear me-2"></i>Makao <span class="text-white">PMS</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-3">
                    <li class="nav-item"><a class="nav-link active"  rel="noopener noreferrer">Home</a></li>
                </ul>
                <div class="d-flex gap-2">
                    <a href="../admin/adminlogin.php" class="btn btn-outline-light btn-sm px-3" target="_blank" rel="noopener noreferrer">Admin Login</a>
                    <a href="../ownerpages/ownerlogin.php" class="btn btn-primary btn-sm px-3" target="_blank" rel="noopener noreferrer">Owner Login</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="hero-section text-white">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-3">Simplify Property & Tenant Management</h1>
                    <p class="lead mb-4 text-white-50">Streamline rent tracking, tenant onboarding, utility sub-metering, and property analytics in one centralized platform.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="../tenantpages/tenantlogin.php" class="btn btn-light btn-lg text-primary fw-semibold px-4" target="_blank" rel="noopener noreferrer">Tenant Login</a>
                        <a href="../managerpages/managerlogin.php" class="btn btn-outline-light btn-lg px-4" target="_blank" rel="noopener noreferrer">Manager's Login</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card bg-white text-dark p-4 shadow-lg rounded-4">
                        <h4 class="fw-bold mb-3"><i class="bi bi-speedometer2 text-primary me-2"></i>Quick Overview</h4>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span><i class="bi bi-person-badge text-primary me-2"></i> Tenant Directory Management</span>
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span><i class="bi bi-lightning-charge text-warning me-2"></i> Automated Utility Metering</span>
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span><i class="bi bi-lightning-charge text-warning me-2"></i> Automated Billing to tenants</span>
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span><i class="bi bi-receipt text-danger me-2"></i> Payment Tracking & Alerts</span>
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </li>
                        </ul>
                        <div class="alert alert-info mb-0 small">
                            <i class="bi bi-info-circle me-1"></i> Access property metrics anywhere on mobile and desktop.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-dark text-white-50 py-4 border-top border-secondary">
        <div class="container text-center text-md-between d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div>
                <p class="mb-0">&copy; <?php echo date("Y"); ?> Makao Property Management System.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.bundle.min.js"></script>
</body>
</html>