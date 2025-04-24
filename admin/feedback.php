<?php
require("header.php");

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new feedback
    if (isset($_POST['add_feedback'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $hotel = mysqli_real_escape_string($conn, $_POST['hotel']);
        $date_of_stay = $_POST['date_of_stay'] ? "'" . mysqli_real_escape_string($conn, $_POST['date_of_stay']) . "'" : "NULL";
        $comments = mysqli_real_escape_string($conn, $_POST['comments']);

        $sql = "INSERT INTO feedback (name, email, phone, hotel, date_of_stay, comments) 
                VALUES ('$name', '$email', '$phone', '$hotel', $date_of_stay, '$comments')";

        if (mysqli_query($conn, $sql)) {
            $success_message = "Feedback added successfully!";
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }

    // Update existing feedback
    if (isset($_POST['edit_feedback'])) {
        $feedback_id = intval($_POST['feedback_id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $hotel = mysqli_real_escape_string($conn, $_POST['hotel']);
        $date_of_stay = $_POST['date_of_stay'] ? "'" . mysqli_real_escape_string($conn, $_POST['date_of_stay']) . "'" : "NULL";
        $comments = mysqli_real_escape_string($conn, $_POST['comments']);

        $sql = "UPDATE feedback SET 
                name = '$name', 
                email = '$email', 
                phone = '$phone', 
                hotel = '$hotel', 
                date_of_stay = $date_of_stay, 
                comments = '$comments' 
                WHERE id = $feedback_id";

        if (mysqli_query($conn, $sql)) {
            $success_message = "Feedback updated successfully!";
        } else {
            $error_message = "Error updating feedback: " . mysqli_error($conn);
        }
    }

    // Delete feedback
    if (isset($_POST['delete_feedback'])) {
        $feedback_id = intval($_POST['feedback_id']);
        
        $sql = "DELETE FROM feedback WHERE id = $feedback_id";
        
        if (mysqli_query($conn, $sql)) {
            $success_message = "Feedback deleted successfully!";
        } else {
            $error_message = "Error deleting feedback: " . mysqli_error($conn);
        }
    }
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Build query based on filters
$where_clause = "1=1"; // Always true condition to start with
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$from_date = isset($_GET['from_date']) ? mysqli_real_escape_string($conn, $_GET['from_date']) : '';
$to_date = isset($_GET['to_date']) ? mysqli_real_escape_string($conn, $_GET['to_date']) : '';
$sort_by = isset($_GET['sort_by']) ? mysqli_real_escape_string($conn, $_GET['sort_by']) : 'created_at DESC';

if (!empty($search)) {
    $where_clause .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR hotel LIKE '%$search%')";
}

if (!empty($from_date)) {
    $where_clause .= " AND created_at >= '$from_date 00:00:00'";
}

if (!empty($to_date)) {
    $where_clause .= " AND created_at <= '$to_date 23:59:59'";
}

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM feedback WHERE $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get feedback data
$sql = "SELECT * FROM feedback WHERE $where_clause ORDER BY $sort_by LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $sql);

// Get hotel list for dropdowns
$hotel_sql = "SELECT DISTINCT hotel FROM feedback WHERE hotel IS NOT NULL AND hotel != '' ORDER BY hotel";
$hotel_result = mysqli_query($conn, $hotel_sql);
$hotels = [];
while ($row = mysqli_fetch_assoc($hotel_result)) {
    $hotels[] = $row['hotel'];
}
?>

<title>Admin Feedback Form</title>

<div class="hotel-content-wrapper">
    <div class="container-fluid">
        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <i class="fa fa-check-circle me-2"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <i class="fa fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Header Section -->
        <div class="row mb-4 animate__animated animate__fadeIn">
            <div class="col-md-8">
                <h2 class="hotel-title">Feedback Management</h2>
                <p class="hotel-subtitle">View and manage customer feedback submissions</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="hotel-btn-primary btn" data-bs-toggle="modal" data-bs-target="#addFeedbackModal">
                    <i class="fa fa-plus-circle me-2"></i> Add New Feedback
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card hotel-card mb-4 animate__animated animate__fadeIn animate__delay-1s">
            <div class="card-header hotel-card-header">
                <h5 class="mb-0"><i class="fa fa-filter me-2"></i> Filter Feedback</h5>
            </div>
            <div class="card-body hotel-card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="hotel-search-box">
                                <input type="text" name="search" class="form-control hotel-input mt-4" placeholder="Search by name, email or hotel..." value="<?php echo htmlspecialchars($search); ?>">
                                
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="hotel-date-range">
                                <div>
                                    <label class="hotel-form-label">From</label>
                                    <input type="date" name="from_date" class="form-control hotel-input" value="<?php echo htmlspecialchars($from_date); ?>">
                                </div>
                                <div>
                                    <label class="hotel-form-label">To</label>
                                    <input type="date" name="to_date" class="form-control hotel-input" value="<?php echo htmlspecialchars($to_date); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="hotel-btn-primary btn w-100">
                                <i class="fa fa-search me-2"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>">
                </form>
            </div>
        </div>

        <!-- Feedback Table -->
        <div class="card hotel-card animate__animated animate__fadeIn animate__delay-2s">
            <div class="card-header hotel-card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-comments me-2"></i> Customer Feedback</h5>
                <div>   
                    <div class="dropdown d-inline-block hotel-dropdown">
                        
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $sort_by == 'created_at DESC' ? 'active' : ''; ?>"
                                    href="?search=<?php echo urlencode($search); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&sort_by=created_at+DESC">Newest First</a></li>
                            <li><a class="dropdown-item <?php echo $sort_by == 'created_at ASC' ? 'active' : ''; ?>"
                                    href="?search=<?php echo urlencode($search); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&sort_by=created_at+ASC">Oldest First</a></li>
                            <li><a class="dropdown-item <?php echo $sort_by == 'hotel ASC' ? 'active' : ''; ?>"
                                    href="?search=<?php echo urlencode($search); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&sort_by=hotel+ASC">Hotel Name</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <form id="bulkActionForm" method="POST">
                    <div class="hotel-table-responsive">
                        <table class="table hotel-table-striped hotel-feedback-table mb-0 ">
                            <thead>
                                <tr class="hotel-table-header">
                                    <th scope="col" width="50">
                                    </th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Hotel</th>
                                    <th scope="col">Date of Stay</th>
                                    <th scope="col">Submitted On</th>
                                    <th scope="col" width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr class="animate__animated animate__fadeIn">
                                            <td>
                                            
                                            </td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($row['hotel']); ?></td>
                                            <td><?php echo $row['date_of_stay'] ? date('Y-m-d', strtotime($row['date_of_stay'])) : '-'; ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                            <td class="hotel-feedback-actions">
                                                
                                                <button type="button" class="btn btn-sm hotel-action-btn-secondary edit-btn"
                                                    data-id="<?php echo $row['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                                    data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                    data-phone="<?php echo htmlspecialchars($row['phone']); ?>"
                                                    data-hotel="<?php echo htmlspecialchars($row['hotel']); ?>"
                                                    data-date="<?php echo $row['date_of_stay'] ? date('Y-m-d', strtotime($row['date_of_stay'])) : ''; ?>"
                                                    data-comments="<?php echo htmlspecialchars($row['comments']); ?>"
                                                    data-bs-toggle="modal" data-bs-target="#editFeedbackModal">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm hotel-action-btn-danger delete-btn"
                                                    data-id="<?php echo $row['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr class="animate__animated animate__fadeIn">
                                        <td colspan="8" class="text-center py-4">No feedback records found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                </form>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>Showing <strong><?php echo $total_records > 0 ? $offset + 1 : 0; ?>-<?php echo min($offset + $records_per_page, $total_records); ?></strong> of <strong><?php echo $total_records; ?></strong> entries</div>
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination hotel-pagination mb-0">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&sort_by=<?php echo urlencode($sort_by); ?>" tabindex="-1" <?php echo $page <= 1 ? 'aria-disabled="true"' : ''; ?>>Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&sort_by=<?php echo urlencode($sort_by); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&sort_by=<?php echo urlencode($sort_by); ?>" <?php echo $page >= $total_pages ? 'aria-disabled="true"' : ''; ?>>Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Feedback Modal -->
<div class="modal fade" id="addFeedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animate__animated animate__fadeIn">
            <div class="modal-header hotel-modal-header">
                <h5 class="modal-title"><i class="fa fa-plus-circle me-2"></i> Add New Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="feedbackForm" method="POST" action="">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="hotel-form-label">Full Name *</label>
                            <input type="text" class="form-control hotel-input" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="hotel-form-label">Email Address *</label>
                            <input type="email" class="form-control hotel-input" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="hotel-form-label">Phone Number *</label>
                            <input type="tel" class="form-control hotel-input" id="phone" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label for="hotel" class="hotel-form-label">Hotel</label>
                            <select class="form-select hotel-input" id="hotel" name="hotel">
                                <option value="">Select Hotel</option>
                                <?php foreach ($hotels as $hotel): ?>
                                    <option value="<?php echo htmlspecialchars($hotel); ?>"><?php echo htmlspecialchars($hotel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="date_of_stay" class="hotel-form-label">Date of Stay</label>
                        <input type="date" class="form-control hotel-input" id="date_of_stay" name="date_of_stay">
                    </div>
                    <div class="mb-3">
                        <label for="comments" class="hotel-form-label">Comments</label>
                        <textarea class="form-control hotel-input" id="comments" name="comments" rows="4"></textarea>
                    </div>
                    <input type="hidden" name="add_feedback" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn hotel-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn hotel-btn-primary">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Feedback Modal -->
<div class="modal fade" id="editFeedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animate__animated animate__fadeIn">
            <div class="modal-header hotel-modal-header">
                <h5 class="modal-title"><i class="fa fa-edit me-2"></i> Edit Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editFeedbackForm" method="POST" action="">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_name" class="hotel-form-label">Full Name *</label>
                            <input type="text" class="form-control hotel-input" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_email" class="hotel-form-label">Email Address *</label>
                            <input type="email" class="form-control hotel-input" id="edit_email" name="email" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_phone" class="hotel-form-label">Phone Number *</label>
                            <input type="tel" class="form-control hotel-input" id="edit_phone" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_hotel" class="hotel-form-label">Hotel</label>
                            <select class="form-select hotel-input" id="edit_hotel" name="hotel">
                                <option value="">Select Hotel</option>
                                <?php foreach ($hotels as $hotel): ?>
                                    <option value="<?php echo htmlspecialchars($hotel); ?>"><?php echo htmlspecialchars($hotel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_date_of_stay" class="hotel-form-label">Date of Stay</label>
                        <input type="date" class="form-control hotel-input" id="edit_date_of_stay" name="date_of_stay">
                    </div>
                    <div class="mb-3">
                        <label for="edit_comments" class="hotel-form-label">Comments</label>
                        <textarea class="form-control hotel-input" id="edit_comments" name="comments" rows="4"></textarea>
                    </div>
                    <input type="hidden" name="feedback_id" id="edit_feedback_id">
                    <input type="hidden" name="edit_feedback" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn hotel-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn hotel-btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animate__animated animate__fadeIn">
            <div class="modal-header hotel-danger-header">
                <h5 class="modal-title"><i class="fa fa-trash me-2"></i> Delete Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <p>Are you sure you want to delete feedback from <strong id="delete_name"></strong>? This action cannot be undone.</p>
                    <input type="hidden" name="feedback_id" id="delete_feedback_id">
                    <input type="hidden" name="delete_feedback" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn hotel-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn hotel-danger-btn">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for Modal Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit button functionality
    const editButtons = document.querySelectorAll('.edit-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const email = this.getAttribute('data-email');
            const phone = this.getAttribute('data-phone');
            const hotel = this.getAttribute('data-hotel');
            const date = this.getAttribute('data-date');
            const comments = this.getAttribute('data-comments');
            
            document.getElementById('edit_feedback_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_phone').value = phone;
            
            // For select element, we need to find the option
            const hotelSelect = document.getElementById('edit_hotel');
            for(let i = 0; i < hotelSelect.options.length; i++) {
                if(hotelSelect.options[i].value === hotel) {
                    hotelSelect.selectedIndex = i;
                    break;
                }
            }
            
            document.getElementById('edit_date_of_stay').value = date;
            document.getElementById('edit_comments').value = comments;
        });
    });
    
    // Delete button functionality
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            document.getElementById('delete_feedback_id').value = id;
            document.getElementById('delete_name').textContent = name;
            
            // Show the delete confirmation modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>