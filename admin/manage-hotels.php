<?php
// Start output buffering to prevent headers already sent error
ob_start();
require_once("header.php");

// Set default values for pagination, search and sorting
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // Number of hotels per page
$offset = ($page - 1) * $per_page;
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? htmlspecialchars($_GET['sort_by']) : 'id';
$sort_order = isset($_GET['sort_order']) ? htmlspecialchars($_GET['sort_order']) : 'asc';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_hotel'])) {
        // Add new hotel
        $name = $conn->real_escape_string($_POST['name']);
        $tagline = $conn->real_escape_string($_POST['tagline']);
        $background_image = $conn->real_escape_string($_POST['background_image']);
        $about_title = $conn->real_escape_string($_POST['about_title']);
        $about_description1 = $conn->real_escape_string($_POST['about_description1']);
        $about_description2 = $conn->real_escape_string($_POST['about_description2']);
        $rooms_title = $conn->real_escape_string($_POST['rooms_title']);
        $rooms_description = $conn->real_escape_string($_POST['rooms_description']);
        $amenities_title = $conn->real_escape_string($_POST['amenities_title']);
        $amenities_description = $conn->real_escape_string($_POST['amenities_description']);
        $gallery_title = $conn->real_escape_string($_POST['gallery_title']);
        $gallery_description = $conn->real_escape_string($_POST['gallery_description']);
        $map_embed_url = $conn->real_escape_string($_POST['map_embed_url']);

        // Handle file upload
        $about_image = '';
        if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] == 0) {
            $target_dir = "uploads/hotels/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_ext = pathinfo($_FILES['about_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'hotel_' . time() . '.' . $file_ext;
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES['about_image']['tmp_name'], $target_file)) {
                $about_image = $target_file;
            }
        }

        $query = "INSERT INTO hotels (name, tagline, background_image, about_title, about_description1, about_description2, about_image, 
                  rooms_title, rooms_description, amenities_title, amenities_description, gallery_title, gallery_description, map_embed_url)
                  VALUES ('$name', '$tagline', '$background_image', '$about_title', '$about_description1', '$about_description2', '$about_image',
                  '$rooms_title', '$rooms_description', '$amenities_title', '$amenities_description', '$gallery_title', '$gallery_description', '$map_embed_url')";

        if ($conn->query($query)) {
            setcookie("success_message", "Hotel added successfully!", time() + 5, "/");
        } else {
            setcookie("error_message", "Error adding hotel: " . $conn->error, time() + 5, "/");
        }
        // Use JavaScript to redirect
        echo '<script>window.location.href = "manage-hotels.php";</script>';
        exit();
    } elseif (isset($_POST['edit_hotel'])) {
        // Edit existing hotel
        $id = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $tagline = $conn->real_escape_string($_POST['tagline']);
        $background_image = $conn->real_escape_string($_POST['background_image']);
        $about_title = $conn->real_escape_string($_POST['about_title']);
        $about_description1 = $conn->real_escape_string($_POST['about_description1']);
        $about_description2 = $conn->real_escape_string($_POST['about_description2']);
        $rooms_title = $conn->real_escape_string($_POST['rooms_title']);
        $rooms_description = $conn->real_escape_string($_POST['rooms_description']);
        $amenities_title = $conn->real_escape_string($_POST['amenities_title']);
        $amenities_description = $conn->real_escape_string($_POST['amenities_description']);
        $gallery_title = $conn->real_escape_string($_POST['gallery_title']);
        $gallery_description = $conn->real_escape_string($_POST['gallery_description']);
        $map_embed_url = $conn->real_escape_string($_POST['map_embed_url']);

        // Handle file upload if a new image is provided
        $about_image = '';
        if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] == 0) {
            $target_dir = "uploads/hotels/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_ext = pathinfo($_FILES['about_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'hotel_' . time() . '.' . $file_ext;
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES['about_image']['tmp_name'], $target_file)) {
                $about_image = $target_file;
                // Delete old image if exists
                $old_image_query = "SELECT about_image FROM hotels WHERE id = $id";
                $old_image_result = $conn->query($old_image_query);
                if ($old_image_result && $old_image_row = $old_image_result->fetch_assoc()) {
                    if (!empty($old_image_row['about_image']) && file_exists($old_image_row['about_image'])) {
                        unlink($old_image_row['about_image']);
                    }
                }
                $image_update = ", about_image = '$about_image'";
            }
        } else {
            $image_update = "";
        }

        $query = "UPDATE hotels SET 
                  name = '$name', 
                  tagline = '$tagline', 
                  background_image = '$background_image', 
                  about_title = '$about_title', 
                  about_description1 = '$about_description1', 
                  about_description2 = '$about_description2', 
                  rooms_title = '$rooms_title', 
                  rooms_description = '$rooms_description', 
                  amenities_title = '$amenities_title', 
                  amenities_description = '$amenities_description', 
                  gallery_title = '$gallery_title', 
                  gallery_description = '$gallery_description', 
                  map_embed_url = '$map_embed_url'
                  $image_update
                  WHERE id = $id";

        if ($conn->query($query)) {
            setcookie("success_message", "Hotel updated successfully!", time() + 5, "/");
        } else {
            setcookie("error_message", "Error updating hotel: " . $conn->error, time() + 5, "/");
        }
        // Use JavaScript to redirect
        echo '<script>window.location.href = "manage-hotels.php";</script>';
        exit();
    } elseif (isset($_POST['delete_hotel'])) {
        // Delete hotel
        $id = intval($_POST['id']);

        // First, get the image path to delete it
        $image_query = "SELECT about_image FROM hotels WHERE id = $id";
        $image_result = $conn->query($image_query);
        $image_path = '';
        if ($image_result && $image_row = $image_result->fetch_assoc()) {
            $image_path = $image_row['about_image'];
        }

        $query = "DELETE FROM hotels WHERE id = $id";
        if ($conn->query($query)) {
            // Delete the associated image file if it exists
            if (!empty($image_path) && file_exists($image_path)) {
                unlink($image_path);
            }
            setcookie("success_message", "Hotel deleted successfully!", time() + 5, "/");
        } else {
            setcookie("error_message", "Error deleting hotel: " . $conn->error, time() + 5, "/");
        }
        // Use JavaScript to redirect
        echo '<script>window.location.href = "manage-hotels.php";</script>';
        exit();
    }
}

// Build query for fetching hotels with search and sort
$count_sql = "SELECT COUNT(*) as total FROM hotels h";
$sql = "SELECT h.*, COUNT(r.id) as room_count FROM hotels h LEFT JOIN rooms r ON h.id = r.hotel_id";

// Add search condition if search term is provided
if (!empty($search)) {
    $search_condition = " WHERE h.name LIKE ? OR h.tagline LIKE ?";
    $count_sql .= $search_condition;
    $sql .= $search_condition;
    $search_param = "%$search%";
}

// Add sorting
$valid_sort_columns = ['id', 'name', 'tagline', 'room_count'];
$valid_sort_orders = ['asc', 'desc'];

if (!in_array($sort_by, $valid_sort_columns)) {
    $sort_by = 'id';
}

if (!in_array($sort_order, $valid_sort_orders)) {
    $sort_order = 'asc';
}

$sql .= " GROUP BY h.id ORDER BY $sort_by $sort_order";

// Add pagination
$sql .= " LIMIT $per_page OFFSET $offset";

// Fetch total number of hotels for pagination
if (!empty($search)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("ss", $search_param, $search_param);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_hotels = $count_row['total'];
    $count_stmt->close();
} else {
    $count_result = $conn->query($count_sql);
    $count_row = $count_result->fetch_assoc();
    $total_hotels = $count_row['total'];
}

// Calculate total pages
$total_pages = ceil($total_hotels / $per_page);

// Fetch hotels
$hotels = [];
if (!empty($search)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $hotels[] = $row;
        }
    }
    $stmt->close();
} else {
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $hotels[] = $row;
        }
    }
}

// Function to create sort URL
function getSortUrl($column, $current_sort_by, $current_sort_order) {
    $new_sort_order = ($current_sort_by === $column && $current_sort_order === 'asc') ? 'desc' : 'asc';
    $params = $_GET;
    $params['sort_by'] = $column;
    $params['sort_order'] = $new_sort_order;
    return '?' . http_build_query($params);
}

// Function to get sort icon
function getSortIcon($column, $current_sort_by, $current_sort_order) {
    if ($current_sort_by !== $column) {
        return '<i class="fas fa-sort"></i>';
    }
    return ($current_sort_order === 'asc') ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
}
?>
<title>Manage Hotels - Elysian Stays</title>
<div class="container my-5 animate__animated animate__fadeIn">
    <div class="card">
        <div class="card-body">
            <h1 class="text-center mb-4" style="color: #ad8b3a;">Manage Hotel</h1>
            <!-- Display Success/Error Messages -->
            <?php if (isset($_COOKIE['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_COOKIE['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php setcookie("success_message", "", time() - 3600, "/"); ?>
            <?php endif; ?>
            <?php if (isset($_COOKIE['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_COOKIE['error_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php setcookie("error_message", "", time() - 3600, "/"); ?>
            <?php endif; ?>

            <div class="d-flex justify-content-between mb-3">
                <h5 class="" style="color: #ad8b3a;">Hotel List</h5>
                <button class="btn btn-theme" data-bs-toggle="modal" data-bs-target="#addHotelModal">Add hotel</button>
            </div>

            <!-- Search and Filters -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form action="" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search hotels..." value="<?php echo $search; ?>">
                        <input type="hidden" name="sort_by" value="<?php echo $sort_by; ?>">
                        <input type="hidden" name="sort_order" value="<?php echo $sort_order; ?>">
                        <button type="submit" class="btn btn-theme">Search</button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <?php if(!empty($search)): ?>
                        <a href="manage-hotels.php" class="btn btn-outline-secondary">Clear Search</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover fade-in hotel-table">
                    <thead>
                        <tr class="hotel-table-header">
                            <th><a href="<?php echo getSortUrl('id', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">#<?php echo getSortIcon('id', $sort_by, $sort_order); ?></a></th>
                            <th><a href="<?php echo getSortUrl('name', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Name<?php echo getSortIcon('name', $sort_by, $sort_order); ?></a></th>
                            <th><a href="<?php echo getSortUrl('tagline', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Tagline<?php echo getSortIcon('tagline', $sort_by, $sort_order); ?></a></th>
                            <th>Status</th>
                            <th><a href="<?php echo getSortUrl('room_count', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Rooms<?php echo getSortIcon('room_count', $sort_by, $sort_order); ?></a></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($hotels) > 0): ?>
                            <?php foreach ($hotels as $index => $hotel): ?>
                                <tr>
                                    <td><?php echo (($page - 1) * $per_page) + $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($hotel['name']); ?></td>
                                    <td><?php echo htmlspecialchars($hotel['tagline']); ?></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td><?php echo $hotel['room_count']; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-warning me-1 edit-hotel-btn" title="Edit Hotel"
                                                data-bs-toggle="modal" data-bs-target="#editHotelModal"
                                                data-hotel-id="<?php echo $hotel['id']; ?>"
                                                data-hotel-name="<?php echo htmlspecialchars($hotel['name']); ?>"
                                                data-hotel-tagline="<?php echo htmlspecialchars($hotel['tagline']); ?>"
                                                data-hotel-background-image="<?php echo htmlspecialchars($hotel['background_image']); ?>"
                                                data-hotel-about-title="<?php echo htmlspecialchars($hotel['about_title']); ?>"
                                                data-hotel-about-desc1="<?php echo htmlspecialchars($hotel['about_description1']); ?>"
                                                data-hotel-about-desc2="<?php echo htmlspecialchars($hotel['about_description2']); ?>"
                                                data-hotel-about-image="<?php echo htmlspecialchars($hotel['about_image']); ?>"
                                                data-hotel-rooms-title="<?php echo htmlspecialchars($hotel['rooms_title']); ?>"
                                                data-hotel-rooms-desc="<?php echo htmlspecialchars($hotel['rooms_description']); ?>"
                                                data-hotel-amenities-title="<?php echo htmlspecialchars($hotel['amenities_title']); ?>"
                                                data-hotel-amenities-desc="<?php echo htmlspecialchars($hotel['amenities_description']); ?>"
                                                data-hotel-gallery-title="<?php echo htmlspecialchars($hotel['gallery_title']); ?>"
                                                data-hotel-gallery-desc="<?php echo htmlspecialchars($hotel['gallery_description']); ?>"
                                                data-hotel-map-url="<?php echo htmlspecialchars($hotel['map_embed_url']); ?>">
                                                <i class="fa fa-edit"></i><span class="d-none d-md-inline"> Edit</span>
                                            </button>
                                            <button class="btn btn-sm btn-danger me-1 delete-hotel-btn" title="Delete Hotel"
                                                data-bs-toggle="modal" data-bs-target="#deleteHotelModal"
                                                data-hotel-id="<?php echo $hotel['id']; ?>"
                                                data-hotel-name="<?php echo htmlspecialchars($hotel['name']); ?>">
                                                <i class="fa fa-trash"></i><span class="d-none d-md-inline"> Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No hotels found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
                <nav aria-label="Hotel pagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>">Previous</a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

            <div class="mt-3 text-center">
                <p>Showing <?php echo min(($page - 1) * $per_page + 1, $total_hotels); ?> to <?php echo min($page * $per_page, $total_hotels); ?> of <?php echo $total_hotels; ?> hotels</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Hotel Modal -->
<div class="modal fade" id="addHotelModal" tabindex="-1" aria-labelledby="addHotelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="manage-hotels.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="addHotelModalLabel">Add New Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Hotel Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   data-validation="required" data-min="3" data-max="100" required>
                            <span id="nameError" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tagline" class="form-label">Tagline</label>
                            <input type="text" class="form-control" id="tagline" name="tagline" 
                                   data-validation="required" data-min="5" data-max="200" required>
                            <span id="taglineError" class="text-danger"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="background_image" class="form-label">Background Image URL</label>
                        <input type="text" class="form-control" id="background_image" name="background_image" 
                               data-validation="required url" required>
                        <small class="form-text text-muted">Direct URL to an image that will be used as background for your hotel header</small>
                        <span id="background_imageError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="about_title" class="form-label">About Title</label>
                        <input type="text" class="form-control" id="about_title" name="about_title" 
                               data-validation="required" data-min="3" data-max="100" required>
                        <span id="about_titleError" class="text-danger"></span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="about_description1" class="form-label">About Description Part 1</label>
                            <textarea class="form-control" id="about_description1" name="about_description1" rows="3" 
                                      data-validation="required" data-min="10" data-max="500" required></textarea>
                            <span id="about_description1Error" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="about_description2" class="form-label">About Description Part 2</label>
                            <textarea class="form-control" id="about_description2" name="about_description2" rows="3" 
                                      data-validation="required" data-min="10" data-max="500" required></textarea>
                            <span id="about_description2Error" class="text-danger"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="about_image" class="form-label">About Image</label>
                        <input type="file" class="form-control" id="about_image" name="about_image" accept="image/*" 
                               data-validation="required file" required>
                        <span id="about_imageError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="rooms_title" class="form-label">Rooms Title</label>
                        <input type="text" class="form-control" id="rooms_title" name="rooms_title" 
                               data-validation="required" data-min="3" data-max="100" required>
                        <span id="rooms_titleError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="rooms_description" class="form-label">Rooms Description</label>
                        <textarea class="form-control" id="rooms_description" name="rooms_description" rows="3" 
                                  data-validation="required" data-min="10" data-max="500" required></textarea>
                        <span id="rooms_descriptionError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="amenities_title" class="form-label">Amenities Title</label>
                        <input type="text" class="form-control" id="amenities_title" name="amenities_title" 
                               data-validation="required" data-min="3" data-max="100" required>
                        <span id="amenities_titleError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="amenities_description" class="form-label">Amenities Description</label>
                        <textarea class="form-control" id="amenities_description" name="amenities_description" rows="3" 
                                  data-validation="required" data-min="10" data-max="500" required></textarea>
                        <span id="amenities_descriptionError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="gallery_title" class="form-label">Gallery Title</label>
                        <input type="text" class="form-control" id="gallery_title" name="gallery_title" 
                               data-validation="required" data-min="3" data-max="100" required>
                        <span id="gallery_titleError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="gallery_description" class="form-label">Gallery Description</label>
                        <textarea class="form-control" id="gallery_description" name="gallery_description" rows="3" 
                                  data-validation="required" data-min="10" data-max="500" required></textarea>
                        <span id="gallery_descriptionError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="map_embed_url" class="form-label">Map Embed URL</label>
                        <input type="text" class="form-control" id="map_embed_url" name="map_embed_url" 
                               data-validation="required url" required>
                        <span id="map_embed_urlError" class="text-danger"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="add_hotel">Add Hotel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Hotel Modal -->
<div class="modal fade" id="editHotelModal" tabindex="-1" aria-labelledby="editHotelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="manage-hotels.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_hotel_id">
                <input type="hidden" name="edit_hotel" value="1">
                <div class="modal-header">
                    <h5 class="modal-title" id="editHotelModalLabel">Edit Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Hotel Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" 
                                   data-validation="required" data-min="3" data-max="100" required>
                            <span id="edit_nameError" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_tagline" class="form-label">Tagline</label>
                            <input type="text" class="form-control" id="edit_tagline" name="tagline" 
                                   data-validation="required" data-min="5" data-max="200" required>
                            <span id="edit_taglineError" class="text-danger"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_background_image" class="form-label">Background Image URL</label>
                        <input type="text" class="form-control" id="edit_background_image" name="background_image" 
                               data-validation="required url" required>
                        <small class="form-text text-muted">Direct URL to an image that will be used as background for your hotel header</small>
                        <span id="edit_background_imageError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="edit_about_title" class="form-label">About Title</label>
                        <input type="text" class="form-control" id="edit_about_title" name="about_title" 
                               data-validation="required" data-min="3" data-max="100" required>
                        <span id="edit_about_titleError" class="text-danger"></span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_about_description1" class="form-label">About Description Part 1</label>
                            <textarea class="form-control" id="edit_about_description1" name="about_description1" rows="3" 
                                      data-validation="required" data-min="10" data-max="500" required></textarea>
                            <span id="edit_about_description1Error" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_about_description2" class="form-label">About Description Part 2</label>
                            <textarea class="form-control" id="edit_about_description2" name="about_description2" rows="3" 
                                      data-validation="required" data-min="10" data-max="500" required></textarea>
                            <span id="edit_about_description2Error" class="text-danger"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_about_image" class="form-label">About Image</label>
                        <input type="file" class="form-control" id="edit_about_image" name="about_image" accept="image/*" 
                               data-validation="file">
                        <div class="mt-2">
                            <img id="current_about_image" src="" class="img-thumbnail" style="max-height: 150px; display: none;">
                        </div>
                        <span id="edit_about_imageError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="edit_rooms_title" class="form-label">Rooms Title</label>
                        <input type="text" class="form-control" id="edit_rooms_title" name="rooms_title" 
                               data-validation="required" data-min="3" data-max="100" required>
                        <span id="edit_rooms_titleError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="edit_rooms_description" class="form-label">Rooms Description</label>
                        <textarea class="form-control" id="edit_rooms_description" name="rooms_description" rows="3" 
                                  data-validation="required" data-min="10" data-max="500" required></textarea>
                        <span id="edit_rooms_descriptionError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="edit_amenities_title" class="form-label">Amenities Title</label>
                        <input type="text" class="form-control" id="edit_amenities_title" name="amenities_title" 
                               data-validation="required" data-min="3" data-max="100" required>
                        <span id="edit_amenities_titleError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="edit_amenities_description" class="form-label">Amenities Description</label>
                        <textarea class="form-control" id="edit_amenities_description" name="amenities_description" rows="3" 
                                  data-validation="required" data-min="10" data-max="500" required></textarea>
                        <span id="edit_amenities_descriptionError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="edit_gallery_title" class="form-label">Gallery Title</label>
                        <input type="text" class="form-control" id="edit_gallery_title" name="gallery_title" 
                               data-validation="required" data-min="3" data-max="100" required>
                        <span id="edit_gallery_titleError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="edit_gallery_description" class="form-label">Gallery Description</label>
                        <textarea class="form-control" id="edit_gallery_description" name="gallery_description" rows="3" 
                                  data-validation="required" data-min="10" data-max="500" required></textarea>
                        <span id="edit_gallery_descriptionError" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label for="edit_map_embed_url" class="form-label">Map Embed URL</label>
                        <input type="text" class="form-control" id="edit_map_embed_url" name="map_embed_url" 
                               data-validation="required url" required>
                        <span id="edit_map_embed_urlError" class="text-danger"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Hotel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Hotel Modal -->
<div class="modal fade" id="deleteHotelModal" tabindex="-1" aria-labelledby="deleteHotelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="manage-hotels.php" method="POST">
                <input type="hidden" name="id" id="delete_hotel_id">
                <input type="hidden" name="delete_hotel" value="1">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteHotelModalLabel">Delete Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the hotel: <strong id="hotel_to_delete"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will delete all associated rooms and bookings.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Hotel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Handle edit button click to populate the edit modal
    document.querySelectorAll('.edit-hotel-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const hotelId = this.getAttribute('data-hotel-id');
            document.getElementById('edit_hotel_id').value = hotelId;
            
            document.getElementById('edit_name').value = this.getAttribute('data-hotel-name');
            document.getElementById('edit_tagline').value = this.getAttribute('data-hotel-tagline');
            document.getElementById('edit_background_image').value = this.getAttribute('data-hotel-background-image');
            document.getElementById('edit_about_title').value = this.getAttribute('data-hotel-about-title');
            document.getElementById('edit_about_description1').value = this.getAttribute('data-hotel-about-desc1');
            document.getElementById('edit_about_description2').value = this.getAttribute('data-hotel-about-desc2');
            document.getElementById('edit_rooms_title').value = this.getAttribute('data-hotel-rooms-title');
            document.getElementById('edit_rooms_description').value = this.getAttribute('data-hotel-rooms-desc');
            document.getElementById('edit_amenities_title').value = this.getAttribute('data-hotel-amenities-title');
            document.getElementById('edit_amenities_description').value = this.getAttribute('data-hotel-amenities-desc');
            document.getElementById('edit_gallery_title').value = this.getAttribute('data-hotel-gallery-title');
            document.getElementById('edit_gallery_description').value = this.getAttribute('data-hotel-gallery-desc');
            document.getElementById('edit_map_embed_url').value = this.getAttribute('data-hotel-map-url');

            // Handle image display
            const imagePath = this.getAttribute('data-hotel-about-image');
            const imgElement = document.getElementById('current_about_image');
            if (imagePath) {
                imgElement.src = imagePath;
                imgElement.style.display = 'block';
            } else {
                imgElement.style.display = 'none';
            }
        });
    });

    // Handle delete button click to populate the delete modal
    document.querySelectorAll('.delete-hotel-btn').forEach(button => {
        button.addEventListener('click', function() {
            const hotelId = this.getAttribute('data-hotel-id');
            const hotelName = this.getAttribute('data-hotel-name');

            document.getElementById('delete_hotel_id').value = hotelId;
            document.getElementById('hotel_to_delete').textContent = hotelName;
        });
    });
</script>

<?php require_once("footer.php"); ?>