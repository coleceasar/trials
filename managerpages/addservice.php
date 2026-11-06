<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_id'])) {
    header('Location: managerlogin.php');
    exit();
}

$managerId = $_SESSION['manager_id'];

$database = new Database();
$db = $database->getConnection();

$serviceName = $_POST['service_name'] ?? '';
$quantity = trim($_POST['quantity'] ?? '');
$price = trim($_POST['price'] ?? '');

$successMessage = '';
$errorMessage = '';

// Fetch the property assigned to this manager via manager_properties
$propertyName = 'No Property Assigned';
$assignedPropertyId = null;

try {
    $sqlProperty = "SELECT p.property_id, p.property_name 
        FROM manager_properties mp
        INNER JOIN property p ON mp.property_id = p.property_id
        WHERE mp.manager_id = :manager_id
        LIMIT 1
    ";
    
    $stmtProperty = $db->prepare($sqlProperty);
    $stmtProperty->bindValue(':manager_id', $managerId, PDO::PARAM_INT);
    $stmtProperty->execute();
    $propertyData = $stmtProperty->fetch(PDO::FETCH_ASSOC);

    if ($propertyData) {
        $propertyName = $propertyData['property_name'];
        $assignedPropertyId = $propertyData['property_id'];
    }
} catch (PDOException $e) {
    $errorMessage = 'Database error fetching property: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$assignedPropertyId) {
        $errorMessage = 'You do not have a property assigned to manage.';
    } elseif ($serviceName === '' || $quantity === '' || $price === '') {
        $errorMessage = 'Please fill in all fields.';
    } elseif (!ctype_digit($quantity) || (int)$quantity < 0) {
        $errorMessage = 'Quantity must be a non-negative integer.';
    } elseif (!is_numeric($price) || (float)$price < 0) {
        $errorMessage = 'Price must be a non-negative number.';
    } else {
        // Sanitize price: remove currency symbols and commas
        $priceSanitized = preg_replace('/[^0-9.\-]/', '', $price);

        if ($priceSanitized === '' || !is_numeric($priceSanitized)) {
            $errorMessage = 'Price must be a valid number.';
        } else {
            $priceValue = number_format((float)$priceSanitized, 2, '.', '');

            if ((float)$priceValue > 99999.99) {
                $errorMessage = 'Price is too large.';
            } else {
                try {
                    $db->beginTransaction();

                    // 1. Create entry in global services table
                    $stmt1 = $db->prepare(
                        'INSERT INTO services (service_name, quantity, price) VALUES (:service_name, :quantity, :price)'
                    );
                    $stmt1->bindValue(':service_name', $serviceName, PDO::PARAM_STR);
                    $stmt1->bindValue(':quantity', (int)$quantity, PDO::PARAM_INT);
                    $stmt1->bindValue(':price', $priceValue, PDO::PARAM_STR);
                    $stmt1->execute();

                    $newServiceId = $db->lastInsertId();

                    // 2. Scope service strictly to this property and manager in service_property table
                    $stmt2 = $db->prepare(
                        'INSERT INTO service_property (service_id, property_id, manager_id) VALUES (:service_id, :property_id, :manager_id)'
                    );
                    $stmt2->bindValue(':service_id', (int)$newServiceId, PDO::PARAM_INT);
                    $stmt2->bindValue(':property_id', (int)$assignedPropertyId, PDO::PARAM_INT);
                    $stmt2->bindValue(':manager_id', (int)$managerId, PDO::PARAM_INT);
                    $stmt2->execute();

                    $db->commit();

                    $successMessage = 'Service successfully added to ' . htmlspecialchars($propertyName) . '.';
                    $serviceName = '';
                    $quantity = '';
                    $price = '';
                } catch (PDOException $e) {
                    $db->rollBack();
                    $errorMessage = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Service</title>
    <link rel="stylesheet" href="../admin/service.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h2>Create Service</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="property_display">Property Name:</label>
                <input type="text" id="property_display" value="<?php echo htmlspecialchars($propertyName); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed;">
            </div>

            <div class="form-group">
                <label for="service_name">Service Name</label>
                <input type="text" id="service_name" name="service_name" value="<?php echo htmlspecialchars($serviceName); ?>" placeholder="Garbage Collection" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input type="text" id="price" name="price" required value="<?php echo htmlspecialchars($price); ?>" placeholder="KSH 250.00">
            </div>

            <button type="submit" class="btn-primary" <?php echo !$assignedPropertyId ? 'disabled' : ''; ?>>Create Service</button>
        </form>

        <div style="margin-top: 20px;">
            <a href="managersdashboard.php" class="btn-primary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>