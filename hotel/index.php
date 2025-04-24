<?php
require_once '../restrict_access.php';
restrictAccess(['hotel']);
?>
<?php
require_once 'config/database.php';

// TEMPORARY: Setting default values for testing without login
$_SESSION['user_id']; // Assuming user ID 1 exists

// Check if user is logged in


// Check if user has the hotel role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'hotel') {
    header('Location: ../unauthorized.php');
    exit;
}

// Get hotel information for this user
$stmt = $conn->prepare("SELECT * FROM hotels WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

// If no hotel found, redirect to create hotel page
if (!$hotel) {
    header('Location: create_hotel.php');
    exit;
}

// Store hotel ID in session for use in other pages
$_SESSION['hotel_id'] = $hotel['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Owner Dashboard - <?php echo htmlspecialchars($hotel['name']); ?></title>
    
    <!-- jQuery and Validation CDN -->
    <script src="../js/jquery-3.6.4.min.js"></script>
    <script src="../js/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/additional-methods.min.js"></script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    
    <style>
        /* Full page loading screen */
        .page-loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }
        
        .page-loading-screen.hide {
            opacity: 0;
            pointer-events: none;
        }
        
        .page-loading-screen .logo {
            width: 150px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        .page-loading-screen .spinner {
            font-size: 2rem;
            color: var(--primary-color);
            animation: spin 1s infinite linear;
        }
        
        .page-loading-screen .loading-text {
            margin-top: 20px;
            color: var(--primary-color);
            font-size: 1.2rem;
            animation: fadeInOut 2s infinite;
        }
        
        @keyframes fadeInOut {
            0% { opacity: 0.3; }
            50% { opacity: 1; }
            100% { opacity: 0.3; }
        }
        
        /* Animation delay classes */
        .animate-delay-100 { animation-delay: 0.1s; }
        .animate-delay-200 { animation-delay: 0.2s; }
        .animate-delay-300 { animation-delay: 0.3s; }
        .animate-delay-400 { animation-delay: 0.4s; }
        .animate-delay-500 { animation-delay: 0.5s; }
        .animate-delay-600 { animation-delay: 0.6s; }
        .animate-delay-700 { animation-delay: 0.7s; }
        
        /* Add hover animations */
        .hover-pulse:hover {
            animation: pulse 1s;
        }
        
        .hover-bounce:hover {
            animation: bounce 1s;
        }
        
        .hover-tada:hover {
            animation: tada 1s;
        }
        
        .hover-heartbeat:hover {
            animation: heartBeat 1.3s;
        }
        
        /* Loading spinner animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-spinner i {
            animation: spin 1s infinite linear;
        }
        
        /* Notification animations */
        .notification {
            animation: fadeInRight 0.5s, fadeOut 0.5s 4.5s;
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        /* Animated background for dashboard */
        .dashboard-container {
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-container::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at right, rgba(173, 139, 58, 0.05), transparent 60%);
            animation: gradientPulse 15s infinite alternate;
            z-index: -1;
        }
        
        @keyframes gradientPulse {
            0% { opacity: 0.3; }
            100% { opacity: 0.8; }
        }
        
        /* Animated icons */
        .animate-icon {
            transition: all 0.3s ease;
        }
        
        .animate-icon:hover {
            transform: scale(1.2);
            color: var(--primary-color);
        }
        
        /* Card animation classes */
        .card {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="animate__animated animate__fadeIn">
    <!-- Loading Screen -->
    <div class="page-loading-screen">
        <img src="https://i.ibb.co/p6hTnpd9/Elysian-Stays.png" alt="Elysian Stays Logo" class="logo">
        <i class="fas fa-spinner spinner"></i>
        <div class="loading-text">Loading Dashboard...</div>
    </div>

    <div class="dashboard-container">
    <!-- Sidebar Overlay (for mobile) -->
    <div class="sidebar-overlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar animate__animated animate__fadeInLeft">
        <div class="hotel-name animate__animated animate__fadeInDown animate__delay-1s">
            <?php echo htmlspecialchars($hotel['name']); ?>
        </div>
        <ul class="sidebar-menu">
            <li class="animate__animated animate__fadeInLeft animate-delay-100"><a href="#" data-section="overview" class="hover-pulse"><i class="fas fa-home animate-icon"></i> Overview</a></li>
            <li class="animate__animated animate__fadeInLeft animate-delay-200"><a href="#" data-section="bookings" class="hover-pulse"><i class="fas fa-calendar-alt animate-icon"></i> Manage Bookings</a></li>
            <li class="animate__animated animate__fadeInLeft animate-delay-300"><a href="#" data-section="rooms" class="hover-pulse"><i class="fas fa-bed animate-icon"></i> Manage Rooms</a></li>
            <li class="animate__animated animate__fadeInLeft animate-delay-400"><a href="#" data-section="reviews" class="hover-pulse"><i class="fas fa-star animate-icon"></i> Customer Reviews</a></li>
            <li class="animate__animated animate__fadeInLeft animate-delay-500"><a href="#" data-section="revenue-analytics" class="hover-pulse"><i class="fas fa-chart-line animate-icon"></i> Revenue Analytics</a></li>
            <li class="animate__animated animate__fadeInLeft animate-delay-600"><a href="#" data-section="profile" class="hover-pulse"><i class="fas fa-hotel animate-icon"></i> Hotel Profile</a></li>
            <li class="animate__animated animate__fadeInLeft animate-delay-700"><a href="#" data-section="owner_profile" class="hover-pulse"><i class="fas fa-user-circle animate-icon"></i> Owner Profile</a></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar animate__animated animate__fadeInDown">
            <button id="sidebarToggle" class="sidebar-toggle animate__animated animate__pulse animate__repeat-2 hover-bounce">
                <i class="fas fa-bars"></i>
            </button>
            <div class="user-menu animate__animated animate__fadeInRight">
                <a href="#" id="logoutLink" class="hover-heartbeat">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
        <div id="content" class="animate__animated animate__fadeIn">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>
    
    <!-- Notification System -->
    <div id="notification-container"></div>
    
    <script>
    $(document).ready(function() {
        // Hide loading screen after page is fully loaded
        $(window).on('load', function() {
            setTimeout(function() {
                $('.page-loading-screen').addClass('hide');
                setTimeout(function() {
                    $('.page-loading-screen').remove();
                }, 500);
            }, 1000);
        });
        
        // Add animation classes to elements
        animateElements();
        
        // Load default section (overview)
        loadSection('overview');
        
        // Handle section navigation
        $('.sidebar-menu a').on('click', function(e) {
            e.preventDefault();
            const section = $(this).data('section');
            
            // Add active class with animation
            $('.sidebar-menu a').removeClass('active');
            $(this).addClass('active animate__animated animate__pulse');
            
            loadSection(section);
            
            // On mobile, close sidebar after selection
            if (window.innerWidth < 992) {
                $('.dashboard-container').removeClass('sidebar-open');
            }
        });
        
        // Mobile sidebar toggle
        $('#sidebarToggle').on('click', function() {
            $('.dashboard-container').toggleClass('sidebar-open');
            // Add pulse animation when toggling
            $(this).addClass('animate__animated animate__pulse');
            setTimeout(() => {
                $(this).removeClass('animate__animated animate__pulse');
            }, 1000);
        });
        
        // Handle logout
        $('#logoutLink').on('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                $(this).addClass('animate__animated animate__fadeOutRight');
                setTimeout(() => {
                    $.post('sections/owner_profile.php', {action: 'logout'}, function(response) {
                        if (response.success) {
                            window.location.href = response.redirect;
                        }
                    }, 'json');
                }, 500);
            }
        });
    });
    
    // Function to add animation classes to various elements
    function animateElements() {
        // Apply animations to dynamically created buttons
        $('button, .btn').addClass('hover-pulse');
        
        // Apply animations to icons
        $('.fas, .far, .fab').addClass('animate-icon');
        
        // Apply card animations
        $('.card, .stat-card').addClass('animate__animated animate__fadeInUp');
        
        // Apply fadein to images
        $('img').addClass('animate__animated animate__fadeIn');
    }
    
    // Function to load section content with animation
    function loadSection(section) {
        $('#content').html('<div class="loading-spinner animate__animated animate__fadeIn"><i class="fas fa-spinner fa-spin"></i></div>');
        
        $.get('sections/' + section + '.php', function(data) {
            $('#content').removeClass('animate__fadeIn').addClass('animate__animated animate__fadeOut');
            
            setTimeout(function() {
                $('#content').html(data).removeClass('animate__fadeOut').addClass('animate__fadeIn');
                
                // Add animations to cards and elements within the loaded content
                $('#content .stat-card, #content .card, #content .chart-container').addClass('animate__animated animate__fadeInUp');
                animateContentElements(section);
                
                // Apply staggered animations to lists and repeating elements
                $('#content .list-item, #content .table tr').each(function(index) {
                    $(this).addClass('animate__animated animate__fadeInRight')
                           .css('animation-delay', (index * 0.1) + 's');
                });
                
                // Add hover effects to buttons in content
                $('#content button, #content .btn').addClass('hover-pulse');
                
                // Add card hover effects
                $('#content .card, #content .stat-card, #content .room-card').each(function() {
                    $(this).hover(
                        function() { $(this).addClass('animate__pulse'); },
                        function() { $(this).removeClass('animate__pulse'); }
                    );
                });
            }, 300);
        }).fail(function() {
            $('#content').html('<div class="error-message animate__animated animate__shakeX">Failed to load section</div>');
        });
    }
    
    // Function to animate specific content elements based on section
    function animateContentElements(section) {
        switch(section) {
            case 'overview':
                $('.overview-card').addClass('animate__animated animate__bounceIn');
                $('.quick-stats .stat').addClass('animate__animated animate__fadeInUp');
                break;
                
            case 'bookings':
                $('.booking-filter').addClass('animate__animated animate__fadeIn');
                $('.booking-actions button').addClass('hover-bounce');
                break;
                
            case 'rooms':
                $('.room-card').addClass('animate__animated animate__fadeInUp');
                $('.room-card img').addClass('animate__animated animate__fadeIn animate-delay-300');
                $('.room-actions button').addClass('hover-tada');
                break;
                
            case 'revenue-analytics':
                $('.chart-container canvas').addClass('animate__animated animate__fadeIn animate-delay-500');
                $('.revenue-value').addClass('animate__animated animate__fadeInUp');
                break;
        }
    }
    
    // Enhanced notification function with animations
    function showNotification(message, type = 'info') {
        const notification = $('<div class="notification animate__animated animate__fadeInRight ' + type + '">' + message + '</div>');
        $('#notification-container').append(notification);
        
        // Auto remove after 5 seconds with animation
        setTimeout(function() {
            notification.addClass('animate__fadeOutRight');
            setTimeout(function() {
                notification.remove();
            }, 500);
        }, 5000);
    }
    </script>
</body>
</html> 