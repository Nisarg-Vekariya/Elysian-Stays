<?php
// admin_about_edit.php
require_once '../../db_connect.php'; 
require '../operation/about_functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_hero'])) {
        try {
            // Update hero section
            $stmt = $conn->prepare("UPDATE page_hero SET title = ?, background_image = ?, search_placeholder = ? WHERE page_name = 'about'");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            // Sanitize inputs
            $title = filter_var($_POST['hero_title'], FILTER_SANITIZE_STRING);
            $image = filter_var($_POST['hero_image'], FILTER_SANITIZE_URL);
            $placeholder = filter_var($_POST['hero_placeholder'], FILTER_SANITIZE_STRING);
            
            $stmt->bind_param("sss", $title, $image, $placeholder);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            // Log error and redirect with error flag
            error_log($e->getMessage());
            header("Location: ../platform-settings.php?error=hero_update");
            exit();
        }
    } elseif (isset($_POST['save_content'])) {
        // Update about content sections
        try {
            $conn->begin_transaction();
            
            // Clear existing content
            if (!$conn->query("DELETE FROM about_content")) {
                throw new Exception("Delete failed: " . $conn->error);
            }
            
            $stmt = $conn->prepare("INSERT INTO about_content (section_name, title, content, display_order) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            foreach ($_POST['section_name'] as $index => $sectionName) {
                $title = filter_var($_POST['section_title'][$index], FILTER_SANITIZE_STRING);
                $content = filter_var($_POST['section_content'][$index], FILTER_SANITIZE_STRING);
                $order = $index + 1;
                
                $stmt->bind_param("sssi", $sectionName, $title, $content, $order);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed for section $index: " . $stmt->error);
                }
            }
            
            $conn->commit();
            $stmt->close();
        } catch (Exception $e) {
            try {
                $conn->rollback();
            } catch (Exception $rollbackException) {
                // Transaction was not active or already committed/rolled back
            }
            error_log($e->getMessage());
            header("Location: ../platform-settings.php?error=content_update");
            exit();
        }
    } elseif (isset($_POST['save_facts'])) {
        // Update facts/statistics
        try {
            $conn->begin_transaction();
            
            // Clear existing facts
            if (!$conn->query("DELETE FROM about_facts")) {
                throw new Exception("Delete failed: " . $conn->error);
            }
            
            $stmt = $conn->prepare("INSERT INTO about_facts (statistic_value, description, footnote, animation_class, display_order) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            foreach ($_POST['fact_value'] as $index => $value) {
                $description = filter_var($_POST['fact_description'][$index], FILTER_SANITIZE_STRING);
                $footnote = filter_var($_POST['fact_footnote'][$index], FILTER_SANITIZE_STRING);
                $animation = filter_var($_POST['fact_animation'][$index], FILTER_SANITIZE_STRING);
                $order = $index + 1;
                
                $stmt->bind_param("ssssi", $value, $description, $footnote, $animation, $order);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed for fact $index: " . $stmt->error);
                }
            }
            
            $conn->commit();
            $stmt->close();
        } catch (Exception $e) {
            try {
                $conn->rollback();
            } catch (Exception $rollbackException) {
                // Transaction was not active or already committed/rolled back
            }
            error_log($e->getMessage());
            header("Location: ../platform-settings.php?error=facts_update");
            exit();
        }
    }
    
    // Redirect to avoid form resubmission
    header("Location: ../platform-settings.php?success=1");
    exit();
}

// Get current data
$heroData = getHeroData($conn, 'about');
$aboutContent = getAboutContent($conn);
$aboutFacts = getAboutFacts($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit About Page</title>
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
        }
        
        .admin-header {
            background-color: var(--secondary);
            color: var(--light);
            padding: 1.5rem 0;
            margin-bottom: 2rem;
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
        
        .section-content {
            min-height: 120px;
        }
        
        .animate__delay-02s {
            animation-delay: 0.2s;
        }
        
        .animate__delay-04s {
            animation-delay: 0.4s;
        }
        
        .success-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            animation: fadeInDown 0.5s, fadeOutUp 0.5s 2.5s forwards;
        }
        
        .error-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            animation: fadeInDown 0.5s;
        }
    </style>
</head>
<body>
    <!-- Your existing navbar would go here -->
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success success-alert animate__animated animate__fadeInDown">
            <i class="fa fa-check-circle"></i> Changes saved successfully!
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger error-alert animate__animated animate__fadeInDown">
            <i class="fa fa-exclamation-circle"></i> 
            <?php 
                switch($_GET['error']) {
                    case 'hero_update': echo 'Failed to update hero section'; break;
                    case 'content_update': echo 'Failed to update content sections'; break;
                    case 'facts_update': echo 'Failed to update facts section'; break;
                    default: echo 'An error occurred while saving changes';
                }
            ?>
        </div>
    <?php endif; ?>
    
    <div class="admin-header text-center animate__animated animate__fadeIn">
        <div class="container">
            <h1 class="display-4"><i class="fa fa-pencil-square-o"></i> Edit About Page</h1>
            <p class="lead">Manage all content that appears on the About Us page</p>
        </div>
    </div>
    
    <div class="container">
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
                               value="<?= htmlspecialchars($heroData['title'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="hero_image" class="form-label">Background Image URL</label>
                        <input type="url" class="form-control" id="hero_image" name="hero_image" 
                               value="<?= htmlspecialchars($heroData['background_image'] ?? '') ?>" required>
                        <small class="text-muted">Example: /images/about-hero.jpg or full URL</small>
                        <?php if (!empty($heroData['background_image'])): ?>
                            <div class="mt-2">
                                <img src="<?= htmlspecialchars($heroData['background_image']) ?>" 
                                     style="max-height: 100px; max-width: 200px;" 
                                     class="img-thumbnail" alt="Current hero image">
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
        
        <!-- Content Sections Editor -->
        <div class="card animate__animated animate__fadeIn animate__delay-04s">
            <div class="card-header">
                <i class="fa fa-align-left"></i> Content Sections
            </div>
            <div class="card-body">
                <form method="POST" id="contentForm">
                    <div id="contentSections">
                        <?php if (!empty($aboutContent)): ?>
                            <?php foreach ($aboutContent as $index => $section): ?>
                                <div class="content-section mb-4 p-3 border rounded">
                                    <div class="mb-3">
                                        <label class="form-label">Section Name (internal use)</label>
                                        <input type="text" class="form-control" name="section_name[]" 
                                               value="<?= htmlspecialchars($section['section_name'] ?? 'section_'.($index+1)) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Section Title</label>
                                        <input type="text" class="form-control" name="section_title[]" 
                                               value="<?= htmlspecialchars($section['title'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Content (one paragraph per line)</label>
                                        <textarea class="form-control section-content" name="section_content[]" 
                                                  rows="4" required><?= htmlspecialchars($section['content'] ?? '') ?></textarea>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-section">
                                        <i class="fa fa-trash"></i> Remove Section
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="content-section mb-4 p-3 border rounded">
                                <div class="mb-3">
                                    <label class="form-label">Section Name (internal use)</label>
                                    <input type="text" class="form-control" name="section_name[]" value="main_heading">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Section Title</label>
                                    <input type="text" class="form-control" name="section_title[]" 
                                           value="The Unparalleled Guardian of Grandeur" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Content (one paragraph per line)</label>
                                    <textarea class="form-control section-content" name="section_content[]" 
                                              rows="4" required>Built on a vision of grandeur, Elysian Stays conjures a panoply of superlative experiences that are envisioned to indulge and forge unforgettable memories.</textarea>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-section">
                                    <i class="fa fa-trash"></i> Remove Section
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" id="addSection" class="btn btn-outline-primary mt-2">
                        <i class="fa fa-plus"></i> Add Another Section
                    </button>
                    
                    <button type="submit" name="save_content" class="btn btn-primary mt-3">
                        <i class="fa fa-save"></i> Save All Content Sections
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Facts/Statistics Editor -->
        <div class="card animate__animated animate__fadeIn animate__delay-06s">
            <div class="card-header">
                <i class="fa fa-bar-chart"></i> Facts & Statistics
            </div>
            <div class="card-body">
                <form method="POST" id="factsForm">
                    <div id="factsContainer">
                        <?php if (!empty($aboutFacts)): ?>
                            <?php foreach ($aboutFacts as $index => $fact): ?>
                                <div class="fact-item mb-4 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Statistic Value</label>
                                                <input type="text" class="form-control" name="fact_value[]" 
                                                       value="<?= htmlspecialchars($fact['statistic_value'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <input type="text" class="form-control" name="fact_description[]" 
                                                       value="<?= htmlspecialchars($fact['description'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Footnote</label>
                                                <input type="text" class="form-control" name="fact_footnote[]" 
                                                       value="<?= htmlspecialchars($fact['footnote'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="mb-3">
                                                <label class="form-label">Animation</label>
                                                <select class="form-select" name="fact_animation[]">
                                                    <option value="animate__fadeInLeft" <?= ($fact['animation_class'] ?? '') == 'animate__fadeInLeft' ? 'selected' : '' ?>>Left</option>
                                                    <option value="animate__fadeInRight" <?= ($fact['animation_class'] ?? '') == 'animate__fadeInRight' ? 'selected' : '' ?>>Right</option>
                                                    <option value="animate__fadeInUp" <?= ($fact['animation_class'] ?? '') == 'animate__fadeInUp' ? 'selected' : '' ?>>Up</option>
                                                    <option value="animate__fadeInDown" <?= ($fact['animation_class'] ?? '') == 'animate__fadeInDown' ? 'selected' : '' ?>>Down</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-fact">
                                        <i class="fa fa-trash"></i> Remove Fact
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="fact-item mb-4 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Statistic Value</label>
                                            <input type="text" class="form-control" name="fact_value[]" value="8M+" required>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <input type="text" class="form-control" name="fact_description[]" 
                                                   value="active listings worldwide" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Footnote</label>
                                            <input type="text" class="form-control" name="fact_footnote[]" 
                                                   value="as of June 30, 2024">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="mb-3">
                                            <label class="form-label">Animation</label>
                                            <select class="form-select" name="fact_animation[]">
                                                <option value="animate__fadeInLeft">Left</option>
                                                <option value="animate__fadeInRight">Right</option>
                                                <option value="animate__fadeInUp">Up</option>
                                                <option value="animate__fadeInDown">Down</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-fact">
                                    <i class="fa fa-trash"></i> Remove Fact
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" id="addFact" class="btn btn-outline-primary mt-2">
                        <i class="fa fa-plus"></i> Add Another Fact
                    </button>
                    
                    <button type="submit" name="save_facts" class="btn btn-primary mt-3">
                        <i class="fa fa-save"></i> Save All Facts
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
        // Add new content section
        $('#addSection').click(function() {
            const newSection = `
                <div class="content-section mb-4 p-3 border rounded animate__animated animate__fadeIn">
                    <div class="mb-3">
                        <label class="form-label">Section Name (internal use)</label>
                        <input type="text" class="form-control" name="section_name[]" value="section_${$('#contentSections .content-section').length + 1}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Section Title</label>
                        <input type="text" class="form-control" name="section_title[]" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content (one paragraph per line)</label>
                        <textarea class="form-control section-content" name="section_content[]" rows="4" required></textarea>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-section">
                        <i class="fa fa-trash"></i> Remove Section
                    </button>
                </div>
            `;
            $('#contentSections').append(newSection);
        });
        
        // Remove content section
        $(document).on('click', '.remove-section', function() {
            $(this).closest('.content-section').addClass('animate__animated animate__fadeOut');
            setTimeout(() => {
                $(this).closest('.content-section').remove();
            }, 500);
        });
        
        // Add new fact
        $('#addFact').click(function() {
            const newFact = `
                <div class="fact-item mb-4 p-3 border rounded animate__animated animate__fadeIn">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Statistic Value</label>
                                <input type="text" class="form-control" name="fact_value[]" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <input type="text" class="form-control" name="fact_description[]" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Footnote</label>
                                <input type="text" class="form-control" name="fact_footnote[]">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="mb-3">
                                <label class="form-label">Animation</label>
                                <select class="form-select" name="fact_animation[]">
                                    <option value="animate__fadeInLeft">Left</option>
                                    <option value="animate__fadeInRight">Right</option>
                                    <option value="animate__fadeInUp">Up</option>
                                    <option value="animate__fadeInDown">Down</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-fact">
                        <i class="fa fa-trash"></i> Remove Fact
                    </button>
                </div>
            `;
            $('#factsContainer').append(newFact);
        });
        
        // Remove fact
        $(document).on('click', '.remove-fact', function() {
            $(this).closest('.fact-item').addClass('animate__animated animate__fadeOut');
            setTimeout(() => {
                $(this).closest('.fact-item').remove();
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
                hero_title: "Please enter a hero title",
                hero_image: {
                    required: "Please enter an image URL",
                    url: "Please enter a valid URL"
                },
                hero_placeholder: "Please enter placeholder text"
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
        
        $("#contentForm").validate({
            rules: {
                "section_title[]": "required",
                "section_content[]": "required"
            },
            messages: {
                "section_title[]": "Please enter a section title",
                "section_content[]": "Please enter section content"
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
        
        $("#factsForm").validate({
            rules: {
                "fact_value[]": "required",
                "fact_description[]": "required"
            },
            messages: {
                "fact_value[]": "Please enter a statistic value",
                "fact_description[]": "Please enter a description"
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
    });
    </script>
</body>
</html>