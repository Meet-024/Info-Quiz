<?php
require_once 'config/db.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id === 0) {
    header("Location: quiz.php");
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("Quiz not found.");
}


$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();
$total_questions = count($questions);

if ($total_questions === 0) {
    die("This quiz has no questions yet.");
}

$score = 0;
$submitted = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $submitted = true;
    foreach ($questions as $q) {
        $q_id = $q['id'];
        if (isset($_POST['question_'.$q_id]) && $_POST['question_'.$q_id] === $q['correct_option']) {
            $score++;
        }
    }
    

    $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, quiz_id, score, total_questions) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $quiz_id, $score, $total_questions]);
}

$pageTitle = 'Take Quiz: ' . $quiz['title'];
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="main-container" style="justify-content: center;">
    <div class="glass-panel animate-fade-in" style="width: 100%; max-width: 800px;">
        <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;"><?php echo htmlspecialchars($quiz['description']); ?></p>

        <?php if ($submitted): ?>
            <div style="text-align: center; padding: 2rem;">
                <h1 style="color: var(--gold-primary); font-size: 4rem; margin-bottom: 0;">
                    <?php echo $score; ?> / <?php echo $total_questions; ?>
                </h1>
                <p style="font-size: 1.2rem; margin-top: 1rem;">
                    <?php 
                        $percentage = ($score / $total_questions) * 100;
                        if ($percentage == 100) echo "Perfect Score! Excellent job!";
                        elseif ($percentage >= 70) echo "Great job! You passed.";
                        else echo "Keep studying! You can do better next time.";
                    ?>
                </p>
                <div style="margin-top: 2rem;">
                    <a href="quiz.php" class="btn btn-login">Back to Quizzes</a>
                    <a href="student_dashboard.php" class="btn btn-primary">View Dashboard</a>
                </div>
            </div>
        <?php else: ?>
            <form action="quiz_take.php?id=<?php echo $quiz_id; ?>" method="POST">
                <?php foreach ($questions as $index => $q): ?>
                    <div class="card" style="margin-bottom: 1.5rem; background: rgba(0,0,0,0.3);">
                        <h4 style="margin-bottom: 1rem; color: var(--text-main);">
                            <?php echo ($index + 1) . ". " . htmlspecialchars($q['question_text']); ?>
                        </h4>
                        
                        <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 10px;">
                                <input type="radio" name="question_<?php echo $q['id']; ?>" value="A" required>
                                <span>A) <?php echo htmlspecialchars($q['option_a']); ?></span>
                            </label>
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 10px;">
                                <input type="radio" name="question_<?php echo $q['id']; ?>" value="B" required>
                                <span>B) <?php echo htmlspecialchars($q['option_b']); ?></span>
                            </label>
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 10px;">
                                <input type="radio" name="question_<?php echo $q['id']; ?>" value="C" required>
                                <span>C) <?php echo htmlspecialchars($q['option_c']); ?></span>
                            </label>
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 10px;">
                                <input type="radio" name="question_<?php echo $q['id']; ?>" value="D" required>
                                <span>D) <?php echo htmlspecialchars($q['option_d']); ?></span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div style="text-align: right; margin-top: 2rem;">
                    <button type="submit" name="submit_quiz" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">
                        Submit Quiz
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
