<?php
session_start();

include_once '../config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ../admin/adminlogin.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$admin = null;
$propertiesList = [];

try {
    // 1. Fetch Admin Info
    $query = "SELECT * FROM admin WHERE admin_id = :admin_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':admin_id', $_SESSION['admin_id'], PDO::PARAM_INT);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo 'Admin record not found.';
        exit();
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>ADMIN Dashboard</title>
<link rel="stylesheet" href="../style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="dashboard-page">

<div class="dashboard-shell">
<?php include '../components/adminheader.php';?>
<main class="main-content">
<section class="welcome-card" id="overview">
<div>
<p class="eyebrow">ADMIN Dashboard</p>
<h1>Hello <?php echo htmlspecialchars($admin['admin_name']);?></h1>
</div>
</section>

<section class="card-row">
<!-- Profile Information Card -->
<article class="action-card" id="profile">
<h2>Profile Information</h2>
<p><strong>Name:</strong> <?php echo htmlspecialchars($admin['admin_name']);?></p>
<p><strong>Phone:</strong> <?php echo htmlspecialchars($admin['phone_number']);?></p>
</article>

<!-- Add New Tenant Card -->
<article class="action-card">
    <h2>Administrator Actions</h2>
    <div class="action-buttons">
        <a href="newproperty.php" class="action-btn register"><i class="fa-solid fa-user-plus" style="color: rgb(241, 242, 245);"></i> Create New Property</a>
        <a href="register_form.php" class="action-btn register"><i class="fa-solid fa-house-user" style="color: rgb(241, 242, 245);"></i> Register New Tenant</a>
        <a href="owner_registration.php" class="action-btn register"><i class="fa-solid fa-user-plus" style="color: rgb(241, 242, 245);"></i> Add new Landlord</a>
        <a href="manager_registration.php" class="action-btn register"><i class="fa-solid fa-user-plus" style="color: rgb(4, 247, 24);"></i> Add new Manager</a>
        <a href="landlordlist.php" class="action-btn view"><i class="fa-solid fa-users" style="color: rgb(255, 70, 3);"></i> View All Landlords</a>
        <a href="viewcomplaint.php" class="action-btn view"><i class="fa-solid fa-comment" style="color: rgb(241, 242, 245);"></i> View Complaints & Requests</a>
    </div>
</article>
<article class="action-card">
    <h2>Properties</h2>
    <div class="action-buttons">
        <a href="propertylist.php" class="action-btn view"><i class="fa-solid fa-building" style="color: rgb(241, 242, 245);"></i> View All Properties</a>
        <a href="units.php" class="action-btn view"><i class="fa-solid fa-house fa" style="color: rgb(241, 242, 245);"></i>View Units</a>
        <a href="maintenance.php" class="action-btn update"><i class="fa-solid fa-wrench" style="color: rgb(241, 242, 245);"></i> View Maintenance Requests</a>
        <a href="managerlist.php" class="action-btn view"><i class="fa-solid fa-users" style="color: rgb(241, 242, 245);"></i> View All Managers</a>
    </div>
</section>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>