<?php
require_once 'config/db.php';
$pageTitle = 'Information';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$topic_id = isset($_GET['topic']) ? (int)$_GET['topic'] : 0;
$info_items = [];

if ($topic_id > 0) {
    $query = "SELECT i.*, t.title as topic_title, u.username as author FROM information i 
              LEFT JOIN topics t ON i.topic_id = t.id 
              LEFT JOIN users u ON i.created_by = u.id
              WHERE i.topic_id = " . $topic_id . "
              ORDER BY i.created_at DESC";
    $stmt = $pdo->query($query);
    $info_items = $stmt->fetchAll();
}
?>

<div class="main-container">
    <?php require_once 'includes/sidebar.php'; ?>

    <div class="content-area animate-fade-in">
        <h2>Information Hub</h2>
        <p class="card-meta mb-4">Select a topic from the sidebar to view detailed information.</p>

        <?php if ($topic_id == 0): ?>
            <div class="alert alert-success">
                <span><i class="fas fa-info-circle"></i> Please select a topic from the sidebar to begin learning.</span>
            </div>
        <?php elseif (count($info_items) > 0): ?>
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <?php foreach ($info_items as $item): ?>
                    <div class="glass-panel">
                        <div class="card-meta">Topic: <?php echo htmlspecialchars($item['topic_title'] ?? 'Uncategorized'); ?></div>
                        <h3 style="color: var(--gold-primary); margin-bottom: 1rem;"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p style="margin-bottom: 1.5rem; color: var(--text-muted); font-size: 0.9rem;">
                            Published: <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                        </p>
                        <div class="article-content">
                            <?php echo nl2br(htmlspecialchars($item['content'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <span><i class="fas fa-exclamation-circle"></i> No information articles found for this topic.</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
