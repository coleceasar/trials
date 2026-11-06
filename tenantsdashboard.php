<?php
session_start();
include_once 'config.php';

if (!isset($_SESSION['tenant_username'])) {
    header('Location: tregistration.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$username = $_SESSION['tenant_username'];
$tenant = null;

try {
    $query = "SELECT first_name, last_name, phone_number, id_number, floor_no, username, unit_id, block_id FROM tenants WHERE username = :username LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tenant = null;
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
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <header class="topbar">
            <div class="brand">MAKAO Tenant Portal</div>
            <div class="topbar-actions">
                <span>Welcome, <?php echo htmlspecialchars($tenant['first_name'] . ' ' . $tenant['last_name']); ?></span>
                <a href="logout.php" class="topbar-link">Logout</a>
            </div>
        </header>

        <div class="dashboard-grid">
            <aside class="sidebar">
                <div class="sidebar-heading">Tenant Menu</div>
                <nav>
                    <a href="#overview">Overview</a>
                    <a href="#profile">Profile</a>
                    <a href="#payments">Payments</a>
                    <a href="#requests">Maintenance</a>
                    <a href="#support">Support</a>
                </nav>
            </aside>

            <main class="main-content">
                <section class="welcome-card" id="overview">
                    <div>
                        <p class="eyebrow">Tenant dashboard</p>
                        <h1>Hello <?php echo htmlspecialchars($tenant['first_name']); ?></h1>
                        <p>Manage your unit, pay rent, and submit maintenance requests from one place.</p>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <span>Unit</span>
                            <strong><?php echo htmlspecialchars($tenant['block_id'] . ' / ' . $tenant['unit_id']); ?></strong>
                        </div>
                        <div class="stat-card">
                            <span>Floor</span>
                            <strong><?php echo htmlspecialchars($tenant['floor_no']); ?></strong>
                        </div>
                    </div>
                </section>

                <section class="card-row">
                    <article class="action-card" id="profile">
                        <h2>My Profile</h2>
                        <p>Name: <?php echo htmlspecialchars($tenant['first_name'] . ' ' . $tenant['last_name']); ?></p>
                        <p>Phone: <?php echo htmlspecialchars($tenant['phone_number']); ?></p>
                        <p>ID Number: <?php echo htmlspecialchars($tenant['id_number']); ?></p>
                        <a href="#" class="btn-primary">View full profile</a>
                    </article>
                    <article class="action-card" id="payments">
                        <h2>Rent payments</h2>
                        <p>Track payment history, due dates, and get reminders for upcoming rent.</p>
                        <a href="#" class="btn-primary">Pay rent</a>
                    </article>
                </section>

                <section class="card-row">
                    <article class="action-card" id="requests">
                        <h2>Maintenance</h2>
                        <p>Submit a request if something needs fixing in your unit.</p>
                        <a href="#request-form" class="btn-primary">Submit request</a>
                    </article>
                    <article class="action-card" id="support">
                        <h2>Support</h2>
                        <p>Need help? Reach out to property management with one click.</p>
                        <a href="#" class="btn-primary">Contact support</a>
                    </article>
                </section>

                <section class="widget-panel" id="request-form">
                    <div class="widget-header">
                        <h2>Maintenance request</h2>
                        <p>Describe the issue and our team will respond quickly.</p>
                    </div>
                    <form class="widget-form" method="post" action="#">
                        <input type="text" name="issue_subject" placeholder="Subject" required>
                        <textarea name="issue_details" rows="4" placeholder="Describe your issue" required></textarea>
                        <button type="submit" class="btn-primary">Send request</button>
                    </form>
                </section>
            </main>
        </div>
    </div>
</body>
</html>