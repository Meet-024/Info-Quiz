<?php
require_once 'config/db.php';
requireRole(['admin', 'teacher']);

$is_admin = hasRole('admin');
$is_teacher = hasRole('teacher');


if (isset($_GET['delete'])) {
    $target_id = (int)$_GET['delete'];
    

    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$target_id]);
    $target_user = $stmt->fetch();
    
    if ($target_user) {
        if ($is_admin || ($is_teacher && $target_user['role'] === 'student')) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$target_id]);
        }
    }
    header("Location: manage_users.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role']) && $is_admin) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$_POST['role'], $_POST['user_id']]);
    header("Location: manage_users.php");
    exit;
}

$pageTitle = 'Manage Users';
require_once 'includes/header.php';
require_once 'includes/navbar.php';


$filter_role = isset($_GET['role']) ? $_GET['role'] : '';

if ($is_admin) {
    if ($filter_role === 'teacher') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'teacher' ORDER BY created_at DESC");
        $stmt->execute();
    } else {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    }
} else {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC");
}
$users = $stmt->fetchAll();
?>

<div class="main-container" style="justify-content: center;">
    <div class="content-area animate-fade-in" style="max-width: 1000px;">
        <h2><?php echo ($is_admin && $filter_role === 'teacher') ? 'Manage Teachers' : 'Manage Users'; ?></h2>
        <p class="card-meta">
            <?php echo $is_admin ? "You can manage all users." : "You can manage registered students."; ?>
        </p>

        <div class="glass-panel" style="margin-top: 2rem; padding: 1.5rem;">
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Registered On</th>
                            <?php if ($is_admin || $is_teacher): ?><th>Actions</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td>
                                    <?php echo ucfirst($u['role']); ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <?php if ($is_admin || $is_teacher): ?>
                                    <td>
                                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                            <?php if ($is_admin): ?>
                                                <form action="manage_users.php" method="POST" style="display: inline-block;">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <select name="role" onchange="this.form.submit()" style="padding: 0.4rem; background: rgba(0,0,0,0.3); color: white; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                                                        <option value="student" <?php if($u['role']=='student') echo 'selected'; ?>>Student</option>
                                                        <option value="teacher" <?php if($u['role']=='teacher') echo 'selected'; ?>>Teacher</option>
                                                        <option value="admin" <?php if($u['role']=='admin') echo 'selected'; ?>>Admin</option>
                                                    </select>
                                                    <input type="hidden" name="update_role" value="1">
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($is_admin || ($is_teacher && $u['role'] === 'student')): ?>
                                                <a href="manage_users.php?delete=<?php echo $u['id']; ?>" onclick="return confirm('Delete student?');" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; margin-left: <?php echo $is_admin ? '10px' : '0'; ?>;">Delete</a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">Current User</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
