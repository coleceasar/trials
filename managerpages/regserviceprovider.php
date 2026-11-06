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

$providerName   = trim($_POST['provider_name'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$role           = trim($_POST['role'] ?? '');
$onboardingDate = trim($_POST['onboarding_date'] ?? '');

$successMessage = '';
$errorMessage   = '';

// Fetch the property assigned to this manager via manager_properties
$propertyName       = 'No Property Assigned';
$assignedPropertyId = null;

try {
    $sqlProperty = "SELECT p.property_id, p.property_name 
        FROM manager_properties mp
        INNER JOIN property p ON mp.property_id = p.property_id
        WHERE mp.manager_id = :manager_id
        LIMIT 1";
    
    $stmtProperty = $db->prepare($sqlProperty);
    $stmtProperty->bindValue(':manager_id', $managerId, PDO::PARAM_INT);
    $stmtProperty->execute();
    $propertyData = $stmtProperty->fetch(PDO::FETCH_ASSOC);

    if ($propertyData) {
        $propertyName       = $propertyData['property_name'];
        $assignedPropertyId = $propertyData['property_id'];
    }
} catch (PDOException $e) {
    $errorMessage = 'Database error fetching property: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($assignedPropertyId)) {
        $errorMessage = 'You do not have a property assigned to manage.';
    } elseif (empty($providerName) || empty($phone) || empty($role) || empty($onboardingDate)) {
        $errorMessage = 'Please fill in all fields.';
    } elseif (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) {
        $errorMessage = 'Please enter a valid phone number.';
    } else {
        try {
            $db->beginTransaction();

            $sql = "INSERT INTO service_provider (provider_name, phone, role, onboarding_date, property_id, manager_id) 
                    VALUES (:provider_name, :phone, :role, :onboarding_date, :property_id, :manager_id)";
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':provider_name', $providerName, PDO::PARAM_STR);
            $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
            $stmt->bindValue(':role', $role, PDO::PARAM_STR);
            $stmt->bindValue(':onboarding_date', $onboardingDate, PDO::PARAM_STR);
            $stmt->bindValue(':property_id', (int)$assignedPropertyId, PDO::PARAM_INT);
            $stmt->bindValue(':manager_id', (int)$managerId, PDO::PARAM_INT);
            $stmt->execute();

            $db->commit();

            $successMessage = 'Service provider "' . htmlspecialchars($providerName) . '" successfully onboarded for ' . htmlspecialchars($propertyName) . '.';
            
            // Clear inputs on success
            $providerName   = '';
            $phone          = '';
            $role           = '';
            $onboardingDate = '';

        } catch (PDOException $e) {
            $db->rollBack();
            $errorMessage = 'Database error onboarding provider: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Onboard Service Provider</title>
    <link rel="stylesheet" href="../admin/service.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container" style="max-width: 600px; margin: 40px auto;">
        <h2>Onboard Service Provider</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="message error" style="color:red; background:#fee; padding:10px; border-radius:5px; margin-bottom:15px; text-align:center;">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="message success" style="color:green; background:#efe; padding:10px; border-radius:5px; margin-bottom:15px; text-align:center;">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="property_id" value="<?php echo htmlspecialchars($assignedPropertyId ?? ''); ?>">

            <div class="form-group mb-3">
                <label for="property_display">Assigned Property</label>
                <input type="text" id="property_display" class="form-control" value="<?php echo htmlspecialchars($propertyName); ?>" readonly style="background-color: #e9ecef; cursor: not-allowed;">
            </div>

            <div class="form-group mb-3">
                <label for="provider_name">Provider / Company Name</label>
                <input type="text" id="provider_name" name="provider_name" class="form-control" value="<?php echo htmlspecialchars($providerName); ?>" placeholder="e.g. garbage collectors" required>
            </div>

            <div class="form-group mb-3">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" placeholder="0712345678" required>
            </div>

            <div class="form-group mb-3">
                <label for="role">Service Specialty / Role</label>
                <input type="text" id="role" name="role" class="form-control" value="<?php echo htmlspecialchars($role); ?>" placeholder="Garbage Collection" required>
            </div>

            <div class="form-group mb-3">
                <label for="onboarding_date">Onboarding Date</label>
                <input type="date" id="onboarding_date" name="onboarding_date" class="form-control" value="<?php echo htmlspecialchars($onboardingDate ?: date('Y-m-d')); ?>" required>
            </div>

            <button type="submit" class="btn btn-success w-100 mt-3" <?php echo !$assignedPropertyId ? 'disabled' : ''; ?>>Onboard Provider</button>
        </form>

        <div style="margin-top: 20px;">
            <a href="managersdashboard.php" class="btn btn-secondary w-100" style="text-decoration: none; text-align: center; display: block;">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>