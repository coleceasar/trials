<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<style>
    .custom-navbar {
        padding-top: 0.8rem !important;
        padding-bottom: 0.8rem !important;
    }
    
    .custom-navbar .nav-link {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
        font-size: 0.85rem; 
        transition: all 0.2s ease-in-out;
    }

    .custom-navbar .nav-link:hover {
        background-color: rgb(8, 35, 237);
        border-radius: 5px;
    }
</style>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top custom-navbar">
    <div class="container-fluid">

        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="admindashboard.php">
            <img src="../components/logo.png"width="40"height="40"class="me-2 rounded-circle"alt="Logo">
            <span class="fw-bold">MAKAO Real Estate</span>
        </a>

        <button class="navbar-toggler"type="button"data-bs-toggle="collapse"data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link active" href="../admin/admindashboard.php"><i class="bi bi-boxes"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="../admin/managerscontrol.php"><i class="fa-solid fa-user-tie" style="color: rgb(241, 242, 245);"></i>Managers</a></li>
                <li class="nav-item"><a class="nav-link" href="../admin/manageunit.php"><i class="fa-solid fa-house" style="color: rgb(241, 242, 245);"></i>Units</a></li>
                <li class="nav-item"><a class="nav-link" href="../admin/managetenant.php"><i class="fa-solid fa-users" style="color: rgb(241, 242, 245);"></i>Tenants</a></li>
                <li class="nav-item"><a class="nav-link" href="../admin/maintenance.php"><i class="fa-solid fa-toolbox" style="color: rgb(241, 242, 245);"></i>Maintenance</a></li>
                <li class="nav-item"><a class="nav-link" href="../admin/services.php"><i class="fa-solid fa-screwdriver-wrench" style="color: rgb(241, 242, 245);"></i>View Property Services</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item me-3">
                    <span class="navbar-text text-white">
                    <?php
                        echo htmlspecialchars(
                            trim(($manager['first_name'] ?? '') . ' ' . ($manager['last_name'] ?? ''))
                        );
                        ?></span>
                </li>
                <li class="nav-item"><i class="fa-solid fa-user fa-lg" style="color: rgb(234, 19, 38);"></i>
                    <a href="../managerpages/mlogout.php"class="btn btn-danger">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>