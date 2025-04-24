<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

// Include database connection
require_once 'db_connect.php';

// Fetch user data if logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Set default image if none exists
        if (empty($user['profile_pic'])) {
            $user['profile_pic'] = 'default-profile.png';
        }
    }
    $stmt->close();
}
?>
    <style>
        /* Navbar Styles */
        .navbar {
            background: transparent !important;
            padding: 1rem 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-text-container {
            position: relative;
            padding: 0.8rem 1.5rem;
            border-radius: 1.5rem;
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98) !important;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0;
        }

        .navbar.scrolled .nav-text-container {
            background: transparent;
            backdrop-filter: none;
        }

        .nav-link {
            color: white !important;
            font-size: 1.1rem;
            font-weight: 500;
            margin: 0 0.8rem;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar.scrolled .nav-link {
            color: #ad8b3a !important;
        }

        .nav-link:hover {
            color: #ad8b3a !important;
            transform: translateY(-2px);
        }

        .navbar.scrolled .nav-link:hover {
            color: #7a6330 !important;
        }

        .profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .profile-image:hover {
            transform: scale(1.1);
        }

        .dropdown-menu {
            border: none;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.3s ease;
            transform-origin: top center;
            padding: 0.8rem 0;
            min-width: 200px;
            border-top: 3px solid #ad8b3a;
            overflow: hidden;
        }

        .dropdown-item {
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            position: relative;
            transition: all 0.3s ease;
            color: #45443F;
        }

        .dropdown-item i {
            color: #ad8b3a;
            width: 20px;
            margin-right: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: rgba(173, 139, 58, 0.1);
            color: #ad8b3a;
            transform: translateX(5px);
        }

        .dropdown-item:hover i {
            transform: scale(1.2);
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            border-top: 1px solid rgba(173, 139, 58, 0.2);
        }

        .dropdown-item.text-danger {
            color: #dc3545;
        }

        .dropdown-item.text-danger:hover {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .dropdown-item.text-danger i {
            color: #dc3545;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .navbar-brand img {
            height: 65px;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover img {
            transform: scale(1.05);
        }

        @media (max-width: 991px) {
            .nav-text-container {
                background: rgba(0, 0, 0, 0.4);
                margin-top: 1rem;
                border-radius: 1rem;
            }

            .navbar.scrolled .nav-text-container {
                background: rgba(255, 255, 255, 0.98);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .nav-link {
                margin: 0.5rem 0;
            }

            .dropdown-menu {
                background: rgba(255, 255, 255, 0.98);
            }
        }

        .navbar-toggler {
            border: none;
            color: white;
        }

        .navbar.scrolled .navbar-toggler {
            color: #ad8b3a;
        }

        .animate__animated {
            --animate-duration: 0.5s;
        }
    </style>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- Navbar for Guest Users -->
        <nav class="navbar navbar-expand-lg fixed-top navbar-light animate__animated">
            <div class="container">
                <!-- Brand Logo -->
                <a class="navbar-brand" href="index.php">
                    <img src="Images/Elysian_Stays.png" alt="Elysian Stays Logo" height="65" class="animate__animated">
                </a>

                <!-- Mobile Toggler -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Navbar Content -->
                <div class="collapse navbar-collapse" id="navbarContent">
                    <div class="nav-text-container ms-auto animate__animated">
                        <ul class="navbar-nav align-items-center">
                            <li class="nav-item">
                                <a class="nav-link animate__animated" href="index.php">
                                    <i class="fa-solid fa-house"></i> Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link animate__animated" href="about-us.php">
                                    <i class="fas fa-bell"></i> About Us
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link animate__animated" href="contact.php">
                                    <i class="fas fa-phone"></i> Contact Us
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link animate__animated" href="list-your-place.php">
                                    <i class="fas fa-list"></i> List Your Place
                                </a>
                            </li>
                            <li class="nav-item ms-2">
                                <a class="btn-mod-nav animate__animated" href="login.php" style="text-decoration: none;">Sign In</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    <?php else: ?>
        <!-- Navbar for Logged-In Users -->
        <nav class="navbar navbar-expand-lg fixed-top navbar-light animate__animated">
            <div class="container">
                <!-- Brand Logo -->
                <a class="navbar-brand" href="index.php">
                    <img src="Images/Elysian_Stays.png" alt="Elysian Stays Logo" height="65" class="animate__animated">
                </a>
                <!-- Mobile Toggler -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Navbar Content -->
                <div class="collapse navbar-collapse" id="navbarContent">
                    <div class="nav-text-container ms-auto animate__animated">
                        <ul class="navbar-nav align-items-center">
                            <li class="nav-item">
                                <a class="nav-link animate__animated" href="index.php">
                                    <i class="fa-solid fa-house"></i> Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link animate__animated" href="about-us.php">
                                    <i class="fas fa-bell"></i> About Us
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link animate__animated" href="contact.php">
                                    <i class="fas fa-phone"></i> Contact Us
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link animate__animated" href="my-booking.php">
                                    <i class="fas fa-list"></i> My Bookings
                                </a>
                            </li>
                            <!-- Profile Dropdown -->
                            <li class="nav-item dropdown ms-2">
                                <a class="nav-link dropdown-toggle d-flex align-items-center animate__animated" href="#"
                                    id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    <img src="uploads/<?php echo $user['profile_pic']; ?>" alt="Profile" class="profile-image me-2">
                                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="update-profile-user.php"><i class="fas fa-user me-2"></i> My Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    <?php endif; ?>