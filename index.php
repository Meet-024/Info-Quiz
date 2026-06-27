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

// Fetch Statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM quizzes");
$totalQuizzes = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM information");
$totalInfo = $stmt->fetchColumn();

// Fetch 3 recent quizzes
$stmt = $pdo->query("SELECT q.*, t.title as topic_title FROM quizzes q LEFT JOIN topics t ON q.topic_id = t.id ORDER BY q.created_at DESC LIMIT 3");
$recentQuizzes = $stmt->fetchAll();
?>

<div class="main-container animate-fade-in" style="flex-direction: column; justify-content: space-between; min-height: 75vh;">
    <div class="hero-section" style="margin-top: 2rem;">
        <h1 class="hero-title">Welcome to the InfoQuiz Platform</h1>
        <p class="hero-subtitle">
            Explore a wide range of topics, dive into curated information, and test your knowledge through our interactive MCQs.
        </p>
        
        <div class="hero-actions" style="margin-bottom: 3rem;">
            <a href="info.php" class="btn btn-primary">Browse Information</a>
            <?php if (isLoggedIn()): ?>
                <a href="quiz.php" class="btn btn-outline">Take a Quiz</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline">Login to take Quizzes</a>
            <?php endif; ?>
        </div>

        <!-- Live Statistics -->
        <div class="card-grid" style="width: 100%; max-width: 900px; margin: 0 auto; gap: 1rem; grid-template-columns: repeat(3, 1fr);">
            <div class="glass-panel" style="padding: 1.5rem; text-align: center;">
                <h3 style="color: var(--gold-secondary); font-size: 2rem; margin: 0;"><?php echo $totalUsers; ?></h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Active Users</p>
            </div>
            <div class="glass-panel" style="padding: 1.5rem; text-align: center;">
                <h3 style="color: var(--gold-secondary); font-size: 2rem; margin: 0;"><?php echo $totalInfo; ?></h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Curated Articles</p>
            </div>
            <div class="glass-panel" style="padding: 1.5rem; text-align: center;">
                <h3 style="color: var(--gold-secondary); font-size: 2rem; margin: 0;"><?php echo $totalQuizzes; ?></h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Interactive Quizzes</p>
            </div>
        </div>
    </div>

    <!-- Recent Quizzes Section -->
    <?php if (count($recentQuizzes) > 0): ?>
    <div style="width: 100%; max-width: 900px; margin: 3rem auto 1rem;">
        <h2 style="text-align: center; margin-bottom: 2rem;">Recently Added Quizzes</h2>
        <div class="card-grid">
            <?php foreach ($recentQuizzes as $quiz): ?>
                <div class="card">
                    <div class="card-meta"><?php echo htmlspecialchars($quiz['topic_title'] ?? 'General'); ?></div>
                    <h3 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; flex-grow: 1;">
                        <?php echo htmlspecialchars(substr($quiz['description'], 0, 80)) . '...'; ?>
                    </p>
                    <a href="quiz_take.php?id=<?php echo $quiz['id']; ?>" class="btn btn-outline" style="width: 100%; justify-content: center;">Attempt Quiz</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
