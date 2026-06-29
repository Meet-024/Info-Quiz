<?php
require_once 'config/db.php';
requireRole(['admin', 'teacher']);

$is_admin = hasRole('admin');


if (isset($_GET['delete_quiz'])) {
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ? " . ($is_admin ? "" : "AND created_by = ?"));
    if ($is_admin) $stmt->execute([$_GET['delete_quiz']]);
    else $stmt->execute([$_GET['delete_quiz'], $_SESSION['user_id']]);
    header("Location: manage_quizzes.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_quiz'])) {
    $stmt = $pdo->prepare("INSERT INTO quizzes (topic_id, title, description, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['topic_id'], $_POST['title'], $_POST['description'], $_SESSION['user_id']]);
    header("Location: manage_quizzes.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_question'])) {

    $stmt = $pdo->prepare("SELECT id FROM quizzes WHERE id = ? " . ($is_admin ? "" : "AND created_by = ?"));
    if ($is_admin) $stmt->execute([$_POST['quiz_id']]);
    else $stmt->execute([$_POST['quiz_id'], $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['quiz_id'], $_POST['question_text'], 
            $_POST['option_a'], $_POST['option_b'], $_POST['option_c'], $_POST['option_d'], 
            $_POST['correct_option']
        ]);
    }
    header("Location: manage_quizzes.php?manage_qs=" . $_POST['quiz_id']);
    exit;
}


if (isset($_GET['delete_q']) && isset($_GET['quiz_id'])) {

    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ? AND quiz_id = ?");
    $stmt->execute([$_GET['delete_q'], $_GET['quiz_id']]);
    header("Location: manage_quizzes.php?manage_qs=" . $_GET['quiz_id']);
    exit;
}

$pageTitle = 'Manage Quizzes';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$topics = $pdo->query("SELECT * FROM topics")->fetchAll();

if (!$is_admin) {
    $stmt = $pdo->prepare("SELECT q.*, t.title as topic_title FROM quizzes q LEFT JOIN topics t ON q.topic_id = t.id WHERE q.created_by = ? ORDER BY q.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->query("SELECT q.*, t.title as topic_title FROM quizzes q LEFT JOIN topics t ON q.topic_id = t.id ORDER BY q.created_at DESC");
}
$quizzes = $stmt->fetchAll();


$manage_quiz = null;
$questions = [];
if (isset($_GET['manage_qs'])) {
    $q_id = (int)$_GET['manage_qs'];
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
    $stmt->execute([$q_id]);
    $manage_quiz = $stmt->fetch();
    
    if ($manage_quiz) {
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
        $stmt->execute([$q_id]);
        $questions = $stmt->fetchAll();
    }
}
?>

<div class="main-container">
    <div class="sidebar" style="width: 350px;">
        <div class="glass-panel" style="margin-bottom: 1.5rem;">
            <h3>Create Quiz</h3>
            <form action="manage_quizzes.php" method="POST">
                <div class="form-group">
                    <select name="topic_id" class="form-control" required>
                        <option value="">Select Topic</option>
                        <?php foreach ($topics as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="title" class="form-control" placeholder="Quiz Title" required>
                </div>
                <div class="form-group">
                    <textarea name="description" class="form-control" placeholder="Quiz Description" rows="3"></textarea>
                </div>
                <button type="submit" name="add_quiz" class="btn btn-primary" style="width: 100%;">Create Quiz</button>
            </form>
        </div>

        <?php if ($manage_quiz): ?>
        <div class="glass-panel" style="border-color: var(--success);">
            <h3 style="color: var(--success);">Add Question to: <br><small style="color: white;"><?php echo htmlspecialchars($manage_quiz['title']); ?></small></h3>
            <form action="manage_quizzes.php" method="POST">
                <input type="hidden" name="quiz_id" value="<?php echo $manage_quiz['id']; ?>">
                <div class="form-group">
                    <textarea name="question_text" class="form-control" placeholder="Question Text" rows="3" required></textarea>
                </div>
                <div class="form-group"><input type="text" name="option_a" class="form-control" placeholder="Option A" required></div>
                <div class="form-group"><input type="text" name="option_b" class="form-control" placeholder="Option B" required></div>
                <div class="form-group"><input type="text" name="option_c" class="form-control" placeholder="Option C" required></div>
                <div class="form-group"><input type="text" name="option_d" class="form-control" placeholder="Option D" required></div>
                <div class="form-group">
                    <select name="correct_option" class="form-control" required>
                        <option value="">Select Correct Option</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
                <button type="submit" name="add_question" class="btn btn-success" style="width: 100%; background: var(--success); color: white;">Add Question</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="content-area animate-fade-in">
        <?php if ($manage_quiz): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2>Questions for: <?php echo htmlspecialchars($manage_quiz['title']); ?></h2>
                <a href="manage_quizzes.php" class="btn btn-login">Back to Quizzes</a>
            </div>
            
            <?php if (count($questions) > 0): ?>
                <?php foreach ($questions as $index => $q): ?>
                    <div class="card" style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <h4>Q<?php echo $index + 1; ?>: <?php echo htmlspecialchars($q['question_text']); ?></h4>
                            <a href="manage_quizzes.php?delete_q=<?php echo $q['id']; ?>&quiz_id=<?php echo $manage_quiz['id']; ?>" class="btn btn-danger" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Delete question?');">Delete</a>
                        </div>
                        <ul style="list-style: none; padding-top: 0.5rem;">
                            <li style="color: <?php echo $q['correct_option'] == 'A' ? 'var(--success)' : 'inherit'; ?>;">A: <?php echo htmlspecialchars($q['option_a']); ?></li>
                            <li style="color: <?php echo $q['correct_option'] == 'B' ? 'var(--success)' : 'inherit'; ?>;">B: <?php echo htmlspecialchars($q['option_b']); ?></li>
                            <li style="color: <?php echo $q['correct_option'] == 'C' ? 'var(--success)' : 'inherit'; ?>;">C: <?php echo htmlspecialchars($q['option_c']); ?></li>
                            <li style="color: <?php echo $q['correct_option'] == 'D' ? 'var(--success)' : 'inherit'; ?>;">D: <?php echo htmlspecialchars($q['option_d']); ?></li>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-danger">No questions added to this quiz yet.</div>
            <?php endif; ?>

        <?php else: ?>
            <h2>Manage Quizzes</h2>
            <div class="glass-panel" style="padding: 1.5rem;">
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Topic</th>
                                <th>Title</th>
                                <th>Created</th>
                                <th>Questions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizzes as $q): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($q['topic_title'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($q['title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($q['created_at'])); ?></td>
                                    <td>
                                        <a href="manage_quizzes.php?manage_qs=<?php echo $q['id']; ?>" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Add/Edit MCQs</a>
                                    </td>
                                    <td>
                                        <a href="manage_quizzes.php?delete_quiz=<?php echo $q['id']; ?>" onclick="return confirm('Delete quiz?');" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
