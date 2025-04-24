<?php
require_once("header.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];
    $success_message = '';

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check if email is already taken by another user
    $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $email_check->bind_param("si", $email, $user_id);
    $email_check->execute();
    if ($email_check->get_result()->num_rows > 0) {
        $errors[] = "Email is already taken";
    }
    $email_check->close();

    // If password change is requested
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }

        // Validate new password
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long";
        }

        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
    }

    if (empty($errors)) {
        // Update user information
        $update_query = "UPDATE users SET name = ?, email = ?, phone = ?";
        $params = [$name, $email, $phone];
        $types = "sss";

        // If password is being changed
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query .= ", password = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }

        $update_query .= " WHERE id = ?";
        $params[] = $user_id;
        $types .= "i";

        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param($types, ...$params);

        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Refresh user data
            $query = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
        } else {
            $errors[] = "Error updating profile: " . $conn->error;
        }
        $update_stmt->close();
    }
}
?>

<title>Profile - Elysian Stays</title>
<div class="container my-5 animate__animated animate__fadeIn">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h1 class="text-center mb-4" style="color: var(--primary-color);">Profile Settings</h1>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">Change Password</h5>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-theme">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once("footer.php"); ?>