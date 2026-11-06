<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['tenant_username'])) {
    header('Location: tenantlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$tenantId = null;
$unitId = null;
$errorMessage = '';

if ($db) {
    $stmt = $db->prepare(
        "SELECT tp.tenant_id, t.unit_id
         FROM tenant_profile tp
         LEFT JOIN tenants t ON tp.tenant_id = t.tenant_id
         WHERE tp.username = :username
         LIMIT 1"
    );
    $stmt->execute([':username' => $_SESSION['tenant_username']]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tenant) {
        $tenantId = $tenant['tenant_id'] ?? null;
        $unitId = $tenant['unit_id'] ?? null;
    } else {
        $errorMessage = 'Unable to locate your tenant profile.';
    }
} else {
    $errorMessage = 'Database connection failed.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Communication</title>
    <link rel="stylesheet" href="complaints.css">
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-heading">Manager Menu</div>
                <nav>
                 <a href="../tenantpages/tdashboard.php">Dashboard</a>
                </nav>
         </aside>
        <h2>Submit Complaint, Suggestion, or Feedback</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['communication_message'])): ?>
            <div class="message <?= htmlspecialchars($_SESSION['communication_message']['type']) ?>">
                <?= htmlspecialchars($_SESSION['communication_message']['text']) ?>
            </div>
            <?php unset($_SESSION['communication_message']); ?>
        <?php endif; ?>

        <?php if ($tenantId && $unitId): ?>
            <form id="complaintForm" action="process_communication.php" method="POST">
                <input type="hidden" name="unit_id" value="<?php echo htmlspecialchars((int)$unitId); ?>">
                <input type="hidden" name="tenant_id" value="<?php echo htmlspecialchars((int)$tenantId); ?>">

                <label for="complainttype">Complaint Type:</label>
                <select name="complaint_type" id="complainttype" required>
                    <option value="General Information" selected>General Information</option>
                    <option value="Request">Request</option>
                    <option value="Complaint">Complaint</option>
                    <option value="Inquiry">Inquiry</option>
                    <option value="Suggestion">Suggestion</option>
                    <option value="others">Others</option>
                </select>

                <label for="priority">Priority:</label>
                <select name="priority" id="priority" required>
                    <option value="" disabled selected>Select priority...</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>

                <label for="subject">Subject:</label>
                <input type="text" name="subject" id="subject" required>

                <label for="description">Description:</label>
                <textarea name="description" id="description" rows="5" required></textarea>

                <button type="button" id="openModalBtn">Submit</button>
            </form>
        <?php else: ?>
            <p>Please log in properly or contact support if your complaint form is not available.</p>
        <?php endif; ?>

        <div style="margin-top: 20px;">
            <a href="view_complaints.php">View My Submitted Complaints</a>
        </div>
    </div>

    <!-- Confirmation Modal Popup -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">Confirm Details</div>
            <div class="modal-body">
                <p>Please review your information before submitting:</p>
                <p><strong>Type:</strong> <span id="confirmType"></span></p>
                <p><strong>Priority:</strong> <span id="confirmPriority"></span></p>
                <p><strong>Subject:</strong> <span id="confirmSubject"></span></p>
                <p><strong>Description:</strong> <span id="confirmDescription"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelBtn">Edit</button>
                <button type="button" class="btn-confirm" id="confirmSubmitBtn">Confirm & Submit</button>
            </div>
        </div>
    </div>

    <!-- External JavaScript File -->
    <script src="complaint.js"></script>
</body>
</html>