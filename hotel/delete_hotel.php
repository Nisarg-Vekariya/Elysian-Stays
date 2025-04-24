<?php
require_once 'config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Validate database connection
if (!isset($conn) || !$conn) {
    die("Database connection failed. Please try again later.");
}

$user_id = $_SESSION['user_id'];

// Get hotel ID for the logged-in user
try {
    $stmt = $conn->prepare("SELECT id FROM hotels WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement");
    }
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$hotel_id = $result['id'];

// Handle deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Start transaction
        $conn->beginTransaction();

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

        // Commit transaction
        $conn->commit();

        // Clear session
        session_destroy();

        // Redirect to home page with success message
        setcookie("success_message", "Your hotel account has been successfully deleted.", time() + 5, "/");
        header('Location: ../index.php');
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $error = "An error occurred while deleting your account. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Hotel Account - Elysian Stays</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .delete-section {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .warning-icon {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .btn-delete:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="delete-section">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle warning-icon"></i>
                <h2 class="mb-4">Delete Hotel Account</h2>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-circle"></i> Warning</h5>
                <p>Are you sure you want to delete your hotel account? This action will:</p>
                <ul>
                    <li>Delete all your hotel information</li>
                    <li>Remove all rooms and bookings</li>
                    <li>Delete all reviews and ratings</li>
                    <li>Remove all gallery images</li>
                    <li>Delete all contact information</li>
                </ul>
                <p class="mb-0"><strong>This action cannot be undone!</strong></p>
            </div>

            <form method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete your hotel account? This action cannot be undone.');">
                <div class="d-grid gap-2">
                    <button type="submit" name="confirm_delete" class="btn btn-delete">
                        <i class="fas fa-trash-alt"></i> Yes, Delete My Account
                    </button>
                    <a href="index.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 