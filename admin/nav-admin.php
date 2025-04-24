<!-- Navbar -->
<header class="sidebar-header" id="header">
    <div class="sidebar-toggle" id="header-toggle" aria-expanded="false" aria-controls="nav-bar">
        <i class="fas fa-bars"></i>
    </div>
    <div class="sidebar-img">
        <img src="../Images/user-iconset-no-profile-.png" alt="" onclick="window.location.href='profile.php'" style="cursor: pointer;">
    </div>
</header>

<div class="sidebar-navbar" id="nav-bar" aria-label="Main Navigation">
    <nav class="sidebar-nav">
        <div>
            <a href="#" class="sidebar-nav-logo" data-bs-toggle="tooltip" data-bs-placement="right" title="Admin Panel">
                <i class="fas fa-layer-group sidebar-nav-logo-icon"></i>
                <span class="sidebar-nav-logo-name">Admin Panel</span>
            </a>
            <div class="sidebar-nav-list">
                <a href="index.php" class="sidebar-nav-link sidebar-active" data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
                    <i class="fas fa-th-large sidebar-icon"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage-users.php" class="sidebar-nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Manage Users">
                    <i class="fas fa-users sidebar-icon"></i>
                    <span>Manage Users</span>
                </a>
                <a href="manage-hotels.php" class="sidebar-nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Manage Hotels">
                    <i class="fas fa-hotel sidebar-icon"></i>
                    <span>Manage Hotels</span>
                </a>
                <a href="manage-bookings.php" class="sidebar-nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Manage Bookings">
                    <i class="fas fa-calendar-check sidebar-icon"></i>
                    <span>Manage Bookings</span>
                </a>
                <a href="feedback.php" class="sidebar-nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Feedback">
                    <i class="fas fa-comment-alt sidebar-icon"></i>
                    <span>Feedback</span>
                </a>

                <a href="platform-settings.php" class="sidebar-nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Platform Settings">
                    <i class="fas fa-cogs sidebar-icon"></i>
                    <span>Platform Settings</span>
                </a>
                <a href="revenue-analytics.php" class="sidebar-nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Revenue Analytics">
                    <i class="fas fa-chart-line sidebar-icon"></i>
                    <span>Revenue Analytics</span>
                </a>
            </div>
        </div>
        <a href="../logout.php" class="sidebar-nav-link" data-bs-toggle="tooltip" data-bs-placement="right" title="Sign Out">
            <i class="fas fa-sign-out-alt sidebar-icon"></i>
            <span>Sign Out</span>
        </a>
        </a>
    </nav>
</div>