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
                    <a href="managersdashboard.php">Dashboard</a>
                </nav>
            </aside>
            <main class="main-content">
                <section class="card-row" id="tenants">
                    <article class="action-card">
                        <h2>Add Blocks</h2>
                        <p>Add new blocks to the property</p>
                        <a href="addblock.php" class="btn-primary">Add Blocks</a>
                    </article>
                    <article class="action-card">
                        <h2>View  Blocks</h2>
                        <p>Add Blocks to the Property</p>
                        <a href="viewblocks.php" class="btn-primary">View Blocks</a>
                    </article>
                    <article class="action-card">
                        <h2>Add Unit</h2>
                        <p>Add Units to the Property</p>
                        <a href="addunit.php" class="btn-primary">Add Units</a>
                    </article>
                    <article class="action-card">
                        <h2>View Units</h2>
                        <p>Review all unit records stored in the Property</p>
                        <a href="../units/unitsdata.php" class="btn-primary">View Units</a>
                    </article>
                    <article class="action-card">
                        <h2>Update  units</h2>
                        <p>Modify an existing unit's information.</p>
                        <a href="../units/updateunit.php" class="btn-primary">Update Unit</a>
                    </article>
                </section>
            </main>
        </div>
    </div>
</body>
</html>

