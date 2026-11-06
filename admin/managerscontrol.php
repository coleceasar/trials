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
                    <a href="admindashboard.php">Dashboard</a>
                </nav>
            </aside>

            <main class="main-content">
                <section class="card-row" id="tenants">
                    <article class="action-card">
                        <h2>Assign Property</h2>
                        <p>Assign a new Manager To a property.</p>
                        <a href="assignprop.php" class="btn-primary">Assign Property</a>
                    </article>

                    <article class="action-card">
                        <h2>Deactivate Manager</h2>
                        <p>Change the status of a manager</p>
                        <a href="removemanager.php" class="btn-primary">Deactivate Manager</a>
                    </article>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
