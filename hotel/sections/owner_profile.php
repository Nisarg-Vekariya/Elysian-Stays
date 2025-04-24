<?php
require_once '../config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Return JSON response for AJAX requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'redirect' => '../login.php']);
        exit;
    }
    // Redirect to login page for direct access
    header('Location: ../login.php');
    exit;
}

// Set user ID from session
$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        // Get form data
        $name = $_POST['name'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $city = $_POST['city'];
        $country = $_POST['country'];
        
        // Handle password update (only if provided)
        $password_query = "";
        $params = [$name, $username, $email, $phone, $city, $country];
        
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $password_query = ", password = ?";
            $params[] = $password;
        }
        
        // Handle profile pic upload
        $profile_pic_query = "";
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $upload_dir = '../uploads/users/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_path)) {
                $profile_pic = 'uploads/users/' . $new_filename;
                $profile_pic_query = ", profile_pic = ?";
                $params[] = $profile_pic;
            }
        }
        
        // Update user information
        $sql = "UPDATE users SET 
                name = ?, 
                username = ?,
                email = ?,
                phone = ?,
                city = ?,
                country = ?" . $password_query . $profile_pic_query . "
                WHERE id = ?";
                
        $params[] = $user_id;
        
        $stmt = $conn->prepare($sql);
        if ($stmt->execute($params)) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
        }
        exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'logout') {
        // Handle logout
        session_destroy();
        echo json_encode(['success' => true, 'redirect' => '../login.php']);
        exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_account') {
        // Handle account deletion
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // Get hotel ID for the user
            $stmt = $conn->prepare("SELECT id FROM hotels WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $hotel_id = $result['id'];
                
                // Delete related records first (due to foreign key constraints)
                
                // Delete gallery images
                $stmt = $conn->prepare("DELETE FROM gallery_images WHERE hotel_id = ?");
                $stmt->execute([$hotel_id]);
                
                // Delete contact information
                $stmt = $conn->prepare("DELETE FROM contact_info WHERE hotel_id = ?");
                $stmt->execute([$hotel_id]);
                
                // Delete hotel amenities
                $stmt = $conn->prepare("DELETE FROM hotel_amenities WHERE hotel_id = ?");
                $stmt->execute([$hotel_id]);
                
                // Delete reviews
                $stmt = $conn->prepare("DELETE FROM reviews WHERE hotel_id = ?");
                $stmt->execute([$hotel_id]);
                
                // Delete bookings
                $stmt = $conn->prepare("DELETE FROM bookings WHERE hotel_id = ?");
                $stmt->execute([$hotel_id]);
                
                // Delete rooms
                $stmt = $conn->prepare("DELETE FROM rooms WHERE hotel_id = ?");
                $stmt->execute([$hotel_id]);
                
                // Finally, delete the hotel
                $stmt = $conn->prepare("DELETE FROM hotels WHERE id = ? AND user_id = ?");
                $stmt->execute([$hotel_id, $user_id]);
            }
            
            // Delete user account
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            // Commit transaction
            $conn->commit();
            
            // Clear session
            session_destroy();
            
            echo json_encode(['success' => true, 'message' => 'Account deleted successfully', 'redirect' => '../index.php']);
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            echo json_encode(['success' => false, 'error' => 'Failed to delete account: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user exists and initialize default values
if ($user === false) {
    // User not found, initialize with default values
    $user = [
        'name' => '',
        'username' => '',
        'email' => '',
        'phone' => '',
        'city' => '',
        'country' => '',
        'role' => 'Hotel Owner',
        'profile_pic' => 'assets/images/default-profile.jpg'
    ];
} else {
    // Set default values if user data fields are empty
    if (empty($user['name'])) $user['name'] = '';
    if (empty($user['username'])) $user['username'] = '';
    if (empty($user['email'])) $user['email'] = '';
    if (empty($user['phone'])) $user['phone'] = '';
    if (empty($user['city'])) $user['city'] = '';
    if (empty($user['country'])) $user['country'] = '';
    if (empty($user['profile_pic'])) $user['profile_pic'] = 'assets/images/default-profile.jpg';
}
?>

<div class="content-section">
    <div class="section-header">
        <h2>Owner Profile</h2>
        <button class="btn btn-danger" id="logoutBtn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>
    
    <div class="profile-container">
        <div class="profile-sidebar">
            <div class="profile-image-container">
                <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" id="profileImage">
                <div class="profile-image-overlay">
                    <i class="fas fa-camera"></i>
                    <span>Change Photo</span>
                </div>
            </div>
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                <p><?php echo htmlspecialchars($user['role']); ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                <?php if (!empty($user['phone'])): ?>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="profile-content">
            <form id="profileForm" class="profile-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                <input type="file" id="profilePicInput" name="profile_pic" accept="image/*" style="display: none;">
                
                <div class="form-section">
                    <h3>Personal Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Location</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($user['country']); ?>" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Security</h3>
                    
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                        <small class="form-text text-muted">Min. 8 characters with numbers and letters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm new password">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-danger" id="deleteAccountBtn">
                        <i class="fas fa-trash-alt"></i> Delete Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.btn-danger {
    background-color: var(--danger);
    color: white;
}

.profile-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 20px;
}

.profile-sidebar {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.profile-image-container {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto 20px;
    cursor: pointer;
}

.profile-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid var(--primary-color);
}

.profile-image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: rgba(0,0,0,0.5);
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.profile-image-container:hover .profile-image-overlay {
    opacity: 1;
}

.profile-info {
    text-align: center;
}

.profile-info h3 {
    margin-bottom: 5px;
    color: var(--primary-color);
}

.profile-info p {
    margin-bottom: 10px;
    color: #666;
}

.profile-content {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* Responsive design */
@media (max-width: 992px) {
    .profile-container {
        grid-template-columns: 1fr;
    }
    
    .profile-sidebar {
        text-align: center;
    }
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 10px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Profile picture change
    $('#profileImage, .profile-image-overlay').on('click', function() {
        $('#profilePicInput').click();
    });
    
    $('#profilePicInput').on('change', function(e) {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#profileImage').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Form validation
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate password match
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        
        if (password && password !== confirmPassword) {
            showNotification('Passwords do not match', 'error');
            return false;
        }
        
        // Show loading indicator
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        submitBtn.prop('disabled', true);
        
        // Send the data
        var formData = new FormData(this);
        
        $.ajax({
            url: 'sections/owner_profile.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                // Reset the button
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                
                if (response.success) {
                    showNotification('Profile updated successfully', 'success');
                    loadSection('owner_profile');
                } else {
                    showNotification(response.error || 'Failed to update profile', 'error');
                }
            },
            error: function(xhr, status, error) {
                // Reset the button
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                
                showNotification('An error occurred while updating the profile: ' + error, 'error');
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    });
    
    // Logout functionality
    $('#logoutBtn').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to logout?')) {
            $.ajax({
                url: 'sections/owner_profile.php',
                type: 'POST',
                data: {action: 'logout'},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    }
                }
            });
        }
    });

    // Delete account functionality
    $('#deleteAccountBtn').on('click', function() {
        window.location.href = 'delete_hotel.php';
    });
});
</script> 