<?php
require_once '../config/database.php';
session_start();

// Get hotel ID from session or use default for testing
$hotel_id = $_SESSION['hotel_id'] ?? 1;

// Handle room operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $response = ['success' => false, 'message' => 'Unknown error'];
        
        try {
            switch ($_POST['action']) {
                case 'add':
                    $stmt = $conn->prepare("INSERT INTO rooms (hotel_id, name, description, image, price, capacity, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        $hotel_id,
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['image'],
                        $_POST['price'],
                        $_POST['capacity'],
                        'available'
                    ]);
                    
                    if ($result) {
                        $response = ['success' => true, 'message' => 'Room added successfully'];
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to add room'];
                    }
                    break;

                case 'update':
                    $stmt = $conn->prepare("UPDATE rooms SET name = ?, description = ?, image = ?, price = ?, capacity = ?, status = ? WHERE id = ? AND hotel_id = ?");
                    $result = $stmt->execute([
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['image'],
                        $_POST['price'],
                        $_POST['capacity'],
                        $_POST['status'],
                        $_POST['room_id'],
                        $hotel_id
                    ]);
                    
                    if ($result) {
                        $response = ['success' => true, 'message' => 'Room updated successfully'];
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to update room'];
                    }
                    break;

                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ? AND hotel_id = ?");
                    $result = $stmt->execute([$_POST['room_id'], $hotel_id]);
                    
                    if ($result) {
                        $response = ['success' => true, 'message' => 'Room deleted successfully'];
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to delete room'];
                    }
                    break;
            }
        } catch (PDOException $e) {
            $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Get all rooms
$stmt = $conn->prepare("SELECT * FROM rooms WHERE hotel_id = ? ORDER BY id DESC");
$stmt->execute([$hotel_id]);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-section">
    <div class="section-header">
        <h2>Manage Rooms</h2>
        <button class="btn" onclick="showAddRoomModal()">
            <i class="fas fa-plus"></i> Add New Room
        </button>
    </div>

    <div class="rooms-grid">
        <?php foreach ($rooms as $room): ?>
            <div class="room-card">
                <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>">
                <div class="room-card-content">
                    <h3><?php echo htmlspecialchars($room['name']); ?></h3>
                    <p class="room-description">
                        <?php echo htmlspecialchars($room['description']); ?>
                    </p>
                    <div class="room-details">
                        <span class="room-price">$<?php echo number_format($room['price'], 2); ?></span>
                        <span class="room-capacity"><i class="fas fa-user"></i> <?php echo isset($room['capacity']) ? $room['capacity'] : 2; ?> Guests</span>
                        <span class="status-badge <?php echo $room['status']; ?>">
                            <?php echo ucfirst($room['status']); ?>
                        </span>
                    </div>
                    <div class="room-actions">
                        <button class="btn" onclick="editRoom(<?php echo htmlspecialchars(json_encode($room)); ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger" onclick="deleteRoom(<?php echo $room['id']; ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add/Edit Room Modal -->
<div id="roomModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <div class="modal-header">
            <h3 id="modalTitle">Add New Room</h3>
        </div>
        <form id="roomForm" onsubmit="handleRoomSubmit(event)">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="room_id" value="">
            
            <div class="form-group">
                <label>Room Name</label>
                <input type="text" name="name" required placeholder="Enter room name">
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required placeholder="Enter room description" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label>Image URL</label>
                <input type="text" name="image" required placeholder="Enter image URL">
                <small class="form-hint">Enter a valid image URL (e.g., https://example.com/image.jpg)</small>
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Price per Night ($)</label>
                    <input type="number" name="price" required min="0" step="0.01" placeholder="0.00">
                </div>
                
                <div class="form-group half">
                    <label>Capacity (Guests)</label>
                    <input type="number" name="capacity" required min="1" max="10" value="2" placeholder="2">
                    <small class="form-hint">Maximum number of guests that can stay in this room</small>
                </div>
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="available">Available</option>
                    <option value="booked">Booked</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Room</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Section Layout */
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

.section-header .btn {
    background-color: #ad8b3a;
    color: white;
    transition: all 0.3s;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-header .btn:hover {
    background-color: #8a6e2e;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.section-header .btn i {
    font-size: 14px;
}

/* Rooms Grid */
.rooms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 30px;
}

/* Room Card */
.room-card {
    background-color: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: all 0.3s;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.room-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 20px rgba(0,0,0,0.1);
}

.room-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-bottom: 3px solid #ad8b3a;
}

.room-card-content {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.room-card h3 {
    margin: 0 0 10px 0;
    font-size: 20px;
    color: #333;
}

.room-description {
    color: #666;
    margin-bottom: 15px;
    font-size: 14px;
    line-height: 1.5;
    flex-grow: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.room-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.room-price {
    font-weight: 600;
    font-size: 18px;
    color: #ad8b3a;
}

.room-capacity {
    font-weight: 500;
    font-size: 14px;
    color: #666;
}

.room-capacity i {
    margin-right: 5px;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.available {
    background-color: #d1e7dd;
    color: #198754;
}

.status-badge.booked {
    background-color: #f8d7da;
    color: #dc3545;
}

.room-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: auto;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

.room-actions .btn {
    padding: 8px 16px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.room-actions .btn i {
    font-size: 12px;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    backdrop-filter: blur(5px);
}

.modal-content {
    background: white;
    width: 90%;
    max-width: 600px;
    margin: 50px auto;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    position: relative;
    animation: modalSlideIn 0.3s ease-out;
}

.modal-close {
    position: absolute;
    right: 20px;
    top: 20px;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    transition: color 0.3s;
}

.modal-close:hover {
    color: #333;
}

.modal-header {
    padding: 20px 30px;
    border-bottom: 1px solid #eee;
    position: relative;
}

.modal-header:after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 30px;
    width: 80px;
    height: 3px;
    background-color: #ad8b3a;
}

.modal-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.5rem;
}

#roomForm {
    padding: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group.half {
    flex: 1;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #ad8b3a;
    box-shadow: 0 0 0 3px rgba(173, 139, 58, 0.1);
    outline: none;
}

.form-hint {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background: #ad8b3a;
    color: white;
}

.btn-primary:hover {
    background: #8a6e2e;
}

.btn-secondary {
    background: #f5f5f5;
    color: #333;
}

.btn-secondary:hover {
    background: #e5e5e5;
}

/* Delete Room Confirmation Modal */
#deleteConfirmModal .modal-body {
    padding: 30px;
    text-align: center;
}

#deleteConfirmModal .warning-icon {
    font-size: 48px;
    color: #dc3545;
    margin-bottom: 20px;
}

#deleteConfirmModal p {
    font-size: 16px;
    margin-bottom: 25px;
    color: #555;
}

/* Animation */
@keyframes modalSlideIn {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Responsive Design */
@media (max-width: 992px) {
    .rooms-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
}

@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .rooms-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        flex-direction: column;
        gap: 20px;
    }
}

@media (max-width: 576px) {
    .content-section {
        padding: 20px 15px;
    }
    
    .modal-content {
        margin: 20px auto;
        width: 95%;
    }
    
    #roomForm {
        padding: 20px;
    }
}
</style>

<script>
function showAddRoomModal() {
    document.getElementById('modalTitle').textContent = 'Add New Room';
    document.getElementById('roomForm').reset();
    document.querySelector('input[name="action"]').value = 'add';
    document.querySelector('input[name="room_id"]').value = '';
    document.getElementById('roomModal').style.display = 'block';
}

function editRoom(room) {
    document.getElementById('modalTitle').textContent = 'Edit Room';
    const form = document.getElementById('roomForm');
    document.querySelector('input[name="action"]').value = 'update';
    document.querySelector('input[name="room_id"]').value = room.id;
    form.querySelector('input[name="name"]').value = room.name;
    form.querySelector('textarea[name="description"]').value = room.description;
    form.querySelector('input[name="image"]').value = room.image;
    form.querySelector('input[name="price"]').value = room.price;
    form.querySelector('input[name="capacity"]').value = room.capacity;
    form.querySelector('select[name="status"]').value = room.status;
    document.getElementById('roomModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('roomModal').style.display = 'none';
}

function deleteRoom(roomId) {
    // Replace the confirm with a Bootstrap modal
    $('#deleteRoomId').val(roomId);
    $('#deleteConfirmModal').modal('show');
}

function handleRoomSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const formObject = {};
    
    // Convert FormData to a regular object
    formData.forEach((value, key) => {
        formObject[key] = value;
    });
    
    $.ajax({
        url: 'sections/rooms.php',
        type: 'POST',
        data: formObject,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                closeModal();
                showNotification(response.message, 'success');
                loadSection('rooms');
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            showNotification('Failed to submit form: ' + error, 'error');
        }
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('roomModal');
    if (event.target === modal) {
        closeModal();
    }
}

// Initialize modal confirmation handling when document is ready
$(document).ready(function() {
    // Add event listener for confirmation button
    $('#confirmDeleteBtn').on('click', function() {
        const roomId = $('#deleteRoomId').val();
        
        // Hide modal
        $('#deleteConfirmModal').modal('hide');
        
        // Process the room deletion
        $.ajax({
            url: 'sections/rooms.php',
            type: 'POST',
            data: {
                action: 'delete',
                room_id: roomId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    loadSection('rooms');
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Failed to connect to server', 'error');
            }
        });
    });
});
</script>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p>Are you sure you want to delete this room? This action cannot be undone.</p>
                <input type="hidden" id="deleteRoomId" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Room</button>
            </div>
        </div>
    </div>
</div> 