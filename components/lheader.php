<header class="topbar">
            <div class="brand">MAKAO Landlords Portal</div>
            <div class="topbar-actions">
                <?php if (!empty($landlord['first_name']) || !empty($landlord['last_name'])): ?>
                    <span>Welcome, <?php echo htmlspecialchars(trim(($landlord['first_name'] ?? '') . ' ' . ($landlord['last_name'] ?? ''))); ?></span>
                <?php else: ?>
                    <span>Welcome</span>
                <?php endif; ?>
                <a href="../ownerpages/ologout.php" class="topbar-link">Logout</a>
            </div>
</header>