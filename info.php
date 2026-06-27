<?php
require_once 'config/db.php';
$pageTitle = 'Information';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$topic_id = isset($_GET['topic']) ? (int)$_GET['topic'] : 0;
$info_items = [];
$selected_topic = null;

// Fetch all topics for the directory view or quick-swapper tabs
$all_topics = $pdo->query("SELECT * FROM topics ORDER BY title ASC")->fetchAll();

if ($topic_id > 0) {
    // Fetch selected topic details
    $stmt = $pdo->prepare("SELECT * FROM topics WHERE id = ?");
    $stmt->execute([$topic_id]);
    $selected_topic = $stmt->fetch();

    if ($selected_topic) {
        $query = "SELECT i.*, t.title as topic_title, u.username as author FROM information i 
                  LEFT JOIN topics t ON i.topic_id = t.id 
                  LEFT JOIN users u ON i.created_by = u.id
                  WHERE i.topic_id = " . $topic_id . "
                  ORDER BY i.created_at DESC";
        $stmt = $pdo->query($query);
        $info_items = $stmt->fetchAll();
    }
}
?>

<div class="main-container" style="justify-content: center; max-width: 1200px;">
    <div class="content-area animate-fade-in" style="width: 100%;">
        
        <?php if ($topic_id == 0): ?>
            <!-- Topics Directory View (No Sidebar) -->
            <div style="margin-bottom: 2rem; text-align: center;">
                <h2 style="font-size: 2.2rem; color: var(--gold-primary); margin-bottom: 0.5rem;">Learning Hub</h2>
                <p style="color: var(--text-muted); font-size: 1.1rem;">Select a topic below to dive into detailed articles and expand your knowledge.</p>
            </div>

            <?php if (count($all_topics) > 0): ?>
                <div class="card-grid" style="margin-top: 2rem;">
                    <?php foreach ($all_topics as $topic): ?>
                        <a href="info.php?topic=<?php echo $topic['id']; ?>" class="card" style="text-decoration: none; color: inherit; transition: transform var(--transition-fast), border-color var(--transition-fast);">
                            <h4 style="color: var(--gold-secondary); margin-bottom: 0.75rem; font-size: 1.2rem;"><?php echo htmlspecialchars($topic['title']); ?></h4>
                            <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; margin-bottom: 0;">
                                <?php echo htmlspecialchars($topic['description']); ?>
                            </p>
                            <span style="display: inline-block; margin-top: 1.5rem; font-size: 0.85rem; color: var(--gold-primary); font-weight: 600;">
                                Open Topic <i class="fas fa-chevron-right" style="margin-left: 5px; font-size: 0.75rem;"></i>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-muted);">No learning topics available at the moment.</p>
            <?php endif; ?>

        <?php else: ?>
            <!-- Topic Detail View (No Sidebar) -->
            <?php if ($selected_topic): ?>
                <!-- Navigation Sub-header / Back Button & Dropdown Selector -->
                <div style="margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <a href="info.php" class="btn btn-outline" style="padding: 0.5rem 1.25rem; font-size: 0.9rem;">
                        <i class="fas fa-arrow-left"></i> Back to Topics
                    </a>
                    
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="color: var(--text-muted); font-size: 0.9rem;"><i class="fas fa-exchange-alt"></i> Switch Topic:</span>
                        <select onchange="location = this.value;" style="background: rgba(0,0,0,0.4); color: var(--gold-secondary); border: 1px solid var(--gold-primary); padding: 0.5rem 2.2rem 0.5rem 1rem; border-radius: var(--radius-sm); font-size: 0.9rem; cursor: pointer; outline: none; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23d4af37%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.9%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.7rem top 50%; background-size: 0.65rem auto;">
                            <?php foreach ($all_topics as $ot): ?>
                                <option value="info.php?topic=<?php echo $ot['id']; ?>" <?php echo $ot['id'] == $topic_id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ot['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="glass-panel" style="margin-bottom: 2rem; border-left: 5px solid var(--gold-primary);">
                    <h2 style="color: var(--gold-secondary); margin-bottom: 0.5rem; font-size: 1.8rem;"><?php echo htmlspecialchars($selected_topic['title']); ?></h2>
                    <p style="color: var(--text-muted); font-size: 1rem; margin: 0; line-height: 1.5;"><?php echo htmlspecialchars($selected_topic['description']); ?></p>
                </div>

                <?php if (count($info_items) > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 2rem;">
                        <?php foreach ($info_items as $item): ?>
                            <div class="glass-panel">
                                <h3 style="color: var(--gold-primary); margin-bottom: 1.5rem; font-size: 1.4rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h3>
                                <?php if (hasRole('admin')): ?>
                                    <p style="margin-bottom: 1.5rem; color: var(--text-muted); font-size: 0.9rem; margin-top: -1rem;">
                                        Published: <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                                    </p>
                                <?php endif; ?>
                                <div class="article-content">
                                    <?php echo nl2br(htmlspecialchars($item['content'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger" style="margin-top: 2rem;">
                        <span><i class="fas fa-exclamation-circle"></i> No learning articles found for this topic.</span>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-danger" style="margin-top: 2rem;">
                    <span><i class="fas fa-exclamation-circle"></i> Topic not found. <a href="info.php" style="color: var(--gold-secondary); text-decoration: underline;">Return to Topics</a></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
