<title>Platform Settings</title>
<?php require_once("header.php"); ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 animate__animated animate__fadeIn">
            <h1 class="text-center mb-4" style="color: #ad8b3a;">Platform Settings</h1>
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php
                    if (isset($_GET['status'])) {
                        $status = $_GET['status'];
                        if ($status === 'success') {
                            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Operation completed successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                  </div>';
                        } elseif ($status === 'error') {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    An error occurred. Please try again.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                  </div>';
                        } elseif ($status === 'deleted') {
                            echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    Offer deleted successfully.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                  </div>';
                        }
                    }
                    ?>
                    

                    <!-- Content Management Section -->
                    <hr class="my-4">
                    <h5 class="mb-3" style="color: #ad8b3a;">Content Management</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title mb-3">Index Page</h6>
                                    <a href="content/index-page.php" class="btn btn-theme btn-sm">Edit</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title mb-3">About Page</h6>
                                    <a href="content/about-page.php" class="btn btn-theme btn-sm">Edit</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title mb-3">Contact Page</h6>
                                    <a href="content/contact-page.php" class="btn btn-theme btn-sm">Edit</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title mb-3">List Your Place</h6>
                                    <a href="content/list-place.php" class="btn btn-theme btn-sm">Edit</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title mb-3">Terms of Service</h6>
                                    <a href="content/terms.php" class="btn btn-theme btn-sm">Edit</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title mb-3">Payments Terms</h6>
                                    <a href="content/ptos.php" class="btn btn-theme btn-sm">Edit</a>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title mb-3"></h6>
                                    <a href="content/faq.php" class="btn btn-theme btn-sm">Edit</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title mb-3">Blog</h6>
                                    <a href="content/blog.php" class="btn btn-theme btn-sm">Edit</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h6 class="card-title mb-3">Newsletter</h6>
                                    <a href="content/newsletter.php" class="btn btn-theme btn-sm">Edit</a>
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <!-- Slider Offers Settings -->
                    <hr class="my-4">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 style="color: #ad8b3a;">Slider Offers</h5>
                        <button class="btn btn-theme" data-bs-toggle="modal" data-bs-target="#addOfferModal">Add Offer</button>
                    </div>

                    <!-- List of Existing Offers -->
                    <div class="table-responsive">
                        <table class="table table-hover fade-in">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Coupon Code</th>
                                    <th>Discount (%)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                require '../db_connect.php'; // Include database connection
                                $query = "SELECT * FROM sliders ORDER BY created_at DESC";
                                $result = $conn->query($query);

                                if (!$result) {
                                    die("Database query failed: " . $conn->error);
                                }

                                if ($result->num_rows > 0) {
                                    while ($offer = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $offer['id']; ?></td>
                                            <td><?php echo htmlspecialchars($offer['title']); ?></td>
                                            <td><?php echo htmlspecialchars($offer['coupon_code']); ?></td>
                                            <td><?php echo htmlspecialchars($offer['discount'] ?? '0'); ?>%</td>
                                            <td>
                                                <span class="badge <?php echo $offer['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $offer['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="operation/edit_offer.php?id=<?php echo $offer['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="operation/delete_offer.php?id=<?php echo $offer['id']; ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this offer?');">Delete</a>
                                            </td>
                                        </tr>
                                <?php endwhile;
                                } else {
                                    echo '<tr><td colspan="6" class="text-center">No offers found.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Offer Modal -->
<div class="modal fade" id="addOfferModal" tabindex="-1" aria-labelledby="addOfferModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="operation/save_offer.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addOfferModalLabel">Add New Offer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Offer Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="coupon_code" class="form-label">Coupon Code</label>
                        <input type="text" class="form-control" id="coupon_code" name="coupon_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="discount" class="form-label">Discount Percentage (%)</label>
                        <input type="number" class="form-control" id="discount" name="discount" min="0" max="100" step="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="is_active" class="form-label">Status</label>
                        <select class="form-select" id="is_active" name="is_active" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-theme">Add Offer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to proceed? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<?php require_once("footer.php"); ?>