<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Add Unit</title>
    <link rel="stylesheet" href="../style.css">
    <body class="dashboard-page">
    <div class="dashboard-shell">
        <header class="topbar">
            <div class="brand">MAKAO Managers Portal</div>
        </header>

        <div class="dashboard-grid">
            <aside class="sidebar">
                <div class="sidebar-heading">Admin Menu</div>
                <nav>
                    <a href="../admin/admindashboard.php">Dashboard</a>
                </nav>
            </aside>
            <main class="main-content">
                <section class="card-row" id="tenants">
                    <article class="action-card">
                        <h2>Billing Statements</h2>
                        <p>Create and manage billing statements for tenants.</p>
                        <a href="generatebilling.php" class="btn-primary">Generate Statements</a>
                    </article>
                    <article class="action-card">
                        <h2>Water Bill</h2>
                        <p>Insert and Calculate Water Bills</p>
                        <a href="waterreading.php" class="btn-primary">Water Bills</a>
                    </article>
                    <article class="action-card">
                        <h2>Electricity Bill</h2>
                        <p>Insert and Calculate Electricity Bills</p>
                        <a href="electricityreading.php" class="btn-primary">Electricity Bills</a>
                    </article>
                </section>
            </main>
        </div>
    </div>
</body>
</html>

