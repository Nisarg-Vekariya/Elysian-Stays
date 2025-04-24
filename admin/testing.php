<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Analytics - Hotel Booking</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/animate.min.css">
    <style>
        body {
            background-color: #f7f7f7;
            color: #45443F;
        }

        /* General Card Styling */
        .revenue-overview .card,
        .revenue-by-hotel .card {
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .revenue-overview .card:hover,
        .revenue-by-hotel .card:hover {
            transform: translateY(-10px);
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
        }

        /* Card Header Styling */
        .revenue-overview .card-header,
        .revenue-by-hotel .card-header {
            background-color: #ad8b3a;
            color: white;
        }

        /* Revenue Overview Specific Styling */
        .revenue-overview .revenue-card {
            background-color: #f5f5f5;
            padding: 30px;
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .revenue-overview .revenue-card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.15);
        }

        .revenue-overview .revenue-card h4 {
            color: #45443F;
            font-size: 24px;
        }

        .revenue-overview .revenue-value {
            font-size: 30px;
            color: #ad8b3a;
        }

        .revenue-overview .revenue-description {
            font-size: 14px;
            color: #45443F;
        }

        /* Revenue by Hotel Specific Styling */
        .revenue-by-hotel .hotel-revenue-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .revenue-by-hotel .hotel-revenue-card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.15);
        }

        .revenue-by-hotel .hotel-revenue-card h5 {
            font-size: 20px;
            color: #45443F;
        }

        .revenue-by-hotel .hotel-revenue-value {
            font-size: 28px;
            color: #ad8b3a;
        }
    </style>
</head>

<body>
<?php require("nav-admin.php"); ?>
    <div class="container mt-5">
        <div class="row">
            <!-- Revenue Overview -->
            <div class="col-lg-6 col-md-12 revenue-overview">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5><i class="fa fa-line-chart"></i> Revenue Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="revenue-card">
                                    <h4>Total Revenue</h4>
                                    <p class="revenue-value">$5,200</p>
                                    <p class="revenue-description">This month</p>
                                    <p class="revenue-change text-success">+12% from last month</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="revenue-card">
                                    <h4>Bookings</h4>
                                    <p class="revenue-value">320</p>
                                    <p class="revenue-description">Total bookings this month</p>
                                    <p class="revenue-change text-success">+8% from last month</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue by Each Hotel -->
            <div class="col-lg-12 revenue-by-hotel">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5><i class="fa fa-hotel"></i> Revenue by Each Hotel</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="hotel-revenue-card">
                                    <h5>Hotel A</h5>
                                    <p class="hotel-revenue-value">$2,000</p>
                                    <p>Top performer with a 20% revenue increase.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="hotel-revenue-card">
                                    <h5>Hotel B</h5>
                                    <p class="hotel-revenue-value">$1,500</p>
                                    <p>Steady bookings and consistent growth.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="hotel-revenue-card">
                                    <h5>Hotel C</h5>
                                    <p class="hotel-revenue-value">$700</p>
                                    <p>Revenue dipped; promotional strategies suggested.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
