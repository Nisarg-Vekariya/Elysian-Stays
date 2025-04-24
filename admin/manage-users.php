<?php
session_start();
ob_start(); // Start output buffering
require_once("header.php");
include_once("../db_connect.php"); // Include the database connection file

// Set default values for pagination, search and sorting
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // Number of users per page
$offset = ($page - 1) * $per_page;
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? htmlspecialchars($_GET['sort_by']) : 'id';
$sort_order = isset($_GET['sort_order']) ? htmlspecialchars($_GET['sort_order']) : 'asc';

// Handle Add User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addUser'])) {
    $name = htmlspecialchars($_POST['userName']);
    $username = htmlspecialchars($_POST['userUsername']);
    $email = htmlspecialchars($_POST['userEmail']);
    $phone = htmlspecialchars($_POST['userPhone']);
    $role = htmlspecialchars($_POST['userRole']);
    $password = password_hash($_POST['userPassword'], PASSWORD_DEFAULT); // Hash the password
    $status = 'active'; // Default status for new users

    // Insert new user into the database
    $sql = "INSERT INTO users (name, username, email, phone, role, password, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $name, $username, $email, $phone, $role, $password, $status);

    if ($stmt->execute()) {
        setcookie("success_message", "User added successfully!", time() + 5, "/");
    } else {
        setcookie("error_message", "Error adding user: " . $stmt->error, time() + 5, "/");
    }
    $stmt->close();
    header("Location: manage-users.php");
    ob_end_flush(); // End output buffering and send output
    exit();
}

// Handle Edit User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editUser'])) {
    $userId = htmlspecialchars($_POST['userId']);
    $name = htmlspecialchars($_POST['userName']);
    $email = htmlspecialchars($_POST['userEmail']);
    $phone = htmlspecialchars($_POST['userPhone']);
    $role = htmlspecialchars($_POST['userRole']);
    $status = htmlspecialchars($_POST['userStatus']);

    // Update user in the database
    $sql = "UPDATE users SET name = ?, email = ?, phone = ?, role = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $name, $email, $phone, $role, $status, $userId);

    if ($stmt->execute()) {
        setcookie("success_message", "User updated successfully!", time() + 5, "/");
    } else {
        setcookie("error_message", "Error updating user: " . $stmt->error, time() + 5, "/");
    }
    $stmt->close();
    header("Location: manage-users.php");
    ob_end_flush(); // End output buffering and send output
    exit();
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $userId = htmlspecialchars($_GET['delete']);

    // Delete user from the database
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        setcookie("success_message", "User deleted successfully!", time() + 5, "/");
    } else {
        setcookie("error_message", "Error deleting user: " . $stmt->error, time() + 5, "/");
    }
    $stmt->close();
    header("Location: manage-users.php");
    ob_end_flush(); // End output buffering and send output
    exit();
}

// Build query for fetching users with search and sort
$count_sql = "SELECT COUNT(*) as total FROM users";
$sql = "SELECT * FROM users";

// Add search condition if search term is provided
if (!empty($search)) {
    $search_condition = " WHERE name LIKE ? OR username LIKE ? OR email LIKE ? OR phone LIKE ? OR role LIKE ?";
    $count_sql .= $search_condition;
    $sql .= $search_condition;
    $search_param = "%$search%";
}

// Add sorting
$valid_sort_columns = ['id', 'name', 'username', 'email', 'phone', 'role', 'status'];
$valid_sort_orders = ['asc', 'desc'];

if (!in_array($sort_by, $valid_sort_columns)) {
    $sort_by = 'id';
}

if (!in_array($sort_order, $valid_sort_orders)) {
    $sort_order = 'asc';
}

$sql .= " ORDER BY $sort_by $sort_order";

// Add pagination
$sql .= " LIMIT $per_page OFFSET $offset";

// Fetch total number of users for pagination
if (!empty($search)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("sssss", $search_param, $search_param, $search_param, $search_param, $search_param);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_users = $count_row['total'];
    $count_stmt->close();
} else {
    $count_result = $conn->query($count_sql);
    $count_row = $count_result->fetch_assoc();
    $total_users = $count_row['total'];
}

// Calculate total pages
$total_pages = ceil($total_users / $per_page);

// Fetch users
$users = [];
if (!empty($search)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $search_param, $search_param, $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    $stmt->close();
} else {
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
}

$conn->close();

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

<div class="container my-5 animate__animated animate__fadeIn">
    <h1 class="text-center mb-4" style="color: #ad8b3a;">Manage Users</h1>
    <div class="card">
        <div class="card-body">
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
                <h5 class="" style="color: #ad8b3a;">User List</h5>
                <button class="btn btn-theme" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
            </div>

            <!-- Search and Filters -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form action="" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search users..." value="<?php echo $search; ?>">
                        <input type="hidden" name="sort_by" value="<?php echo $sort_by; ?>">
                        <input type="hidden" name="sort_order" value="<?php echo $sort_order; ?>">
                        <button type="submit" class="btn btn-theme">Search</button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <?php if(!empty($search)): ?>
                        <a href="manage-users.php" class="btn btn-outline-secondary">Clear Search</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover fade-in manage-users">
                    <thead>
                        <tr>
                            <th><a href="<?php echo getSortUrl('id', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">#<?php echo getSortIcon('id', $sort_by, $sort_order); ?></a></th>
                            <th><a href="<?php echo getSortUrl('name', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Name<?php echo getSortIcon('name', $sort_by, $sort_order); ?></a></th>
                            <th><a href="<?php echo getSortUrl('username', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Username<?php echo getSortIcon('username', $sort_by, $sort_order); ?></a></th>
                            <th><a href="<?php echo getSortUrl('email', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Email<?php echo getSortIcon('email', $sort_by, $sort_order); ?></a></th>
                            <th><a href="<?php echo getSortUrl('phone', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Phone<?php echo getSortIcon('phone', $sort_by, $sort_order); ?></a></th>
                            <th><a href="<?php echo getSortUrl('role', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Role<?php echo getSortIcon('role', $sort_by, $sort_order); ?></a></th>
                            <th><a href="<?php echo getSortUrl('status', $sort_by, $sort_order); ?>" class="text-decoration-none text-dark">Status<?php echo getSortIcon('status', $sort_by, $sort_order); ?></a></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $index => $user): ?>
                                <tr>
                                    <td><?php echo (($page - 1) * $per_page) + $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                                data-id="<?php echo $user['id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($user['name']); ?>" 
                                                data-username="<?php echo htmlspecialchars($user['username']); ?>" 
                                                data-email="<?php echo htmlspecialchars($user['email']); ?>" 
                                                data-phone="<?php echo htmlspecialchars($user['phone']); ?>" 
                                                data-role="<?php echo htmlspecialchars($user['role']); ?>"
                                                data-status="<?php echo htmlspecialchars($user['status']); ?>">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal" 
                                                data-id="<?php echo $user['id']; ?>">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
                <nav aria-label="User pagination">
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
                <p>Showing <?php echo min(($page - 1) * $per_page + 1, $total_users); ?> to <?php echo min($page * $per_page, $total_users); ?> of <?php echo $total_users; ?> users</p>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="userName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="userName" name="userName" placeholder="Enter user name" 
                               data-validation="required alphabetical" data-min="3" data-max="50" required>
                        <span id="userNameError" class="text-danger"></span>
                    </div>
                    <div class="mb-3">
                        <label for="userUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="userUsername" name="userUsername" placeholder="Enter username" 
                               data-validation="required username" data-min="3" data-max="20" required>
                        <span id="userUsernameError" class="text-danger"></span>
                    </div>
                    <div class="mb-3">
                        <label for="userEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="userEmail" name="userEmail" placeholder="Enter user email" 
                               data-validation="required email" required>
                        <span id="userEmailError" class="text-danger"></span>
                    </div>
                    <div class="mb-3">
                        <label for="userPhone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="userPhone" name="userPhone" placeholder="Enter user phone" 
                               data-validation="required phone" required>
                        <span id="userPhoneError" class="text-danger"></span>
                    </div>
                    <div class="mb-3">
                        <label for="userPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="userPassword" name="userPassword" placeholder="Enter password" 
                               data-validation="required strongPassword" required>
                        <span id="userPasswordError" class="text-danger"></span>
                    </div>
                    <div class="mb-3">
                        <label for="userRole" class="form-label">Role</label>
                        <select class="form-select" id="userRole" name="userRole" data-validation="required" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                            <option value="hotel">Hotel</option>
                        </select>
                        <span id="userRoleError" class="text-danger"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="addUser" class="btn btn-theme">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editUserId" name="userId">
                    <div class="mb-3">
                        <label for="editUserName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="editUserName" name="userName" placeholder="Enter user name" 
                               data-validation="required alphabetical" data-min="3" data-max="50" required>
                        <span id="editUserNameError" class="text-danger"></span>
                    </div>
                    <div class="mb-3">
                        <label for="editUserEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editUserEmail" name="userEmail" placeholder="Enter user email" 
                               data-validation="required email" required>
                        <span id="editUserEmailError" class="text-danger"></span>
                    </div>
                    <div class="mb-3">
                        <label for="editUserPhone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="editUserPhone" name="userPhone" placeholder="Enter user phone" 
                               data-validation="required phone" required>
                        <span id="editUserPhoneError" class="text-danger"></span>
                    </div>
                    <div class="mb-3">
                        <label for="editUserRole" class="form-label">Role</label>
                        <select class="form-select" id="editUserRole" name="userRole" data-validation="required" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                            <option value="hotel">Hotel</option>
                        </select>
                        <span id="editUserRoleError" class="text-danger"></span>
                    </div>
                    <div class="mb-3">
                        <label for="editUserStatus" class="form-label">Status</label>
                        <select class="form-select" id="editUserStatus" name="userStatus" data-validation="required" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <span id="editUserStatusError" class="text-danger"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="editUser" class="btn btn-theme">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this user?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="deleteUserLink" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Populate Edit Modal with User Data
    document.getElementById('editUserModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        const userId = button.getAttribute('data-id');
        const userName = button.getAttribute('data-name');
        const userEmail = button.getAttribute('data-email');
        const userPhone = button.getAttribute('data-phone');
        const userRole = button.getAttribute('data-role');
        const userStatus = button.getAttribute('data-status');

        // Update modal fields
        document.getElementById('editUserId').value = userId;
        document.getElementById('editUserName').value = userName;
        document.getElementById('editUserEmail').value = userEmail;
        document.getElementById('editUserPhone').value = userPhone;
        document.getElementById('editUserRole').value = userRole;
        document.getElementById('editUserStatus').value = userStatus;
    });

    // Populate Delete Modal with User ID
    document.getElementById('deleteUserModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        const userId = button.getAttribute('data-id');
        document.getElementById('deleteUserLink').href = "?delete=" + userId;
    });
</script>

<?php require_once("footer.php"); ?>