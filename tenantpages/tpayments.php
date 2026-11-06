<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['tenant_username'])) {
    header('Location: tenantlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$username = trim($_SESSION['tenant_username']);
$tenant = null;
$monthlyRent = 0.00;
$tenantId = null;
$message = '';
$paymentHistory = [];

try {

    $query = "SELECT t.*, 
            u.monthly_rent,
            b.block_name,
            t.name AS tenant_name,
            u.unit_name
              FROM tenants t
              LEFT JOIN units u ON t.unit_id = u.unit_id
              LEFT JOIN blocks b ON u.block_id = b.block_id
              WHERE t.username = :username 
              LIMIT 1";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tenant = null;
}

if ($tenant) {
    $tenantId = $tenant['payments_id'] ?? $tenant['id'] ?? $tenant['tenant_id'] ?? null;
    $monthlyRent = (float)($tenant['monthly_rent'] ?? 0.00);
}

$amountDue = $monthlyRent;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $transactionReference = trim($_POST['transaction_reference_number'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $monthlyRentValue = trim($_POST['monthly_rent'] ?? '');

    if (!$paymentMethod) {
        $message = 'Please choose a payment method.';
    } elseif ($paymentMethod === 'Mpesa' && !$phoneNumber) {
        $message = 'Please enter your phone number for M-Pesa payments.';
    } elseif (!$transactionReference) {
        $message = 'Please enter a transaction reference.';
    } else {
        try {
            $phoneNumberValue = $phoneNumber !== '' ? $phoneNumber : '0';
            $insertStmt = $db->prepare("INSERT INTO payments (tenant_id, payment_method, amount, transaction_reference_number, status, phone_number) VALUES (:tenant_id, :payment_method, :amount, :transaction_reference, 'paid', :phone_number)");
            $insertStmt->bindValue(':tenant_id', $tenantId ?? null, PDO::PARAM_INT);
            $insertStmt->bindValue(':payment_method', $paymentMethod);
            $insertStmt->bindValue(':amount', $monthlyRentValue !== '' ? $monthlyRentValue : $amountDue);
            $insertStmt->bindValue(':transaction_reference', $transactionReference);
            $insertStmt->bindValue(':phone_number', $phoneNumberValue, PDO::PARAM_INT);
            $insertStmt->execute();
            $message = 'Payment completed successfully. Your transaction reference has been recorded.';
        } catch (PDOException $e) {
            $message = 'Unable to record your payment: ' . $e->getMessage();
        }
    }
}

try {
    if ($tenantId !== null) {
        $historyStmt = $db->prepare("SELECT * FROM payments WHERE tenant_id = :tenant_id ORDER BY id DESC LIMIT 5");
        $historyStmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $historyStmt->execute();
        $paymentHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $paymentHistory = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../payments.css">
</head>

<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/theader.php'; ?>
        <div class="dashboard-grid">
          <?php include '../components/tsidebar.php'; ?>

            <main class="main-content">
 <section class="welcome-card" id="overview">

    <div class="payment-container">
        <h1>Payments</h1>
        <p>Select the payment method you would like to use.</p>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="payment-summary">
                <p><strong>Tenant:</strong> <?php echo htmlspecialchars(($tenant['tenant_name'] ?? '')); ?></p>
                <p><strong>Block name:</strong> <?php echo htmlspecialchars(($tenant['block_name'] ?? '') ); ?></p>
                <p><strong>Unit name:</strong> <?php echo htmlspecialchars($tenant['unit_name'] ?? ''); ?></p>
                <p><strong>Monthly rent:</strong> KES <?php echo number_format($monthlyRent, 2); ?></p>
                <p><strong>Total amount due:</strong> <span class="amount-due">KES <?php echo number_format($amountDue, 2); ?></span></p>
            </div>
        <form id="payment-form" method="post">
            <input type="hidden" name="tenant_name" value="<?php echo htmlspecialchars($tenant['tenant_name'] ?? ''); ?>">
            <input type="hidden" name="block_name" value="<?php echo htmlspecialchars($tenant['block_name'] ?? ''); ?>">
            <input type="hidden" name="unit_name" value="<?php echo htmlspecialchars($tenant['unit_name'] ?? ''); ?>">
            <input type="hidden" name="monthly_rent" value="<?php echo number_format($monthlyRent, 2, '.', ''); ?>">
            <div class="payment-option">
                <input type="radio" id="mpesa" name="payment_method" value="Mpesa" required>
                <label for="mpesa">M-Pesa</label>
            </div>
            <div class="payment-option">
                <input type="radio" id="bank" name="payment_method" value="Bank Transfer">
                <label for="bank">Bank Transfer</label>
            </div>
            <div class="payment-option">
                <input type="radio" id="cheque" name="payment_method" value="Cheques">
                <label for="cheque">Cheques</label>
            </div>
            <div class="payment-option">
                <input type="radio" id="card" name="payment_method" value="Credit Card">
                <label for="card">Credit Card</label>
            </div>

            <div class="payment-field">
                <label for="amount">Monthly Rent</label>
                <input type="text" id="amount" name="amount" value="<?php echo number_format($monthlyRent, 2, '.', ''); ?>" readonly>
            </div>

            <div id="mpesa-fields" class="payment-field">
                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" placeholder="e.g. 0712345678">
            </div>

            <div id="transaction-wrapper" class="payment-field">
                <label for="transaction_reference_number">Transaction Reference Number</label>
                <input type="text" id="transaction_reference_number" name="transaction_reference_number" placeholder="Enter transaction reference">
            </div>

            <div id="form-message" class="message" style="display:none;"></div>

            <button type="submit">Complete Payment</button>
        </form>

        <?php if (!empty($paymentHistory)): ?>
            <h2>Recent payments</h2>
            <ul id="recent-payments-list">
                <?php foreach ($paymentHistory as $payment): ?>
                    <li>
                        <?php echo htmlspecialchars($payment['payment_method'] ?? ''); ?> - KES <?php echo number_format((float) ($payment['amount'] ?? 0), 2); ?>
                        - Ref: <?php echo htmlspecialchars($payment['transaction_reference_number'] ?? ''); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p class="back-link"><a href="../tenantpages/tdashboard.php">Back to dashboard</a></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('payment-form');
            const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
            const mpesaFields = document.getElementById('mpesa-fields');
            const transactionWrapper = document.getElementById('transaction-wrapper');
            const phoneInput = document.getElementById('phone_number');
            const transactionInput = document.getElementById('transaction_reference_number');
            const amountInput = document.getElementById('amount');
            const formMessage = document.getElementById('form-message');

            function toggleFields() {
                const selected = document.querySelector('input[name="payment_method"]:checked');
                const isMpesa = selected && selected.value === 'Mpesa';

                mpesaFields.style.display = isMpesa ? 'block' : 'none';
                transactionWrapper.style.display = isMpesa ? 'none' : 'block';

                phoneInput.required = isMpesa;
                transactionInput.required = !isMpesa; 
                amountInput.readOnly = true;
            }

            paymentOptions.forEach(function (option) {
                option.addEventListener('change', toggleFields);
            });

            toggleFields();

            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                const selected = document.querySelector('input[name="payment_method"]:checked');
                if (!selected) {
                    formMessage.style.display = 'block';
                    formMessage.textContent = 'Please choose a payment method.';
                    return;
                }

                if (selected.value === 'Mpesa') {
                    const phone = phoneInput.value.trim();
                    const amount = amountInput.value;

                    if (!phone) {
                        formMessage.style.display = 'block';
                        formMessage.textContent = 'Please enter your phone number.';
                        return;
                    }

                    formMessage.style.display = 'block';
                    formMessage.textContent = 'Sending STK push...';

                    try {
                        const response = await fetch('stkpush/initiate_stk.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ phone, amount })
                        });

                        const data = await response.json();

                                        if (data.success) {
                                const saveResponse = await fetch('save_payment.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        payment_method: 'Mpesa',
                                        amount: amount,
                                        transaction_reference_number: data.checkout_request_id || 'STK-' + Date.now(),
                                        phone_number: phone
                                    })
                                });

                                const saveData = await saveResponse.json();
                                formMessage.textContent = saveData.message || data.message;

                                if (saveData.success && saveData.payment) {
                                    const paymentList = document.getElementById('recent-payments-list');
                                    const paymentItem = document.createElement('li');
                                    paymentItem.textContent = `${saveData.payment.payment_method} - KES ${parseFloat(saveData.payment.amount).toFixed(2)} - Ref: ${saveData.payment.transaction_reference_number}`;
                                    if (paymentList) {
                                        paymentList.insertBefore(paymentItem, paymentList.firstChild);
                                    }
                                }
                            } else {
                            formMessage.textContent = data.message || 'Unable to send STK push.';
                        }
                    } catch (error) {
                        formMessage.textContent = 'Network error while sending STK push.';
                        console.error(error);
                    }

                    return;
                }

                form.submit();
            });
        });
    </script>

 </section>



            </main>
        </div>
    </div>
</body>
</html>