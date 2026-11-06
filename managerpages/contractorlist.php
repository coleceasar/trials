<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_id'])) {
    header('Location: managerlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$providers = [];

try {
    // Fetch service providers scoped to the logged-in manager
    $query = "SELECT 
                sp.provider_name,  
                sp.role,
                sp.phone,
                sp.onboarding_date
              FROM service_provider sp
              WHERE sp.manager_id = :manager_id
              ORDER BY sp.provider_name ASC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':manager_id', $_SESSION['manager_id'], PDO::PARAM_INT);
    $stmt->execute();
    $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($providers)) {
        $message = 'No active service providers found for your managed properties.';
    }
} catch (PDOException $e) {
    $message = 'Unable to load service providers: ' . $e->getMessage();
}

$columns = ['provider_name', 'role', 'phone'];
$columnLabels = [
    'provider_name'   => 'Provider / Company Name',
    'role'            => 'Specialty / Role',
    'phone'           => 'Phone Number'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Providers List</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/mheader.php'; ?>
        <main class="main-content">
            <section class="welcome-card my-3">
                <div>
                    <p class="eyebrow text-muted mb-1">Manager Tools</p>
                    <h1>Service Providers List</h1>
                </div>
            </section>

            <section class="action-card">
                <div class="page-actions mb-3 d-flex gap-2">
                    <a href="regserviceprovider.php" class="btn btn-success">+ Onboard New Provider</a>
                    <a href="managersdashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>

                <?php if ($message !== '' && empty($providers)): ?>
                    <p class="alert alert-warning text-center fw-bold"><?php echo htmlspecialchars($message); ?></p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <?php foreach ($columns as $column): ?>
                                        <th><?php echo htmlspecialchars($columnLabels[$column] ?? $column); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($providers as $provider): ?>
                                    <tr>
                                        <?php foreach ($columns as $column): ?>
                                            <td>
                                                <?php 
                                                if ($column === 'onboarding_date' && !empty($provider[$column])) {
                                                    echo htmlspecialchars(date('M d, Y', strtotime($provider[$column])));
                                                } else {
                                                    echo htmlspecialchars((string)($provider[$column] ?? 'N/A'));
                                                }
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../components/list-filter.js" defer></script>
</body>
</html>