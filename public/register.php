<?php
// public/register.php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';
session_start();

if (isLogged()) {
    redirect('index.php');
}

// Check if an organizer already exists
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'organizer'");
$organizerExists = $stmt->fetchColumn() > 0;
$admin_secret = "ADMIN123"; // The secret key for additional admins

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token.";
    } else {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $phone = sanitize($_POST['phone'] ?? '');
        $provided_key = $_POST['admin_key'] ?? '';

        // Determine role based on selection and key
        $requested_role = $_POST['role'] ?? 'attendee';
        $role = 'attendee';

        if ($requested_role === 'organizer') {
            if (!$organizerExists || $provided_key === $admin_secret) {
                $role = 'organizer';
            } else {
                $error = "Incorrect Secret Admin Key.";
            }
        }

        if (!$error) {
            if (empty($name) || empty($email) || empty($password) || empty($phone)) {
                $error = "All fields (including phone/contact) are required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = "Email already registered.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
                    if ($stmt->execute([$name, $email, $hashed_password, $phone, $role])) {
                        $_SESSION['user_id'] = $pdo->lastInsertId();
                        $_SESSION['name'] = $name;
                        $_SESSION['role'] = $role;
                        redirect('index.php');
                    } else {
                        $error = "Registration failed.";
                    }
                }
            }
        }
    }
}
?>
<div class="auth-page">
    <div class="auth-container">
        <h1>Create Account</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required class="form-control" placeholder="Full Name">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required class="form-control" placeholder="email@example.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required class="form-control" placeholder="Min. 8 characters">
            </div>
            <div class="form-group">
                <label>Contact Number (Phone)</label>
                <input type="text" name="phone" required class="form-control" placeholder="+977-XXXXXXXXXX">
            </div>
            <div class="form-group">
                <label>Join as</label>
                <select name="role" id="role-select" class="form-control">
                    <option value="attendee">Attendee</option>
                    <option value="organizer">Organizer</option>
                </select>
            </div>

            <div id="admin-key-section" class="form-group" style="display:none;">
                <label>Secret Admin Key</label>
                <input type="password" name="admin_key" class="form-control"
                    placeholder="Enter key for organizer registration">
                <?php if ($organizerExists): ?>
                    <p class="info-text">Note: An organizer already exists. A secret key is required to register as another
                        organizer.</p>
                <?php endif; ?>
            </div>

            <script>
                document.getElementById('role-select').addEventListener('change', function() {
                    const keySection = document.getElementById('admin-key-section');
                    const organizerExists = <?php echo $organizerExists ? 'true' : 'false'; ?>;
                    if (this.value === 'organizer' && organizerExists) {
                        keySection.style.display = 'block';
                    } else {
                        keySection.style.display = 'none';
                    }
                });
            </script>
            <button type="submit" class="btn btn-primary">Register Now</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>
<?php require_once '../includes/footer.php';?>