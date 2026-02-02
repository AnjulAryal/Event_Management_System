<?php
// public/view.php
require_once '../config/db.php';
require_once '../includes/header.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT e.*, c.name as category_name, u.name as organizer_name 
                      FROM events e 
                      LEFT JOIN categories c ON e.category_id = c.id 
                      JOIN users u ON e.organizer_id = u.id 
                      WHERE e.id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    redirect('index.php');
}

$is_registered = false;
if (isLogged()) {
    $rstmt = $pdo->prepare("SELECT id FROM registrations WHERE event_id = ? AND attendee_id = ?");
    $rstmt->execute([$id, $_SESSION['user_id']]);
    $is_registered = (bool) $rstmt->fetch();
}

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
$count_stmt->execute([$id]);
$registrations_count = $count_stmt->fetchColumn();
$available_slots = $event['capacity'] - $registrations_count;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLogged() && !$is_registered && $available_slots > 0) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $stmt = $pdo->prepare("INSERT INTO registrations (event_id, attendee_id) VALUES (?, ?)");
        if ($stmt->execute([$id, $_SESSION['user_id']])) {
            $is_registered = true;
            $available_slots--;
            $success = "Successfully booked event: " . sanitize($event['title']);
        }
    } else {
        $error = "Invalid security token.";
    }
}?>

<div class="event-detail-container">
    <div class="event-detail-header">
        <a href="index.php" class="back-link">← Back to Events</a>
        <?php if ($event['image_url']): ?>
            <img src="../<?php echo sanitize($event['image_url']); ?>" alt="<?php echo sanitize($event['title']); ?>"
                class="detail-img">
        <?php endif; ?>
    </div>

    <div class="event-detail-body">
        <div class="detail-main">
            <span class="category-tag">
                <?php echo sanitize($event['category_name'] ?? 'General'); ?>
            </span>
            <h1>
                <?php echo sanitize($event['title']); ?>
            </h1>
            <p class="organizer">Organized by: <strong>
                    <?php echo sanitize($event['organizer_name']); ?>
                </strong></p>

            <div class="description">
                <h3>Event Description</h3>
                <p>
                    <?php echo nl2br(sanitize($event['description'])); ?>
                </p>
            </div>
        </div>

        <div class="detail-sidebar">
            <div class="sidebar-info">
                <div class="info-item">
                    <span class="icon">📅</span>
                    <div>
                        <label>Date & Time</label>
                        <p>
                            <?php echo date('F d, Y \a\t H:i', strtotime($event['event_date'])); ?>
                        </p>
                    </div>
                </div>
                <div class="info-item">
                    <span class="icon">📍</span>
                    <div>
                        <label>Location</label>
                        <p>
                            <?php echo sanitize($event['location']); ?>
                        </p>
                    </div>
                </div>
                <div class="info-item">
                    <span class="icon">👥</span>
                    <div>
                        <label>Availability</label>
                        <p>
                            <?php echo $available_slots; ?> slots remaining (of
                            <?php echo $event['capacity']; ?>)
                        </p>
                    </div>
                </div>
            </div>

            <?php if (isLogged()): ?>
                <?php if ($is_registered): ?>
                    <button class="btn btn-disabled" disabled>You are booked</button>
                <?php elseif ($available_slots <= 0): ?>
                    <button class="btn btn-disabled" disabled>Sold Out</button>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <button type="submit" class="btn btn-primary">Book Event</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">Login to Book</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>