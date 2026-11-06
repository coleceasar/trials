<header class="topbar">
            <div class="brand">MAKAO Tenant Portal</div>
            <div class="topbar-actions">
                <?php if (!empty($tenant['first_name']) || !empty($tenant['last_name'])): ?>
                    <span>Welcome, <?php echo htmlspecialchars(trim(($tenant['first_name'] ?? '') . ' ' . ($tenant['last_name'] ?? ''))); ?></span>
                <?php else: ?>
                    <span>Welcome</span>
                <?php endif; ?>
                <a href="../tenantpages/tlogout.php" class="topbar-link">Logout</a>
            </div>
</header>