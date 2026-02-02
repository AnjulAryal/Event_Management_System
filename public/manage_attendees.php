<?php
// public/manage_attendees.php

require_once '../config/db.php';
require_once '../includes/header.php';

if (!isLogged() || !isOrganizer()) {
    redirect('login.php');
}

$organizer_id = $_SESSION['user_id'];

// Get all events organized by this user
$stmt = $pdo->prepare("SELECT id, title FROM events WHERE organizer_id = ? ORDER BY created_at DESC");
$stmt->execute([$organizer_id]);
$my_events = $stmt->fetchAll();

$selected_event_id = (int) ($_GET['event_id'] ?? ($my_events[0]['id'] ?? 0));

$attendees = [];
if ($selected_event_id > 0) {
    // Verify ownership
    $check = $pdo->prepare("SELECT id FROM events WHERE id = ? AND organizer_id = ?");
    $check->execute([$selected_event_id, $organizer_id]);
    if ($check->fetch()) {
        $stmt = $pdo->prepare("SELECT u.name, u.email, u.phone, r.registration_date 
                              FROM registrations r 
                              JOIN users u ON r.attendee_id = u.id 
                              WHERE r.event_id = ? 
                              ORDER BY r.registration_date DESC");
        $stmt->execute([$selected_event_id]);
        $attendees = $stmt->fetchAll();
    }
}
?>

<div class="manage-container">
    <div class="dashboard-header">
        <h1>Attendee Management</h1>
        <div class="search-bar">
            <form method="GET" action="">
                <select name="event_id" class="form-control" onchange="this.form.submit()">
                    <?php foreach ($my_events as $ev): ?>
                        <option value="<?php echo $ev['id']; ?>" <?php echo $ev['id'] == $selected_event_id ? 'selected' : ''; ?>>
                            <?php echo sanitize($ev['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="attendee-list-container glass-card">
        <?php if (empty($attendees)): ?>
            <div class="no-attendees">
                <p>No attendees registered for this event yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="attendee-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact (Phone)</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendees as $att): ?>
                            <tr>
                                <td class="att-name">
                                    <?php echo sanitize($att['name']); ?>
                                </td>
                                <td class="att-email">
                                    <?php echo sanitize($att['email']); ?>
                                </td>
                                <td class="att-phone">
                                    <?php echo sanitize($att['phone'] ?? 'N/A'); ?>
                                </td>
                                <td class="att-date">
                                    <?php echo date('M d, Y H:i', strtotime($att['registration_date'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
