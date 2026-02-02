<?php
// public/index.php
require_once '../config/db.php';
require_once '../includes/header.php';

$stmt = $pdo->query("SELECT e.*, c.name as category_name, u.name as organizer_name 
                    FROM events e 
                    LEFT JOIN categories c ON e.category_id = c.id 
                    JOIN users u ON e.organizer_id = u.id 
                    WHERE e.status = 'approved' 
                    ORDER BY e.event_date ASC");
$events = $stmt->fetchAll();
?>
  <?php if (isLogged()): ?>
     <span class="user-greeting">Hi, <?php echo sanitize($_SESSION['name']); ?> 👋</span>
    <?php endif; ?>
<div class="dashboard-header">
    <h1>Upcoming Events</h1>
    <div class="search-bar">
        <input type="text" id="live-search" placeholder="Search events by title, location or category..."
            class="form-control">
    </div>
</div>

<div class="event-grid" id="event-container">
    <?php if (empty($events)): ?>
        <p class="no-events">No upcoming events found.</p>
    <?php else: ?>
        <?php foreach ($events as $event): ?>
            <div class="event-card">
                <?php if (isLogged() && isOrganizer() && $event['organizer_id'] == $_SESSION['user_id']): ?>
                    <div class="admin-actions" style="display: flex; gap: 0.5rem; position: absolute; top: 1rem; right: 1rem; z-index: 10;">
                        <a href="edit.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <form action="delete.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                            <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="event-img-box">
                    <?php if ($event['image_url']): ?>
                        <img src="../<?php echo sanitize($event['image_url']); ?>" alt="<?php echo sanitize($event['title']); ?>">
                    <?php else: ?>
                        <div class="event-img-placeholder">📅</div>
                    <?php endif; ?>
                </div>

                <div class="event-body">
                    <span class="category-tag">
                        <?php echo sanitize($event['category_name'] ?? 'General'); ?>
                    </span>
                    <h3><?php echo sanitize($event['title']); ?></h3>

                    <div class="event-meta">
                        <span>🕒 <?php echo date('M d, Y @ H:i', strtotime($event['event_date'])); ?></span>
                        <span>📍 <?php echo sanitize($event['location']); ?></span>
                    </div>

                    <div class="event-footer">
                        <a href="view.php?id=<?php echo $event['id']; ?>" class="btn btn-view">Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>