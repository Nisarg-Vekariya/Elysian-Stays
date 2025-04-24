<?php
session_start();
require_once 'config/database.php';
require_once '../restrict_access.php';
restrictAccess(['hotel']);

// Check if user already has a hotel
$stmt = $conn->prepare("SELECT * FROM hotels WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

// If hotel already exists, redirect to dashboard
if ($hotel) {
    header('Location: index.php');
    exit;
}

// Process form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $name = trim($_POST['name']);
        $tagline = trim($_POST['tagline']);
        $about_title = trim($_POST['about_title']);
        $about_description1 = trim($_POST['about_description1']);
        $about_description2 = trim($_POST['about_description2']);
        $about_image = trim($_POST['about_image']);
        $background_image = trim($_POST['background_image']);
        $rooms_title = trim($_POST['rooms_title']);
        $rooms_description = trim($_POST['rooms_description']);
        $amenities_title = trim($_POST['amenities_title']);
        $amenities_description = trim($_POST['amenities_description']);
        $gallery_title = trim($_POST['gallery_title']);
        $gallery_description = trim($_POST['gallery_description']);
        $map_embed_url = trim($_POST['map_embed_url']);
        $contact_email = trim($_POST['contact_email']);
        $contact_phone = trim($_POST['contact_phone']);
        $contact_address = trim($_POST['contact_address']);
        
        // Validate required fields
        if (empty($name) || empty($tagline) || empty($about_title)) {
            throw new Exception('Please fill in all required fields.');
        }

        // Validate email
        if (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }

        // Function to validate image URL
        function validateImageUrl($url) {
            if (empty($url)) {
                return true; // Empty URL is allowed
            }
            
            // Check if URL is valid
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new Exception('Please enter a valid image URL.');
            }
            
            // Check if URL points to an image
            $headers = @get_headers($url);
            if (!$headers || strpos($headers[0], '200') === false) {
                throw new Exception('The image URL is not accessible. Please check the URL.');
            }
            
            // Check if URL points to an image file
            $content_type = '';
            foreach ($headers as $header) {
                if (stripos($header, 'Content-Type:') !== false) {
                    $content_type = trim(substr($header, 14));
                    break;
                }
            }
            
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($content_type, $allowed_types)) {
                throw new Exception('The URL must point to a valid image file (JPG, PNG, WebP, or GIF).');
            }
            
            return true;
        }

        // Validate image URLs
        if (!empty($about_image)) {
            validateImageUrl($about_image);
        }
        
        if (!empty($background_image)) {
            validateImageUrl($background_image);
        }

        // Start transaction
        $conn->beginTransaction();

        // Insert hotel data
        $sql = "INSERT INTO hotels (
            name, tagline, about_title, about_description1, about_description2, 
            about_image, background_image, rooms_title, rooms_description, 
            amenities_title, amenities_description, gallery_title, 
            gallery_description, map_embed_url, user_id, owner_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $name, $tagline, $about_title, $about_description1, $about_description2,
            $about_image, $background_image, $rooms_title, $rooms_description,
            $amenities_title, $amenities_description, $gallery_title,
            $gallery_description, $map_embed_url, $_SESSION['user_id'], $_SESSION['user_id']
        ]);
        
        // Get the new hotel ID
        $hotel_id = $conn->lastInsertId();
        
        // Insert contact information
        $stmt = $conn->prepare("INSERT INTO contact_info (hotel_id, email, phone, address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$hotel_id, $contact_email, $contact_phone, $contact_address]);

        // Insert gallery images
        if (!empty($_POST['gallery_image'])) {
            $stmt = $conn->prepare("INSERT INTO gallery_images (hotel_id, image, alt_text) VALUES (?, ?, ?)");
            foreach ($_POST['gallery_image'] as $index => $image_url) {
                if (!empty($image_url)) {
                    // Gallery images don't need validation
                    $alt_text = $_POST['gallery_alt'][$index] ?? '';
                    $stmt->execute([$hotel_id, $image_url, $alt_text]);
                }
            }
        }

        // Commit transaction
        $conn->commit();
        
        // Set hotel_id in session
        $_SESSION['hotel_id'] = $hotel_id;
        
        $success = "Hotel created successfully. Redirecting to dashboard...";
        header("refresh:2;url=index.php");
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Hotel - Hotel Owner Dashboard</title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #ad8b3a;
            --secondary-color: #8a6e2e;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
            --danger: #dc3545;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            opacity: 0.8;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .form-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .form-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .form-section h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
        }
        
        .form-section h2:after {
            content: '';
            position: absolute;
            width: 50px;
            height: 2px;
            background-color: var(--primary-color);
            bottom: 0;
            left: 25px;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
            font-size: 15px;
        }
        
        .required {
            color: #e74c3c;
            margin-left: 4px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 3px rgba(173, 139, 58, 0.2);
            background-color: #fff;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(173, 139, 58, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(173, 139, 58, 0.2);
        }
        
        .error-message {
            background-color: #ffebee;
            color: var(--danger);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: fadeIn 0.5s ease;
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: var(--secondary-color);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .input-group {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            width: 100%;
        }
        
        .input-group-text {
            display: flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            text-align: center;
            white-space: nowrap;
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 0.25rem 0 0 0.25rem;
        }
        
        .input-group .form-control {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        
        .input-group i {
            color: var(--primary-color);
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .gallery-item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .gallery-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .image-preview {
            width: 100%;
            height: 200px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .image-preview img:hover {
            transform: scale(1.05);
        }
        
        .no-image {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #adb5bd;
        }
        
        .no-image i {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .gallery-inputs {
            padding: 15px;
        }
        
        .gallery-inputs .form-group {
            margin-bottom: 10px;
        }
        
        .gallery-inputs .form-group:last-child {
            margin-bottom: 0;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .content {
                padding: 20px;
            }
            
            .form-section {
                padding: 20px;
            }
            
            .gallery-grid {
                grid-template-columns: 1fr;
            }
            
            .input-group {
                flex-direction: column;
            }
            
            .input-group-text {
                border-radius: 0.25rem;
                margin-bottom: 0.5rem;
            }
            
            .input-group .form-control {
                border-radius: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-hotel me-2"></i>Create Your Hotel</h1>
            <p>Set up your hotel profile to get started</p>
        </div>
        
        <div class="content">
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <!-- Basic Information -->
                <div class="form-section">
                    <h2><i class="fas fa-info-circle me-2"></i>Basic Information</h2>
                    
                    <div class="form-group">
                        <label for="name">Hotel Name <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-hotel"></i></span>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="tagline">Tagline <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-quote-left"></i></span>
                            <input type="text" id="tagline" name="tagline" class="form-control" required placeholder="A short catchy phrase describing your hotel">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="background_image">Background Image URL <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-image"></i></span>
                            <input type="url" id="background_image" name="background_image" class="form-control" required placeholder="URL to a background image for your hotel">
                        </div>
                        <small class="form-text text-muted">This image will be displayed as the background for your hotel's header</small>
                    </div>
                </div>
                
                <!-- About Section -->
                <div class="form-section">
                    <h2><i class="fas fa-info-circle me-2"></i>About Section</h2>
                    
                    <div class="form-group">
                        <label for="about_title">About Title <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-heading"></i></span>
                            <input type="text" id="about_title" name="about_title" class="form-control" value="About Our Hotel" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="about_description1">About Description (Part 1) <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea id="about_description1" name="about_description1" class="form-control" required placeholder="Main description about your hotel"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="about_description2">About Description (Part 2)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea id="about_description2" name="about_description2" class="form-control" placeholder="Additional information about your hotel"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="about_image">About Image URL <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-image"></i></span>
                            <input type="url" id="about_image" name="about_image" class="form-control" required placeholder="URL to an image">
                        </div>
                    </div>
                </div>
                
                <!-- Rooms Section -->
                <div class="form-section">
                    <h2><i class="fas fa-bed me-2"></i>Rooms Section</h2>
                    
                    <div class="form-group">
                        <label for="rooms_title">Rooms Title <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-heading"></i></span>
                            <input type="text" id="rooms_title" name="rooms_title" class="form-control" value="Rooms and Suites" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="rooms_description">Rooms Description <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea id="rooms_description" name="rooms_description" class="form-control" required placeholder="Description of your hotel's rooms"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Amenities Section -->
                <div class="form-section">
                    <h2><i class="fas fa-concierge-bell me-2"></i>Amenities Section</h2>
                    
                    <div class="form-group">
                        <label for="amenities_title">Amenities Title <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-heading"></i></span>
                            <input type="text" id="amenities_title" name="amenities_title" class="form-control" value="Hotel Amenities" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="amenities_description">Amenities Description <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea id="amenities_description" name="amenities_description" class="form-control" required placeholder="Description of your hotel's amenities"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Gallery Section -->
                <div class="form-section">
                    <h2><i class="fas fa-images me-2"></i>Gallery Section</h2>
                    
                    <div class="form-group">
                        <label for="gallery_title">Gallery Title <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-heading"></i></span>
                            <input type="text" id="gallery_title" name="gallery_title" class="form-control" value="Photo Gallery" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="gallery_description">Gallery Description <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                            <textarea id="gallery_description" name="gallery_description" class="form-control" required placeholder="Description of your hotel's gallery"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Gallery Images</label>
                        <p class="form-text text-muted mb-3">Add up to 6 images to your hotel gallery. All images should be high quality and showcase your hotel's best features.</p>
                        
                        <div class="gallery-grid">
                            <?php for ($i = 0; $i < 6; $i++): ?>
                                <div class="gallery-item">
                                    <div class="image-preview" id="preview_<?php echo $i; ?>">
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                            <span>No image selected</span>
                                        </div>
                                    </div>
                                    <div class="gallery-inputs">
                                        <div class="form-group">
                                            <input type="url" 
                                                   name="gallery_image[]" 
                                                   class="form-control gallery-url" 
                                                   placeholder="Image URL"
                                                   data-preview="preview_<?php echo $i; ?>"
                                                   onchange="previewImage(this)">
                                        </div>
                                        <div class="form-group">
                                            <input type="text" 
                                                   name="gallery_alt[]" 
                                                   class="form-control" 
                                                   placeholder="Image description">
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Map Section -->
                <div class="form-section">
                    <h2><i class="fas fa-map-marker-alt me-2"></i>Map Section</h2>
                    
                    <div class="form-group">
                        <label for="map_embed_url">Google Maps Embed URL <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map"></i></span>
                            <input type="text" id="map_embed_url" name="map_embed_url" class="form-control" required placeholder="Google Maps embed URL">
                        </div>
                        <small class="form-text text-muted">Enter the full Google Maps embed URL for your hotel's location</small>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="form-section">
                    <h2><i class="fas fa-address-card me-2"></i>Contact Information</h2>
                    
                    <div class="form-group">
                        <label for="contact_email">Email Address <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" 
                                   id="contact_email" 
                                   name="contact_email" 
                                   class="form-control" 
                                   required 
                                   placeholder="Enter your hotel's email address">
                        </div>
                        <small class="form-text text-muted">This email will be used for guest inquiries and notifications</small>
                    </div>

                    <div class="form-group">
                        <label for="contact_phone">Phone Number <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="tel" 
                                   id="contact_phone" 
                                   name="contact_phone" 
                                   class="form-control" 
                                   required 
                                   placeholder="Enter your hotel's phone number including country code"
                                   pattern="[0-9+\s()-]*"
                                   title="Please enter a valid phone number with only digits, spaces, plus sign, parentheses or hyphens">
                        </div>
                        <small class="form-text text-muted">Enter international format with country code (e.g., +1 123 456 7890 for US)</small>
                    </div>

                    <div class="form-group">
                        <label for="contact_address">Address <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <textarea id="contact_address" 
                                      name="contact_address" 
                                      class="form-control" 
                                      rows="3" 
                                      required 
                                      placeholder="Enter your hotel's complete address"></textarea>
                        </div>
                        <small class="form-text text-muted">Provide the complete address including street, city, state/province, and postal code</small>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-save me-2"></i>Create Hotel
                </button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image preview functionality
        function previewImage(input) {
            const previewDiv = document.getElementById(input.dataset.preview);
            const url = input.value.trim();
            
            if (url) {
                // Create new image preview
                previewDiv.innerHTML = `
                    <img src="${url}" alt="Gallery preview" onerror="handleImageError(this)">
                `;
            } else {
                // Show no image placeholder
                previewDiv.innerHTML = `
                    <div class="no-image">
                        <i class="fas fa-image"></i>
                        <span>No image selected</span>
                    </div>
                `;
            }
        }

        function handleImageError(img) {
            const previewDiv = img.parentElement;
            previewDiv.innerHTML = `
                <div class="no-image">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Invalid image URL</span>
                </div>
            `;
        }

        // Add animation to form sections
        const formSections = document.querySelectorAll('.form-section');
        formSections.forEach((section, index) => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                section.style.opacity = '1';
                section.style.transform = 'translateY(0)';
            }, 100 * index);
        });
    });
    </script>
</body>
</html> 