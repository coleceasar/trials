<?php
session_start();
include_once '../config.php';

// 1. Authentication Check
if (empty($_SESSION['manager_id'])) {
    header('Location: managerlogin.php');
    exit();
}

// 2. Generate CSRF Token if missing
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Retrieve unit_id from session (Set in property.php)
$unit_id = $_SESSION['registration']['unit_id'] ?? null;

// 4. Process POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF Token
    $user_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $user_token)) {
        $_SESSION['error_message'] = "Invalid CSRF token. Please try again.";
        header('Location: tregistration.php');
        exit();
    }

    // Ensure unit selection exists in session before insertion
    if (empty($unit_id)) {
        $_SESSION['error_message'] = "Unit selection is missing. Please restart registration from Step 1.";
        header('Location: property.php');
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $national_id = trim($_POST['idnumber'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: null;
    $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'inactive';

    if (empty($username) || empty($password) || empty($name) || empty($national_id) || empty($phone_number)) {
        $_SESSION['error_message'] = "Please fill in all required tenant details.";
        header('Location: tregistration.php');
        exit();
    }

    try {
        $db->beginTransaction();

        // 1. Insert into tenants FIRST (Parent Table)
        $stmt1 = $db->prepare("INSERT INTO tenants (username, name, national_id, phone_number, email, unit_id, status) 
                           VALUES (:username, :name, :national_id, :phone_number, :email, :unit_id, :status)");
        $stmt1->execute([
            ':username' => $username,
            ':name' => $name,
            ':national_id' => $national_id,
            ':phone_number' => $phone_number,
            ':email' => $email,
            ':unit_id' => $unit_id,
            ':status' => $status
        ]);

        // Get the generated auto-increment tenant_id
        $tenant_id = $db->lastInsertId();

        // 2. Insert into tenant_profile SECOND (Child Table referencing tenant_id)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt2 = $db->prepare("INSERT INTO tenant_profile (tenant_id, username, password) 
                           VALUES (:tenant_id, :username, :password)");
        $stmt2->execute([
            ':tenant_id' => $tenant_id,
            ':username' => $username,
            ':password' => $hashed_password
        ]);

        $db->commit();

        // Pass registered tenant details to tpayments.php
        $_SESSION['registration']['tenant_id'] = (int) $tenant_id;
        $_SESSION['registration']['tenant_name'] = $name;
        $_SESSION['registration']['phone_number'] = $phone_number;

        $_SESSION['success_message'] = "Tenant details registered successfully. Please proceed with payment.";
        header('Location: tpayments.php');
        exit();

    } catch (PDOException $e) {
        $db->rollBack();
        $_SESSION['error_message'] = "Database error during tenant creation: " . $e->getMessage();
        header('Location: tregistration.php');
        exit();
    }

}

// 5. Retrieve Flash Messages & Form Display Setup
$errorMessage = $_SESSION['error_message'] ?? '';
$successMessage = $_SESSION['success_message'] ?? '';
$propertyName = $_SESSION['registration']['property_name'] ?? '';

unset($_SESSION['error_message'], $_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Step 3: Tenant Details</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container" style="max-width:600px; margin:40px auto;">
        <h2>Step 3: Tenant Details</h2>

        <?php if (!empty($propertyName)): ?>
            <p><strong>Property:</strong> <?php echo htmlspecialchars($propertyName); ?></p>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger mb-3">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success mb-3">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="tregistration.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-group mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" required class="form-control">
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" required class="form-control">
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" required class="form-control">
            </div>

            <div class="form-group mb-3">
                <label class="form-label">ID Number / Passport</label>
                <input type="text" name="idnumber" required class="form-control">
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone_number" required class="form-control">
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control">
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive" selected>Inactive</option>
                </select>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <a href="viewservices.php" class="btn btn-secondary">&larr; Back</a>
                <button type="submit" class="btn btn-primary">Next: Move-In & Payments &rarr;</button>
            </div>
        </form>
    </div>
</body>

</html>