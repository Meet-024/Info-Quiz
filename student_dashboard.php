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

    // Recent Information
    $stmt = $pdo->query("SELECT i.*, t.title as topic_title FROM information i 
                         LEFT JOIN topics t ON i.topic_id = t.id 
                         ORDER BY i.created_at DESC LIMIT 3");
    $recent_infos = $stmt->fetchAll();

    // Recent Quizzes with personal best score for current student
    $stmt = $pdo->prepare("SELECT q.*, t.title as topic_title, 
                          (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count,
                          (SELECT MAX(score/total_questions * 100) FROM quiz_results WHERE user_id = ? AND quiz_id = q.id) as best_score
                          FROM quizzes q 
                          LEFT JOIN topics t ON q.topic_id = t.id 
                          ORDER BY q.created_at DESC LIMIT 3");
    $stmt->execute([$user_id]);
    $recent_quizzes = $stmt->fetchAll();

    // Recent attempts log
    $stmt = $pdo->prepare("SELECT r.*, q.title as quiz_title, t.title as topic_title 
                           FROM quiz_results r
                           JOIN quizzes q ON r.quiz_id = q.id
                           LEFT JOIN topics t ON q.topic_id = t.id
                           WHERE r.user_id = ? 
                           ORDER BY r.created_at DESC LIMIT 3");
    $stmt->execute([$user_id]);
    $recent_attempts = $stmt->fetchAll();
}
?>

<div class="main-container">
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="content-area animate-fade-in">
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

            <div class="card-grid" style="margin-bottom: 2rem;">
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

            <!-- Content Grid Section -->
            <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; margin-top: 2rem;">
                <!-- Recent Information Section -->
                <div class="glass-panel" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="color: var(--gold-secondary); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1.5rem; font-size: 1.2rem;">
                            <i class="fas fa-book-open" style="margin-right: 0.5rem;"></i> Recent Information
                        </h3>
                        <?php if (count($recent_infos) > 0): ?>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <?php foreach ($recent_infos as $info_item): ?>
                                    <?php 
                                        $word_count = str_word_count(strip_tags($info_item['content']));
                                        $read_time = ceil($word_count / 150); // estimation at 150 wpm
                                    ?>
                                    <div style="background: rgba(0, 0, 0, 0.2); padding: 1.2rem; border-radius: var(--radius-sm); border-left: 3px solid var(--gold-primary); transition: transform var(--transition-fast);" onmouseover="this.style.transform='translateX(4px)'" onmouseout="this.style.transform='none'">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                            <div class="card-meta" style="margin-bottom: 0; font-size: 0.75rem;">Topic: <?php echo htmlspecialchars($info_item['topic_title'] ?? 'Uncategorized'); ?></div>
                                            <span style="font-size: 0.7rem; color: var(--text-muted);"><i class="far fa-clock"></i> <?php echo $read_time; ?> min read</span>
                                        </div>
                                        <h4 style="margin-bottom: 0.5rem; font-size: 1rem; color: var(--text-main);"><?php echo htmlspecialchars($info_item['title']); ?></h4>
                                        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4; margin-bottom: 0;">
                                            <?php echo htmlspecialchars(substr($info_item['content'], 0, 110)) . (strlen($info_item['content']) > 110 ? '...' : ''); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">No information articles found.</p>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 1.5rem; text-align: right;">
                        <a href="info.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">View All Information</a>
                    </div>
                </div>

                <!-- Recent Quizzes Section -->
                <div class="glass-panel" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="color: var(--gold-secondary); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1.5rem; font-size: 1.2rem;">
                            <i class="fas fa-tasks" style="margin-right: 0.5rem;"></i> Recent Quizzes
                        </h3>
                        <?php if (count($recent_quizzes) > 0): ?>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <?php foreach ($recent_quizzes as $quiz_item): ?>
                                    <div style="background: rgba(0, 0, 0, 0.2); padding: 1.2rem; border-radius: var(--radius-sm); border-left: 3px solid var(--gold-primary); display: flex; justify-content: space-between; align-items: center; transition: transform var(--transition-fast);" onmouseover="this.style.transform='translateX(4px)'" onmouseout="this.style.transform='none'">
                                        <div style="min-width: 0; flex: 1; padding-right: 1rem;">
                                            <div class="card-meta" style="margin-bottom: 0.25rem; font-size: 0.75rem;">Topic: <?php echo htmlspecialchars($quiz_item['topic_title'] ?? 'Uncategorized'); ?></div>
                                            <h4 style="margin-bottom: 0.25rem; font-size: 1rem; color: var(--text-main); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?php echo htmlspecialchars($quiz_item['title']); ?></h4>
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <span class="badge badge-student" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;"><?php echo $quiz_item['question_count']; ?> Qs</span>
                                                <?php if ($quiz_item['best_score'] !== null): ?>
                                                    <span class="badge" style="background: rgba(46, 213, 115, 0.2); color: var(--success); border: 1px solid rgba(46, 213, 115, 0.4); font-size: 0.7rem; padding: 0.15rem 0.5rem;">Best: <?php echo round($quiz_item['best_score']); ?>%</span>
                                                <?php else: ?>
                                                    <span class="badge" style="background: rgba(255, 255, 255, 0.05); color: var(--text-muted); border: 1px solid rgba(255,255,255,0.1); font-size: 0.7rem; padding: 0.15rem 0.5rem;">Not Taken</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if ($quiz_item['question_count'] > 0): ?>
                                            <a href="quiz_take.php?id=<?php echo $quiz_item['id']; ?>" class="btn btn-primary" style="font-size: 0.8rem; padding: 0.4rem 0.8rem; flex-shrink: 0;">Take Quiz</a>
                                        <?php else: ?>
                                            <span class="badge badge-admin" style="font-size: 0.7rem; flex-shrink: 0;">Empty</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">No quizzes available.</p>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 1.5rem; text-align: right;">
                        <a href="quiz.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">View All Quizzes</a>
                    </div>
                </div>
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
                        You haven't attempted any quizzes yet. <a href="quiz.php" style="color: var(--gold-secondary); text-decoration: underline;">Take your first quiz!</a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($total_quizzes == 0): ?>
                <div class="alert alert-success" style="margin-top: 2rem;">
                    <span><i class="fas fa-info-circle"></i> You haven't taken any quizzes yet. Start learning and test your knowledge!</span>
                </div>
            <?php endif; ?>

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
                        <a href="quiz.php" class="btn btn-primary">Browse Quizzes</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($view === 'info'): ?>
            <h2 style="color: var(--gold-primary); margin-bottom: 0.5rem;">Information Feed</h2>
            <p class="card-meta">Select a topic from the sidebar to view detailed information.</p>
            
            <?php if ($topic_id == 0): ?>
                <div class="alert alert-success" style="margin-top: 2rem;">
                    <span><i class="fas fa-info-circle"></i> Please select a topic from the sidebar to begin learning.</span>
                </div>
            <?php elseif (count($info_items) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 2rem; margin-top: 2rem;">
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
                <div class="alert alert-danger" style="margin-top: 2rem;">
                    <span><i class="fas fa-exclamation-circle"></i> No information articles found for this topic.</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
