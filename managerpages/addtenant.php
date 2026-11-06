<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Add Tenant</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">

        <div class="dashboard-grid">
            <aside class="sidebar">
                <div class="sidebar-heading">Manager Menu</div>
                <nav>
                    <a href="managersdashboard.php">Dashboard</a>
                </nav>
            </aside>

            <main class="main-content">
                <section class="card-row" id="tenants">
                    <article class="action-card">
                        <h2>Add New Tenant</h2>
                        <p>Register a new tenant into the property.</p>
                        <a href="property.php" class="btn-primary">Register New Tenant</a>
                    </article>

                    <article class="action-card">
                        <h2>View Tenants</h2>
                        <p>Review all tenant records stored in the database.</p>
                        <a href="tenantslist.php" class="btn-primary">View Tenants</a>
                    </article>

                    <article class="action-card">
                        <h2>Deactivate Tenant</h2>
                        <p>Change the status of a tenant to inactive.</p>
                        <a href="removetenant.php" class="btn-primary">Deactivate Tenant</a>
                    </article>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
