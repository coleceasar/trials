<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_id'])) {
    header('Location: managerlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$manager_id  = $_SESSION['manager_id'];
$tenant_id   = $_SESSION['registration']['tenant_id'] ?? null;
$tenant_name = $_SESSION['registration']['tenant_name'] ?? '';
$phone_num   = $_SESSION['registration']['phone_number'] ?? '';
$selected_unit_id = $_SESSION['registration']['unit_id'] ?? null;

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    $tenant_id      = filter_input(INPUT_POST, 'tenant_id', FILTER_VALIDATE_INT);
    $unit_id        = filter_input(INPUT_POST, 'unit_id', FILTER_VALIDATE_INT);
    $movingin_date  = $_POST['movingin_date'] ?? '';
    $tenant_deposit = filter_input(INPUT_POST, 'tenant_deposit', FILTER_VALIDATE_FLOAT);
    $amount_payable = filter_input(INPUT_POST, 'amount_payable', FILTER_VALIDATE_FLOAT);
    $amount_paid    = filter_input(INPUT_POST, 'amount_paid', FILTER_VALIDATE_FLOAT);
    $balance        = filter_input(INPUT_POST, 'balance', FILTER_VALIDATE_FLOAT);
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $phone_number   = trim($_POST['phone_number'] ?? '');
    $transaction_ref = trim($_POST['transaction_reference_number'] ?? '');

    if ($tenant_id && $unit_id && !empty($movingin_date)) {
        try {
            $db->beginTransaction();

            $checkStmt = $db->prepare("SELECT status FROM units 
            WHERE unit_id = :unit_id FOR UPDATE");
            $checkStmt->execute([':unit_id' => $unit_id]);
            $unitState = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$unitState || $unitState['status'] !== 'available') {
                throw new Exception("Selected unit is no longer available.");
            }

            $updateTenant = $db->prepare("UPDATE tenants 
            SET unit_id = :unit_id, movingin_date = :movingin_date, 
            status = 'active' 
            WHERE tenant_id = :tenant_id");
            $updateTenant->execute([
                ':unit_id' => $unit_id,
                ':movingin_date' => $movingin_date,
                ':tenant_id' => $tenant_id
            ]);

            $updateUnit = $db->prepare("UPDATE units SET status = 'occupied' WHERE unit_id = :unit_id");
            $updateUnit->execute([':unit_id' => $unit_id]);

            $paymentStmt = $db->prepare("INSERT INTO payments 
                (tenant_id, unit_id, amount, tenant_deposit, amount_payable, balance, payment_method, phone_number, transaction_reference_number, status) 
                VALUES (:tenant_id, :unit_id, :amount, :tenant_deposit, :amount_payable, :balance, :payment_method, :phone_number, :transaction_ref, 'paid')");
            
            $paymentStmt->execute([
                ':tenant_id' => $tenant_id,
                ':unit_id' => $unit_id,
                ':amount' => $amount_paid,
                ':tenant_deposit' => $tenant_deposit,
                ':amount_payable' => $amount_payable,
                ':balance' => $balance,
                ':payment_method' => $payment_method,
                ':phone_number' => $phone_number,
                ':transaction_ref' => $transaction_ref
            ]);

            $db->commit();
            unset($_SESSION['registration']);
            $successMessage = "Tenant Has Been Successsully Registered!";

        } catch (Exception $e) {
            $db->rollBack();
            $errorMessage = "Transaction Failed: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please complete all mandatory fields.";
    }
}

$units = [];
try {
    $unitStmt = $db->prepare("SELECT 
            u.unit_id, 
            u.unit_name, 
            u.monthly_rent, 
            u.unit_deposit,
            COALESCE(SUM(s.price), 0) AS total_service_cost
        FROM units u
        INNER JOIN blocks b ON u.block_id = b.block_id
        INNER JOIN manager_properties mp ON b.property_id = mp.property_id
        LEFT JOIN service_property sp ON b.property_id = sp.property_id
        LEFT JOIN services s ON sp.service_id = s.service_id
        WHERE mp.manager_id = :manager_id AND u.status = 'available'
        GROUP BY u.unit_id ORDER BY u.unit_name ASC");
    $unitStmt->execute([':manager_id' => $manager_id]);
    $units = $unitStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Data loading error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Step 4: Move-In & Payments</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container" style="max-width:700px; margin:40px auto;">
        <h2>Step 4: Move-In & Payments</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger mb-3"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success mb-3"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <a href="managersdashboard.php" class="btn btn-primary">Back To Dashboard</a>
        <?php else: ?>
            <form method="post" action="tpayments.php">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="tenant_id" value="<?php echo htmlspecialchars($tenant_id ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                <div class="form-group mb-3">
                    <label>Tenant Name</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($tenant_name, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>

                <div class="form-group mb-3">
                    <label>Select Unit *</label>
                    <select id="unit_id" name="unit_id" class="form-select" required>
                        <option value="">-- Select Available Unit --</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?php echo (int)$u['unit_id']; ?>" 
                                    data-rent="<?php echo (float)$u['monthly_rent']; ?>"
                                    data-deposit="<?php echo (float)$u['unit_deposit']; ?>"
                                    data-services="<?php echo (float)$u['total_service_cost']; ?>"
                                    <?php echo ($selected_unit_id == $u['unit_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['unit_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>Moving-In Date *</label>
                    <input type="datetime-local" name="movingin_date" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Monthly Rent</label>
                        <input type="number" step="0.01" id="monthly_rent" name="monthly_rent" class="form-control" readonly required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Unit Deposit</label>
                        <input type="number" step="0.01" id="tenant_deposit" name="tenant_deposit" class="form-control" readonly required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Service Cost</label>
                        <input type="number" step="0.01" id="service_cost" name="service_cost" class="form-control" readonly required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Total Amount Payable</label>
                        <input type="number" step="0.01" id="amount_payable" name="amount_payable" class="form-control fw-bold" readonly required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Amount Paid *</label>
                        <input type="number" step="0.01" min="0" id="amount_paid" name="amount_paid" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Remaining Balance</label>
                        <input type="number" step="0.01" id="balance" name="balance" class="form-control fw-bold" readonly required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Payment Method *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="mpesa">M-Pesa</option>
                            <option value="bank transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($phone_num, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Transaction Ref / Receipt No</label>
                        <input type="text" name="transaction_reference_number" class="form-control" placeholder="e.g. QKH789234">
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-lg w-100 mt-2">Complete Registration</button>
            </form>
        <?php endif; ?>
    </div>
    <script src="movein.js"></script>
</body>
</html>