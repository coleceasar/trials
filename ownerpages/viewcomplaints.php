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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="complaints.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>My Complaints & Enquiries</h2>
            <div class="page-actions">
                <a href="ownerdashboard.php" class="btn-primary">Back to dashboard</a>
            </div>
        </div>

        <?php if (empty($complaints)): ?>
            <div class="card empty-state" style="margin-top: 15px;">
                <p>No complaints have been submitted yet.</p>
            </div>
        <?php else: ?>
            <div class="card table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="col-type">Type</th>
                            <th class="col-priority">Priority</th>
                            <th class="col-status">Status</th>
                            <th class="col-subject">Subject</th>
                            <th class="col-description">Description</th>
                            <th class="col-reply">Manager Reply</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $item): ?>
                            <?php 
                            $status = strtolower(trim($item['status'] ?? 'pending'));
                            $badgeClass = 'badge-pending';
                            if ($status === 'replied') {
                                $badgeClass = 'badge-replied';
                            } elseif ($status === 'under review') {
                                $badgeClass = 'badge-review';
                            }
                            ?>
                            <tr>
                                <td class="col-type"><?= htmlspecialchars($item['complaint_type'] ?? 'N/A') ?></td>
                                <td class="col-priority"><?= htmlspecialchars(ucfirst($item['priority'] ?? 'Low')) ?></td>
                                <td class="col-status">
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars(ucfirst($status)) ?>
                                    </span>
                                </td>
                                <td class="col-subject"><?= htmlspecialchars($item['subject'] ?? '') ?></td>
                                <td class="col-description"><?= nl2br(htmlspecialchars($item['description'] ?? '')) ?></td>
                                <td class="col-reply">
                                    <?php if (!empty($item['manager_reply'])): ?>
                                        <?= nl2br(htmlspecialchars($item['manager_reply'])) ?>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-style: italic;">No reply yet.</span>
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