<?php
session_start();
require_once '../../db_connect.php';
require '../operation/contact_functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_hero'])) {
        try {
            // Update hero section with proper error handling
            $stmt = $conn->prepare("UPDATE contact_hero SET title = ?, background_image = ?, search_placeholder = ? LIMIT 1");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            // Sanitize inputs
            $title = filter_var($_POST['hero_title'], FILTER_SANITIZE_STRING);
            $image = trim($_POST['hero_image']); // Don't filter URL to preserve special characters
            $placeholder = filter_var($_POST['hero_placeholder'], FILTER_SANITIZE_STRING);
            
            $stmt->bind_param("sss", $title, $image, $placeholder);
            
            if ($stmt->execute()) {
                $_SESSION['toast'] = ['type' => 'success', 'message' => 'Hero section updated successfully'];
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Error updating hero section: ' . $e->getMessage()];
            error_log('Hero section update error: ' . $e->getMessage());
        }
    }

    elseif (isset($_POST['save_numbers'])) {
        // Update contact numbers
        $conn->begin_transaction();
        try {
            if (!$conn->query("DELETE FROM contact_numbers")) {
                throw new Exception("Delete failed: " . $conn->error);
            }
            
            $stmt = $conn->prepare("INSERT INTO contact_numbers (region, number, display_order) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            foreach ($_POST['number_region'] as $index => $region) {
                $number = filter_var($_POST['number_value'][$index], FILTER_SANITIZE_STRING);
                $region = filter_var($region, FILTER_SANITIZE_STRING);
                $order = $index + 1;
                
                $stmt->bind_param("ssi", $region, $number, $order);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed for number $index: " . $stmt->error);
                }
            }
            
            $conn->commit();
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Contact numbers updated successfully'];
            $stmt->close();
        } catch (Exception $e) {
            try {
                $conn->rollback();
            } catch (Exception $rollbackException) {
                // Transaction was not active or already committed/rolled back
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Error updating contact numbers'];
            error_log('Contact numbers update error: ' . $e->getMessage());
        }
    }
    elseif (isset($_POST['save_centers'])) {
        // Update assistance centers
        $conn->begin_transaction();
        try {
            if (!$conn->query("DELETE FROM assistance_centers")) {
                throw new Exception("Delete failed: " . $conn->error);
            }
            
            $stmt = $conn->prepare("INSERT INTO assistance_centers (city, address, phone, email, display_order) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            foreach ($_POST['center_city'] as $index => $city) {
                $address = filter_var($_POST['center_address'][$index], FILTER_SANITIZE_STRING);
                $phone = filter_var($_POST['center_phone'][$index], FILTER_SANITIZE_STRING);
                $email = filter_var($_POST['center_email'][$index], FILTER_SANITIZE_EMAIL);
                $city = filter_var($city, FILTER_SANITIZE_STRING);
                $order = $index + 1;
                
                $stmt->bind_param("ssssi", $city, $address, $phone, $email, $order);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed for center $index: " . $stmt->error);
                }
            }
            
            $conn->commit();
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Assistance centers updated successfully'];
            $stmt->close();
        } catch (Exception $e) {
            try {
                $conn->rollback();
            } catch (Exception $rollbackException) {
                // Transaction was not active or already committed/rolled back
            }
            $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Error updating assistance centers'];
            error_log('Assistance centers update error: ' . $e->getMessage());
        }
    }
    
    header("Location: ../platform-settings.php");
    exit();
}

// Get current data
$heroData = getContactHero($conn);
$contactNumbers = getContactNumbers($conn);
$assistanceCenters = getAssistanceCenters($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Contact Page</title>
    <!-- CDN Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #ad8b3a;
            --secondary: #45443F;
            --dark: #000;
            --light: #fff;
        }
        
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        
        .admin-header {
            background-color: var(--secondary);
            color: var(--light);
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
        }
        
        .card {
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .card-header {
            background-color: var(--primary);
            color: var(--light);
            font-weight: bold;
            padding: 1rem 1.25rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: #9c7a32;
            border-color: #9c7a32;
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: var(--light);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(173, 139, 58, 0.25);
        }
        
        .editable-section {
            border: 1px dashed var(--primary);
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: rgba(173, 139, 58, 0.05);
        }
        
        .animate__delay-02s {
            animation-delay: 0.2s;
        }
        
        .animate__delay-04s {
            animation-delay: 0.4s;
        }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
        }
        
        .img-preview {
            max-height: 150px;
            max-width: 100%;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <!-- Your existing navbar would go here -->
    
    <div class="container">
        <!-- Admin Header -->
        <div class="admin-header text-center animate__animated animate__fadeIn">
            <h1 class="display-4"><i class="fa fa-address-book"></i> Contact Page Editor</h1>
            <p class="lead">Manage all content that appears on the Contact page</p>
        </div>
        
        <!-- Toast Notification -->
        <?php if (isset($_SESSION['toast'])): ?>
        <div class="toast-container animate__animated animate__fadeInDown">
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-<?= $_SESSION['toast']['type'] ?> text-white">
                    <strong class="me-auto">Notification</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    <?= htmlspecialchars($_SESSION['toast']['message']) ?>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['toast']); endif; ?>
        
        <!-- Hero Section Editor -->
        <div class="card animate__animated animate__fadeIn animate__delay-02s">
            <div class="card-header">
                <i class="fa fa-image"></i> Hero Section
            </div>
            <div class="card-body">
                <form method="POST" id="heroForm">
                    <div class="mb-3">
                        <label for="hero_title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="hero_title" name="hero_title" 
                               value="<?= htmlspecialchars($heroData['title'] ?? 'Contact') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="hero_image" class="form-label">Background Image URL</label>
                        <input type="url" class="form-control" id="hero_image" name="hero_image" 
                               value="<?= htmlspecialchars($heroData['background_image'] ?? '') ?>" required>
                        <small class="text-muted">Example: /images/contact-hero.jpg or full URL</small>
                        <?php if (!empty($heroData['background_image'])): ?>
                            <div class="mt-2">
                                <img src="<?= htmlspecialchars($heroData['background_image']) ?>" 
                                     class="img-preview" alt="Current hero image"
                                     onerror="this.style.display='none'">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="hero_placeholder" class="form-label">Search Placeholder Text</label>
                        <input type="text" class="form-control" id="hero_placeholder" name="hero_placeholder" 
                               value="<?= htmlspecialchars($heroData['search_placeholder'] ?? '') ?>" required>
                    </div>
                    <button type="submit" name="save_hero" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Hero Section
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Contact Numbers Editor -->
        <div class="card animate__animated animate__fadeIn animate__delay-04s">
            <div class="card-header">
                <i class="fa fa-phone"></i> Contact Numbers
            </div>
            <div class="card-body">
                <form method="POST" id="numbersForm">
                    <div id="numbersContainer">
                        <?php if (!empty($contactNumbers)): ?>
                            <?php foreach ($contactNumbers as $index => $number): ?>
                            <div class="editable-section animate__animated animate__fadeIn">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Region</label>
                                            <input type="text" class="form-control" name="number_region[]" 
                                                   value="<?= htmlspecialchars($number['region']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Contact Number</label>
                                            <input type="text" class="form-control" name="number_value[]" 
                                                   value="<?= htmlspecialchars($number['number']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-number">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="editable-section animate__animated animate__fadeIn">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Region</label>
                                            <input type="text" class="form-control" name="number_region[]" 
                                                   value="North America" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Contact Number</label>
                                            <input type="text" class="form-control" name="number_value[]" 
                                                   value="+1 (800) 123-4567" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-number">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" id="addNumber" class="btn btn-outline-primary mt-2">
                        <i class="fa fa-plus"></i> Add Another Number
                    </button>
                    
                    <button type="submit" name="save_numbers" class="btn btn-primary mt-3">
                        <i class="fa fa-save"></i> Save All Contact Numbers
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Assistance Centers Editor -->
        <div class="card animate__animated animate__fadeIn animate__delay-06s">
            <div class="card-header">
                <i class="fa fa-globe"></i> Assistance Centers
            </div>
            <div class="card-body">
                <form method="POST" id="centersForm">
                    <div id="centersContainer">
                        <?php if (!empty($assistanceCenters)): ?>
                            <?php foreach ($assistanceCenters as $index => $center): ?>
                            <div class="editable-section animate__animated animate__fadeIn">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">City</label>
                                            <input type="text" class="form-control" name="center_city[]" 
                                                   value="<?= htmlspecialchars($center['city']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Address</label>
                                            <textarea class="form-control" name="center_address[]" rows="2" required><?= 
                                                htmlspecialchars($center['address']) ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control" name="center_phone[]" 
                                                   value="<?= htmlspecialchars($center['phone']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="center_email[]" 
                                                   value="<?= htmlspecialchars($center['email']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-center">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="editable-section animate__animated animate__fadeIn">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">City</label>
                                            <input type="text" class="form-control" name="center_city[]" 
                                                   value="New York" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Address</label>
                                            <textarea class="form-control" name="center_address[]" rows="2" required>123 Main Street, NY 10001</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control" name="center_phone[]" 
                                                   value="+1 (212) 555-1234" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="center_email[]" 
                                                   value="ny@example.com" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-center">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" id="addCenter" class="btn btn-outline-primary mt-2">
                        <i class="fa fa-plus"></i> Add Another Center
                    </button>
                    
                    <button type="submit" name="save_centers" class="btn btn-primary mt-3">
                        <i class="fa fa-save"></i> Save All Assistance Centers
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/additional-methods.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Auto-hide toast after 5 seconds
        setTimeout(function() {
            $('.toast').toast('hide');
        }, 5000);
        
        // Add new contact number
        $('#addNumber').click(function() {
            const newNumber = `
                <div class="editable-section animate__animated animate__fadeIn">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Region</label>
                                <input type="text" class="form-control" name="number_region[]" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" name="number_value[]" required>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-number">
                        <i class="fa fa-trash"></i> Remove
                    </button>
                </div>
            `;
            $('#numbersContainer').append(newNumber);
        });
        
        // Remove contact number
        $(document).on('click', '.remove-number', function() {
            $(this).closest('.editable-section').addClass('animate__animated animate__fadeOut');
            setTimeout(() => {
                $(this).closest('.editable-section').remove();
            }, 500);
        });
        
        // Add new assistance center
        $('#addCenter').click(function() {
            const newCenter = `
                <div class="editable-section animate__animated animate__fadeIn">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="center_city[]" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="center_address[]" rows="2" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="center_phone[]" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="center_email[]" required>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-center">
                        <i class="fa fa-trash"></i> Remove
                    </button>
                </div>
            `;
            $('#centersContainer').append(newCenter);
        });
        
        // Remove assistance center
        $(document).on('click', '.remove-center', function() {
            $(this).closest('.editable-section').addClass('animate__animated animate__fadeOut');
            setTimeout(() => {
                $(this).closest('.editable-section').remove();
            }, 500);
        });
        
        // Form validation
        $("#heroForm").validate({
            rules: {
                hero_title: "required",
                hero_image: {
                    required: true,
                    url: true
                },
                hero_placeholder: "required"
            },
            messages: {
                hero_title: "Please enter a title for the hero section",
                hero_image: {
                    required: "Please enter an image URL",
                    url: "Please enter a valid URL (include http:// or https://)"
                },
                hero_placeholder: "Please enter search placeholder text"
            },
            errorElement: "div",
            errorClass: "invalid-feedback",
            highlight: function(element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function(element) {
                $(element).addClass('is-valid').removeClass('is-invalid');
            }
        });
        
        $("#numbersForm").validate({
            errorElement: "div",
            errorClass: "invalid-feedback",
            highlight: function(element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function(element) {
                $(element).addClass('is-valid').removeClass('is-invalid');
            }
        });
        
        $("#centersForm").validate({
            errorElement: "div",
            errorClass: "invalid-feedback",
            highlight: function(element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function(element) {
                $(element).addClass('is-valid').removeClass('is-invalid');
            }
        });
        
        // Preview image when URL changes
        $('#hero_image').on('change', function() {
            const url = $(this).val();
            if (url) {
                const preview = $(this).next('small').next('div').find('img');
                preview.attr('src', url).show();
            }
        });
    });
    </script>
</body>
</html>