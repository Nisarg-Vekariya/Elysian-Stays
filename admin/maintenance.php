<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - Elysian Stays</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/animate.min.css">
    <style>
        body {
            /* background-color: #45443F; */
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .maintenance-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .maintenance-icon {
            font-size: 100px;
            color: #ad8b3a;
        }
        .message {
            font-size: 24px;
            margin-top: 20px;
        }
        .sub-message {
            font-size: 18px;
            color: #ad8b3a;
        }
    </style>
</head>
<body>
    <div class="maintenance-container animate__animated animate__fadeIn">
        <i class="fa fa-cogs maintenance-icon animate__animated animate__rotateIn"></i>
        <h1 class="message">We Are Currently Under Maintenance</h1>
        <p class="sub-message">We'll be back shortly. Thank you for your patience.</p>
        <button class="btn btn-warning mt-3 animate__animated animate__pulse animate__infinite">
            <a href="javascript:history.back()" class="nav-link"><i class="fa fa-home"></i> Go Back to Home</a>
            
        </button>
    </div>
    <script src="/js/bootstrap.bundle.min.js"></script>
</body>
</html>
