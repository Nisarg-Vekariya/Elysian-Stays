<?php
require '../../db_connect.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the offer details
    $query = "SELECT * FROM sliders WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $offer = $result->fetch_assoc();

    if (!$offer) {
        die("Offer not found.");
    }
} else {
    // Redirect if accessed directly or without an ID
    header("Location: ../platform_settings.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $id = $_POST['id'];
    $title = $_POST['title'];
    $coupon_code = $_POST['coupon_code'];
    $discount = $_POST['discount'];
    $is_active = $_POST['is_active'];

    // Validate input
    if (empty($title) || empty($coupon_code) || !is_numeric($discount) || $discount < 0 || $discount > 100) {
        die("Invalid input data.");
    }

    // Update the offer in the database
    $query = "UPDATE sliders SET title = ?, coupon_code = ?, discount = ?, is_active = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiii", $title, $coupon_code, $discount, $is_active, $id);

    if ($stmt->execute()) {
        // Redirect back to the settings page with a success message
        header("Location: ../platform_settings.php?status=success");
        exit();
    } else {
        // Redirect back with an error message
        header("Location: ../platform_settings.php?status=error");
        exit();
    }
}
?>

<!-- Edit Offer Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Offer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Edit Offer</h3>
                    </div>
                    <div class="card-body">
                    <form method="POST" action="save_edited_offer.php">
                            <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                            <div class="mb-3">
                                <label for="title" class="form-label">Offer Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($offer['title']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="coupon_code" class="form-label">Coupon Code</label>
                                <input type="text" class="form-control" id="coupon_code" name="coupon_code" value="<?php echo htmlspecialchars($offer['coupon_code']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="discount" class="form-label">Discount Percentage (%)</label>
                                <input type="number" class="form-control" id="discount" name="discount" value="<?php echo htmlspecialchars($offer['discount']); ?>" min="0" max="100" step="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-select" id="is_active" name="is_active" required>
                                    <option value="1" <?php echo $offer['is_active'] ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo !$offer['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-theme">Update Offer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>