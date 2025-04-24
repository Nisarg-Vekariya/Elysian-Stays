<?php
require_once '../../db_connect.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_feature'])) {
        // Update feature
        $stmt = $conn->prepare("UPDATE features SET title=?, description=?, icon_url=?, display_order=?, is_active=?, special_class=? WHERE feature_id=?");
        $stmt->bind_param("sssiisi", 
            $_POST['title'],
            $_POST['description'],
            $_POST['icon_url'],
            $_POST['display_order'],
            $_POST['is_active'],
            $_POST['special_class'],
            $_POST['feature_id']
        );
        $stmt->execute();
    } elseif (isset($_POST['add_feature'])) {
        // Add new feature
        $stmt = $conn->prepare("INSERT INTO features (title, description, icon_url, display_order, is_active, special_class) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiis", 
            $_POST['new_title'],
            $_POST['new_description'],
            $_POST['new_icon_url'],
            $_POST['new_display_order'],
            $_POST['new_is_active'],
            $_POST['new_special_class']
        );
        $stmt->execute();
    } elseif (isset($_POST['update_step'])) {
        // Update booking step
        $stmt = $conn->prepare("UPDATE booking_steps SET title=?, description=?, animation_delay=?, display_order=? WHERE step_id=?");
        $stmt->bind_param("sssii", 
            $_POST['step_title'],
            $_POST['step_description'],
            $_POST['step_animation_delay'],
            $_POST['step_display_order'],
            $_POST['step_id']
        );
        $stmt->execute();
    }
    
    // Refresh data after updates
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Fetch current data
$features = $conn->query("SELECT * FROM features ORDER BY display_order")->fetch_all(MYSQLI_ASSOC);
$steps = $conn->query("SELECT * FROM booking_steps ORDER BY display_order")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Content Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">
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
            color: var(--dark);
        }
        
        .admin-header {
            background-color: var(--secondary);
            color: var(--light);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border: none;
        }
        
        .card-header {
            background-color: var(--primary);
            color: var(--light);
            border-radius: 10px 10px 0 0 !important;
            font-weight: bold;
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
        
        .table th {
            background-color: var(--secondary);
            color: var(--light);
        }
        
        .feature-icon-preview {
            max-width: 50px;
            max-height: 50px;
            display: block;
            margin: 0 auto;
        }
        
        .nav-tabs .nav-link.active {
            background-color: var(--primary);
            color: var(--light);
        }
        
        .nav-tabs .nav-link {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header animate__animated animate__fadeInDown">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h1><i class="fa fa-cogs"></i> Content Management</h1>
                    <p class="mb-0">Manage website features and booking steps</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="../platform-settings.php" class="btn btn-outline-light me-2"><i class="fa fa-dashboard"></i> Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container animate__animated animate__fadeIn">
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="features-tab" data-bs-toggle="tab" data-bs-target="#features" type="button" role="tab">Why Choose Us</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="steps-tab" data-bs-toggle="tab" data-bs-target="#steps" type="button" role="tab">Booking Steps</button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="adminTabsContent">
            <!-- Features Tab -->
            <div class="tab-pane fade show active" id="features" role="tabpanel">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <i class="fa fa-star"></i> Manage Features
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Icon</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($features as $feature): ?>
                                    <tr>
                                        <td>
                                            <img src="<?= htmlspecialchars($feature['icon_url']) ?>" class="feature-icon-preview" alt="Icon">
                                        </td>
                                        <td><?= htmlspecialchars($feature['title']) ?></td>
                                        <td><?= htmlspecialchars(substr($feature['description'], 0, 50)) ?>...</td>
                                        <td><?= $feature['display_order'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= $feature['is_active'] ? 'success' : 'danger' ?>">
                                                <?= $feature['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFeatureModal" 
                                                data-id="<?= $feature['feature_id'] ?>"
                                                data-title="<?= htmlspecialchars($feature['title']) ?>"
                                                data-description="<?= htmlspecialchars($feature['description']) ?>"
                                                data-icon="<?= htmlspecialchars($feature['icon_url']) ?>"
                                                data-order="<?= $feature['display_order'] ?>"
                                                data-active="<?= $feature['is_active'] ?>"
                                                data-class="<?= htmlspecialchars($feature['special_class']) ?>">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addFeatureModal">
                            <i class="fa fa-plus"></i> Add New Feature
                        </button>
                    </div>
                </div>
            </div>

            <!-- Steps Tab -->
            <div class="tab-pane fade" id="steps" role="tabpanel">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <i class="fa fa-list-ol"></i> Manage Booking Steps
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Step</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Animation Delay</th>
                                        <th>Order</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($steps as $step): ?>
                                    <tr>
                                        <td><?= $step['step_number'] ?></td>
                                        <td><?= htmlspecialchars($step['title']) ?></td>
                                        <td><?= htmlspecialchars(substr($step['description'], 0, 50)) ?>...</td>
                                        <td><?= $step['animation_delay'] ?></td>
                                        <td><?= $step['display_order'] ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editStepModal" 
                                                data-id="<?= $step['step_id'] ?>"
                                                data-number="<?= $step['step_number'] ?>"
                                                data-title="<?= htmlspecialchars($step['title']) ?>"
                                                data-description="<?= htmlspecialchars($step['description']) ?>"
                                                data-delay="<?= $step['animation_delay'] ?>"
                                                data-order="<?= $step['display_order'] ?>">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Feature Modal -->
    <div class="modal fade" id="editFeatureModal" tabindex="-1" aria-labelledby="editFeatureModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editFeatureModalLabel">Edit Feature</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="feature_id" id="editFeatureId">
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editIconUrl" class="form-label">Icon URL</label>
                            <input type="text" class="form-control" id="editIconUrl" name="icon_url" required>
                            <small class="text-muted">Example: Images/icon-name.jpg</small>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="editDisplayOrder" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="editDisplayOrder" name="display_order" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="editIsActive" class="form-label">Status</label>
                                <select class="form-select" id="editIsActive" name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="editSpecialClass" class="form-label">Special Class</label>
                                <input type="text" class="form-control" id="editSpecialClass" name="special_class" placeholder="e.g., 'unique'">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_feature" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Feature Modal -->
    <div class="modal fade" id="addFeatureModal" tabindex="-1" aria-labelledby="addFeatureModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addFeatureModalLabel">Add New Feature</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="newTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="newTitle" name="new_title" required>
                        </div>
                        <div class="mb-3">
                            <label for="newDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="newDescription" name="new_description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="newIconUrl" class="form-label">Icon URL</label>
                            <input type="text" class="form-control" id="newIconUrl" name="new_icon_url" required>
                            <small class="text-muted">Example: Images/icon-name.jpg</small>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="newDisplayOrder" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="newDisplayOrder" name="new_display_order" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="newIsActive" class="form-label">Status</label>
                                <select class="form-select" id="newIsActive" name="new_is_active">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="newSpecialClass" class="form-label">Special Class</label>
                                <input type="text" class="form-control" id="newSpecialClass" name="new_special_class" placeholder="e.g., 'unique'">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_feature" class="btn btn-primary">Add Feature</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Step Modal -->
    <div class="modal fade" id="editStepModal" tabindex="-1" aria-labelledby="editStepModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editStepModalLabel">Edit Booking Step</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="step_id" id="editStepId">
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label for="editStepNumber" class="form-label">Step Number</label>
                                <input type="number" class="form-control" id="editStepNumber" name="step_number" min="1" readonly>
                            </div>
                            <div class="col-md-10 mb-3">
                                <label for="editStepTitle" class="form-label">Title</label>
                                <input type="text" class="form-control" id="editStepTitle" name="step_title" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editStepDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editStepDescription" name="step_description" rows="4" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editStepAnimationDelay" class="form-label">Animation Delay</label>
                                <input type="text" class="form-control" id="editStepAnimationDelay" name="step_animation_delay" required>
                                <small class="text-muted">Example: 1s, 2s, etc.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editStepDisplayOrder" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="editStepDisplayOrder" name="step_display_order" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_step" class="btn btn-primary">Save Changes</button>
                    </div>
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
        // Edit Feature Modal
        $('#editFeatureModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);
            
            modal.find('#editFeatureId').val(button.data('id'));
            modal.find('#editTitle').val(button.data('title'));
            modal.find('#editDescription').val(button.data('description'));
            modal.find('#editIconUrl').val(button.data('icon'));
            modal.find('#editDisplayOrder').val(button.data('order'));
            modal.find('#editIsActive').val(button.data('active'));
            modal.find('#editSpecialClass').val(button.data('class'));
        });
        
        // Edit Step Modal
        $('#editStepModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);
            
            modal.find('#editStepId').val(button.data('id'));
            modal.find('#editStepNumber').val(button.data('number'));
            modal.find('#editStepTitle').val(button.data('title'));
            modal.find('#editStepDescription').val(button.data('description'));
            modal.find('#editStepAnimationDelay').val(button.data('delay'));
            modal.find('#editStepDisplayOrder').val(button.data('order'));
        });
        
        // Form Validation
        $("form").validate({
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
    });
    </script>
</body>
</html>