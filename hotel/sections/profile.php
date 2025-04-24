<?php
require_once '../config/database.php';

// Get user ID from session
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get hotel ID for the logged-in user
$stmt = $conn->prepare("SELECT id FROM hotels WHERE user_id = ?");
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// If hotel not found, create a new one for this user
if (!$result) {
    $stmt = $conn->prepare("INSERT INTO hotels (
        name, 
        tagline, 
        about_title, 
        about_description1, 
        about_description2, 
        about_image, 
        background_image,
        rooms_title, 
        rooms_description, 
        amenities_title, 
        amenities_description, 
        gallery_title, 
        gallery_description, 
        map_embed_url, 
        user_id
    ) VALUES (
        '', '', '', '', '', '', '', '', '', '', '', '', '', '', ?
    )");
    $stmt->execute([$user_id]);
    $hotel_id = $conn->lastInsertId();
} else {
    $hotel_id = $result['id'];
}

// Get all available amenities
$stmt = $conn->prepare("SELECT * FROM amenities WHERE hotel_id IS NULL");
$stmt->execute();
$available_amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get hotel's selected amenities
$stmt = $conn->prepare("SELECT amenity_id FROM hotel_amenities WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$selected_amenities = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get gallery images
$stmt = $conn->prepare("SELECT * FROM gallery_images WHERE hotel_id = ? ORDER BY id ASC LIMIT 6");
$stmt->execute([$hotel_id]);
$gallery_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ensure we always have 6 image slots
while (count($gallery_images) < 6) {
    $gallery_images[] = ['image' => '', 'alt_text' => ''];
}

// Get contact information
$stmt = $conn->prepare("SELECT * FROM contact_info WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$contact_info = $stmt->fetch(PDO::FETCH_ASSOC);

// If no contact info exists, initialize empty array
if (!$contact_info) {
    $contact_info = [
        'email' => '',
        'phone' => '',
        'address' => ''
    ];
}

// Handle profile update
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
        $selected_amenity_ids = isset($_POST['amenities']) ? $_POST['amenities'] : [];

        // Validate required fields
        if (empty($name) || empty($tagline) || empty($about_title)) {
            throw new Exception('Please fill in all required fields.');
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

        // Update hotel information
        $sql = "UPDATE hotels SET 
                name = ?, 
                tagline = ?,
                about_title = ?,
                about_description1 = ?,
                about_description2 = ?,
                about_image = ?,
                background_image = ?,
                rooms_title = ?,
                rooms_description = ?,
                amenities_title = ?,
                amenities_description = ?,
                gallery_title = ?,
                gallery_description = ?,
                map_embed_url = ?
                WHERE id = ? AND user_id = ?";

        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $name,
            $tagline,
            $about_title,
            $about_description1,
            $about_description2,
            $about_image,
            $background_image,
            $rooms_title,
            $rooms_description,
            $amenities_title,
            $amenities_description,
            $gallery_title,
            $gallery_description,
            $map_embed_url,
            $hotel_id,
            $user_id
        ]);

        // Update hotel amenities
        // First, remove all existing amenities for this hotel
        $stmt = $conn->prepare("DELETE FROM hotel_amenities WHERE hotel_id = ?");
        $stmt->execute([$hotel_id]);

        // Then insert the selected amenities
        if (!empty($selected_amenity_ids)) {
            $stmt = $conn->prepare("INSERT INTO hotel_amenities (hotel_id, amenity_id) VALUES (?, ?)");
            foreach ($selected_amenity_ids as $amenity_id) {
                $stmt->execute([$hotel_id, $amenity_id]);
            }
        }

        // Handle gallery images
        // First, delete existing gallery images
        $stmt = $conn->prepare("DELETE FROM gallery_images WHERE hotel_id = ?");
        $stmt->execute([$hotel_id]);

        // Insert new gallery images
        $stmt = $conn->prepare("INSERT INTO gallery_images (hotel_id, image, alt_text) VALUES (?, ?, ?)");
        for ($i = 0; $i < 6; $i++) {
            $image_url = trim($_POST['gallery_image'][$i]);
            $image_alt = trim($_POST['gallery_alt'][$i]);
            
            if (!empty($image_url)) {
                // Validate image URL
                validateImageUrl($image_url);
                $stmt->execute([$hotel_id, $image_url, $image_alt]);
            }
        }

        // Handle contact information
        $email = trim($_POST['contact_email']);
        $phone = trim($_POST['contact_phone']);
        $address = trim($_POST['contact_address']);

        // Validate email
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }

        // Check if contact info exists
        $stmt = $conn->prepare("SELECT id FROM contact_info WHERE hotel_id = ?");
        $stmt->execute([$hotel_id]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Update existing contact info
            $stmt = $conn->prepare("UPDATE contact_info SET email = ?, phone = ?, address = ? WHERE hotel_id = ?");
            $stmt->execute([$email, $phone, $address, $hotel_id]);
        } else {
            // Insert new contact info
            $stmt = $conn->prepare("INSERT INTO contact_info (hotel_id, email, phone, address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$hotel_id, $email, $phone, $address]);
        }

        // Commit transaction
        $conn->commit();

        // Redirect with success message
        header('Location: ' . $_SERVER['PHP_SELF'] . '?updated=true');
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $error = $e->getMessage();
    }
}

// Get hotel information
$stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ? AND user_id = ?");
$stmt->execute([$hotel_id, $user_id]);
$hotel = $stmt->fetch(PDO::FETCH_ASSOC);

// If no hotel record exists or query failed, initialize an empty array
if (!$hotel) {
    $hotel = [];
}

// Set default values if hotel data is incomplete
$defaults = [
    'name' => '', 'tagline' => '', 'about_title' => '', 'about_description1' => '',
    'about_description2' => '', 'about_image' => '', 'background_image' => '', 'rooms_title' => '',
    'rooms_description' => '', 'amenities_title' => '', 'amenities_description' => '',
    'gallery_title' => '', 'gallery_description' => '', 'map_embed_url' => ''
];

foreach ($defaults as $key => $value) {
    if (empty($hotel[$key])) {
        $hotel[$key] = $value;
    }
}
?>

<div class="content-section">
    <div class="section-header">
        <h2><i class="fas fa-hotel me-2"></i>Hotel Profile</h2>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['updated']) && $_GET['updated'] == 'true'): ?>
        <div class="alert alert-success">Profile updated successfully!</div>
    <?php endif; ?>
    
    <form id="profileForm" class="profile-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <!-- Basic Information -->
        <div class="form-section">
            <h3><i class="fas fa-info-circle me-2"></i>Basic Information</h3>
            <div class="form-group">
                <label for="name">Hotel Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($hotel['name']); ?>" required class="form-control">
            </div>

            <div class="form-group">
                <label for="tagline">Tagline <span class="required">*</span></label>
                <input type="text" id="tagline" name="tagline" value="<?php echo htmlspecialchars($hotel['tagline']); ?>" required class="form-control">
            </div>
            
            <div class="form-group">
                <label for="background_image">Hotel Background Image URL <span class="required">*</span></label>
                <div class="image-url-container">
                    <?php if (!empty($hotel['background_image'])): ?>
                        <div class="current-image">
                            <img src="<?php echo htmlspecialchars($hotel['background_image']); ?>" alt="Hotel background image" class="preview-image">
                        </div>
                    <?php endif; ?>
                    <div class="url-controls">
                        <input type="url" id="background_image" name="background_image" value="<?php echo htmlspecialchars($hotel['background_image']); ?>" required class="form-control" placeholder="https://example.com/background.jpg">
                        <small class="form-text text-muted">Enter the direct URL of your background image (JPG, PNG, WebP, or GIF)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- About Section -->
        <div class="form-section">
            <h3><i class="fas fa-info-circle me-2"></i>About Section</h3>
            <div class="form-group">
                <label for="about_title">About Title <span class="required">*</span></label>
                <input type="text" id="about_title" name="about_title" value="<?php echo htmlspecialchars($hotel['about_title']); ?>" required class="form-control">
            </div>

            <div class="form-group">
                <label for="about_description1">About Description (Part 1) <span class="required">*</span></label>
                <textarea id="about_description1" name="about_description1" rows="4" required class="form-control"><?php echo htmlspecialchars($hotel['about_description1']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="about_description2">About Description (Part 2)</label>
                <textarea id="about_description2" name="about_description2" rows="4" class="form-control"><?php echo htmlspecialchars($hotel['about_description2']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="about_image">About Image URL <span class="required">*</span></label>
                <div class="image-url-container">
                    <?php if (!empty($hotel['about_image'])): ?>
                        <div class="current-image">
                            <img src="<?php echo htmlspecialchars($hotel['about_image']); ?>" alt="Current hotel image" class="preview-image">
                        </div>
                    <?php endif; ?>
                    <div class="url-controls">
                        <input type="url" id="about_image" name="about_image" value="<?php echo htmlspecialchars($hotel['about_image']); ?>" required class="form-control" placeholder="https://example.com/image.jpg">
                        <small class="form-text text-muted">Enter the direct URL of your image (JPG, PNG, WebP, or GIF)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rooms Section -->
        <div class="form-section">
            <h3><i class="fas fa-bed me-2"></i>Rooms Section</h3>
            <div class="form-group">
                <label for="rooms_title">Rooms Title <span class="required">*</span></label>
                <input type="text" id="rooms_title" name="rooms_title" value="<?php echo htmlspecialchars($hotel['rooms_title']); ?>" required class="form-control">
            </div>

            <div class="form-group">
                <label for="rooms_description">Rooms Description <span class="required">*</span></label>
                <textarea id="rooms_description" name="rooms_description" rows="4" required class="form-control"><?php echo htmlspecialchars($hotel['rooms_description']); ?></textarea>
            </div>
        </div>

        <!-- Amenities Section -->
        <div class="form-section">
            <h3><i class="fas fa-concierge-bell me-2"></i>Amenities Section</h3>
            <div class="form-group">
                <label for="amenities_title">Amenities Title <span class="required">*</span></label>
                <input type="text" id="amenities_title" name="amenities_title" value="<?php echo htmlspecialchars($hotel['amenities_title']); ?>" required class="form-control">
            </div>

            <div class="form-group">
                <label for="amenities_description">Amenities Description <span class="required">*</span></label>
                <textarea id="amenities_description" name="amenities_description" rows="4" required class="form-control"><?php echo htmlspecialchars($hotel['amenities_description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Available Amenities</label>
                <div class="amenities-grid">
                    <?php foreach ($available_amenities as $amenity): ?>
                        <div class="amenity-item">
                            <input type="checkbox" 
                                   id="amenity_<?php echo $amenity['id']; ?>" 
                                   name="amenities[]" 
                                   value="<?php echo $amenity['id']; ?>"
                                   <?php echo in_array($amenity['id'], $selected_amenities) ? 'checked' : ''; ?>>
                            <label for="amenity_<?php echo $amenity['id']; ?>" class="amenity-label">
                                <i class="<?php echo htmlspecialchars($amenity['icon']); ?>"></i>
                                <span class="amenity-name"><?php echo htmlspecialchars($amenity['name']); ?></span>
                                <span class="amenity-description"><?php echo htmlspecialchars($amenity['description']); ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Gallery Section -->
        <div class="form-section">
            <h3><i class="fas fa-images me-2"></i>Gallery Section</h3>
            <div class="form-group">
                <label for="gallery_title">Gallery Title <span class="required">*</span></label>
                <input type="text" id="gallery_title" name="gallery_title" value="<?php echo htmlspecialchars($hotel['gallery_title']); ?>" required class="form-control">
            </div>

            <div class="form-group">
                <label for="gallery_description">Gallery Description <span class="required">*</span></label>
                <textarea id="gallery_description" name="gallery_description" rows="4" required class="form-control"><?php echo htmlspecialchars($hotel['gallery_description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Gallery Images</label>
                <p class="form-text text-muted mb-3">Add up to 6 images to your hotel gallery. All images should be high quality and showcase your hotel's best features.</p>
                
                <div class="gallery-grid">
                    <?php foreach ($gallery_images as $index => $image): ?>
                        <div class="gallery-item">
                            <div class="image-preview" id="preview_<?php echo $index; ?>">
                                <?php if (!empty($image['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($image['image']); ?>" alt="Gallery preview">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                        <span>No image selected</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="gallery-inputs">
                                <div class="form-group">
                                    <input type="url" 
                                           name="gallery_image[]" 
                                           class="form-control gallery-url" 
                                           placeholder="Image URL"
                                           value="<?php echo htmlspecialchars($image['image']); ?>"
                                           data-preview="preview_<?php echo $index; ?>"
                                           onchange="previewImage(this)">
                                </div>
                                <div class="form-group">
                                    <input type="text" 
                                           name="gallery_alt[]" 
                                           class="form-control" 
                                           placeholder="Image description"
                                           value="<?php echo htmlspecialchars($image['alt_text']); ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <div class="form-section">
            <h3><i class="fas fa-map-marker-alt me-2"></i>Map Section</h3>
            <div class="form-group">
                <label for="map_embed_url">Google Maps Embed URL <span class="required">*</span></label>
                <input type="text" id="map_embed_url" name="map_embed_url" value="<?php echo htmlspecialchars($hotel['map_embed_url']); ?>" required class="form-control">
                <small class="form-text text-muted">Enter the full Google Maps embed URL</small>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="form-section">
            <h3><i class="fas fa-address-card me-2"></i>Contact Information</h3>
            <div class="form-group">
                <label for="contact_email">Email Address <span class="required">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" 
                           id="contact_email" 
                           name="contact_email" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($contact_info['email']); ?>" 
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
                           value="<?php echo htmlspecialchars($contact_info['phone']); ?>" 
                           required 
                           placeholder="Enter your hotel's phone number">
                </div>
                <small class="form-text text-muted">Include country code if applicable (e.g., +1 for US)</small>
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
                              placeholder="Enter your hotel's complete address"><?php echo htmlspecialchars($contact_info['address']); ?></textarea>
                </div>
                <small class="form-text text-muted">Provide the complete address including street, city, state/province, and postal code</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
        </div>
    </form>
</div>

<style>
/* Alert styles */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 15px;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

/* Main content styles */
.content-section {
    padding: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.section-header h2 {
    font-size: 28px;
    color: #333;
    margin: 0;
    position: relative;
    padding-bottom: 10px;
    display: flex;
    align-items: center;
}

.section-header h2:after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 80px;
    height: 3px;
    background-color: #ad8b3a;
}

.me-2 {
    margin-right: 0.5rem;
}

/* Form sections */
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

.form-section h3 {
    font-size: 20px;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
}

.form-section h3:after {
    content: '';
    position: absolute;
    width: 50px;
    height: 2px;
    background-color: #ad8b3a;
    bottom: 0;
    left: 25px;
}

/* Form elements */
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
    border-color: #ad8b3a;
    outline: 0;
    box-shadow: 0 0 0 3px rgba(173, 139, 58, 0.2);
    background-color: #fff;
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

.image-url-container {
    margin-top: 15px;
}

.current-image {
    margin-bottom: 20px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.preview-image {
    max-width: 250px;
    max-height: 250px;
    border-radius: 8px;
    display: block;
    transition: transform 0.3s ease;
}

.preview-image:hover {
    transform: scale(1.03);
}

.url-controls {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-text {
    font-size: 13px;
    color: #7f8c8d;
    margin-top: 5px;
}

.form-actions {
    margin-top: 30px;
    text-align: right;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-primary {
    background-color: #ad8b3a;
    color: white;
    box-shadow: 0 4px 6px rgba(173, 139, 58, 0.2);
}

.btn-primary:hover {
    background-color: #8a6e2e;
    box-shadow: 0 6px 8px rgba(173, 139, 58, 0.3);
    transform: translateY(-2px);
}

.btn-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(173, 139, 58, 0.2);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .content-section {
        padding: 20px;
    }
    
    .form-section {
        padding: 20px;
    }
    
    .btn {
        width: 100%;
    }
    
    .form-actions {
        text-align: center;
    }
}

/* Amenities Grid */
.amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.amenity-item {
    position: relative;
}

.amenity-item input[type="checkbox"] {
    display: none;
}

.amenity-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background-color: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.amenity-label i {
    font-size: 24px;
    margin-bottom: 10px;
    color: #ad8b3a;
}

.amenity-name {
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.amenity-description {
    font-size: 13px;
    color: #666;
    line-height: 1.4;
}

.amenity-item input[type="checkbox"]:checked + .amenity-label {
    background-color: #fff;
    border-color: #ad8b3a;
    box-shadow: 0 4px 12px rgba(173, 139, 58, 0.15);
    transform: translateY(-2px);
}

.amenity-item:hover .amenity-label {
    border-color: #ad8b3a;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .amenities-grid {
        grid-template-columns: 1fr;
    }
}

/* Gallery Grid */
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
    .gallery-grid {
        grid-template-columns: 1fr;
    }
}

/* Contact Information Styles */
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

.input-group textarea.form-control {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.input-group i {
    color: #ad8b3a;
}

/* Responsive adjustments for contact section */
@media (max-width: 768px) {
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simple image validation for the image URLs
    const validateImage = (input) => {
        const url = input.value.trim();
        if (url && !url.match(/\.(jpeg|jpg|png|webp|gif)$/i)) {
            input.setCustomValidity("Please enter a valid image URL (JPG, PNG, WebP, or GIF)");
        } else {
            input.setCustomValidity("");
        }
    };
    
    // Add validation to image URL inputs
    const imageInputs = document.querySelectorAll('input[type="url"]');
    imageInputs.forEach(input => {
        input.addEventListener('input', function() {
            validateImage(this);
        });
        input.addEventListener('blur', function() {
            validateImage(this);
        });
    });
    
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
    
    // Add loading animation to submit button
    const form = document.getElementById('profileForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        submitBtn.disabled = true;
    });

    // Add animation for amenity selection
    document.querySelectorAll('.amenity-item input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (this.checked) {
                label.style.transform = 'scale(1.02) translateY(-2px)';
                setTimeout(() => label.style.transform = 'translateY(-2px)', 200);
            } else {
                label.style.transform = 'none';
            }
        });
    });

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

    // Initialize all gallery image previews
    document.querySelectorAll('.gallery-url').forEach(input => {
        if (input.value) {
            previewImage(input);
        }
    });

    // Add phone number formatting
    document.getElementById('contact_phone').addEventListener('input', function(e) {
        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
        e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
    });
});
</script>
