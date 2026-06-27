<?php
require_once 'config/db.php';


if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$pageTitle = 'Quizzes';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$topic_id = isset($_GET['topic']) ? (int)$_GET['topic'] : 0;

$query = "SELECT q.*, t.title as topic_title, u.username as author, 
          (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
          FROM quizzes q 
          LEFT JOIN topics t ON q.topic_id = t.id 
          LEFT JOIN users u ON q.created_by = u.id";
if ($topic_id > 0) {
    $query .= " WHERE q.topic_id = " . $topic_id;
}
$query .= " ORDER BY q.created_at DESC";

$stmt = $pdo->query($query);
$quizzes = $stmt->fetchAll();
?>

<div class="main-container" style="justify-content: center;">
    <div class="content-area animate-fade-in" style="width: 100%;">
        <h2>Available Quizzes</h2>
        <p class="card-meta mb-4">Select a topic to filter quizzes. Click "Take Quiz" to start.</p>

        <?php if (count($quizzes) > 0): ?>
            <div class="card-grid">
                <?php foreach ($quizzes as $q): ?>
                    <div class="card">
                        <div class="card-meta">Topic: <?php echo htmlspecialchars($q['topic_title'] ?? 'Uncategorized'); ?></div>
                        <h3 class="card-title"><?php echo htmlspecialchars($q['title']); ?></h3>
                        <p style="margin-bottom: 1rem; color: var(--text-muted);">
                            <?php echo htmlspecialchars($q['description']); ?>
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span class="badge badge-student"><?php echo $q['question_count']; ?> Questions</span>
                            <?php if ($q['question_count'] > 0): ?>
                                <a href="quiz_take.php?id=<?php echo $q['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem;">Take Quiz</a>
                            <?php else: ?>
                                <span class="badge badge-admin">No Questions Yet</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <span><i class="fas fa-exclamation-circle"></i> No quizzes available for this topic.</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
