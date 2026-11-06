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
        background-color: rgb(46, 13, 228);
        border-radius: 5px;
    }
</style>

<nav class="navbar navbar-expand-xl navbar-dark bg-dark shadow-sm sticky-top custom-navbar">
    <div class="container-fluid px-lg-4">

        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center me-4" href="../managerpages/managersdashboard.php">
            <img src="../components/logo.png" width="60" height="40" class="me-2 rounded-circle" alt="Logo">
            <span class="fw-bold fs-5">MAKAO Real Estate</span>
        </a>

        <!-- Mobile Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <!-- Added horizontal spacing (gap-2) between links -->
            <ul class="navbar-nav me-auto mb-2 mb-xl-0 gap-xl-2">
                <li class="nav-item">
                    <a class="nav-link active px-3" href="../managerpages/managersdashboard.php"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="../managerpages/manageunits.php"><i class="fa-solid fa-building me-1"></i> Blocks & Units</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="addtenant.php"><i class="fa-solid fa-users me-1"></i>Tenant Management</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="../managerpages/utilities.php"><i class="fa-solid fa-file-invoice-dollar me-1"></i>Finances</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="maintenancerequest.php"><i class="fa-solid fa-wrench me-1"></i> Maintenance</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="../managerpages/serviceproviders.php"><i class="fa-solid fa-handshake me-1"></i> Service Providers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="services.php"><i class="fa-solid fa-list-check me-1"></i> Services</a>
                </li>
            </ul>

            <!-- Right User Profile & Logout -->
            <ul class="navbar-nav ms-auto align-items-xl-center gap-3">
                <li class="nav-item d-flex align-items-center text-white">
                </li>
                <li class="nav-item">
                    <a href="../managerpages/mlogout.php" class="btn btn-danger px-3 py-2"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
                </li>
            </ul>
        </div>

    </div>
</nav>