<?php
require_once '../../db_connect.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update earnings estimates
    if (isset($_POST['update_earnings'])) {
        $stmt = $conn->prepare("UPDATE earnings_estimates SET base_price = ?, min_nights = ?, max_nights = ? WHERE id = 1");
        $stmt->bind_param("dii", $_POST['base_price'], $_POST['min_nights'], $_POST['max_nights']);
        $stmt->execute();
    }
    
    // Update setup features
    if (isset($_POST['update_setup_features'])) {
        foreach ($_POST['setup_features'] as $id => $feature) {
            $stmt = $conn->prepare("UPDATE setup_features SET title = ?, description = ?, display_order = ?, is_active = ? WHERE id = ?");
            $is_active = isset($feature['is_active']) ? 1 : 0;
            $stmt->bind_param("ssiii", $feature['title'], $feature['description'], $feature['display_order'], $is_active, $id);
            $stmt->execute();
        }
    }
    
    // Update protection features
    if (isset($_POST['update_protection_features'])) {
        foreach ($_POST['protection_features'] as $id => $feature) {
            $stmt = $conn->prepare("UPDATE protection_features SET title = ?, description = ?, elysian_has = ?, competitors_have = ?, display_order = ?, is_active = ? WHERE id = ?");
            $elysian_has = isset($feature['elysian_has']) ? 1 : 0;
            $competitors_have = isset($feature['competitors_have']) ? 1 : 0;
            $is_active = isset($feature['is_active']) ? 1 : 0;
            $stmt->bind_param("ssiiiii", $feature['title'], $feature['description'], $elysian_has, $competitors_have, $feature['display_order'], $is_active, $id);
            $stmt->execute();
        }
    }
    
    // Add success message
    $_SESSION['message'] = "Changes saved successfully!";
    header("Location: ../platform-settings.php");
    exit;
}

// Get current data
$earningsData = $conn->query("SELECT * FROM earnings_estimates LIMIT 1")->fetch_assoc();
$setupFeatures = $conn->query("SELECT * FROM setup_features ORDER BY display_order")->fetch_all(MYSQLI_ASSOC);
$protectionFeatures = $conn->query("SELECT * FROM protection_features ORDER BY display_order")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elysian Stays Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #ad8b3a;
            --dark-color: #45443F;
        }
        body {
            background-color: #f8f9fa;
        }
        .admin-header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .card {
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
        }
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
            padding: 15px 20px;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #9c7a32;
            border-color: #9c7a32;
        }
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .feature-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }
        .feature-item:last-child {
            border-bottom: none;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(173, 139, 58, 0.25);
        }
        .alert-success {
            background-color: rgba(173, 139, 58, 0.1);
            border-color: rgba(173, 139, 58, 0.3);
            color: var(--dark-color);
        }
        .toggle-checkbox {
            width: 50px;
            height: 24px;
        }
        .toggle-checkbox:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Your existing navbar would go here -->

    <div class="admin-header py-3">
        <div class="container">
            <h2 class="mb-0 animate__animated animate__fadeInDown"><i class="fa fa-cog me-2"></i> Elysian Stays Admin Panel</h2>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success animate__animated animate__fadeIn">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="animate__animated animate__fadeIn">
            <!-- Earnings Calculator Settings -->
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-calculator me-2"></i> Earnings Calculator Settings
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Base Price (₹)</label>
                            <input type="number" class="form-control" name="base_price" value="<?php echo $earningsData['base_price'] ?? 4898; ?>" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Minimum Nights</label>
                            <input type="number" class="form-control" name="min_nights" value="<?php echo $earningsData['min_nights'] ?? 1; ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Maximum Nights</label>
                            <input type="number" class="form-control" name="max_nights" value="<?php echo $earningsData['max_nights'] ?? 30; ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="update_earnings" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Save Earnings Settings
                    </button>
                </div>
            </div>

            <!-- Setup Features -->
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-cogs me-2"></i> Setup Features
                </div>
                <div class="card-body">
                    <?php foreach ($setupFeatures as $feature): ?>
                        <div class="feature-item animate__animated animate__fadeInUp">
                            <input type="hidden" name="setup_features[<?php echo $feature['id']; ?>][id]" value="<?php echo $feature['id']; ?>">
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="setup_features[<?php echo $feature['id']; ?>][title]" value="<?php echo htmlspecialchars($feature['title']); ?>" required>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="setup_features[<?php echo $feature['id']; ?>][description]" rows="2" required><?php echo htmlspecialchars($feature['description']); ?></textarea>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" class="form-control" name="setup_features[<?php echo $feature['id']; ?>][display_order]" value="<?php echo $feature['display_order']; ?>" required>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-checkbox" type="checkbox" role="switch" name="setup_features[<?php echo $feature['id']; ?>][is_active]" id="setup_active_<?php echo $feature['id']; ?>" <?php echo $feature['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="setup_active_<?php echo $feature['id']; ?>">Active</label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" name="update_setup_features" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Save Setup Features
                    </button>
                </div>
            </div>

            <!-- Protection Features -->
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-shield me-2"></i> Protection Features
                </div>
                <div class="card-body">
                    <?php foreach ($protectionFeatures as $feature): ?>
                        <div class="feature-item animate__animated animate__fadeInUp">
                            <input type="hidden" name="protection_features[<?php echo $feature['id']; ?>][id]" value="<?php echo $feature['id']; ?>">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="protection_features[<?php echo $feature['id']; ?>][title]" value="<?php echo htmlspecialchars($feature['title']); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="protection_features[<?php echo $feature['id']; ?>][description]" rows="2" required><?php echo htmlspecialchars($feature['description']); ?></textarea>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" class="form-control" name="protection_features[<?php echo $feature['id']; ?>][display_order]" value="<?php echo $feature['display_order']; ?>" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Options</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="protection_features[<?php echo $feature['id']; ?>][elysian_has]" id="elysian_<?php echo $feature['id']; ?>" <?php echo $feature['elysian_has'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="elysian_<?php echo $feature['id']; ?>">Elysian</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="protection_features[<?php echo $feature['id']; ?>][competitors_have]" id="competitors_<?php echo $feature['id']; ?>" <?php echo $feature['competitors_have'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="competitors_<?php echo $feature['id']; ?>">Competitors</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-checkbox" type="checkbox" role="switch" name="protection_features[<?php echo $feature['id']; ?>][is_active]" id="protection_active_<?php echo $feature['id']; ?>" <?php echo $feature['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="protection_active_<?php echo $feature['id']; ?>">Active</label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" name="update_protection_features" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Save Protection Features
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/additional-methods.min.js"></script>
    <script>
        $(document).ready(function() {
            // Form validation
            $('form').validate({
                errorClass: "is-invalid",
                validClass: "is-valid",
                errorElement: "div",
                errorPlacement: function(error, element) {
                    error.addClass("invalid-feedback");
                    element.after(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass(errorClass).removeClass(validClass);
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass(errorClass).addClass(validClass);
                }
            });

            // Animate success message
            if ($('.alert-success').length) {
                setTimeout(function() {
                    $('.alert-success').fadeOut();
                }, 3000);
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>