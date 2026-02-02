<?php
// public/search.php
require_once '../config/db.php';
require_once '../includes/functions.php';

$query = sanitize($_GET['q'] ?? '');
$category = (int) ($_GET['category'] ?? 0);

$sql = "SELECT e.*, c.name as category_name, u.name as organizer_name 
        FROM events e 
        LEFT JOIN categories c ON e.category_id = c.id 
        JOIN users u ON e.organizer_id = u.id 
        WHERE e.status = 'approved'";

$params = [];

if (!empty($query)) {
    $sql .= " AND (e.title LIKE ? OR e.location LIKE ? OR c.name LIKE ?)";
    $q = "%$query%";
    $params = array_merge($params, [$q, $q, $q]);
}

if ($category > 0) {
    $sql .= " AND e.category_id = ?";
    $params[] = $category;
}

$sql .= " ORDER BY e.event_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

// Header is not needed as this will be called via Ajax
if (isset($_GET['ajax'])): ?>
    <?php if (empty($events)): ?>
        <p class="no-events">No events found matching your search.</p>
    <?php else: ?>
        <?php foreach ($events as $event): ?>
            <div class="event-card">
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
<?php endif; ?>