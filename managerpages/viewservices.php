<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_id'])) {
    header('Location: managerlogin.php');
    exit();
}

// Ensure Step 1 was completed
if (empty($_SESSION['registration']['property_id'])) {
    header('Location: property.php');
    exit();
}

$propertyId = $_SESSION['registration']['property_id'];
$propertyName = $_SESSION['registration']['property_name'];

$database = new Database();
$db = $database->getConnection();

$availableServices = [];
$errorMessage = '';

try {
    // Fetch services linked to the selected property
    $sqlServices = "SELECT s.service_id, s.service_name, s.price 
                    FROM services s
                    INNER JOIN service_property sp ON s.service_id = sp.service_id
                    WHERE sp.property_id = :property_id";
    $stmtServ = $db->prepare($sqlServices);
    $stmtServ->bindValue(':property_id', $propertyId, PDO::PARAM_INT);
    $stmtServ->execute();
    $availableServices = $stmtServ->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Database error fetching services: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedServices = $_POST['services'] ?? [];

    // Save selected services into session
    $_SESSION['registration']['service_ids'] = array_map('intval', $selectedServices);

    header('Location:tregistration.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Step 2: Select Services</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container" style="max-width:600px; margin:40px auto;">
        <h2>Step 2: Select Services</h2>
        <p><strong>Selected Property:</strong> <?php echo htmlspecialchars($propertyName); ?></p>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger" style="color:red; margin-bottom:15px;"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group" style="margin-bottom:15px;">
                <label>Available Services (Optional)</label>
                <?php if (empty($availableServices)): ?>
                    <p><em>No services available for this property. Click Next to proceed.</em></p>
                <?php else: ?>
                    <?php foreach ($availableServices as $srv): ?>
                        <div style="margin-top:8px;">
                            <label>
                                <input type="checkbox" name="services[]" value="<?php echo $srv['service_id']; ?>">
                                <?php echo htmlspecialchars($srv['service_name']) . " — KSH " . number_format((float)$srv['price'], 2); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div style="display:flex; gap:10px;">
                <a href="property.php" class="btn-primary" style="background:#6c757d; text-decoration:none;">&larr; Back</a>
                <button type="submit" class="btn-primary">Next: Enter Tenant Details &rarr;</button>
            </div>
        </form>
    </div>
</body>
</html>