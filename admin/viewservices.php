<?php
session_start();
include_once '../config.php';

// Check for admin session authentication
if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$services = [];
$errorMessage = '';

try {
    // Select service details along with the property name via service_property table
    $query = "SELECT s.service_id,
                     s.service_name,
                     s.quantity,
                     s.price,
                     IFNULL(p.property_name, 'Unassigned') AS property_name
              FROM services s
              LEFT JOIN service_property sp ON s.service_id = sp.service_id
              LEFT JOIN property p ON sp.property_id = p.property_id
              ORDER BY s.service_id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Admin - All Property Services</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="dashboard-page">
    <main class="main-content">
        <section class="welcome-card">
            <div>
                <p class="eyebrow">Admin Portal</p>
                <h1 class="heading">System Services Overview</h1>
            </div>
        </section>

        <section class="action-card">
            <div class="page-actions mb-3">
                <a href="admindashboard.php" class="btn-primary">Back to Dashboard</a>
            </div>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <?php if (empty($services)): ?>
                <p>No services found in the database.</p>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="tenant-table table table-striped">
                        <thead>
                            <tr>
                                <th>Service Name</th>
                                <th>Property Name</th>
                                <th>Quantity</th>
                                <th>Price (KSH)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service['service_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($service['property_name'] ?? 'ALL Properties'); ?></td>
                                    <td><?php echo htmlspecialchars($service['quantity'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(number_format((float)($service['price'] ?? 0), 2)); ?></td>
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