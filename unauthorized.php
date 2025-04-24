<?php
// unauthorized.php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied - Elysian Stays</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ad8b3a;
            --secondary: #45443F;
            --dark: #000;
            --light: #fff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f8f8;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background-color: var(--light);
            padding: 60px 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 500px;
            text-align: center;
        }

        .container h1 {
            font-family: 'Playfair Display', serif;
            font-size: 80px;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .container h2 {
            font-size: 24px;
            color: var(--secondary);
            margin-bottom: 20px;
        }

        .container p {
            color: #555;
            margin-bottom: 30px;
        }

        .btn {
            background-color: var(--primary);
            color: var(--light);
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #8a6f2b;
        }

        @media (max-width: 600px) {
            .container {
                padding: 40px 20px;
            }

            .container h1 {
                font-size: 60px;
            }

            .container h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>401</h1>
        <h2>Unauthorized Access</h2>
        <p>Sorry, you don’t have permission to view this page.<br>
        If you believe this is a mistake, please contact support.</p>
        <a href="index.php" class="btn">Return to Home</a>
    </div>
</body>
</html>
