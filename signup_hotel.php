<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Adjust path if needed (for Composer installation)
// If not using Composer, use:
// require 'path/to/PHPMailer/src/Exception.php';
// require 'path/to/PHPMailer/src/PHPMailer.php';
// require 'path/to/PHPMailer/src/SMTP.php';

// Initialize variables
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Database connection
        $servername = "localhost";
        $username = "root"; // Replace with your database username
        $password = ""; // Replace with your database password
        $dbname = "Elysian_Stays"; // Replace with your database name

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }

        // Get form data
        $name = htmlspecialchars($_POST['name']);
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $country_code = htmlspecialchars($_POST['country_code']);
        $phone_number = htmlspecialchars($_POST['phone']);
        $phone = $country_code . $phone_number; // Combine country code and phone number
        $city = htmlspecialchars($_POST['city']);
        $country = htmlspecialchars($_POST['country']);

        // Handle file upload
        $target_dir = "uploads/";
        $default_profile_pic = "user-iconset-no-profile.jpg";
        $profile_pic = $default_profile_pic;

        if (!empty($_FILES['profilePic']['name'])) {
            $profile_pic = basename($_FILES['profilePic']['name']);
            $profile_pic_tmp_name = $_FILES['profilePic']['tmp_name'];
            $target_file = $target_dir . $profile_pic;

            $max_file_size = 2 * 1024 * 1024; // 2MB
            if ($_FILES['profilePic']['size'] > $max_file_size) {
                throw new Exception("Profile picture size must be less than 2MB.");
            }

            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (!move_uploaded_file($profile_pic_tmp_name, $target_file)) {
                throw new Exception("Error uploading profile picture.");
            }
        }

        // Generate token
        $token = bin2hex(random_bytes(50));

        // Validate form data
        if (empty($name) || empty($username) || empty($email) || empty($_POST['password']) || empty($phone_number) || empty($country_code) || empty($city) || empty($country)) {
            throw new Exception("All fields are required");
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        } elseif (strlen($_POST['password']) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        } elseif (!preg_match('/^[0-9]+$/', $phone_number)) {
            throw new Exception("Phone number must contain only digits");
        }

        // Insert data into the database
        $sql = "INSERT INTO users (name, username, email, password, phone, city, country, profile_pic, token, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'hotel', 'inactive')";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("sssssssss", $name, $username, $email, $password, $phone, $city, $country, $profile_pic, $token);

        if ($stmt->execute()) {
            // Send verification email using PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
                $mail->SMTPAuth = true;
                $mail->Username = 'use your mail'; // Replace with your SMTP username
                $mail->Password = 'use your app password'; // Replace with your SMTP password or App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('no-reply@elysianstays.com', 'Elysian Stays');
                $mail->addAddress($email, $name);

                // Content
                $verification_link = "http://localhost/elysian-stays/verify.php?token=$token"; // Replace with your domain
                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email - Elysian Stays';
                // $mail->Body    = "
                //     <h2>Welcome to Elysian Stays!</h2>
                //     <p>Hello $name,</p>
                //     <p>Thank you for signing up with Elysian Stays. Please click the link below to verify your email address:</p>
                //     <p><a href='$verification_link' style='background-color: #ad8b3a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Email</a></p>
                //     <p>If you did not sign up for this account, please ignore this email.</p>
                //     <p>Best regards,<br>Elysian Stays Team</p>
                // ";
                $mail->Body = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        <title>Email Verification</title>
                    </head>
                    <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                        <div style='max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                            <div style='text-align: center; padding-bottom: 20px;'>
                                <img src='https://ibb.co/tfL21zc' alt='Elysian Stays' style='max-width: 150px;'>
                            </div>
                            <h2 style='color: #ad8b3a; text-align: center;'>Verify Your Email Address</h2>
                            <p style='color: #333; font-size: 16px;'>Hi $name,</p>
                            <p style='color: #555; font-size: 16px;'>Thank you for signing up with <strong>Elysian Stays</strong>. To complete your registration, please verify your email address by clicking the button below:</p>
                            <div style='text-align: center; margin: 20px 0;'>
                                <a href='$verification_link' style='background-color: #ad8b3a; color: #ffffff; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-size: 16px; display: inline-block;'>Verify Email</a>
                            </div>
                            <p style='color: #555; font-size: 16px;'>Or you can copy and paste the following link into your browser:</p>
                            <p style='word-break: break-all; text-align: center;'><a href='$verification_link' style='color: #ad8b3a;'>$verification_link</a></p>
                            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                            <p style='color: #777; font-size: 14px; text-align: center;'>If you did not sign up for an account, please ignore this email.</p>
                            <p style='color: #777; font-size: 14px; text-align: center;'>Thanks,<br><strong>Elysian Stays Team</strong></p>
                        </div>
                    </body>
                    </html>
                    ";
                $mail->AltBody = "Hello $name,\n\nThank you for signing up with Elysian Stays. Please visit this link to verify your email address: $verification_link\n\nIf you did not sign up for this account, please ignore this email.\n\nBest regards,\nElysian Stays Team";

                $mail->send();
                $success_message = "Signup successful! A verification email has been sent to your email address.";
            } catch (Exception $e) {
                throw new Exception("Error sending verification email: " . $mail->ErrorInfo);
            }
        } else {
            throw new Exception("Error: " . $stmt->error);
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Elysian Stays</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">
    
    <style>
        :root {
            --theme-color: #ad8b3a;
            --theme-hover: #8c6f2e;
            --theme-secondary: #45443F;
        }
        
        body {
            background-color: #f8f9fa;
            /* font-family: 'Arial', sans-serif; */
        }

        .signup-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .form-label {
            color: var(--theme-color);
            font-weight: 600;
        }

        .form-control {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 0.75rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--theme-color);
            box-shadow: 0 0 5px rgba(173, 139, 58, 0.3);
        }

        .btn-theme {
            background-color: var(--theme-color);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-theme:hover {
            background-color: var(--theme-hover);
        }

        .error {
            color: #dc3545;
            font-size: 0.9em;
            margin-top: 0.25rem;
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: var(--theme-color) !important;
        }
        
        /* Theme styling for select dropdowns */
        select.form-control {
            background-color: white;
            border: 1px solid #ced4da;
            color: var(--theme-secondary);
            transition: all 0.3s ease;
        }
        
        select.form-control:focus {
            border-color: var(--theme-color);
            box-shadow: 0 0 5px rgba(173, 139, 58, 0.3);
        }
        
        /* Phone container styling */
        .phone-container select {
            border-color: #ced4da;
            background-color: white;
            color: var(--theme-secondary);
        }
        
        .phone-container select:focus,
        .phone-container select:hover {
            border-color: var(--theme-color);
            box-shadow: 0 0 5px rgba(173, 139, 58, 0.3);
        }
        
        /* Selected option styling */
        select option:checked, 
        select option:focus,
        select option:hover {
            background: var(--theme-color) !important;
            color: white !important;
        }
        
        /* City suggestions styling */
        #city-suggestions {
            border: 1px solid #ced4da;
        }
        
        .city-suggestion {
            color: var(--theme-secondary);
            transition: all 0.2s ease;
        }
        
        .city-suggestion:hover {
            background-color: var(--theme-color);
            color: white;
        }
        
        /* Remove default focus outline and replace with themed one */
        select:focus, input:focus,
        select:hover, input:hover {
            outline: none !important;
            border-color: var(--theme-color) !important;
        }
        
        select:focus, input:focus {
            box-shadow: 0 0 5px rgba(173, 139, 58, 0.3) !important;
        }
        
        /* All form controls on hover */
        .form-control:hover {
            border-color: var(--theme-color);
        }
        
        /* Button hover reinforcement */
        .btn-theme:hover {
            background-color: var(--theme-hover);
            color: white;
        }
        
        /* Style for the select dropdown arrow */
        select.form-control {
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23ad8b3a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
            padding-right: 2.5rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-light bg-light">
        <div class="container">
            <a href="list-your-place.php" class="btn btn-link me-3">
                <img src="Images/Fallback.svg" alt="back" width="20">
            </a>
            <a class="navbar-brand fw-bold" href="index.php">Elysian Stays</a>
        </div>
    </nav>

    <!-- Messages -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Signup Form -->
    <div class="container mt-5 mb-5">
        <div class="signup-container">
            <h2 class="text-center mb-4" style="color: var(--theme-color);">Sign Up for Elysian Stays Partner Program</h2>
            <form id="signupForm" action="signup_hotel.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name">
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
                </div>
                <div class="mb-3">
                    <label for="country" class="form-label">Country</label>
                    <select class="form-control" id="country" name="country">
                        <option value="" selected disabled>Select Country</option>
                        <!-- Will be populated by JS -->
                    </select>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <div class="phone-container" style="display: flex; width: 100%; margin-bottom: 5px;">
                        <select id="country_code" name="country_code" class="form-control" style="width: 30%; border-top-right-radius: 0; border-bottom-right-radius: 0; padding: 0.75rem 5px 0.75rem 10px; border-right: 0;">
                            <!-- Popular country codes only -->
                            <option value="+1" data-country="United States" selected>🇺🇸 +1</option>
                            <option value="+44" data-country="United Kingdom">🇬🇧 +44</option>
                            <option value="+1" data-country="Canada">🇨🇦 +1</option>
                            <option value="+61" data-country="Australia">🇦🇺 +61</option>
                            <option value="+91" data-country="India">🇮🇳 +91</option>
                            <option value="" disabled style="border-top: 1px solid #ced4da;">──────────</option>
                            <option value="other" data-country="">Other</option>
                        </select>
                        <input type="tel" class="form-control" id="phone" name="phone" style="width: 70%; border-top-left-radius: 0; border-bottom-left-radius: 0; margin-left: -1px;">
                    </div>
                    <div id="other_country_codes" style="display: none; margin-bottom: 15px;">
                        <select id="full_country_code" class="form-control" style="width: 100%;">
                            <option value="" selected disabled>Select Country Code</option>
                            <!-- Will be populated by JS -->
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="city" class="form-label">City</label>
                    <input type="text" class="form-control" id="city" name="city" autocomplete="off">
                    <div id="city-suggestions" style="display:none; position:absolute; z-index:10; background:white; width:calc(100% - 2rem); max-height:150px; overflow-y:auto; box-shadow:0 2px 4px rgba(0,0,0,0.2); border-radius:5px;"></div>
                </div>
                <div class="mb-4">
                    <label for="profilePic" class="form-label">Profile Picture</label>
                    <input type="file" class="form-control" id="profilePic" name="profilePic" accept="image/*">
                </div>
                <div class="text-center mb-4">
                    <small>By selecting Agree and continue, I agree to Elysian Stay's
                        <a href="ToS.php" target="_blank" style="color: var(--theme-color);">Terms of Service</a>,
                        <a href="TPS.php" target="_blank" style="color: var(--theme-color);">Payments Terms of Service</a>, and acknowledge the
                        <a href="privacy-policy.php" target="_blank" style="color: var(--theme-color);">Privacy Policy</a>.
                    </small>
                </div>
                <div class="text-center">
                    <button type="submit" name="submit" class="btn btn-theme text-white"> Agree and continue</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts in correct order -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show full country code select when "Other" is chosen
            $('#country_code').on('change', function() {
                if ($(this).val() === 'other') {
                    $('#other_country_codes').show();
                } else {
                    $('#other_country_codes').hide();
                }
            });
            
            // When selecting from full country dropdown, update the main dropdown
            $('#full_country_code').on('change', function() {
                const selectedCode = $(this).val();
                const selectedCountry = $('#full_country_code option:selected').attr('data-country');
                
                // Add the selected code to the main dropdown if not already there
                if ($('#country_code option[value="' + selectedCode + '"]').length === 0) {
                    const newOption = new Option(
                        $('#full_country_code option:selected').text(), 
                        selectedCode, 
                        true, 
                        true
                    );
                    $(newOption).attr('data-country', selectedCountry);
                    $('#country_code').append(newOption);
                }
                
                $('#country_code').val(selectedCode).trigger('change');
                $('#other_country_codes').hide();
            });

            // Fetch countries and populate dropdown
            fetch('https://restcountries.com/v3.1/all')
                .then(response => response.json())
                .then(data => {
                    // Sort countries by name
                    data.sort((a, b) => a.name.common.localeCompare(b.name.common));
                    
                    // Create a map to store country data
                    window.countryData = {};
                    
                    // Populate countries dropdown
                    const countrySelect = document.getElementById('country');
                    const fullCountryCodeSelect = document.getElementById('full_country_code');
                    
                    // Popular countries (already in compact dropdown)
                    const popularCountries = ['United States', 'United Kingdom', 'Canada', 'Australia', 'India'];
                    
                    data.forEach(country => {
                        // Add to country dropdown
                        const option = document.createElement('option');
                        option.value = country.name.common;
                        option.textContent = country.name.common;
                        countrySelect.appendChild(option);
                        
                        // Add to full country code dropdown if calling codes are available
                        if (country.idd && country.idd.root) {
                            const code = country.idd.root + (country.idd.suffixes ? country.idd.suffixes[0] : '');
                            const flag = country.flag || '';
                            
                            const codeOption = document.createElement('option');
                            codeOption.value = code;
                            codeOption.textContent = `${flag} ${code} (${country.name.common})`;
                            codeOption.setAttribute('data-country', country.name.common);
                            fullCountryCodeSelect.appendChild(codeOption);
                            
                            // Store country data
                            window.countryData[country.name.common] = {
                                code: code,
                                flag: flag,
                                cities: []
                            };
                        }
                    });
                    
                    // Fix for popular countries that might not be in the global data
                    if (!window.countryData['United States']) window.countryData['United States'] = { code: '+1', flag: '🇺🇸', cities: [] };
                    if (!window.countryData['United Kingdom']) window.countryData['United Kingdom'] = { code: '+44', flag: '🇬🇧', cities: [] };
                    if (!window.countryData['Canada']) window.countryData['Canada'] = { code: '+1', flag: '🇨🇦', cities: [] };
                    if (!window.countryData['Australia']) window.countryData['Australia'] = { code: '+61', flag: '🇦🇺', cities: [] };
                    if (!window.countryData['India']) window.countryData['India'] = { code: '+91', flag: '🇮🇳', cities: [] };
                })
                .catch(error => console.error('Error loading countries:', error));
            
            // Sync country selection with country code
            $('#country').on('change', function() {
                const selectedCountry = $(this).val();
                if (selectedCountry && window.countryData[selectedCountry]) {
                    const countryInfo = window.countryData[selectedCountry];
                    
                    // Check if code exists in the main dropdown
                    const existingOption = $('#country_code option').filter(function() {
                        return $(this).attr('data-country') === selectedCountry;
                    });
                    
                    if (existingOption.length) {
                        $('#country_code').val(existingOption.val()).trigger('change');
                    } else {
                        // Show full dropdown for selection
                        $('#country_code').val('other').trigger('change');
                        
                        // Pre-select in the full dropdown
                        $('#full_country_code option').each(function() {
                            if ($(this).attr('data-country') === selectedCountry) {
                                $('#full_country_code').val($(this).val());
                                return false;
                            }
                        });
                    }
                    
                    // Load cities for selected country
                    loadCities(selectedCountry);
                }
            });
            
            // City search with suggestions
            function loadCities(country) {
                if (!country) return;
                
                // City suggestion API
                fetch(`https://secure.geonames.org/searchJSON?name_startsWith=&country=${getCountryCode(country)}&featureClass=P&orderby=population&maxRows=30&username=demo`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.geonames) {
                            window.countryData[country].cities = data.geonames.map(city => city.name);
                        }
                    })
                    .catch(error => console.error('Error loading cities:', error));
            }
            
            // Helper function to get ISO country code for API calls
            function getCountryCode(countryName) {
                // This is a simple mapping of some common countries
                const countryCodes = {
                    'United States': 'US',
                    'Canada': 'CA',
                    'United Kingdom': 'GB',
                    'Australia': 'AU',
                    'India': 'IN',
                    'Germany': 'DE',
                    'France': 'FR',
                    'Italy': 'IT',
                    'Spain': 'ES',
                    'Japan': 'JP',
                    'China': 'CN'
                };
                return countryCodes[countryName] || 'US'; // Default to US if not found
            }
            
            // Handle city input for suggestions
            $('#city').on('input', function() {
                const input = $(this).val().toLowerCase();
                const selectedCountry = $('#country').val();
                
                if (!input || !selectedCountry || !window.countryData[selectedCountry].cities.length) {
                    $('#city-suggestions').hide();
                    return;
                }
                
                // Filter cities based on input
                const suggestions = window.countryData[selectedCountry].cities.filter(city => 
                    city.toLowerCase().startsWith(input)
                );
                
                // Build suggestions dropdown
                if (suggestions.length) {
                    let suggestionsHtml = '';
                    suggestions.slice(0, 10).forEach(city => {
                        suggestionsHtml += `<div class="city-suggestion" style="padding:8px 12px; cursor:pointer; hover:background-color:#f0f0f0;">${city}</div>`;
                    });
                    
                    $('#city-suggestions').html(suggestionsHtml).show();
                    
                    // Handle suggestion click
                    $('.city-suggestion').on('click', function() {
                        $('#city').val($(this).text());
                        $('#city-suggestions').hide();
                    });
                } else {
                    $('#city-suggestions').hide();
                }
            });
            
            // Hide suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#city, #city-suggestions').length) {
                    $('#city-suggestions').hide();
                }
            });

            $("#signupForm").validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 2,
                        pattern: /^[a-zA-Z\s]+$/
                    },
                    username: {
                        required: true,
                        minlength: 4
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true,
                        minlength: 8,
                        pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/
                    },
                    confirmPassword: {
                        required: true,
                        equalTo: "#password"
                    },
                    country_code: {
                        required: true
                    },
                    phone: {
                        required: true,
                        digits: true,
                        minlength: 10,
                        maxlength: 15
                    },
                    city: {
                        required: true
                    },
                    country: {
                        required: true
                    },
                    profilePic: {
                        extension: "jpg|jpeg|png|gif"
                    }
                },
                messages: {
                    name: {
                        required: "Please enter your full name",
                        minlength: "Name must be at least 2 characters long"
                    },
                    username: {
                        required: "Please enter a username",
                        minlength: "Username must be at least 4 characters long"
                    },
                    email: {
                        required: "Please enter your email",
                        email: "Please enter a valid email address"
                    },
                    password: {
                        required: "Please enter a password",
                        minlength: "Password must be at least 8 characters long",
                        pattern: "Password must include uppercase, lowercase, number, and special character"
                    },
                    confirmPassword: {
                        required: "Please confirm your password",
                        equalTo: "Passwords do not match"
                    },
                    country_code: {
                        required: "Please select a country code"
                    },
                    phone: {
                        required: "Please enter your phone number",
                        digits: "Please enter only numbers",
                        minlength: "Phone number must be 10-15 digits",
                        maxlength: "Phone number must be 10-15 digits"
                    },
                    city: {
                        required: "Please enter your city"
                    },
                    country: {
                        required: "Please select a country"
                    },
                    profilePic: {
                        accept: "Please upload a valid image file (JPG, JPEG, PNG, or GIF)"
                    }
                },
                errorElement: "div",
                errorPlacement: function(error, element) {
                    error.addClass("error");
                    error.insertAfter(element);
                },
                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },
                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                }
            });
        });
    </script>
</body>
</html>