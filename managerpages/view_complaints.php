<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['manager_id'])) {
    header('Location: managerlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_submit'])) {
    $complaintId = (int)($_POST['complaint_id'] ?? 0);
    $reply = trim($_POST['reply'] ?? '');
    
    if ($complaintId > 0 && $reply !== '') {
        $stmt = $db->prepare(
            "SELECT tenant_id
             FROM complaints
             WHERE complaint_id = :complaint_id AND manager_id = :manager_id
             LIMIT 1"
        );
        $stmt->execute([
            ':complaint_id' => $complaintId,
            ':manager_id' => (int)$_SESSION['manager_id'],
        ]);
        $complaint = $stmt->fetch(PDO::FETCH_ASSOC);
        $tenantId = $complaint['tenant_id'] ?? null;
        
        if ($tenantId) {
            $stmt = $db->prepare(
                "UPDATE complaints
                 SET status = 'replied', updated_at = CURDATE()
                 WHERE complaint_id = :complaint_id AND manager_id = :manager_id"
            );
            $stmt->execute([
                ':complaint_id' => $complaintId,
                ':manager_id' => (int)$_SESSION['manager_id'],
            ]);
            
            $stmt = $db->prepare(
                "INSERT INTO feedback (complaint_id, tenant_id, manager_id, manager_reply)
                 VALUES (:complaint_id, :tenant_id, :manager_id, :reply_insert)
                 ON DUPLICATE KEY UPDATE manager_reply = :reply_update"
            );
            $stmt->execute([
                ':complaint_id' => $complaintId,
                ':tenant_id' => $tenantId,
                ':manager_id' => (int)$_SESSION['manager_id'],
                ':reply_insert' => $reply,
                ':reply_update' => $reply,
            ]);
            $message = 'Reply saved successfully.';
        } else {
            $message = 'Complaint not found.';
        }
    } else {
        $message = 'Reply cannot be empty.';
    }
}

$query = 
    "SELECT c.complaint_id, c.tenant_id,
    COALESCE(t.name,'Unknown') AS name,
    COALESCE(t.phone_number,'') AS tenant_phone,
    COALESCE(t.email,'') AS tenant_email,
    COALESCE(u.unit_name,'N/A') AS unit_name,
    c.complaint_type, c.priority, c.status, c.subject, c.description, c.created_at,
    COALESCE(f.manager_reply,'') AS manager_reply 
    FROM complaints c 
    LEFT JOIN tenants t ON c.tenant_id = t.tenant_id 
    LEFT JOIN units u ON c.unit_id = u.unit_id 
    LEFT JOIN feedback f ON c.complaint_id = f.complaint_id 
    WHERE c.manager_id = :manager_id 
    ORDER BY c.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute([':manager_id' => $_SESSION['manager_id']]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Complaints</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="complaints.css">
</head>
<body>
    <div class="card">
                <div class="card">
            <h2>Properties Complaints & Enquiries</h2>
            <div class="page-actions">
                <a href="managersdashboard.php" class="btn-primary">Back to dashboard</a>
            </div>
        </div>

        <h2>Tenant Complaints & Enquiries</h2>
        <?php if (!empty($message)): ?>
            <p class="message success"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </div>

    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th class="col-tenant">Tenant Name</th>
                    <th class="col-unit">Unit Name</th>
                    <th class="col-type">Type</th>
                    <th class="col-priority">Priority</th>
                    <th class="col-status">Status</th>
                    <th class="col-subject">Subject</th>
                    <th class="col-description">Description</th>
                    <th class="col-reply">Manager Reply</th>
                    <th class="col-actions">Send Via</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <?php
                    $phone = trim((string)($ticket['tenant_phone'] ?? ''));
                    $email = trim($ticket['tenant_email'] ?? '');
                    $whatsappNumber = '';
                    if ($phone !== '') {
                        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                        if (substr($cleanPhone, 0, 1) === '0') {
                            $whatsappNumber = '254' . substr($cleanPhone, 1);
                        } elseif (substr($cleanPhone, 0, 3) === '254') {
                            $whatsappNumber = $cleanPhone;
                        } else {
                            $whatsappNumber = $cleanPhone;
                        }
                    }
                    $status = strtolower($ticket['status'] ?? 'pending');
                    ?>
                    <tr>
                        <td class="col-tenant"><?= htmlspecialchars($ticket['name'] ?? 'Unknown') ?></td>
                        <td class="col-unit"><?= htmlspecialchars($ticket['unit_name'] ?? 'N/A') ?></td>
                        <td class="col-type"><?= htmlspecialchars($ticket['complaint_type'] ?? 'N/A') ?></td>
                        <td class="col-priority"><?= htmlspecialchars(ucfirst($ticket['priority'] ?? 'N/A')) ?></td>
                        <td class="col-status">
                            <span class="badge <?= $status === 'replied' ? 'badge-replied' : 'badge-pending' ?>">
                                <?= htmlspecialchars(ucfirst($status)) ?>
                            </span>
                        </td>
                        <td class="col-subject"><?= htmlspecialchars($ticket['subject'] ?? '') ?></td>
                        <td class="col-description"><?= nl2br(htmlspecialchars($ticket['description'] ?? '')) ?></td>
                        <td class="col-reply">
                            <div class="reply-section">
                                <?php if (!empty($ticket['manager_reply'])): ?>
                                    <div style="margin-bottom:8px; padding:8px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px;">
                                        <strong>Current Reply:</strong><br>
                                        <?= nl2br(htmlspecialchars($ticket['manager_reply'])) ?>
                                    </div>
                                <?php endif; ?>
                                <form method="POST">
                                    <input type="hidden" name="complaint_id" value="<?= (int)$ticket['complaint_id'] ?>">
                                    <textarea id="reply_<?= (int)$ticket['complaint_id'] ?>" name="reply" placeholder="Type your reply to the tenant..." required></textarea>
                                    <button type="submit" name="reply_submit" class="btn-primary" style="margin-top:6px; width:100%;"><i class="fa-solid fa-paper-plane"></i> Save Reply</button>
                                </form>
                            </div>
                        </td>
                        <td class="col-actions">
                            <div class="send-options">
                                <?php if ($whatsappNumber !== ''): ?>
                                    <a href="#" class="send-icon whatsapp" title="Send through WhatsApp" onclick="sendWhatsApp('<?= htmlspecialchars($whatsappNumber, ENT_QUOTES) ?>', <?= (int)$ticket['complaint_id'] ?>); return false;"><i class="fa-brands fa-whatsapp"></i></a>
                                <?php else: ?>
                                    <span class="send-icon disabled" title="Tenant phone number not available"><i class="fa-brands fa-whatsapp"></i></span>
                                <?php endif; ?>

                                <?php if ($phone !== ''): ?>
                                    <a href="#" class="send-icon sms" title="Send SMS" onclick="sendSMS('<?= htmlspecialchars($phone, ENT_QUOTES) ?>', <?= (int)$ticket['complaint_id'] ?>); return false;"><i class="fa-solid fa-comment-sms"></i></a>
                                <?php else: ?>
                                    <span class="send-icon disabled" title="Tenant phone number not available"><i class="fa-solid fa-comment-sms"></i></span>
                                <?php endif; ?>

                                <?php if ($email !== ''): ?>
                                    <a href="#" class="send-icon gmail" title="Open Gmail" onclick="sendGmail('<?= htmlspecialchars($email, ENT_QUOTES) ?>', <?= (int)$ticket['complaint_id'] ?>); return false;"><i class="fa-solid fa-envelope"></i></a>
                                <?php else: ?>
                                    <span class="send-icon disabled" title="Tenant email address not available"><i class="fa-solid fa-envelope"></i></span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function getReply(complaintId) {
        const textarea = document.getElementById('reply_' + complaintId);
        if (!textarea) {
            alert('Reply box not found.');
            return '';
        }
        const reply = textarea.value.trim();
        if (reply === '') {
            alert('Please type a reply before selecting WhatsApp, Gmail or SMS.');
            textarea.focus();
            return '';
        }
        return reply;
    }

    function sendWhatsApp(phone, complaintId) {
        const reply = getReply(complaintId);
        if (!reply) return;
        const message = 'Reply regarding complaint: ' + reply;
        const whatsappURL = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(message);
        window.open(whatsappURL, '_blank');
    }

    function sendGmail(email, complaintId) {
        const reply = getReply(complaintId);
        if (!reply) return;
        const subject = 'Response to Complaint';
        const body = 'Dear Tenant,\n\n' + reply + '\n\nRegards,\nProperty Management';
        const gmailURL = 'https://mail.google.com/mail/?view=cm&fs=1&to=' + encodeURIComponent(email) + '&su=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(body);
        window.open(gmailURL, '_blank');
    }

    function sendSMS(phone, complaintId) {
        const reply = getReply(complaintId);
        if (!reply) return;
        const message = 'Complaint Response: ' + reply;
        const smsURL = 'sms:' + phone + '?body=' + encodeURIComponent(message);
        window.location.href = smsURL;
    }
    </script>
<script src="../components/list-filter.js" defer></script>
</body>
</html>