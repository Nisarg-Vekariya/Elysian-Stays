<?php
require_once '../restrict_access.php';
restrictAccess(['admin']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/animate.min.css">

    <!-- linking css of other pages -->
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/manage-users.css">
    <link rel="stylesheet" href="css/manage-hotels.css">
    <link rel="stylesheet" href="css/manage-bookings.css">
    <link rel="stylesheet" href="css/platform-settings.css">
    <link rel="stylesheet" href="css/revenue-analytics.css">
    <link rel="stylesheet" href="css/feedback.css">

    <style>
        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Sidebar Styling */
        :root {
            --sidebar-header-height: 3rem;
            --sidebar-nav-width: 68px;
            --sidebar-bg-color: #000000;
            --sidebar-highlight-color: #ad8b3a;
            --sidebar-text-color: #F7F6FB;
            --sidebar-font-size: 1rem;
            --sidebar-z-index: 100;
            --transition-speed: 0.3s;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: var(--sidebar-header-height) 0 0 0;
            padding: 0 1rem;
            font-size: var(--sidebar-font-size);
            transition: var(--transition-speed);
        }

        a {
            text-decoration: none;
        }

        .sidebar-header {
            width: 100%;
            height: var(--sidebar-header-height);
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            background-color: var(--sidebar-text-color);
            z-index: var(--sidebar-z-index);
            transition: var(--transition-speed);
        }

        .sidebar-toggle {
            color: var(--sidebar-bg-color);
            font-size: 1.5rem;
            cursor: pointer;
            transition: var(--transition-speed);
        }

        .sidebar-toggle.active i {
            transform: rotate(90deg);
        }

        .sidebar-img {
            width: 35px;
            height: 35px;
            display: flex;
            justify-content: center;
            border-radius: 50%;
            overflow: hidden;
        }

        .sidebar-img img {
            width: 40px;
        }

        .sidebar-navbar {
            position: fixed;
            top: 0;
            left: -30%;
            width: var(--sidebar-nav-width);
            height: 100vh;
            background-color: var(--sidebar-bg-color);
            padding: 0.5rem 1rem 0 0;
            transition: var(--transition-speed);
            z-index: var(--sidebar-z-index);
        }

        .sidebar-nav {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .sidebar-nav-logo,
        .sidebar-nav-link {
            display: grid;
            grid-template-columns: max-content max-content;
            align-items: center;
            column-gap: 1rem;
            padding: 0.5rem 0 0.5rem 1.5rem;
        }

        .sidebar-nav-logo {
            margin-bottom: 2rem;
        }

        .sidebar-nav-logo-icon {
            font-size: 1.25rem;
            color: var(--sidebar-text-color);
        }

        .sidebar-nav-logo-name {
            color: var(--sidebar-text-color);
            font-weight: 700;
        }

        .sidebar-nav-link {
            position: relative;
            color: var(--sidebar-highlight-color);
            margin-bottom: 1.5rem;
            transition: var(--transition-speed);
        }

        .sidebar-nav-link:hover {
            color: var(--sidebar-text-color);
        }

        .sidebar-icon {
            font-size: 1.25rem;
        }

        .sidebar-show {
            left: 0;
        }

        .sidebar-body-pd {
            padding-left: calc(var(--sidebar-nav-width) + 1rem);
        }

        /* .sidebar-active {
            color: var(--sidebar-highlight-color);
        }

        .sidebar-active::before {
            content: '';
            position: absolute;
            left: 0;
            width: 2px;
            height: 32px;
            background-color: var(--sidebar-highlight-color);
        } */

        @media screen and (min-width: 768px) {
            body {
                margin: calc(var(--sidebar-header-height) + 1rem) 0 0 0;
                padding-left: calc(var(--sidebar-nav-width) + 2rem);
            }

            .sidebar-header {
                height: calc(var(--sidebar-header-height) + 1rem);
                padding: 0 2rem 0 calc(var(--sidebar-nav-width) + 2rem);
            }

            .sidebar-navbar {
                left: 0;
                padding: 1rem 1rem 0 0;
            }

            .sidebar-show {
                width: calc(var(--sidebar-nav-width) + 156px);
            }

            .sidebar-body-pd {
                padding-left: calc(var(--sidebar-nav-width) + 188px);
            }
        }

    </style>
    <style>
    .modal-backdrop + .modal-backdrop {
        display: none;
    }
    
    /* Make sure the modal content is always on top */
    .modal-content {
        z-index: 1056 !important;
    }
    
    /* Optional: If you still want the backdrop but not interfering */
    .modal-backdrop {
        pointer-events: none;
    }

    /* Fun Loading Screen Styles */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #ffffff;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.3s ease-out;
    }

    .loading-content {
        text-align: center;
        position: relative;
    }

    .loading-hotel {
        width: 120px;
        height: 100px;
        background-color: #45443F;
        position: relative;
        margin: 0 auto;
        border-radius: 5px 5px 0 0;
        overflow: visible;
        animation: hotelBounce 3s infinite;
    }

    .loading-door {
        width: 30px;
        height: 40px;
        background-color: #ad8b3a;
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        border-radius: 3px 3px 0 0;
        animation: doorSpin 3s infinite;
        transform-origin: bottom center;
    }

    .loading-person {
        width: 16px;
        height: 30px;
        background-color: #000;
        position: absolute;
        bottom: -30px;
        left: -20px;
        border-radius: 50% 50% 0 0;
        animation: personJump 3s infinite;
        overflow: visible;
    }

    /* Head */
    .loading-person:before {
        content: '';
        position: absolute;
        width: 12px;
        height: 12px;
        background-color: #f5d7b5;
        border-radius: 50%;
        top: -8px;
        left: 2px;
    }

    /* Arms */
    .loading-person:after {
        content: '';
        position: absolute;
        width: 24px;
        height: 6px;
        background-color: #000;
        top: 5px;
        left: -4px;
        border-radius: 3px;
        transform-origin: center;
        animation: personWave 3s infinite;
    }

    /* Luggage */
    .loading-luggage {
        position: absolute;
        width: 15px;
        height: 18px;
        background-color: #ad8b3a;
        border-radius: 2px;
        bottom: -18px;
        left: 20px;
        animation: luggageFollow 3s infinite;
    }

    .loading-luggage:before {
        content: '';
        position: absolute;
        width: 5px;
        height: 5px;
        background-color: #000;
        border-radius: 50%;
        left: 5px;
        top: 5px;
    }

    .loading-luggage:after {
        content: '';
        position: absolute;
        width: 6px;
        height: 3px;
        background-color: #45443F;
        top: -3px;
        left: 4px;
    }

    .loading-roof {
        width: 140px;
        height: 30px;
        background-color: #ad8b3a;
        position: absolute;
        top: -15px;
        left: -10px;
        transform: rotate(-5deg);
        border-radius: 5px;
        animation: roofWiggle 3s infinite;
    }

    .loading-window {
        width: 15px;
        height: 15px;
        background-color: #e8e8e8;
        position: absolute;
        top: 20px;
        border-radius: 2px;
        overflow: hidden;
    }

    .loading-window:nth-child(1) {
        left: 20px;
        animation: windowWink 3s infinite;
    }

    .loading-window:nth-child(2) {
        right: 20px;
        animation: windowWink 3s infinite 1.5s;
    }

    .loading-window:after {
        content: '';
        position: absolute;
        width: 15px;
        height: 5px;
        background-color: #000;
        bottom: -10px;
        left: 0;
        animation: windowEye 3s infinite;
    }

    .loading-smoke {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #ccc;
        top: -25px;
        right: 20px;
        opacity: 0;
        animation: smokeRise 3s infinite;
    }

    .loading-smoke:nth-child(3) {
        animation-delay: 0.5s;
        right: 30px;
    }

    .loading-smoke:nth-child(4) {
        animation-delay: 1s;
        right: 10px;
    }

    @keyframes doorSpin {
        0%, 15%, 85%, 100% { transform: translateX(-50%) rotateY(0deg); }
        20%, 80% { transform: translateX(-50%) rotateY(70deg); }
    }

    @keyframes personWave {
        0%, 15% { transform: rotate(0deg); }
        20%, 25% { transform: rotate(15deg); }
        30%, 35% { transform: rotate(-10deg); }
        40%, 60% { transform: rotate(0deg); }
        65%, 70% { transform: rotate(-10deg); }
        75%, 80% { transform: rotate(15deg); }
        85%, 100% { transform: rotate(0deg); }
    }

    @keyframes luggageFollow {
        0%, 15% { bottom: -18px; left: -30px; opacity: 1; }
        20% { bottom: -5px; left: 0px; opacity: 1; }
        25% { bottom: 5px; left: 20px; opacity: 1; }
        30% { bottom: -5px; left: 40px; opacity: 1; }
        40%, 60% { bottom: -5px; left: 42px; opacity: 0; }
        90%, 100% { bottom: -18px; left: 160px; opacity: 0; }
    }

    @keyframes personJump {
        0%, 15% { bottom: -30px; left: -20px; }
        20% { bottom: 0px; left: 10px; }
        25% { bottom: 10px; left: 30px; }
        30% { bottom: 0px; left: 50px; }
        40%, 60% { bottom: 0px; left: 52px; transform: scaleX(1); }
        70% { bottom: 0px; left: 90px; transform: scaleX(-1); }
        80% { bottom: 10px; left: 120px; transform: scaleX(-1); }
        90%, 100% { bottom: -30px; left: 150px; transform: scaleX(-1); }
    }

    @keyframes windowWink {
        0%, 48%, 52%, 100% { height: 15px; }
        50% { height: 2px; }
    }

    @keyframes windowEye {
        0%, 48%, 52%, 100% { bottom: -10px; }
        50% { bottom: 7px; }
    }

    @keyframes hotelBounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    @keyframes roofWiggle {
        0%, 100% { transform: rotate(-5deg); }
        50% { transform: rotate(-2deg); }
    }

    @keyframes smokeRise {
        0%, 15% { opacity: 0; transform: scale(0.5); }
        20%, 40% { opacity: 0.8; transform: scale(1) translate(5px, -10px); }
        60% { opacity: 0.4; transform: scale(1.5) translate(10px, -20px); }
        80%, 100% { opacity: 0; transform: scale(2) translate(15px, -30px); }
    }

    .loading-text {
        color: #45443F;
        font-size: 1.2rem;
        font-weight: 500;
        margin-top: 30px;
        text-align: center;
    }

    .loading-joke {
        color: #ad8b3a;
        font-size: 0.9rem;
        font-style: italic;
        margin-top: 15px;
        max-width: 300px;
        text-align: center;
    }
</style>
</head>

<body>
    <?php
    // Only show loading screen on admin/index.php page
    $currentPage = basename($_SERVER['SCRIPT_NAME']);
    $isIndexPage = ($currentPage === 'index.php');
    
    if ($isIndexPage):
    ?>
    <!-- Fun Loading Screen - Only for Dashboard -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="loading-hotel">
                <div class="loading-roof"></div>
                <div class="loading-window"></div>
                <div class="loading-window"></div>
                <div class="loading-door"></div>
                <div class="loading-person"></div>
                <div class="loading-luggage"></div>
                <div class="loading-smoke"></div>
                <div class="loading-smoke"></div>
                <div class="loading-smoke"></div>
            </div>
            <div class="loading-text">Welcome to Elysian Stays Admin</div>
            <div class="loading-joke" id="loadingJoke">Why did the hotel manager go to prison? He got too many bookings!</div>
        </div>
    </div>
    <?php endif; ?>

    <?php require_once("nav-admin.php"); 
    include_once("../db_connect.php");
    ?>
    