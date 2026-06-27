<?php
require_once 'config/db.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Username already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
            if ($stmt->execute([$username, $hashed])) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Something went wrong. Please try again later.";
            }
        }
    }
}

$pageTitle = 'Register';
require_once 'includes/header.php';
?>

<?php require_once 'includes/navbar.php'; ?>

<div class="main-container" style="justify-content: center; align-items: center; min-height: 80vh;">
    <div class="glass-panel animate-fade-in" style="width: 100%; max-width: 400px;">
        <h2 style="text-align: center;">Register</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <span><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <span><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
        </form>
        <p style="text-align: center; margin-top: 1.5rem;">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
