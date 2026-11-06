<?php
session_start();
include_once '../config.php';

if (empty($_SESSION['landlord_username'])) {
    header('Location: ownerlogin.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$username = trim($_SESSION['landlord_username']);
$landlord = null;
$properties = [];

try {
    $query = "SELECT * FROM landlords WHERE username = :username LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $landlord = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $landlord = null;
}

if (!$landlord) {
    echo 'Landlord record not found. Please register again.';
    exit;
}

$landlordId = $landlord['owner_id'] ?? null;

if ($landlordId !== null) {
    try {
        $propQuery = "SELECT
                        p.property_id,
                        p.property_name,
                        COUNT(DISTINCT b.block_id) AS numberofblocks,
                        m.name AS manager_name,
                        m.phone_number AS manager_phone
                    FROM property p
                    INNER JOIN landlord_properties lp ON p.property_id = lp.property_id
                    LEFT JOIN blocks b ON p.property_id = b.property_id
                    LEFT JOIN manager_properties mp ON p.property_id = mp.property_id
                    LEFT JOIN manager m ON mp.manager_id = m.manager_id
                    WHERE lp.owner_id = :landlord_id
                    GROUP BY p.property_id, p.property_name, m.name, m.phone_number
                    ORDER BY p.property_name";
                    
        $propStmt = $db->prepare($propQuery);
        $propStmt->bindValue(':landlord_id', (int)$landlordId, PDO::PARAM_INT);
        $propStmt->execute();
        $properties = $propStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        $properties = [];
    }
} else {
    $properties = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Dashboard</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <?php include '../components/lheader.php'; ?>
        <div class="dashboard-grid">
            <?php include '../components/lsidebar.php'; ?>
            <main class="main-content">
                <section class="welcome-card" id="overview">
                    <div>
                        <p class="eyebrow">Landlord dashboard</p>
                        <h1>Hello <?php echo htmlspecialchars($landlord['name'] ?? ''); ?></h1>
                    </div>
                </section>
                
                <section class="card-row">
                    <article class="action-card" id="profile">
                        <h2>Profile Information</h2>
                        <p>Name: <?php echo htmlspecialchars($landlord['name'] ?? ''); ?></p>
                        <p>Phone: <?php echo htmlspecialchars($landlord['phone_number'] ?? ''); ?></p>
                        <p>Email: <?php echo htmlspecialchars($landlord['email'] ?? ''); ?></p>
                    </article>
                    
                  <article class="action-card" id="properties">
                      <h2>My Properties</h2>
                           <?php if (!empty($properties)): ?>
                     <ul>
                        <?php foreach ($properties as $property): ?>
                        <li>
                            <p><strong>Property:</strong> <?php echo htmlspecialchars($property['property_name'] ?? ''); ?></p>
                            <p><strong>Blocks:</strong> <?php echo htmlspecialchars((string)($property['numberofblocks'] ?? 0)); ?></p>
                            <p><strong>Manager:</strong> <?php echo htmlspecialchars($property['manager_name'] ?? 'N/A'); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($property['manager_phone'] ?? 'N/A'); ?></p>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                        <?php else: ?>
                            <p>No properties found.</p>
                        <?php endif; ?>
                  </article>
                </section>
            </main>
        </div>
    </div>
</body>
</html>