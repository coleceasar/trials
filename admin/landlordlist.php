<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$owners = [];
try {
    $query = "SELECT l.owner_id,
                     l.name,
                     l.username,
                     l.phone_number,
                     lp.status,
                     GROUP_CONCAT(DISTINCT p.property_name SEPARATOR ', ') AS assigned_properties
              FROM landlords l
              LEFT JOIN landlord_properties lp ON l.owner_id = lp.owner_id
              LEFT JOIN property p ON lp.property_id = p.property_id
              GROUP BY l.owner_id, l.name, l.phone_number, lp.status
              ORDER BY l.name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $owners = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Error fetching owner list: ' . $e->getMessage();
    $owners = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Owner List</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
</head>
<body class="dashboard-page">
    <main class="main-content">
        <section class="welcome-card">
            <div>
                <p class="eyebrow">Admin Portal</p>
                <h1 class="heading">All Owners</h1>
            </div>
        </section>

        <section class="action-card">
            <div class="page-actions mb-3" style="display: flex; gap: 10px;">
                <a href="admindashboard.php" class="btn-primary">Back to Dashboard</a>
                <?php if (!empty($owners)): ?>
                    <button type="button" class="btn-secondary" onclick="exportTableToExcel('ownersTable', 'Owners_List')">
                        Export to Excel
                    </button>
                <?php endif; ?>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <?php if (empty($owners)): ?>
                <p>No owners found in the system.</p>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table id="ownersTable" class="tenant-table table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Assigned Property</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($owners as $owner): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($owner['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($owner['phone_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($owner['assigned_properties'] ?? 'None'); ?></td>
                                    <td><?php echo htmlspecialchars($owner['status'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
    function exportTableToExcel(tableID, filename = '') {
        const table = document.getElementById(tableID);
        
        // Convert the HTML table to a workbook object
        const workbook = XLSX.utils.table_to_book(table, { sheet: "Landlords" });
        
        // Generate name and trigger download
        filename = filename ? filename + '.xlsx' : 'excel_data.xlsx';
        XLSX.writeFile(workbook, filename);
    }
    </script>
<script src="../components/list-filter.js" defer></script>
</body>
</html>