<?php
// public/edit.php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isLogged() || !isOrganizer()) {
    redirect('login.php');
}

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND organizer_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$event = $stmt->fetch();

if (!$event) {
    redirect('index.php');
}

$error = '';
$success = '';
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Security: Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token";
    } else {
        // Collect Inputs
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $event_date = $_POST['event_date'];
        $location = sanitize($_POST['location']);
        $capacity = (int) $_POST['capacity'];
        $category_id = (int) $_POST['category_id'];

        // 3. Update Database
        if (empty($error)) {

            if (empty($title) || empty($event_date) || empty($location) || $capacity <= 0) {
                $error = "Please fill in all required fields correctly.";
            } else {
                // Handle Image Upload
                $image_url = $event['image_url']; // Keep existing by default
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
                    // Update query includes image_url
                    $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, capacity = ?, category_id = ?, image_url = ? 
                                          WHERE id = ? AND organizer_id = ?");

                    if ($stmt->execute([$title, $description, $event_date, $location, $capacity, $category_id, $image_url, $id, $_SESSION['user_id']])) {
                        $success = "Event updated successfully!";
                        $event = array_merge($event, $_POST);
                        $event['image_url'] = $image_url; 
                    } else {
                        $error = "Failed to update event.";
                    }
                }
            }
        }
    }
}
?>

<div class="form-container">
    <h1>Edit Event</h1>

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
                <input type="text" name="title" value="<?php echo sanitize($event['title']); ?>" required
                    class="form-control">
            </div>
            <div class="form-group">
                <label>Cover Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <?php if (!empty($event['image_url'])): ?>
                    <small>Current image: <a href="../<?php echo $event['image_url']; ?>" target="_blank">View</a></small>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $event['category_id'] ? 'selected' : ''; ?>>
                            <?php echo sanitize($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date & Time *</label>
                <input type="datetime-local" name="event_date"
                    value="<?php echo date('Y-m-d\TH:i', strtotime($event['event_date'])); ?>" required
                    class="form-control">
            </div>
            <div class="form-group">
                <label>Location *</label>
                <input type="text" name="location" value="<?php echo sanitize($event['location']); ?>" required
                    class="form-control">
            </div>
            <div class="form-group">
                <label>Capacity *</label>
                <input type="number" name="capacity" value="<?php echo (int) $event['capacity']; ?>" min="1" required
                    class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="5"
                class="form-control"><?php echo sanitize($event['description']); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Event</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>