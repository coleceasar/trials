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
$blocks = [];

try {
    // Filter blocks by the logged-in manager's assigned properties
    $query = "SELECT 
                b.block_id,  
                b.block_name,
                b.number_of_floors,
                p.property_id,
                p.property_name
              FROM blocks b
              INNER JOIN property p ON b.property_id = p.property_id
              INNER JOIN manager_properties mp ON p.property_id = mp.property_id
              WHERE mp.manager_id = :manager_id
              ORDER BY p.property_name ASC, b.block_name ASC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':manager_id', $_SESSION['manager_id'], PDO::PARAM_INT);
    $stmt->execute();
    $blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($blocks)) {
        $message = 'No active blocks found for your assigned properties.';
    }
} catch (PDOException $e) {
    $message = 'Unable to load blocks: ' . $e->getMessage();
}

// Added property_name so managers can see which property each block belongs to
$columns = [ 'property_name', 'block_name', 'number_of_floors' ];
$columnLabels = [
    'block_name'       => 'Block Name',
    'number_of_floors' => 'Number of Floors',
    'property_name'    => 'Property Name'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Property Blocks</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <main class="main-content">
            <section class="welcome-card">
                <div>
                    <p class="eyebrow">Manager tools</p>
                    <h1>Assigned Property Blocks</h1>
                </div>
            </section>

            <section class="action-card">
                <div class="page-actions mb-3">
                    <a href="managersdashboard.php" class="btn-primary">Back to Dashboard</a>
                </div>

                <?php if ($message !== '' && empty($blocks)): ?>
                    <p style="color:#b91c1c; font-weight:700;"><?php echo htmlspecialchars($message); ?></p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="blocks-table table table-striped">
                            <thead>
                                <tr>
                                    <?php foreach ($columns as $column): ?>
                                        <th><?php echo htmlspecialchars($columnLabels[$column] ?? $column); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blocks as $block): ?>
                                    <tr>
                                        <?php foreach ($columns as $column): ?>
                                            <td><?php echo htmlspecialchars((string)($block[$column] ?? '')); ?></td>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../components/list-filter.js" defer></script>
</body>
</html>