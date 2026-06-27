<?php
require_once 'config/db.php';
requireRole(['admin', 'teacher']);

$is_admin = hasRole('admin');


if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM information WHERE id = ? " . ($is_admin ? "" : "AND created_by = ?"));
    if ($is_admin) $stmt->execute([$_GET['delete']]);
    else $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
    header("Location: manage_info.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_topic'])) {
    $stmt = $pdo->prepare("INSERT INTO topics (title, description, created_by) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['title'], $_POST['description'], $_SESSION['user_id']]);
    header("Location: manage_info.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_info'])) {
    $stmt = $pdo->prepare("INSERT INTO information (topic_id, title, content, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['topic_id'], $_POST['title'], $_POST['content'], $_SESSION['user_id']]);
    header("Location: manage_info.php");
    exit;
}

$pageTitle = 'Manage Information';
require_once 'includes/header.php';
require_once 'includes/navbar.php';


$topics = $pdo->query("SELECT * FROM topics")->fetchAll();


$query = "SELECT i.*, t.title as topic_title FROM information i LEFT JOIN topics t ON i.topic_id = t.id";
if (!$is_admin) {
    $query .= " WHERE i.created_by = " . (int)$_SESSION['user_id'];
}
$query .= " ORDER BY i.created_at DESC";
$info = $pdo->query($query)->fetchAll();
?>

<div class="main-container">
    <div class="sidebar" style="width: 350px;">
        <div class="glass-panel" style="margin-bottom: 1.5rem;">
            <h3>Add Topic</h3>
            <form action="manage_info.php" method="POST">
                <div class="form-group">
                    <input type="text" name="title" class="form-control" placeholder="Topic Title" required>
                </div>
                <div class="form-group">
                    <textarea name="description" class="form-control" placeholder="Topic Description" rows="2"></textarea>
                </div>
                <button type="submit" name="add_topic" class="btn btn-primary" style="width: 100%;">Create Topic</button>
            </form>
        </div>

        <div class="glass-panel">
            <h3>Add Information</h3>
            <form action="manage_info.php" method="POST">
                <div class="form-group">
                    <select name="topic_id" class="form-control" required>
                        <option value="">Select Topic</option>
                        <?php foreach ($topics as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="title" class="form-control" placeholder="Information Title" required>
                </div>
                <div class="form-group">
                    <textarea name="content" class="form-control" placeholder="Information Content" rows="6" required></textarea>
                </div>
                <button type="submit" name="add_info" class="btn btn-primary" style="width: 100%;">Post Information</button>
            </form>
        </div>
    </div>

    <div class="content-area animate-fade-in">
        <h2>Manage Information</h2>
        
        <div class="glass-panel" style="padding: 1.5rem;">
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Topic</th>
                            <th>Title</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($info as $i): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($i['topic_title'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($i['title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($i['created_at'])); ?></td>
                                <td>
                                    <a href="manage_info.php?delete=<?php echo $i['id']; ?>" onclick="return confirm('Delete information?');" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
