<?php
// public/add.php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isLogged() || !isOrganizer()) {
    redirect('login.php');
}

$error = '';
$success = '';
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Security Check: Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token";
    } else {
        // 2. Sanitize and Collect Input Data
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $event_date = $_POST['event_date'];
        $location = sanitize($_POST['location']);
        $capacity = (int) $_POST['capacity'];
        $category_id = (int) $_POST['category_id'];
        $organizer_id = $_SESSION['user_id'];

        // 4. Validate Required Fields (only if no previous errors)
        if (empty($error)) {
            if (empty($title) || empty($event_date) || empty($location) || $capacity <= 0) {
                $error = "Please fill in all required fields correctly.";
            } else {
                // Handle Image Upload
                $image_url = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $filename = $_FILES['image']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        $new_filename = uniqid('event_', true) . '.' . $ext;
                        $upload_dir = '../assets/uploads/';
                        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                            $image_url = 'assets/uploads/' . $new_filename;
                        }
                    } else {
                        $error = "Invalid file type. Allowed: jpg, jpeg, png, gif, webp";
                    }
                }

                if (empty($error)) {
                    // 5. Insert Event into Database
                    $stmt = $pdo->prepare("INSERT INTO events (organizer_id, title, description, event_date, location, capacity, category_id, image_url) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
                    if ($stmt->execute([$organizer_id, $title, $description, $event_date, $location, $capacity, $category_id, $image_url])) {
                        $success = "Event created successfully!";
                    } else {
                        $error = "Failed to create event.";
                    }
                }
            }
        }
    }
}
?>

<div class="form-container">
    <h1>Create New Event</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="main-form" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Event Title *</label>
                <input type="text" name="title" required class="form-control">
            </div>
            <div class="form-group">
                <label>Cover Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo sanitize($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date & Time *</label>
                <input type="datetime-local" name="event_date" required class="form-control">
            </div>
            <div class="form-group">
                <label>Location *</label>
                <input type="text" name="location" required class="form-control">
            </div>
            <div class="form-group">
                <label>Capacity *</label>
                <input type="number" name="capacity" min="1" required class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="5" class="form-control"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Event</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>