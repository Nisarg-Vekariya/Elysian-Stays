<?php
require_once 'db_connect.php';

// Fetch payment terms page settings
$settingsQuery = "SELECT * FROM payment_page_settings LIMIT 1";
$settingsResult = $conn->query($settingsQuery);

if ($settingsResult && $settingsResult->num_rows > 0) {
    $pageSettings = $settingsResult->fetch_assoc();
} else {
    // Default values if settings not found
    $pageSettings = [
        'page_title' => 'Payments Terms of Service',
        'effective_date' => 'January 2025',
        'footer_text' => '&copy; 2025 Elysian Stays. All rights reserved. | <a href="privacy-policy.php" target="_blank">Privacy Policy</a> | <a href="ToS.php" target="_blank">Terms of Service</a> | <a href="TPS.php" target="_blank">Terms of Payments Service</a>'
    ];
}

// Fetch payment terms sections
$termsQuery = "SELECT * FROM payment_terms WHERE is_active = TRUE ORDER BY display_order";
$termsResult = $conn->query($termsQuery);
$termsSections = [];
if ($termsResult) {
    while ($row = $termsResult->fetch_assoc()) {
        $termsSections[] = $row;
    }
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageSettings['page_title']); ?> - Elysian Stays</title>
    <link rel="icon" href="Image/Elysian_Stays.png" type="image/png">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .hero {
            color: black;
            text-align: center;
            padding: 50px 15px;
            margin-bottom: 30px;
            box-shadow: 0px 5px 15px rgba(0,0,0,0.2);
            animation: fadeIn 1.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .container {
            padding: 20px;
            border-radius: 8px;
            background-color: white;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
            animation: slideUp 1.5s;
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        h1, h2 {
            color: #ad8b3a;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 15px;
            background-color: black;
            color: white;
            font-size: 0.9rem;
        }
        .footer a {
            color: #ad8b3a;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="hero">
        <h1><?php echo htmlspecialchars($pageSettings['page_title']); ?></h1>
        <p>Effective Date: <?php echo htmlspecialchars($pageSettings['effective_date']); ?></p>
    </div>

    <div class="container">
        <?php foreach ($termsSections as $section): ?>
            <h2><?php echo htmlspecialchars($section['section_title']); ?></h2>
            <p><?php echo $section['section_content']; ?></p>
        <?php endforeach; ?>
    </div>

    <div class="footer">
        <p><?php echo $pageSettings['footer_text']; ?></p>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>