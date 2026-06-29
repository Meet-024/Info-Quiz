<?php
require_once 'config/db.php';

if (isLoggedIn()) {
    if (hasRole('admin')) {
        header("Location: admin_dashboard.php");
    } elseif (hasRole('teacher')) {
        header("Location: teacher_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit;
}

$pageTitle = 'Home';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM quizzes");
$totalQuizzes = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM information");
$totalInfo = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT q.*, t.title as topic_title FROM quizzes q LEFT JOIN topics t ON q.topic_id = t.id ORDER BY q.created_at DESC LIMIT 4");
$recentQuizzes = $stmt->fetchAll();

$stmt = $pdo->query("SELECT i.*, t.title as topic_title FROM information i LEFT JOIN topics t ON i.topic_id = t.id ORDER BY i.created_at DESC LIMIT 4");
$recentArticles = $stmt->fetchAll();
?>

<style>
    .hero-glow-container {
        position: relative;
        overflow: hidden;
        border-radius: var(--radius-lg);
        background: linear-gradient(135deg, rgba(21, 21, 21, 0.7) 0%, rgba(10, 10, 10, 0.95) 100%);
        border: 1px solid var(--border-color);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
        padding: 5rem 2rem;
        margin-top: 1rem;
        text-align: center;
    }

    .hero-glow-bg {
        position: absolute;
        top: -50px;
        right: -50px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(212, 175, 55, 0.12) 0%, transparent 75%);
        pointer-events: none;
        z-index: 1;
        filter: blur(40px);
    }

    .hero-glow-bg-left {
        position: absolute;
        bottom: -100px;
        left: -100px;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(212, 175, 55, 0.08) 0%, transparent 70%);
        pointer-events: none;
        z-index: 1;
        filter: blur(50px);
    }

    .stat-circle-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(212, 175, 55, 0.08);
        border: 1px solid rgba(212, 175, 55, 0.25);
        color: var(--gold-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
        font-size: 1.5rem;
        transition: all var(--transition-fast);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .glass-panel:hover .stat-circle-icon {
        background: var(--gold-primary);
        color: var(--bg-dark);
        box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
        transform: translateY(-2px);
    }

    .section-divider {
        width: 80px;
        height: 3px;
        background: linear-gradient(to right, transparent, var(--gold-primary), transparent);
        margin: 4rem auto;
    }

    .feature-card {
        text-align: center;
        background: rgba(21, 21, 21, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all var(--transition-normal);
        padding: 2.5rem 1.75rem;
    }

    .feature-card:hover {
        border-color: var(--border-color);
        background: rgba(21, 21, 21, 0.8);
        transform: translateY(-4px);
    }

    .feature-icon-wrapper {
        width: 65px;
        height: 65px;
        border-radius: var(--radius-sm);
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gold-primary);
        font-size: 1.8rem;
        margin: 0 auto 1.5rem;
        transition: all var(--transition-normal);
    }

    .feature-card:hover .feature-icon-wrapper {
        background: rgba(212, 175, 55, 0.1);
        border-color: var(--gold-primary);
        color: var(--gold-secondary);
        transform: scale(1.05);
    }
</style>

<div class="main-container animate-fade-in" style="flex-direction: column; min-height: 80vh; gap: 0;">
    <!-- Hero Panel with Glow Effects -->
    <div class="hero-glow-container">
        <div class="hero-glow-bg"></div>
        <div class="hero-glow-bg-left"></div>
        
        <h1 class="hero-title" style="position: relative; z-index: 2; margin-bottom: 1.5rem; font-weight: 800;">
            Expand Your Knowledge & <br><span style="color: var(--gold-secondary);">Test Your Limits</span>
        </h1>
        <p class="hero-subtitle" style="position: relative; z-index: 2; margin: 0 auto 2.5rem; font-size: 1.2rem; line-height: 1.6;">
            Access curated study articles across 12 main topics, master core concepts, and track your progress through interactive multiple-choice quizzes.
        </p>
        
        <div class="hero-actions" style="position: relative; z-index: 2; margin-bottom: 4rem;">
            <a href="info.php" class="btn btn-primary" style="padding: 0.9rem 2.2rem;">Get Started</a>
            <?php if (isLoggedIn()): ?>
                <a href="quiz.php" class="btn btn-outline" style="padding: 0.9rem 2.2rem;">Take a Quiz</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline" style="padding: 0.9rem 2.2rem;">Login to Platform</a>
            <?php endif; ?>
        </div>

        <!-- Live Statistics Counter Grid -->
        <div class="card-grid" style="width: 100%; max-width: 950px; margin: 0 auto; gap: 1.5rem; position: relative; z-index: 2;">
            <div class="glass-panel" style="padding: 2rem; border-radius: var(--radius-md);">
                <div class="stat-circle-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 style="color: var(--text-main); font-size: 2.2rem; margin-bottom: 0.25rem; font-weight: 700;"><?php echo $totalUsers; ?></h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600;">Active Users</p>
            </div>
            <div class="glass-panel" style="padding: 2rem; border-radius: var(--radius-md);">
                <div class="stat-circle-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 style="color: var(--text-main); font-size: 2.2rem; margin-bottom: 0.25rem; font-weight: 700;"><?php echo $totalInfo; ?></h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600;">Curated Articles</p>
            </div>
            <div class="glass-panel" style="padding: 2rem; border-radius: var(--radius-md);">
                <div class="stat-circle-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 style="color: var(--text-main); font-size: 2.2rem; margin-bottom: 0.25rem; font-weight: 700;"><?php echo $totalQuizzes; ?></h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600;">Interactive Quizzes</p>
            </div>
        </div>
    </div>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Platform Features Walkthrough -->
    <div style="width: 100%; margin-bottom: 2rem;">
        <h2 style="text-align: center; margin-bottom: 0.5rem; font-size: 2rem;">Explore the Platform Features</h2>
        <p style="text-align: center; color: var(--text-muted); font-size: 1.05rem; margin-bottom: 3rem;">Designed to give you a modern, structured learning and assessment environment.</p>
        <div class="card-grid" style="gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(300px, 420px)); justify-content: center;">
            <div class="card feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-book"></i>
                </div>
                <h3 class="card-title" style="color: var(--gold-secondary);">1. Study Core Topics</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.5;">
                    Browse through 12 essential computer science and software engineering topics. Read rich learning articles right from your dashboard.
                </p>
            </div>
            
            <div class="card feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3 class="card-title" style="color: var(--gold-secondary);">2. Take Quizzes</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.5;">
                    Verify your learning with multiple-choice questions. Get instantaneous score logs, custom tips, and retry permissions.
                </p>
            </div>
            
            <div class="card feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="card-title" style="color: var(--gold-secondary);">3. Track Progress</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.5;">
                    Access your personalized results panel directly on the navigation bar. Compare attempts, track score growth, and target weak modules.
                </p>
            </div>
        </div>
    </div>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Recently Added Articles (Content) Section -->
    <?php if (count($recentArticles) > 0): ?>
    <div style="width: 100%; margin-bottom: 2rem;">
        <h2 style="text-align: center; margin-bottom: 0.5rem; font-size: 2rem;">Recently Added Articles</h2>
        <p style="text-align: center; color: var(--text-muted); font-size: 1.05rem; margin-bottom: 3rem;">Explore the latest curated educational content.</p>
        <div class="card-grid" style="gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            <?php foreach ($recentArticles as $art): ?>
                <div class="card" style="background: var(--bg-card); border-radius: var(--radius-md); border-color: rgba(212, 175, 55, 0.15); text-align: center;">
                    <div class="card-meta" style="color: var(--gold-primary); font-weight: 600; font-size: 0.8rem;"><?php echo htmlspecialchars($art['topic_title'] ?? 'General'); ?></div>
                    <h3 class="card-title" style="margin-top: 0.25rem; font-size: 1.3rem;"><?php echo htmlspecialchars($art['title']); ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.75rem; flex-grow: 1; line-height: 1.5;">
                        <?php echo htmlspecialchars(substr(strip_tags($art['content']), 0, 95)) . '...'; ?>
                    </p>
                    <a href="info.php?topic=<?php echo $art['topic_id']; ?>" class="btn btn-outline" style="width: 100%; justify-content: center; padding: 0.75rem;">Read Article <i class="fas fa-book-open" style="font-size: 0.8rem; margin-left: 5px;"></i></a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Recent Quizzes Section -->
    <?php if (count($recentQuizzes) > 0): ?>
    <div style="width: 100%; margin-bottom: 4rem;">
        <h2 style="text-align: center; margin-bottom: 0.5rem; font-size: 2rem;">Recently Added Quizzes</h2>
        <p style="text-align: center; color: var(--text-muted); font-size: 1.05rem; margin-bottom: 3rem;">Jump right into the latest additions to test your skills.</p>
        <div class="card-grid" style="gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            <?php foreach ($recentQuizzes as $quiz): ?>
                <div class="card" style="background: var(--bg-card); border-radius: var(--radius-md); border-color: rgba(212, 175, 55, 0.15); text-align: center;">
                    <div class="card-meta" style="color: var(--gold-primary); font-weight: 600; font-size: 0.8rem;"><?php echo htmlspecialchars($quiz['topic_title'] ?? 'General'); ?></div>
                    <h3 class="card-title" style="margin-top: 0.25rem; font-size: 1.3rem;"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.75rem; flex-grow: 1; line-height: 1.5;">
                        <?php echo htmlspecialchars(substr($quiz['description'], 0, 95)) . '...'; ?>
                    </p>
                    <a href="quiz_take.php?id=<?php echo $quiz['id']; ?>" class="btn btn-outline" style="width: 100%; justify-content: center; padding: 0.75rem;">Attempt Quiz <i class="fas fa-arrow-right" style="font-size: 0.8rem; margin-left: 5px;"></i></a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
