<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['tenant_username'])) {
    header('Location: tenantlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$username = $_SESSION['tenant_username'];

// Initialize message variables
$errorMessage = '';
$successMessage = '';

try {
    // 1. Fetch tenant_id using session username
    $stmt = $db->prepare("SELECT tenant_id FROM tenant_profile WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    $tenant_id = $tenant['tenant_id'] ?? null;

    if (!$tenant_id) {
        $errorMessage = "Tenant profile not found.";
        $services = [];
    } else {
        // 2. Fetch assigned services using the service_tenant table
        $query = "SELECT 
                    s.service_id,
                    s.service_name,
                    s.quantity,
                    s.price,
                    p.property_name,
                    u.unit_name
                FROM service_tenant st
                INNER JOIN services s ON st.service_id = s.service_id
                INNER JOIN tenants t ON st.tenant_id = t.tenant_id
                LEFT JOIN units u ON t.unit_id = u.unit_id
                LEFT JOIN blocks b ON u.block_id = b.block_id
                LEFT JOIN property p ON b.property_id = p.property_id
                WHERE st.tenant_id = :tenant_id
                ORDER BY s.service_id DESC";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $errorMessage = 'Error fetching service list: ' . $e->getMessage();
    $services = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Assigned Services</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <main class="main-content container py-4">
        <section class="welcome-card mb-4">
            <div>
                <p class="eyebrow">Tenants Portal</p>
                <h1 class="heading">Assigned Services</h1>
            </div>
        </section>

        <section class="action-card">
            <div class="page-actions mb-3">
                <a href="tdashboard.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if (empty($services)): ?>
                <div class="alert alert-info">No services currently assigned to your account.</div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="tenant-table table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr> 
                                <th>Property</th>
                                <th>Service Name</th>
                                <th>Unit</th>
                                <th>Quantity</th>
                                <th>Price (KSH)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service['property_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($service['service_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($service['unit_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($service['quantity'] ?? '1'); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($service['price'] ?? 0, 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
<script src="../components/list-filter.js" defer></script>
</body>
</html>