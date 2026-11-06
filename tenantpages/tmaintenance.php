<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config.php';

$database = new Database();
$db = $database->getConnection();

$tenant = null;
if (!empty($_SESSION['tenant_username'])) {
    try {
        $query = "SELECT * FROM tenants WHERE username = :username LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $_SESSION['tenant_username']);
        $stmt->execute();
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $tenant = null;
    }
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $details = trim($_POST['description'] ?? '');
    $priority = trim($_POST['priority'] ?? 'Medium');

    if ($details !== '') {
        if (empty($_SESSION['tenant_username'])) {
            $message = 'Unable to identify tenant. Please log in again.';
        } else {
            $tenantUsername = trim($_SESSION['tenant_username']);
            try {
                $query = "SELECT * FROM tenants WHERE username = :username LIMIT 1";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':username', $tenantUsername);
                $stmt->execute();
                $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $tenant = null;
            }

            if (empty($tenant)) {
                $message = 'Tenant account not found. Please log in again.';
            } else {
                $tenantId = $tenant['tenant_id'] ?? $tenant['id'] ?? null;
                $unitId = $tenant['unit_id'] ?? null;

                if ($tenantId === null) {
                    $message = 'Your tenant account does not have an ID.';
                } else {
                    $allowedPriorities = ['Low', 'Medium', 'High','Urgent/Emergency'];
                    if (!in_array($priority, $allowedPriorities, true)) {
                        $priority = 'Medium';
                    }

                    $description = $details;

                    try {
                        $stmt = $db->prepare("INSERT INTO maintenance (unit_id, tenant_id, description, priority) VALUES (?, ?, ?, ?)");
                        $ok = $stmt->execute([$unitId, $tenantId, $description, $priority]);

                        if ($ok) {
                            $message = 'Your maintenance request has been sent successfully.';
                        } else {
                            $message = 'Unable to submit maintenance request. Please try again.';
                        }
                    } catch (PDOException $e) {
                        $message = 'Database error: ' . $e->getMessage();
                    }
                }
            }
        }
    } else {
        $message = 'Please provide details for your maintenance request.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include __DIR__ . '/../components/theader.php'; ?>
        <div class="dashboard-grid">
          <?php include __DIR__ . '/../components/tsidebar.php'; ?>
          <main class="main-content">
          <section class="card-row">
                    <article class="action-card" id="requests">
                        <h2>Maintenance</h2>
                        <p>Submit a request if something needs fixing in your unit.</p>
                    </article>
                </section>
                          <section class="card-row">
                    <article class="action-card" id="requests">
                        <h2>Submited Maintenance Requests</h2>
                        <p>View and manage your submitted maintenance requests.</p>
                        <a href="dev.php" class="btn-primary">View Maintenance Requests</a>

                    </article>
                </section>

                <section class="widget-panel" id="request-form">
                    <div class="widget-header">
                        <h2>Maintenance request</h2>
                        <p>Describe the issue and our team will respond quickly.</p>
                    </div>
                    <?php if (!empty($message)): ?>
                        <p style="margin-bottom: 15px; color: #0f766e; font-weight: 600;"><?php echo htmlspecialchars($message); ?></p>
                    <?php endif; ?>
                    <form class="widget-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <h3>Select the Priority:</h3>
                        <select name="priority" required>
                            <option value="" selected></option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                        <textarea name="description" rows="4" placeholder="Description" required></textarea>
                        <button type="submit" class="btn-primary">Send request</button>
                    </form>
                </section>
                </main>
        </div>
    </div>
</body>
</html>