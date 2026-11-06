<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$errorMessage = '';
$successMessage = '';

// Handle status update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $manager_id = filter_input(INPUT_POST, 'manager_id', FILTER_VALIDATE_INT);
    $new_status = filter_input(INPUT_POST, 'status', FILTER_DEFAULT);

    // Validate inputs
    if ($manager_id && in_array($new_status, ['Active', 'Inactive'], true)) {
        try {
            $updateQuery = "UPDATE manager SET status = :status WHERE manager_id = :manager_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':status', $new_status, PDO::PARAM_STR);
            $updateStmt->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);

            if ($updateStmt->execute()) {
                $successMessage = "Status updated successfully.";
            } else {
                $errorMessage = "Failed to update status.";
            }
        } catch (PDOException $e) {
            $errorMessage = 'Database error updating status: ' . $e->getMessage();
        }
    } else {
        $errorMessage = "Invalid manager ID or status provided.";
    }
}

// Fetch managers list
$managers = [];
try {
    $query = "SELECT m.manager_id,
                     m.name,
                     m.username,
                     m.email,
                     m.phone_number,
                     m.status,
                     GROUP_CONCAT(DISTINCT p.property_name SEPARATOR ', ') AS assigned_properties
              FROM manager m
              LEFT JOIN manager_properties mp ON m.manager_id = mp.manager_id
              LEFT JOIN property p ON mp.property_id = p.property_id
              GROUP BY m.manager_id, m.name, m.username, m.email, m.phone_number, m.status
              ORDER BY m.name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Error fetching manager list: ' . $e->getMessage();
    $managers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manager List</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="dashboard-page">
    <main class="main-content">
        <section class="welcome-card">
            <div>
                <p class="eyebrow">Admin Portal</p>
                <h1 class="heading">All Managers</h1>
            </div>
        </section>

        <section class="action-card">
        <div class="page-actions mb-3">
                <a href="admindashboard.php" class="btn-primary">Back to Dashboard</a>
            <div class="page-actions mb-3" style="display: flex; gap: 10px;">
            <?php if (!empty($managers)): ?>
             <button type="button" class="btn-secondary" onclick="exportTableToExcel('managersTable', 'Managers_List')">
                Export to Excel
            </button>
            <?php endif; ?>
            </div>
        </div>
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if (empty($managers)): ?>
                <p>No managers found in the system.</p>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="tenant-table table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Assigned Property</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($managers as $manager): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($manager['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($manager['username'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($manager['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($manager['phone_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($manager['assigned_properties'] ?? 'None'); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($manager['status'] === 'Active') ? 'badge-success' : 'badge-secondary'; ?>">
                                            <?php echo htmlspecialchars($manager['status'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
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
        const workbook = XLSX.utils.table_to_book(table, { sheet: "Managers" });
        
        // Generate name and trigger download
        filename = filename ? filename + '.xlsx' : 'excel_data.xlsx';
        XLSX.writeFile(workbook, filename);
    }
    </script>
<script src="../components/list-filter.js" defer></script>
</body>
</html>