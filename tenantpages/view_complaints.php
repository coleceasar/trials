<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['tenant_username'])) {
    header('Location: tenantlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$username = $_SESSION['tenant_username'];
$stmt = $db->prepare(
    "SELECT tenant_id 
    FROM tenant_profile 
    WHERE username = :username LIMIT 1"
);
$stmt->execute([':username' => $username]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);
$tenantId = $tenant['tenant_id'] ?? null;

$complaints = [];
if ($tenantId) {
    $stmt = $db->prepare(
        "SELECT c.*,
        COALESCE(f.manager_reply, '') AS manager_reply 
        FROM complaints c 
        LEFT JOIN feedback f ON c.complaint_id = f.complaint_id 
        WHERE c.tenant_id = :tenant_id 
        ORDER BY c.created_at DESC"
    );
    $stmt->execute([':tenant_id' => $tenantId]);
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Complaints</title>
    <link rel="stylesheet" href="complaints.css">
</head>
<body>
    <div class="container">
        <h2>My Complaints & Enquiries</h2>
        <div class="page-actions">
            <a href="../tenantpages/tdashboard.php" class="btn-primary">Back to dashboard</a>
        </div>

        <?php if (empty($complaints)): ?>
            <p style="margin-top:20px;">No complaints have been submitted yet.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="complaints-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Manager Reply</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $item): ?>
                            <tr>
                                <td>
                                    <?= isset($item['created_at']) ? htmlspecialchars(date('M d, Y', strtotime($item['created_at']))) : 'N/A' ?>
                                </td>
                                <td><strong><?= htmlspecialchars($item['subject'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($item['complaint_type'] ?? '') ?></td>
                                <td><?= htmlspecialchars(ucfirst($item['priority'] ?? 'Low')) ?></td>
                                <td>
                                    <span class="status-badge">
                                        <?= htmlspecialchars(ucfirst($item['status'] ?? 'pending')) ?>
                                    </span>
                                </td>
                                <td><?= nl2br(htmlspecialchars($item['description'] ?? '')) ?></td>
                                <td>
                                    <?php if (!empty($item['manager_reply'])): ?>
                                        <?= nl2br(htmlspecialchars($item['manager_reply'])) ?>
                                    <?php else: ?>
                                        <em style="color:#888;">No reply yet.</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<script src="../components/list-filter.js" defer></script>
</body>
</html>