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

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Restrict access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch current user data with prepared statement
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$target_dir = "uploads/"; // Directory where the file will be saved

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['fullName'];
    $country_code = $_POST['country_code'];
    $phone_number = $_POST['phone'];
    $phone = $country_code . $phone_number; // Combine country code and phone number
    $country = $_POST['country'];
    $city = $_POST['city'];
    $new_password = $_POST['password'];
    $current_password = $_POST['current_password'];

    // Use the existing username from the database instead of form data
    $username = $user['username'];

    // Validate current password only if a new password is provided
    if (!empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            setcookie('error', 'Current password is incorrect.', time() + 5, '/');
            header("Location: update-profile-user.php");
            exit();
        }
    }

    // Handle profile picture upload
    if (!empty($_FILES['profilePic']['name'])) {
        $profile_pic = time() . '_' . basename($_FILES['profilePic']['name']); // Add timestamp to prevent filename conflicts
        $profile_pic_tmp_name = $_FILES['profilePic']['tmp_name'];
        $target_file = $target_dir . $profile_pic;

        // Check file size (max 2MB)
        $max_file_size = 2 * 1024 * 1024; // 2MB in bytes
        if ($_FILES['profilePic']['size'] > $max_file_size) {
            setcookie('error', 'Profile picture size must be less than 2MB.', time() + 5, '/');
            header("Location: update-profile-user.php");
            exit();
        }

        // Check if the directory exists, if not, create it
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Create directory with full permissions
        }

        // Check file type (only allow images)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['profilePic']['type'], $allowed_types)) {
            setcookie('error', 'Only JPG, PNG, and GIF files are allowed.', time() + 5, '/');
            header("Location: update-profile-user.php");
            exit();
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($profile_pic_tmp_name, $target_file)) {
            // Update user's profile picture path in the database using prepared statement
            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->bind_param("si", $profile_pic, $user_id);
            if (!$stmt->execute()) {
                setcookie('error', 'Error updating profile picture in the database.', time() + 5, '/');
                header("Location: update-profile-user.php");
                exit();
            }
            $stmt->close();
        } else {
            setcookie('error', 'Error uploading profile picture.', time() + 5, '/');
            header("Location: update-profile-user.php");
            exit();
        }
    }

    // Update user information with prepared statement
    $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, phone = ?, country = ?, city = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $username, $phone, $country, $city, $user_id);
    
    // Update password if a new one is provided
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->close(); // Close previous prepared statement
        
        $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, phone = ?, country = ?, city = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $name, $username, $phone, $country, $city, $hashed_password, $user_id);
    }

    // Execute the query
    if ($stmt->execute()) {
        setcookie('success', 'Profile updated successfully!', time() + 5, '/');
    } else {
        setcookie('error', 'Error updating profile: ' . $stmt->error, time() + 5, '/');
    }
    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: update-profile-user.php");
    exit();
}

// Check for success or error messages in cookies
$success = isset($_COOKIE['success']) ? $_COOKIE['success'] : '';
$error = isset($_COOKIE['error']) ? $_COOKIE['error'] : '';

// Clear cookies after displaying messages
if ($success) {
    setcookie('success', '', time() - 3600, '/');
}
if ($error) {
    setcookie('error', '', time() - 3600, '/');
}
?>

<?php require_once 'header.php'; ?>
<title>Update Profile</title>
<div class="profile-update-page">
    <div class="profile-update-container">
        <div class="profile-form-container animate__animated animate__fadeIn animate__slower">
            <!-- Alerts Container -->
            <div class="alerts-container">
                <!-- Display Success Message -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Display Error Message -->
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>

            <form id="profileForm" class="animate__animated animate__fadeIn animate__delay-2s" method="POST" action="" enctype="multipart/form-data">
                <!-- Profile Picture Section -->
                <div class="profile-pic-section">
                    <h2 class="section-title">My Profile</h2>
                    <div class="profile-pic-wrapper animate__animated animate__zoomIn animate__delay-1s">
                        <img id="profileImage" src="<?php echo !empty($user['profile_pic']) ? $target_dir . htmlspecialchars($user['profile_pic']) : 'default-profile.jpg'; ?>" class="profile-pic" alt="Profile Picture">
                        <div class="profile-pic-overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    
                    <div class="file-input-container">
                        <input type="file" id="fileInput" name="profilePic" accept="image/*" class="visually-hidden">
                        <label for="fileInput" class="profile-pic-label animate__animated animate__fadeInUp animate__delay-1s">
                            <i class="fas fa-camera"></i> Change Photo
                        </label>
                        <p class="text-muted small mt-2">Max size: 2MB (JPG, PNG, GIF)</p>
                    </div>
                    
                    <!-- Account Info Section (Read-only) -->
                    <div class="account-info-section">
                        <h3><i class="fas fa-user-shield profile-form-icon"></i> Account Info</h3>
                        <div class="read-only-info">
                            <p class="info-label">Username</p>
                            <p class="info-value"><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                        <div class="read-only-info">
                            <p class="info-label">Email</p>
                            <p class="info-value"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <p class="text-muted small">Account details cannot be changed</p>
                    </div>
                </div>
                
                <!-- Profile Details Section -->
                <div class="profile-details-section">
                    <div class="profile-section-header">
                        <h3><i class="fas fa-id-card profile-form-icon"></i> Personal Information</h3>
                    </div>
                    
                    <div class="profile-form-group animate__animated animate__fadeInLeft animate__delay-2s">
                        <label class="profile-form-label" for="fullName">
                            <i class="fas fa-user profile-form-icon"></i> Full Name
                        </label>
                        <input type="text" id="fullName" name="fullName" class="profile-form-input" placeholder="Enter your full name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <!-- Hidden fields for username to maintain form structure -->
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                    
                    <div class="profile-form-group animate__animated animate__fadeInLeft animate__delay-3s">
                        <label class="profile-form-label" for="phone">
                            <i class="fas fa-phone profile-form-icon"></i> Phone Number
                        </label>
                        <input type="tel" id="phone" name="phone" class="profile-form-input" placeholder="Enter 10-digit phone number" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                    
                    <div class="profile-section-header full-width">
                        <h3><i class="fas fa-map-marker-alt profile-form-icon"></i> Location</h3>
                    </div>
                    
                    <div class="profile-form-group animate__animated animate__fadeInRight animate__delay-3s">
                        <label class="profile-form-label" for="country">
                            <i class="fas fa-globe profile-form-icon"></i> Country
                        </label>
                        <input type="text" id="country" name="country" class="profile-form-input" placeholder="Enter your country" value="<?php echo htmlspecialchars($user['country']); ?>" required>
                    </div>
                    
                    <div class="profile-form-group animate__animated animate__fadeInLeft animate__delay-4s">
                        <label class="profile-form-label" for="city">
                            <i class="fas fa-city profile-form-icon"></i> City
                        </label>
                        <input type="text" id="city" name="city" class="profile-form-input" placeholder="Enter your city" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                    </div>
                
                    <!-- Password Section -->
                    <div class="password-section animate__animated animate__fadeInUp animate__delay-5s">
                        <div class="profile-section-header">
                            <h3><i class="fas fa-lock"></i> Security</h3>
                        </div>
                        
                        <div class="profile-form-group">
                            <label class="profile-form-label" for="password">
                                <i class="fas fa-key"></i> New Password
                            </label>
                            <input type="password" id="password" name="password" class="profile-form-input" placeholder="Enter new password (optional)" minlength="6">
                            <small class="form-text text-muted">Leave blank to keep current password</small>
                        </div>
                        
                        <div class="profile-form-group">
                            <label class="profile-form-label" for="current_password">
                                <i class="fas fa-shield-alt"></i> Current Password
                            </label>
                            <input type="password" id="current_password" name="current_password" class="profile-form-input" placeholder="Required to change password">
                        </div>
                    </div>
                    
                    <!-- Buttons Section -->
                    <div class="button-section animate__animated animate__fadeInUp animate__delay-5s">
                        <button type="submit" class="profile-form-button profile-form-button-save">
                            <i class="fas fa-save"></i> SAVE CHANGES
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Preview uploaded image before form submission
    document.getElementById("fileInput").addEventListener("change", function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById("profileImage").src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Allow clicking the image to trigger file upload
    document.querySelector(".profile-pic-overlay").addEventListener("click", function() {
        document.getElementById("fileInput").click();
    });
</script>

<style>
.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Theme styling for country and phone inputs */
select.profile-form-input {
    background-color: white;
    color: #333;
    transition: all 0.3s ease;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23ad8b3a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
    padding-right: 30px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

.phone-container select {
    border-color: #ccc;
    background-color: white;
    color: #333;
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
    color: #333;
    transition: all 0.2s ease;
}

.city-suggestion:hover {
    background-color: #ad8b3a;
    color: white;
}
</style>

<?php require_once 'footer.php'; ?>