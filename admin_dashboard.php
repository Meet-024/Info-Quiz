<?php
require_once 'config/db.php';
requireRole('admin');

$pageTitle = 'Admin Dashboard';
require_once 'includes/header.php';
require_once 'includes/navbar.php';


$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$user_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM information");
$info_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM quizzes");
$quiz_count = $stmt->fetchColumn();

// Fetch 3 most recent articles (with author username)
$stmt = $pdo->query("SELECT i.*, t.title as topic_title, u.username as author FROM information i 
                     LEFT JOIN topics t ON i.topic_id = t.id 
                     LEFT JOIN users u ON i.created_by = u.id 
                     ORDER BY i.created_at DESC LIMIT 3");
$recent_infos = $stmt->fetchAll();

// Fetch 3 most recent quizzes (with author username)
$stmt = $pdo->query("SELECT q.*, t.title as topic_title, u.username as author, 
                     (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count 
                     FROM quizzes q 
                     LEFT JOIN topics t ON q.topic_id = t.id 
                     LEFT JOIN users u ON q.created_by = u.id 
                     ORDER BY q.created_at DESC LIMIT 3");
$recent_quizzes = $stmt->fetchAll();

// Fetch 3 most recent registered users
$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC LIMIT 3");
$recent_users = $stmt->fetchAll();
?>

<div class="main-container">
    <div class="content-area animate-fade-in">
        <!-- Personalized Greeting Banner -->
        <div class="glass-panel" style="padding: 2rem; margin-bottom: 2rem; background: linear-gradient(135deg, rgba(212, 175, 55, 0.07) 0%, rgba(21, 21, 21, 0.8) 100%); position: relative; overflow: hidden; border-left: 5px solid var(--gold-primary);">
            <div style="position: absolute; right: -20px; bottom: -20px; font-size: 8rem; color: rgba(212, 175, 55, 0.03); transform: rotate(-15deg); pointer-events: none;">
                <i class="fas fa-user-shield"></i>
            </div>
            <h2 style="font-size: 2.2rem; margin-bottom: 0.5rem; color: var(--gold-secondary);">Admin Dashboard</h2>
            <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 0;">
                <?php
                $hour = date('H');
                $greeting = 'Welcome back';
                if ($hour < 12) $greeting = 'Good morning';
                elseif ($hour < 18) $greeting = 'Good afternoon';
                else $greeting = 'Good evening';
                echo $greeting;
                ?>, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! Platform control center is ready.
            </p>
        </div>

        <div class="card-grid" style="margin-bottom: 2rem;">
            <!-- Total Users Card -->
            <div class="stat-card" style="display: flex; align-items: center; justify-content: space-between; text-align: left; padding: 1.5rem 2rem;">
                <div>
                    <h3 style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">Total Users</h3>
                    <p class="stat-value" style="margin: 0; font-size: 2.2rem; color: var(--gold-secondary);"><?php echo $user_count; ?></p>
                    <a href="manage_users.php" style="font-size: 0.8rem; color: var(--gold-primary); text-decoration: underline;">Manage Users</a>
                </div>
                <div style="font-size: 2.5rem; color: rgba(212, 175, 55, 0.15);"><i class="fas fa-users"></i></div>
            </div>

            <!-- Information Articles Card -->
            <div class="stat-card" style="display: flex; align-items: center; justify-content: space-between; text-align: left; padding: 1.5rem 2rem;">
                <div>
                    <h3 style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">Information Articles</h3>
                    <p class="stat-value" style="margin: 0; font-size: 2.2rem; color: var(--gold-secondary);"><?php echo $info_count; ?></p>
                    <a href="manage_info.php" style="font-size: 0.8rem; color: var(--gold-primary); text-decoration: underline;">Manage Content</a>
                </div>
                <div style="font-size: 2.5rem; color: rgba(212, 175, 55, 0.15);"><i class="fas fa-book"></i></div>
            </div>

            <!-- Active Quizzes Card -->
            <div class="stat-card" style="display: flex; align-items: center; justify-content: space-between; text-align: left; padding: 1.5rem 2rem;">
                <div>
                    <h3 style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">Active Quizzes</h3>
                    <p class="stat-value" style="margin: 0; font-size: 2.2rem; color: var(--gold-secondary);"><?php echo $quiz_count; ?></p>
                    <a href="manage_quizzes.php" style="font-size: 0.8rem; color: var(--gold-primary); text-decoration: underline;">Manage Quizzes</a>
                </div>
                <div style="font-size: 2.5rem; color: rgba(212, 175, 55, 0.15);"><i class="fas fa-cubes"></i></div>
            </div>
        </div>

        <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; margin-top: 2rem;">
            <!-- Admin Recent Information Section -->
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
                                    $read_time = ceil($word_count / 150);
                                ?>
                                <div style="background: rgba(0, 0, 0, 0.2); padding: 1.2rem; border-radius: var(--radius-sm); border-left: 3px solid var(--gold-primary); transition: transform var(--transition-fast);" onmouseover="this.style.transform='translateX(4px)'" onmouseout="this.style.transform='none'">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <div class="card-meta" style="margin-bottom: 0; font-size: 0.75rem;">Topic: <?php echo htmlspecialchars($info_item['topic_title'] ?? 'Uncategorized'); ?></div>
                                        <span style="font-size: 0.7rem; color: var(--text-muted);"><i class="far fa-clock"></i> <?php echo $read_time; ?> min read</span>
                                    </div>
                                    <h4 style="margin-bottom: 0.25rem; font-size: 1rem; color: var(--text-main);"><?php echo htmlspecialchars($info_item['title']); ?></h4>
                                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4; margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars(substr($info_item['content'], 0, 110)) . (strlen($info_item['content']) > 110 ? '...' : ''); ?>
                                    </p>
                                    <span style="font-size: 0.75rem; color: var(--gold-primary);"><i class="fas fa-user"></i> By: <?php echo htmlspecialchars($info_item['author'] ?? 'System'); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">No information items created yet.</p>
                    <?php endif; ?>
                </div>
                <div style="margin-top: 1.5rem; text-align: right;">
                    <a href="manage_info.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">Manage Content</a>
                </div>
            </div>

            <!-- Admin Recent Quizzes Section -->
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
                                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                                            <span class="badge badge-student" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;"><?php echo $quiz_item['question_count']; ?> Qs</span>
                                            <span style="font-size: 0.75rem; color: var(--gold-primary);"><i class="fas fa-user"></i> By: <?php echo htmlspecialchars($quiz_item['author'] ?? 'System'); ?></span>
                                        </div>
                                    </div>
                                    <a href="manage_quizzes.php?edit=<?php echo $quiz_item['id']; ?>" class="btn btn-primary" style="font-size: 0.8rem; padding: 0.4rem 0.8rem; flex-shrink: 0;">Edit Qs</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">No quizzes created yet.</p>
                    <?php endif; ?>
                </div>
                <div style="margin-top: 1.5rem; text-align: right;">
                    <a href="manage_quizzes.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">Manage Quizzes</a>
                </div>
            </div>
        </div>

        <!-- Recent Registrations Table -->
        <div class="glass-panel" style="margin-top: 2rem;">
            <h3 style="color: var(--gold-secondary); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1.5rem; font-size: 1.2rem;">
                <i class="fas fa-history" style="margin-right: 0.5rem;"></i> Recent User Registrations
            </h3>
            <?php if (count($recent_users) > 0): ?>
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date Registered</th>
                                <th>Username</th>
                                <th>Assigned Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $ru): ?>
                                <?php 
                                    $badge = 'badge-student';
                                    if ($ru['role'] === 'admin') $badge = 'badge-admin';
                                    elseif ($ru['role'] === 'teacher') $badge = 'badge-teacher';
                                ?>
                                <tr>
                                    <td><?php echo date('M d, Y h:i A', strtotime($ru['created_at'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($ru['username']); ?></strong></td>
                                    <td><span class="badge <?php echo $badge; ?>"><?php echo ucfirst($ru['role']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 1.5rem; text-align: right;">
                    <a href="manage_users.php" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">Manage Users</a>
                </div>
            <?php else: ?>
                <div style="padding: 1.5rem; text-align: center; color: var(--text-muted);">
                    No users registered in the system yet.
                </div>
            <?php endif; ?>
        </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
