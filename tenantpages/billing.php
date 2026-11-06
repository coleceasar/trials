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
                <div class="sidebar-heading">Manager Menu</div>
                <nav>
                    <a href="../tenantpages/tdashboard.php">Dashboard</a>
                </nav>
            </aside>
            <main class="main-content">
                <section class="card-row" id="tenants">
                    <article class="action-card">
                        <h2>Generate Billing Statements</h2>
                        <p>Create and manage billing statements for tenants.</p>
                        <a href="generatebilling.php" class="btn-primary">Generate Statements</a>
                    </article>
                    <article class="action-card">
                        <h2>Generate Invoice</h2>
                        <p>Create and manage invoices for tenants.</p>
                        <a href="generateinvoice.php" class="btn-primary">Generate Invoice</a>
                    </article>
                </section>
            </main>
        </div>
    </div>
</body>
</html>

