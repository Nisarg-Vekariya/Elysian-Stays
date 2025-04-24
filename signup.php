<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$success_message = "";
$error_message = "";

// Include PHPMailer
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Database connection
        $servername = "localhost";
        $username = "root"; // Replace with your database username
        $password = ""; // Replace with your database password
        $dbname = "Elysian_Stays"; // Replace with your database name

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }

        // Get form data
        $name = htmlspecialchars($_POST['name']);
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
        $country_code = htmlspecialchars($_POST['country_code']);
        $phone_number = htmlspecialchars($_POST['phone']);
        $phone = $country_code . $phone_number; // Combine country code and phone number
        $city = htmlspecialchars($_POST['city']);
        $country = htmlspecialchars($_POST['country']);
        $terms = isset($_POST['terms']) ? 1 : 0;

        // Handle file upload
        $target_dir = "uploads/"; // Directory where the file will be saved
        $default_profile_pic = "default-profile.png"; // Default profile picture
        $profile_pic = $default_profile_pic; // Default value

        // Check if a file was uploaded
        if (!empty($_FILES['profilePic']['name'])) {
            $profile_pic = basename($_FILES['profilePic']['name']);
            $profile_pic_tmp_name = $_FILES['profilePic']['tmp_name'];
            $target_file = $target_dir . $profile_pic;

            // Check file size (max 2MB)
            $max_file_size = 2 * 1024 * 1024; // 2MB in bytes
            if ($_FILES['profilePic']['size'] > $max_file_size) {
                throw new Exception("Profile picture size must be less than 2MB.");
            }

            // Check if the directory exists, if not, create it
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true); // Create the directory with full permissions
            }

            // Move the uploaded file to the target directory
            if (!move_uploaded_file($profile_pic_tmp_name, $target_file)) {
                throw new Exception("Error uploading profile picture.");
            }
        }

        // Generate token
        $token = bin2hex(random_bytes(50)); // Generate a more secure token

        // Validate form inputs
        if (empty($name) || empty($username) || empty($email) || empty($_POST['password']) || 
            empty($phone_number) || empty($country_code) || empty($city) || empty($country) || !$terms) {
            throw new Exception("All fields are required and you must agree to the terms of service.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }

        if (!preg_match('/^[0-9]+$/', $phone_number)) {
            throw new Exception("Phone number must contain only digits.");
        }

        // Insert data into the database
        $sql = "INSERT INTO users (name, username, email, password, phone, city, country, profile_pic, token, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'user', 'inactive')";

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("sssssssss", $name, $username, $email, $password, $phone, $city, $country, $profile_pic, $token);

        if ($stmt->execute()) {
            // Send verification email using Gmail SMTP
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            try {
                // Server settings for Gmail
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Gmail's SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'use your mail'; // Your Gmail address
                $mail->Password = 'use your app password'; // Your Gmail app password (not your regular password)
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
                $mail->Port = 587; // TCP port to connect to

                // Recipients
                $mail->setFrom('use your mail', 'Elysian Stays'); // Sender email and name
                $mail->addAddress($email, $name); // Recipient email and name

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Verify your email address';
                $verification_link = "http://localhost/elysian-stays/verify.php?token=$token"; // Replace with your actual domain
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
                                <img src='https://i.ibb.co/4y1S02M/Elysian-Stays.png' alt='Elysian Stays' style='max-width: 150px;'>
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
                    
                    $mail->AltBody = "Hi $name,\n\nThank you for signing up with Elysian Stays. Please verify your email address by clicking the link below:\n\n$verification_link\n\nIf you did not sign up for an account, please ignore this email.\n\nThanks,\nElysian Stays Team";
                    
                $mail->send();
                $success_message = "Signup successful! Please check your email to verify your account.";
            } catch (Exception $e) {
                throw new Exception("Error sending verification email: " . $mail->ErrorInfo);
            }
        } else {
            throw new Exception("Error: " . $stmt->error);
        }

        // Close connection
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
    <title>Sign Up</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">

    <!-- jQuery -->
    <script src="js/jquery-3.6.4.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
    <!-- jQuery Validation -->
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/additional-methods.min.js"></script>

    <style>
        .file-input {
            display: none;
        }

        .file-label {
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            background-color: #ad8b3a;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }

        .file-label i {
            margin-right: 8px;
        }

        /* Change the default checkbox cupdateolor when checked */
        input[type="checkbox"]:checked {
            accent-color: #ad8b3a;
        }
        
        /* Theme styling for select dropdowns */
        select.input-field {
            background-color: white;
            border: 1px solid #ccc;
            color: #45443F;
            transition: all 0.3s ease;
        }
        
        select.input-field:focus {
            border-color: #ad8b3a;
            box-shadow: 0 0 5px rgba(173, 139, 58, 0.3);
        }
        
        /* Phone container styling */
        .phone-container select {
            border-color: #ccc;
            background-color: white;
            color: #45443F;
        }
        
        .phone-container select:focus,
        .phone-container select:hover {
            border-color: #ad8b3a;
            box-shadow: 0 0 5px rgba(173, 139, 58, 0.3);
        }
        
        /* Selected option styling */
        select option:checked, 
        select option:focus,
        select option:hover {
            background: #ad8b3a !important;
            color: white !important;
        }
        
        /* City suggestions styling */
        #city-suggestions {
            border: 1px solid #ccc;
        }
        
        .city-suggestion {
            color: #45443F;
            transition: all 0.2s ease;
        }
        
        .city-suggestion:hover {
            background-color: #ad8b3a;
            color: white;
        }
        
        /* Submit button hover */
        .submit-button:hover {
            background-color: #ad8b3a;
            opacity: 0.9;
        }
        
        /* Remove default focus outline and replace with themed one */
        select:focus, input:focus,
        select:hover, input:hover {
            outline: none !important;
            border-color: #ad8b3a !important;
        }
        
        select:focus, input:focus {
            box-shadow: 0 0 5px rgba(173, 139, 58, 0.3) !important;
        }
        
        /* All clickable elements hover */
        a:hover, button:hover, label:hover {
            color: #ad8b3a;
        }
        
        /* Style for the select dropdown arrow */
        select {
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23ad8b3a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
            padding-right: 30px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
    </style>
</head>

<body>
    <div class="container1">
        <div class="left-section">
            <!-- Display Success/Error Messages -->
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

            <button class="back-button" onclick="window.location.href='index.php'">
                <img src="Images/Fallback.svg" width="40px" height="40px" />
            </button>
            <h1 class="animated-heading">Elysian Stays<span class="blinking-cursor">|</span></h1>
            <p class="description">
                Welcome to Elysian Stays—where adventure, discovery, and extraordinary experiences shape your journey. Let us guide the way!
            </p>
            <form id="signupForm" class="login-form" action="" method="POST" enctype="multipart/form-data">
                <input type="text" id="name" name="name" placeholder="Name" class="input-field">
                <input type="text" id="username" name="username" placeholder="Username" class="input-field">
                <input type="email" id="email" name="email" placeholder="Email" class="input-field">
                <input type="password" id="password" name="password" placeholder="Password" class="input-field">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" class="input-field">
                
                <!-- Country dropdown -->
                <select id="country" name="country" class="input-field">
                    <option value="" selected disabled>Select Country</option>
                </select>

                <!-- Phone number with country code (compact version) -->
                <div class="phone-container" style="display: flex; width: 100%; margin-bottom: 5px;">
                    <select id="country_code" name="country_code" class="input-field" style="width: 30%; border-top-right-radius: 0; border-bottom-right-radius: 0; padding: 10px 5px 10px 10px; border-right: 0;">
                        <!-- Popular country codes only -->
                        <option value="+1" data-country="United States" selected>🇺🇸 +1</option>
                        <option value="+44" data-country="United Kingdom">🇬🇧 +44</option>
                        <option value="+1" data-country="Canada">🇨🇦 +1</option>
                        <option value="+61" data-country="Australia">🇦🇺 +61</option>
                        <option value="+91" data-country="India">🇮🇳 +91</option>
                        <option value="" disabled style="border-top: 1px solid #ccc;">──────────</option>
                        <option value="other" data-country="">Other</option>
                    </select>
                    <input type="text" id="phone" name="phone" placeholder="Phone Number" class="input-field" style="width: 70%; border-top-left-radius: 0; border-bottom-left-radius: 0; margin-left: -1px;">
                </div>
                <div id="other_country_codes" style="display: none; margin-bottom: 15px;">
                    <select id="full_country_code" class="input-field" style="width: 100%; border-color: #ccc;">
                        <option value="" selected disabled>Select Country Code</option>
                        <!-- Will be populated by JS -->
                    </select>
                </div>

                <!-- City input with suggestions -->
                <input type="text" id="city" name="city" placeholder="City" class="input-field" autocomplete="off">
                <div id="city-suggestions" style="display:none; position:absolute; z-index:10; background:white; width:68%; max-height:150px; overflow-y:auto; box-shadow:0 2px 4px rgba(0,0,0,0.2); border-radius:5px;"></div>

                <label for="profilePic" class="file-label">Upload Profile Picture</label>
                <input type="file" id="profilePic" name="profilePic" class="file-input">
                <div class="checkbox-container">
                    <input type="checkbox" id="terms" name="terms">
                    <label for="terms">I agree to the <a href="ToS.php" style="color: #ad8b3a;">Terms of Service</a></label>
                </div>
                <button type="submit" class="submit-button">Sign up</button>
            </form>
            <p class="already-signed-up">
                Already signed up? <a href="Login.php">Login</a>
            </p>
        </div>
        <div class="right-section">
            <img src="Images/login1.jpg" alt="travel_pics" class="transition-image">
            <img src="Images/login2.jpg" alt="travel_pics" class="transition-image">
            <img src="Images/login3.jpg" alt="travel_pics" class="transition-image">
            <img src="Images/login4.jpg" alt="travel_pics" class="transition-image">
            <img src="Images/login5.jpg" alt="travel_pics" class="transition-image">
            <img src="Images/login6.jpg" alt="travel_pics" class="transition-image">
            <img src="Images/login7.jpg" alt="travel_pics" class="transition-image">
        </div>
    </div>

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
                    confirm_password: {
                        required: true,
                        equalTo: "#password"
                    },
                    country_code: {
                        required: true
                    },
                    phone: {
                        required: true,
                        digits: true,
                        minlength: 6,
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
                    },
                    terms: {
                        required: true
                    }
                },
                messages: {
                    name: "Please enter correct name",
                    username: "Username must be at least 4 characters",
                    email: "Please enter a valid email address",
                    password: "Password must be at least 8 characters, including 1 uppercase, 1 lowercase, 1 number, and 1 special character",
                    confirm_password: "Passwords do not match",
                    country_code: "Please select a country code",
                    phone: "Please enter a valid phone number",
                    city: "Please enter your city",
                    country: "Please enter your country",
                    profilePic: "Only image files (jpg, jpeg, png, gif) are allowed",
                    terms: "You must agree to the terms and conditions"
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    form.submit(); // Submit the form
                }
            });
        });
    </script>
</body>

</html>