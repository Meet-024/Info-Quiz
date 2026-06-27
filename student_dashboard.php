<?php
require_once 'config/db.php';
requireRole('student');

$pageTitle = 'Student Dashboard';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$view = isset($_GET['view']) ? $_GET['view'] : (isset($_GET['topic']) ? 'info' : 'overview');
$user_id = $_SESSION['user_id'];

if ($view === 'results') {
    $stmt = $pdo->prepare("SELECT r.*, q.title as quiz_title, t.title as topic_title 
                           FROM quiz_results r
                           JOIN quizzes q ON r.quiz_id = q.id
                           LEFT JOIN topics t ON q.topic_id = t.id
                           WHERE r.user_id = ? 
                           ORDER BY r.created_at DESC");
    $stmt->execute([$user_id]);
    $results = $stmt->fetchAll();
} elseif ($view === 'info') {
    $topic_id = isset($_GET['topic']) ? (int)$_GET['topic'] : 0;
    $selected_topic = null;
    $info_items = [];
    if ($topic_id > 0) {
        // Fetch topic details
        $stmt = $pdo->prepare("SELECT * FROM topics WHERE id = ?");
        $stmt->execute([$topic_id]);
        $selected_topic = $stmt->fetch();

        if ($selected_topic) {
            // Fetch articles for this topic
            $stmt = $pdo->prepare("SELECT i.*, u.username as author FROM information i 
                                   LEFT JOIN users u ON i.created_by = u.id
                                   WHERE i.topic_id = ?
                                   ORDER BY i.created_at DESC");
            $stmt->execute([$topic_id]);
            $info_items = $stmt->fetchAll();
        }
    }
} elseif ($view === 'overview') {
    // Overview Stats
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_results WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_quizzes = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT AVG(score/total_questions * 100) as avg_score, MAX(score/total_questions * 100) as max_score FROM quiz_results WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $scores = $stmt->fetch();
    $avg_score = $scores['avg_score'] ? round($scores['avg_score'], 1) : 0;
    $max_score = $scores['max_score'] ? round($scores['max_score'], 1) : 0;

    // Fetch All Topics
    $all_topics = $pdo->query("SELECT * FROM topics ORDER BY title ASC")->fetchAll();

    // Fetch All Quizzes with question count and student's personal best score
    $stmt = $pdo->prepare("SELECT q.*, t.title as topic_title, 
                          (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count,
                          (SELECT MAX(score/total_questions * 100) FROM quiz_results WHERE user_id = ? AND quiz_id = q.id) as best_score
                          FROM quizzes q 
                          LEFT JOIN topics t ON q.topic_id = t.id 
                          ORDER BY q.title ASC");
    $stmt->execute([$user_id]);
    $all_quizzes = $stmt->fetchAll();

    // Recent attempts log
    $stmt = $pdo->prepare("SELECT r.*, q.title as quiz_title, t.title as topic_title 
                           FROM quiz_results r
                           JOIN quizzes q ON r.quiz_id = q.id
                           LEFT JOIN topics t ON q.topic_id = t.id
                           WHERE r.user_id = ? 
                           ORDER BY r.created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_attempts = $stmt->fetchAll();
}
?>

<div class="main-container" style="justify-content: center; max-width: 1200px;">
    <div class="content-area animate-fade-in" style="width: 100%;">
        <?php if ($view === 'overview'): ?>
            <!-- Personalized Greeting Banner -->
            <div class="glass-panel" style="padding: 2rem; margin-bottom: 2rem; background: linear-gradient(135deg, rgba(212, 175, 55, 0.07) 0%, rgba(21, 21, 21, 0.8) 100%); position: relative; overflow: hidden; border-left: 5px solid var(--gold-primary);">
                <div style="position: absolute; right: -20px; bottom: -20px; font-size: 8rem; color: rgba(212, 175, 55, 0.03); transform: rotate(-15deg); pointer-events: none;">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h2 style="font-size: 2.2rem; margin-bottom: 0.5rem; color: var(--gold-secondary);">Student Dashboard</h2>
                <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 0;">
                    <?php
                    $hour = date('H');
                    $greeting = 'Welcome back';
                    if ($hour < 12) $greeting = 'Good morning';
                    elseif ($hour < 18) $greeting = 'Good afternoon';
                    else $greeting = 'Good evening';
                    echo $greeting;
                    ?>, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! Ready to test your knowledge today?
                </p>
            </div>

            <div class="card-grid" style="margin-bottom: 3rem;">
                <!-- Quizzes Taken Card -->
                <div class="stat-card" style="display: flex; align-items: center; justify-content: space-between; text-align: left; padding: 1.5rem 2rem;">
                    <div>
                        <h3 style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">Quizzes Taken</h3>
                        <p class="stat-value" style="margin: 0; font-size: 2.2rem; color: var(--gold-secondary);"><?php echo $total_quizzes; ?></p>
                        <span style="font-size: 0.75rem; color: var(--success);"><i class="fas fa-check-circle"></i> Keep it up!</span>
                    </div>
                    <div style="font-size: 2.5rem; color: rgba(212, 175, 55, 0.15);"><i class="fas fa-tasks"></i></div>
                </div>

                <!-- Average Score Card -->
                <div class="stat-card" style="display: flex; align-items: center; justify-content: space-between; text-align: left; padding: 1.5rem 2rem;">
                    <div>
                        <h3 style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">Average Score</h3>
                        <p class="stat-value" style="margin: 0; font-size: 2.2rem; color: var(--gold-secondary);"><?php echo $avg_score; ?>%</p>
                        <div style="width: 120px; height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; margin-top: 0.5rem; overflow: hidden;">
                            <div style="width: <?php echo $avg_score; ?>%; height: 100%; background: var(--gold-primary); border-radius: 2px;"></div>
                        </div>
                    </div>
                    <div style="font-size: 2.5rem; color: rgba(212, 175, 55, 0.15);"><i class="fas fa-chart-line"></i></div>
                </div>

                <!-- Highest Score Card -->
                <div class="stat-card" style="display: flex; align-items: center; justify-content: space-between; text-align: left; padding: 1.5rem 2rem;">
                    <div>
                        <h3 style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">Highest Score</h3>
                        <p class="stat-value" style="margin: 0; font-size: 2.2rem; color: var(--gold-secondary);"><?php echo $max_score; ?>%</p>
                        <div style="width: 120px; height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; margin-top: 0.5rem; overflow: hidden;">
                            <div style="width: <?php echo $max_score; ?>%; height: 100%; background: var(--success); border-radius: 2px;"></div>
                        </div>
                    </div>
                    <div style="font-size: 2.5rem; color: rgba(212, 175, 55, 0.15);"><i class="fas fa-trophy"></i></div>
                </div>
            </div>

            <!-- Browse Topics Cards Section -->
            <div style="margin-bottom: 3rem;">
                <h3 style="color: var(--gold-primary); margin-bottom: 1.5rem; font-size: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    <i class="fas fa-book-reader" style="margin-right: 0.5rem;"></i> Browse Learning Topics
                </h3>
                <?php if (count($all_topics) > 0): ?>
                    <div class="card-grid">
                        <?php foreach ($all_topics as $topic): ?>
                            <a href="student_dashboard.php?view=info&topic=<?php echo $topic['id']; ?>" class="card" style="text-decoration: none; color: inherit; transition: transform var(--transition-fast), border-color var(--transition-fast);">
                                <h4 style="color: var(--gold-secondary); margin-bottom: 0.75rem; font-size: 1.2rem;"><?php echo htmlspecialchars($topic['title']); ?></h4>
                                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; margin-bottom: 0;">
                                    <?php echo htmlspecialchars($topic['description']); ?>
                                </p>
                                <span style="display: inline-block; margin-top: 1.5rem; font-size: 0.85rem; color: var(--gold-primary); font-weight: 600;">
                                    Start Learning <i class="fas fa-chevron-right" style="margin-left: 5px; font-size: 0.75rem;"></i>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No learning topics available at the moment.</p>
                <?php endif; ?>
            </div>

            <!-- Browse Quizzes Cards Section -->
            <div style="margin-bottom: 3rem;">
                <h3 style="color: var(--gold-primary); margin-bottom: 1.5rem; font-size: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    <i class="fas fa-tasks" style="margin-right: 0.5rem;"></i> Available Quizzes
                </h3>
                <?php if (count($all_quizzes) > 0): ?>
                    <div class="card-grid">
                        <?php foreach ($all_quizzes as $quiz): ?>
                            <div class="card" style="justify-content: space-between; min-height: 220px;">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; gap: 10px;">
                                        <span class="badge badge-student" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;"><?php echo htmlspecialchars($quiz['topic_title'] ?? 'General'); ?></span>
                                        <?php if ($quiz['best_score'] !== null): ?>
                                            <span class="badge" style="background: rgba(46, 213, 115, 0.15); color: var(--success); border: 1px solid rgba(46, 213, 115, 0.3); font-size: 0.7rem; padding: 0.2rem 0.5rem;">Best: <?php echo round($quiz['best_score']); ?>%</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: rgba(255, 255, 255, 0.05); color: var(--text-muted); border: 1px solid rgba(255,255,255,0.1); font-size: 0.7rem; padding: 0.2rem 0.5rem;">Not Attempted</span>
                                        <?php endif; ?>
                                    </div>
                                    <h4 style="color: var(--text-main); margin-bottom: 0.5rem; font-size: 1.25rem;"><?php echo htmlspecialchars($quiz['title']); ?></h4>
                                    <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.4; margin-bottom: 1.5rem;">
                                        <?php echo htmlspecialchars($quiz['description']); ?>
                                    </p>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.85rem; color: var(--text-muted);"><i class="fas fa-file-alt"></i> <?php echo $quiz['question_count']; ?> Questions</span>
                                    <?php if ($quiz['question_count'] > 0): ?>
                                        <a href="quiz_take.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Attempt Quiz</a>
                                    <?php else: ?>
                                        <span class="badge badge-admin" style="font-size: 0.7rem;">Empty</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-muted);">No quizzes available at the moment.</p>
                <?php endif; ?>
            </div>

            <!-- Recent Quiz Attempts Table -->
            <div class="glass-panel" style="margin-top: 2rem;">
                <h3 style="color: var(--gold-secondary); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1.5rem; font-size: 1.2rem;">
                    <i class="fas fa-history" style="margin-right: 0.5rem;"></i> My Recent Quiz Attempts
                </h3>
                <?php if (count($recent_attempts) > 0): ?>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date Taken</th>
                                    <th>Topic</th>
                                    <th>Quiz Title</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_attempts as $attempt): ?>
                                    <?php 
                                        $pct = ($attempt['score'] / $attempt['total_questions']) * 100;
                                        $color = $pct >= 70 ? 'var(--success)' : 'var(--danger)';
                                    ?>
                                    <tr>
                                        <td><?php echo date('M d, Y h:i A', strtotime($attempt['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($attempt['topic_title'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                                        <td><strong><?php echo $attempt['score'] . ' / ' . $attempt['total_questions']; ?></strong></td>
                                        <td style="color: <?php echo $color; ?>; font-weight: bold;">
                                            <?php echo round($pct, 1); ?>%
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 1.5rem; text-align: right;">
                        <a href="student_dashboard.php?view=results" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">View All Results</a>
                    </div>
                <?php else: ?>
                    <div style="padding: 1.5rem; text-align: center; color: var(--text-muted);">
                        You haven't attempted any quizzes yet.
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($view === 'results'): ?>
            <h2 style="color: var(--gold-primary); margin-bottom: 1rem;">My Quiz Results</h2>
            <div class="glass-panel" style="margin-top: 2rem; padding: 1.5rem;">
                <?php if (count($results) > 0): ?>
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date Taken</th>
                                    <th>Topic</th>
                                    <th>Quiz Title</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $r): ?>
                                    <?php 
                                        $percentage = ($r['score'] / $r['total_questions']) * 100;
                                        $color = $percentage >= 70 ? 'var(--success)' : 'var(--danger)';
                                    ?>
                                    <tr>
                                        <td><?php echo date('M d, Y h:i A', strtotime($r['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($r['topic_title'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($r['quiz_title']); ?></td>
                                        <td><strong><?php echo $r['score'] . ' / ' . $r['total_questions']; ?></strong></td>
                                        <td style="color: <?php echo $color; ?>; font-weight: bold;">
                                            <?php echo round($percentage, 1); ?>%
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="padding: 2rem; text-align: center;">
                        <p style="color: var(--text-muted); margin-bottom: 1rem;">You haven't taken any quizzes yet.</p>
                        <a href="student_dashboard.php" class="btn btn-primary">Browse Quizzes & Topics</a>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($view === 'info'): ?>
            <?php if ($selected_topic): ?>
                <!-- Navigation Sub-header / Back Button -->
                <div style="margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <a href="student_dashboard.php" class="btn btn-outline" style="padding: 0.5rem 1.25rem; font-size: 0.9rem;">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    
                    <!-- Quick Topic Swapper Tabs -->
                    <?php
                    $other_topics = $pdo->query("SELECT id, title FROM topics ORDER BY title ASC")->fetchAll();
                    if (count($other_topics) > 1):
                    ?>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; background: rgba(0,0,0,0.2); padding: 0.3rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                            <?php foreach ($other_topics as $ot): ?>
                                <a href="student_dashboard.php?view=info&topic=<?php echo $ot['id']; ?>" 
                                   class="btn" 
                                   style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background: <?php echo $ot['id'] == $topic_id ? 'var(--gold-primary)' : 'transparent'; ?>; color: <?php echo $ot['id'] == $topic_id ? 'var(--bg-dark)' : 'var(--text-main)'; ?>; border-radius: 4px;">
                                    <?php echo htmlspecialchars($ot['title']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
                    <span><i class="fas fa-exclamation-circle"></i> Topic not found. <a href="student_dashboard.php" style="color: var(--gold-secondary); text-decoration: underline;">Return to Dashboard</a></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
