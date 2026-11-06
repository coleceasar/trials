<?php
session_start();

include_once '../config.php';

if (empty($_SESSION['manager_id']) || empty($_SESSION['property_id'])) {
    header('Location:managerlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$manager = null;
$propertiesList = [];

try {
    // 1. Fetch Manager Info 
    $query = "SELECT * FROM manager WHERE manager_id = :manager_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':manager_id', $_SESSION['manager_id'], PDO::PARAM_INT);
    $stmt->execute();
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$manager) {
        echo 'Manager record not found.';
        exit();
    }

    // 2. Fetch Assigned Properties along with block count, unit , and total monthly manager earnings
    $query = "SELECT 
                p.property_id,
                p.property_name,
                p.location,
                p.numberofblocks,
                COUNT(DISTINCT u.unit_id) AS total_units,
                SUM(CASE WHEN LOWER(u.status) = 'occupied' THEN 1 ELSE 0 END) AS occupied_units,
                (COUNT(DISTINCT u.unit_id) - SUM(CASE WHEN LOWER(u.status) = 'occupied' THEN 1 ELSE 0 END)) AS available_units,
                COALESCE(SUM(u.monthly_rent), 0) AS raw_total_rent,
                (COALESCE(SUM(u.monthly_rent), 0) * (m.percentage_pay / 100)) AS total_monthly_rent
              FROM manager_properties mp
              INNER JOIN property p ON mp.property_id = p.property_id
              INNER JOIN manager m ON mp.manager_id = m.manager_id
              LEFT JOIN blocks b ON p.property_id = b.property_id
              LEFT JOIN units u ON b.block_id = u.block_id
              WHERE mp.manager_id = :manager_id
              GROUP BY p.property_id, p.property_name, p.location, p.numberofblocks, m.percentage_pay";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':manager_id', $_SESSION['manager_id'], PDO::PARAM_INT);
    $stmt->execute();
    $propertiesList = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Manager Dashboard</title>
<link rel="stylesheet" href="../style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="dashboard-page">

<div class="dashboard-shell">
<?php include '../components/mheader.php';?>
<main class="main-content">
<section class="welcome-card" id="overview">
<div>
<p class="eyebrow">Manager Dashboard</p>
<h1>Hello 
<?php echo htmlspecialchars($manager['name']);?>
</h1>
</div>
</section>

<section class="card-row">
<!-- Profile Information Card -->
<article class="action-card" id="profile">
<h2>Profile Information</h2>
<p><strong>Name:</strong> <?php echo htmlspecialchars($manager['name']);?></p>
<p><strong>Phone:</strong> <?php echo htmlspecialchars($manager['phone_number']);?></p>
<p><strong>Commission Rate:</strong> <?php echo htmlspecialchars($manager['percentage_pay']);?>%</p>
</article>

<!-- Manager Actions Card -->
<article class="action-card">
    <h2>Manager Actions</h2>
    <div class="action-buttons">
        <a href="../managerpages/property.php" class="action-btn register"><i class="fa-solid fa-house-user" style="color: rgb(241, 242, 245);"></i> Register New Tenant</a>
        <a href="addblock.php" class="action-btn register"><i class="fa-solid fa-city" style="color: hsl(0, 100%, 99%);"></i>Add Blocks To Property</a>
        <a href="../units/unitsdata.php" class="action-btn view"><i class="fa-solid fa-house fa" style="color: rgb(241, 242, 245);"></i> View Units</a>
        <a href="../units/updateunit.php" class="action-btn update"><i class="fa-solid fa-edit" style="color: rgb(241, 242, 245);"></i> Update Units</a>
        <a href="removetenant.php" class="action-btn remove"><i class="fa-solid fa-trash" style="color: rgb(241, 242, 245);"></i> Deactivate Tenant</a>
        <a href="../managerpages/maintenancerequest.php" class="action-btn update"><i class="fa-solid fa-screwdriver-wrench" style="color: rgb(241, 242, 245);"></i> View Maintenance Requests</a>
        <a href="view_complaints.php" class="action-btn view"><i class="fa-solid fa-comment" style="color: rgb(241, 242, 245);"></i> View Complaints & Requests</a>
    </div>
</article>

<!-- Distinct Article for Each Assigned Property -->
<?php if (!empty($propertiesList)): ?>
    <?php foreach ($propertiesList as $property): ?>
        <article class="action-card property-card">
            <h2>Property: <?php echo htmlspecialchars($property['property_name']); ?></h2>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></p>
            <p><strong>Total Blocks:</strong> <?php echo htmlspecialchars($property['numberofblocks']); ?></p>
            <hr>
            <p><strong>Total Units:</strong> <?php echo (int)$property['total_units']; ?></p>
            <p><strong>Occupied Units:</strong> <?php echo (int)$property['occupied_units']; ?></p>
            <p><strong>Available Units:</strong> <?php echo (int)$property['available_units']; ?></p>
            <p><strong>Total Property Rent:</strong> KSH <?php echo number_format($property['raw_total_rent'], 2); ?></p>
            <p><strong>Manager Monthly Share: </strong>KSH <?php echo number_format($property['total_monthly_rent'], 2); ?></p>
        </article>
    <?php endforeach; ?>
<?php else: ?>
    <article class="action-card">
        <h2>Assigned Properties</h2>
        <p>No properties assigned.</p>
    </article>
<?php endif; ?>

</section>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>