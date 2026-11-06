<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: adminlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$isAdminView = !empty($_SESSION['admin_id']);
$backLink = $isAdminView ? 'admindashboard.php' : 'ownerdashboard.php';

if ($isAdminView) {
    $query = "SELECT 
                c.complaint_id,
                COALESCE(t.username, 'Unknown') AS tenant_name,
                COALESCE(u.unit_name, 'N/A') AS unit_name,
                COALESCE(p.property_name, 'N/A') AS property_name,
                c.complaint_type,
                c.priority,
                c.status,
                c.subject,
                c.description,
                c.created_at,
                COALESCE(f.manager_reply, '') AS manager_reply
              FROM complaints c
              LEFT JOIN tenant_profile t ON c.tenant_id = t.tenant_id
              LEFT JOIN units u ON c.unit_id = u.unit_id
              LEFT JOIN blocks b ON u.block_id = b.block_id
              LEFT JOIN property p ON b.property_id = p.property_id
              LEFT JOIN feedback f ON c.complaint_id = f.complaint_id
              ORDER BY c.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $username = trim($_SESSION['admin_id']);

    $stmt = $db->prepare("SELECT owner_id FROM landlords WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $landlord = $stmt->fetch(PDO::FETCH_ASSOC);
    $landlordId = $landlord['owner_id'] ?? null;

    if (!$landlordId) {
        echo 'Owner record not found.';
        exit;
    }

    $query = "SELECT 
                c.complaint_id,
                COALESCE(t.username, 'Unknown') AS tenant_name,
                COALESCE(u.unit_name, 'N/A') AS unit_name,
                COALESCE(p.property_name, 'N/A') AS property_name,
                c.complaint_type,
                c.priority,
                c.status,
                c.subject,
                c.description,
                c.created_at,
                COALESCE(f.manager_reply, '') AS manager_reply
              FROM complaints c
              LEFT JOIN tenant_profile t ON c.tenant_id = t.tenant_id
              LEFT JOIN units u ON c.unit_id = u.unit_id
              LEFT JOIN blocks b ON u.block_id = b.block_id
              LEFT JOIN property p ON b.property_id = p.property_id
              LEFT JOIN landlord_properties lp ON u.block_id = lp.property_id
              LEFT JOIN feedback f ON c.complaint_id = f.complaint_id
              WHERE lp.owner_id = :landlord_id
              ORDER BY c.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([':landlord_id' => $landlordId]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Property Complaints</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="complaints.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Properties Complaints & Enquiries</h2>
            <div class="page-actions">
                <a href="<?= htmlspecialchars($backLink) ?>" class="btn-primary">Back to dashboard</a>
            </div>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="card empty-state">
                <p>No complaints have been submitted for your properties yet.</p>
            </div>
        <?php else: ?>
            <div class="card table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="col-property">Property</th>
                            <th class="col-tenant">Tenant Name</th>
                            <th class="col-unit">Unit Name</th>
                            <th class="col-type">Type</th>
                            <th class="col-priority">Priority</th>
                            <th class="col-status">Status</th>
                            <th class="col-subject">Subject</th>
                            <th class="col-description">Description</th>
                            <th class="col-reply">Manager Reply</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <?php 
                            $status = strtolower(trim($ticket['status'] ?? 'pending'));
                            $badgeClass = 'badge-pending';
                            if ($status === 'replied') {
                                $badgeClass = 'badge-replied';
                            } elseif ($status === 'under review') {
                                $badgeClass = 'badge-review';
                            }
                            ?>
                            <tr>
                                <td class="col-property"><?= htmlspecialchars($ticket['property_name'] ?? 'N/A') ?></td>
                                <td class="col-tenant"><?= htmlspecialchars($ticket['tenant_name'] ?? 'Unknown') ?></td>
                                <td class="col-unit"><?= htmlspecialchars($ticket['unit_name'] ?? 'N/A') ?></td>
                                <td class="col-type"><?= htmlspecialchars($ticket['complaint_type'] ?? 'N/A') ?></td>
                                <td class="col-priority"><?= htmlspecialchars(ucfirst($ticket['priority'] ?? 'N/A')) ?></td>
                                <td class="col-status">
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars(ucfirst($status)) ?>
                                    </span>
                                </td>
                                <td class="col-subject"><?= htmlspecialchars($ticket['subject'] ?? '') ?></td>
                                <td class="col-description"><?= nl2br(htmlspecialchars($ticket['description'] ?? '')) ?></td>
                                <td class="col-reply">
                                    <?php if (!empty($ticket['manager_reply'])): ?>
                                        <?= nl2br(htmlspecialchars($ticket['manager_reply'])) ?>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; italic;">—</span>
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