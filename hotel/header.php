<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Dashboard</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <script src="../js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="stylesheet" href="style.css">

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
    <link href="../css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="../js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <style>
    :root {
        --sidebar-width: 280px;
        --primary-color: #ad8b3a;
        --dark-bg: #45443F;
        --text-light: #ffffff;
        --transition-speed: 0.3s;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background-color: #f4f4f4;
        font-family: 'Poppins', sans-serif;
        transition: all var(--transition-speed) ease;
        overflow-x: hidden;
        min-height: 100vh;
    }

    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: var(--sidebar-width);
        height: 100vh;
        background: var(--dark-bg);
        transition: transform var(--transition-speed) ease;
        z-index: 1000;
        box-shadow: 3px 0 15px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        transform: translateX(-100%);
    }

    .sidebar.active {
        transform: translateX(0);
    }

    .main-content {
        transition: all var(--transition-speed) ease;
        min-height: 100vh;
        width: 100%;
    }

    .sidebar.active + .main-content {
        transform: translateX(var(--sidebar-width));
    }

    /* Header Styles */
    .main-header {
        background: var(--text-light);
        padding: 1rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 100;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Navigation Menu Styles */
    .nav-menu {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 1rem 0;
    }

    .user-profile {
        padding: 1rem;
        background: rgba(0, 0, 0, 0.2);
        text-align: center;
    }

    .profile-icon {
        margin-top: auto;
        padding: 1rem;
        text-align: center;
    }

    .profile-icon .btn-link {
        padding: 0;
    }

    .profile-icon .fa-user-circle {
        transition: all var(--transition-speed);
        color: var(--primary-color);
        font-size: 2rem;
    }

    .profile-icon .fa-user-circle:hover {
        transform: scale(1.1);
    }

    .profile-dropdown .dropdown-menu {
        background: var(--dark-bg);
        border: 1px solid var(--primary-color);
        margin-top: 10px;
        min-width: 200px;
    }

    /* Navigation Links */
    .nav-link {
        color: var(--text-light) !important;
        padding: 15px 25px !important;
        display: flex !important;
        align-items: center;
        gap: 15px;
        transition: all var(--transition-speed);
        text-decoration: none;
        position: relative;
    }

    .nav-link:hover {
        background: rgba(173, 139, 58, 0.1);
        padding-left: 30px !important;
    }

    .nav-link.active {
        border-left: 4px solid var(--primary-color);
        background: rgba(173, 139, 58, 0.1);
    }

    .nav-link i {
        width: 20px;
        text-align: center;
    }

    /* Animation Classes */
    .animate-delay-100 {
        animation-delay: 0.1s;
    }
    .animate-delay-200 {
        animation-delay: 0.2s;
    }
    .animate-delay-300 {
        animation-delay: 0.3s;
    }
    .animate-delay-400 {
        animation-delay: 0.4s;
    }
    .animate-delay-500 {
        animation-delay: 0.5s;
    }

    /* Sidebar Toggle Button */
    .sidebar-toggle {
        background: transparent;
        border: none;
        color: var(--dark-bg);
        font-size: 1.5rem;
        cursor: pointer;
        transition: all var(--transition-speed);
    }

    .sidebar-toggle:hover {
        color: var(--primary-color);
        transform: scale(1.1);
    }

    /* Responsive Styles */
    @media (min-width: 768px) {
        .sidebar {
            transform: translateX(0);
        }
        
        .sidebar.active + .main-content {
            margin-left: var(--sidebar-width);
            transform: translateX(0);
        }
        
        .sidebar-toggle {
            display: none;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
        }
    }

    /* Overlay for mobile */
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: all var(--transition-speed);
    }

    .sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* Hotel Name in Sidebar */
    .hotel-name {
        padding: 1.5rem;
        color: var(--primary-color);
        font-family: 'Cinzel', serif;
        font-size: 1.3rem;
        font-weight: bold;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    /* Sidebar Menu */
    .sidebar-menu {
        list-style: none;
        padding: 1rem 0;
        flex-grow: 1;
        overflow-y: auto;
    }

    .sidebar-menu li {
        margin-bottom: 0.5rem;
    }

    .sidebar-menu a {
        display: block;
        padding: 0.8rem 1.5rem;
        color: var(--text-light);
        text-decoration: none;
        transition: all var(--transition-speed);
    }

    .sidebar-menu a:hover {
        background: rgba(173, 139, 58, 0.1);
        padding-left: 2rem;
    }

    .sidebar-menu a.active {
        border-left: 4px solid var(--primary-color);
        background: rgba(173, 139, 58, 0.1);
    }

    /* Topbar Styles */
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .user-menu a {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--dark-bg);
        text-decoration: none;
    }

    /* Content Area */
    #content {
        padding: 2rem;
    }

    /* Loading Spinner */
    .loading-spinner {
        display: flex;
        justify-content: center;
        padding: 2rem;
    }
</style>
</head>
<body>