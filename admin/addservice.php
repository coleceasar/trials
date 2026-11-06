<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$serviceName = $_POST['service_name'] ?? '';
$quantity = trim($_POST['quantity'] ?? '');
$price = trim($_POST['price'] ?? '');

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($serviceName === '' || $quantity === '' || $price === '') {
        $errorMessage = 'Please fill in all fields.';
    } elseif (!ctype_digit($quantity) || (int)$quantity < 0) {
        $errorMessage = 'Quantity must be a non-negative integer.';
    } elseif (!is_numeric($price) || (float)$price < 0) {
        $errorMessage = 'Price must be a non-negative number.';
    } else {
        // sanitize price: remove currency symbols and commas
        $priceSanitized = preg_replace('/[^0-9.\-]/', '', $price);

        if ($priceSanitized === '' || !is_numeric($priceSanitized)) {
            $errorMessage = 'Price must be a valid number.';
        } else {
            $priceValue = number_format((float)$priceSanitized, 2, '.', '');

            // enforce a sensible maximum to avoid DB precision/scale issues
            if ((float)$priceValue > 99999999.99) {
                $errorMessage = 'Price is too large.';
            } else {
                try {
                    $stmt = $db->prepare(
                        'INSERT INTO services (service_name, quantity, price) VALUES (:service_name, :quantity, :price)'
                    );
                    $stmt->bindValue(':service_name', $serviceName, PDO::PARAM_STR);
                    $stmt->bindValue(':quantity', (int)$quantity, PDO::PARAM_INT);
                    $stmt->bindValue(':price', $priceValue, PDO::PARAM_STR);
                    $stmt->execute();

                    $successMessage = 'Service successfully created.';
                    $serviceName = '';
                    $quantity = '';
                    $price = '';
                } catch (PDOException $e) {
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
    <link rel="stylesheet" href="service.css">
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
                <label for="service_name">Service Name</label>
                <input type="text" id="service_name" name="service_name" value="<?php echo htmlspecialchars($serviceName); ?>" placeholder="Garbage Collection">
            </div>
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>">
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="text" id="price" name="price" required value="<?php echo htmlspecialchars($price); ?>" placeholder="KSH 250.00">
            </div>
            <button type="submit" class="btn-primary">Create Service</button>
        </form>
        <div style="margin-top: 20px;">
            <a href="admindashboard.php" class="btn-primary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>