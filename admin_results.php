<?php
require_once 'config/db.php';
requireRole(['admin', 'teacher']);

$pageTitle = 'Quiz Results';
require_once 'includes/header.php';
require_once 'includes/navbar.php';


$stmt = $pdo->query("SELECT r.*, u.username, q.title as quiz_title, t.title as topic_title 
                       FROM quiz_results r
                       JOIN users u ON r.user_id = u.id
                       JOIN quizzes q ON r.quiz_id = q.id
                       LEFT JOIN topics t ON q.topic_id = t.id
                       ORDER BY r.created_at DESC");
$results = $stmt->fetchAll();
?>

<div class="main-container" style="justify-content: center;">
    <div class="content-area animate-fade-in" style="max-width: 1000px;">
        <h2>Quiz Results</h2>
        <p class="card-meta">Overview of all student quiz performances.</p>
        
        <div class="glass-panel" style="margin-top: 2rem; padding: 1.5rem;">
            <?php if (count($results) > 0): ?>
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Topic</th>
                                <th>Quiz Title</th>
                                <th>Score</th>
                                <th>Percentage</th>
                                <th>Date Taken</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $r): ?>
                                <?php 
                                    $percentage = ($r['score'] / $r['total_questions']) * 100;
                                    $color = $percentage >= 70 ? 'var(--success)' : 'var(--danger)';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['username']); ?></td>
                                    <td><?php echo htmlspecialchars($r['topic_title'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($r['quiz_title']); ?></td>
                                    <td><strong><?php echo $r['score'] . ' / ' . $r['total_questions']; ?></strong></td>
                                    <td style="color: <?php echo $color; ?>; font-weight: bold;">
                                        <?php echo round($percentage, 1); ?>%
                                    </td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($r['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <span><i class="fas fa-exclamation-circle"></i> No quiz results found.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
