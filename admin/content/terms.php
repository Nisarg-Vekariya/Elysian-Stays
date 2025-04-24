<?php
require_once '../../db_connect.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        $stmt = $conn->prepare("UPDATE page_settings SET page_title = ?, effective_date = ?, footer_text = ? WHERE id = 1");
        $stmt->bind_param("sss", $_POST['page_title'], $_POST['effective_date'], $_POST['footer_text']);
        $stmt->execute();
    }
    
    if (isset($_POST['update_sections'])) {
        foreach ($_POST['sections'] as $id => $section) {
            $stmt = $conn->prepare("UPDATE terms_of_service SET section_title = ?, section_content = ?, display_order = ?, is_active = ? WHERE id = ?");
            $is_active = isset($section['is_active']) ? 1 : 0;
            $stmt->bind_param("ssiii", $section['section_title'], $section['section_content'], $section['display_order'], $is_active, $id);
            $stmt->execute();
        }
    }
    
    if (isset($_POST['add_section'])) {
        $stmt = $conn->prepare("INSERT INTO terms_of_service (section_title, section_content, display_order) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $_POST['new_section_title'], $_POST['new_section_content'], $_POST['new_section_order']);
        $stmt->execute();
    }
    
    $_SESSION['message'] = "Changes saved successfully!";
    header("Location: ../platform-settings.php");
    exit;
}

// Get current data
$settings = $conn->query("SELECT * FROM page_settings LIMIT 1")->fetch_assoc();
$sections = $conn->query("SELECT * FROM terms_of_service ORDER BY display_order")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Terms of Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ad8b3a;
        }
        .card-header {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(173, 139, 58, 0.25);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">Manage Terms of Service</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="card mb-4">
                <div class="card-header">Page Settings</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Page Title</label>
                        <input type="text" class="form-control" name="page_title" value="<?php echo htmlspecialchars($settings['page_title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Effective Date</label>
                        <input type="text" class="form-control" name="effective_date" value="<?php echo htmlspecialchars($settings['effective_date']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Footer Text</label>
                        <textarea class="form-control" name="footer_text" rows="3" required><?php echo htmlspecialchars($settings['footer_text']); ?></textarea>
                    </div>
                    <button type="submit" name="update_settings" class="btn btn-primary">Save Settings</button>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">Add New Section</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Section Title</label>
                            <input type="text" class="form-control" name="new_section_title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="new_section_order" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="new_section_content" rows="3" required></textarea>
                        </div>
                    </div>
                    <button type="submit" name="add_section" class="btn btn-primary">Add Section</button>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">Manage Sections</div>
                <div class="card-body">
                    <?php foreach ($sections as $section): ?>
                        <div class="mb-4 p-3 border rounded">
                            <input type="hidden" name="sections[<?php echo $section['id']; ?>][id]" value="<?php echo $section['id']; ?>">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Section Title</label>
                                    <input type="text" class="form-control" name="sections[<?php echo $section['id']; ?>][section_title]" value="<?php echo htmlspecialchars($section['section_title']); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" class="form-control" name="sections[<?php echo $section['id']; ?>][display_order]" value="<?php echo $section['display_order']; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="sections[<?php echo $section['id']; ?>][is_active]" id="active_<?php echo $section['id']; ?>" <?php echo $section['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="active_<?php echo $section['id']; ?>">Active</label>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Content</label>
                                    <textarea class="form-control" name="sections[<?php echo $section['id']; ?>][section_content]" rows="3" required><?php echo htmlspecialchars($section['section_content']); ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" name="update_sections" class="btn btn-primary">Save Sections</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
<?php
$conn->close();
?>