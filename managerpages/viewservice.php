<?php
session_start();
include_once '../config.php';

// Check for admin session authentication
if (empty($_SESSION['manager_id'])) {
    header('Location: managerlogin.php');
    exit;
}

$manager_id = (int) $_SESSION['manager_id'];

$database = new Database();
$db = $database->getConnection();

$services = [];
$errorMessage = '';
$successMessage = '';

// Handle price update submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_price'])) {
    $service_id = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
    $new_price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

    if ($service_id !== false && $new_price !== false && $new_price >= 0) {
        try {
            // Update price ensuring the service belongs to a property managed by this manager
            $updateQuery = "UPDATE services s
                            INNER JOIN service_property sp ON s.service_id = sp.service_id
                            INNER JOIN property p ON sp.property_id = p.property_id
                            SET s.price = :price 
                            WHERE s.service_id = :service_id AND p.manager_id = :manager_id";

            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':price', $new_price);
            $updateStmt->bindParam(':service_id', $service_id, PDO::PARAM_INT);
            $updateStmt->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);

            if ($updateStmt->execute() && $updateStmt->rowCount() > 0) {
                $successMessage = 'Price updated successfully.';
            } else {
                $errorMessage = 'Failed to update the price or unauthorized action.';
            }
        } catch (PDOException $e) {
            $errorMessage = 'Database error: ' . $e->getMessage();
        }
    } else {
        $errorMessage = 'Invalid price or service selection provided.';
    }
}

try {
    // Select service details filtering strictly by the active manager_id
    $query = "SELECT s.service_id,
                     s.service_name,
                     s.quantity,
                     s.price,
                    p.property_name
                FROM services s
              INNER JOIN service_property sp ON s.service_id = sp.service_id
              INNER JOIN property p ON sp.property_id = p.property_id
              INNER JOIN manager_properties mp ON sp.property_id = mp.property_id
              WHERE mp.manager_id = :manager_id
              ORDER BY s.service_id DESC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
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
    <title>Manager - All Property Services</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="services.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="dashboard-page">
    <main class="main-content container py-4">
        <section class="welcome-card mb-4">
            <div>
                <p class="eyebrow text-muted mb-1">Manager Portal</p>
                <h1 class="heading h2">Property Services Overview</h1>
            </div>
        </section>

        <section class="action-card">
            <div class="page-actions mb-3">
                <a href="managersdashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if (empty($services)): ?>
                <p class="text-muted">No services found for your assigned properties.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle custom-table">
                        <thead>
                            <tr>
                                <th>Service Name</th>
                                <th>Property Name</th>
                                <th>Quantity</th>
                                <th>Price (KSH)</th>
                                <th class="text-center" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <form action="" method="POST">
                                        <input type="hidden" name="service_id"
                                            value="<?php echo (int) $service['service_id']; ?>">

                                        <td><strong><?php echo htmlspecialchars($service['service_name'] ?? 'N/A'); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($service['property_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($service['quantity'] ?? 'N/A'); ?></td>
                                        <td>
                                            <input type="number" name="price" class="form-control form-control-sm price-input"
                                                step="0.01" min="0"
                                                value="<?php echo htmlspecialchars($service['price'] ?? '0.00'); ?>" required>
                                        </td>
                                        <td class="text-center">
                                            <button type="submit" name="update_price" class="btn btn-success btn-sm px-3">
                                                Save
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../components/list-filter.js" defer></script>
</body>

</html>