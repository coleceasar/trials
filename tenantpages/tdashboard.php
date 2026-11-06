<?php
session_start();

include_once '../config.php';

if (empty($_SESSION['tenant_username'])) {
    header('Location: tenant_login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$username = trim($_SESSION['tenant_username']);
$tenant = null;
$paymentHistory = [];

try {
    $query = "SELECT t.*, 
                u.unit_name, u.unit_type, u.unit_deposit, u.monthly_rent,p.property_name
              FROM tenants t
              LEFT JOIN units u ON t.unit_id = u.unit_id
              LEFT JOIN blocks b ON u.block_id = b.block_id
              LEFT JOIN property p ON b.property_id = p.property_id
              WHERE t.username = :username 
              LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tenant = null;
}

if ($tenant) {
    $tenantId = $tenant['tenant_id'] ?? null;
    try {
        if ($tenantId !== null) {
            $historyStmt = $db->prepare("SELECT * FROM payments WHERE tenant_id = :tenant_id ORDER BY id DESC LIMIT 5");
            $historyStmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
            $historyStmt->execute();
            $paymentHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $paymentHistory = [];
    }
}

if (!$tenant) {
    echo 'Tenant record not found. Please register again.';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../payments.css">
</head>

<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/theader.php'; ?>
        <div class="dashboard-grid">
          <?php include '../components/tsidebar.php'; ?>

            <main class="main-content">
                <section class="welcome-card" id="overview">
                    <div>
                        <p class="eyebrow">Tenant dashboard</p>
                        <h1>Hello <?php echo htmlspecialchars($tenant['name']); ?></h1>
                    </div>
                </section>

                <section class="card-row">
                    <article class="action-card" id="profile">
                        <h2>My Profile</h2>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($tenant['name']); ?></p>
                        <p><strong>Phone: </strong><?php echo htmlspecialchars($tenant['phone_number']); ?></p>
                        <p><strong>ID Number:</strong> <?php echo htmlspecialchars($tenant['national_id']); ?></p>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($tenant['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($tenant['email']); ?></p>
                    </article>
                    <article class="action-card" id="property-details">
                        <h2>Property Details</h2>
                        <p><strong>Property Name: </strong><?php echo htmlspecialchars($tenant['property_name'] ?? 'N/A'); ?></p>
                        <p><strong> Unit Name: </strong> <?php echo htmlspecialchars($tenant['unit_name'] ?? 'N/A'); ?></p>
                        <p><strong>Unit type: </strong> <?php echo htmlspecialchars($tenant['unit_type'] ?? 'N/A'); ?></p>
                        <p><strong>Unit Deposit: </strong> <?php echo htmlspecialchars($tenant['unit_deposit'] ?? '0.00'); ?></p>
                        <p><strong>Rent:</strong> <?php echo htmlspecialchars($tenant['monthly_rent'] ?? '0.00'); ?></p>
                    </article>
                    <article class="action-card" id="payments">
                        <h2>Rent payments</h2>
                        <p>Track payment history, due dates, and get reminders for upcoming rent.</p>
                        <a href="paymenthistory.php" class="btn-primary">Payment History</a>
                    </article>
                </section>
            </main>
        </div>
    </div>
</body>
</html>