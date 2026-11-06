<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager- Add Unit</title>
    <link rel="stylesheet" href="../style.css">
    <body class="dashboard-page">
    <div class="dashboard-shell">
        <header class="topbar">
            <div class="brand">MAKAO Managers Portal</div>
        </header>

        <div class="dashboard-grid">
            <aside class="sidebar">
                <div class="sidebar-heading">Manager Menu</div>
                <nav>
                    <a href="../managerpages/managersdashboard.php">Dashboard</a>
                </nav>
            </aside>
            <main class="main-content">
                <section class="card-row" id="tenants">

                    <article class="action-card">
                        <h2>View Units</h2>
                        <p>Review all unit records stored in the Property</p>
                        <a href="unitsdata.php" class="btn-primary">View Units</a>
                    </article>
                    <article class="action-card">
                        <h2>Update  units</h2>
                        <p>Modify an existing unit's information.</p>
                        <a href="updateunit.php" class="btn-primary">Update Unit</a>
                    </article>
                    <article class="action-card">
                        <h2>Add Unit</h2>
                        <p>Add Units to the Property</p>
                        <a href="addunit.php" class="btn-primary">Add Units</a>
                </section>
            </main>
        </div>
    </div>
</body>
</html>

