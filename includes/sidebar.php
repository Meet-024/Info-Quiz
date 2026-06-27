<aside class="sidebar">
    <?php if (isLoggedIn() && hasRole('student')): ?>
        <div style="margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
            <a href="student_dashboard.php" class="btn btn-primary" style="width: 100%; text-align: center; border-radius: 8px;">Dashboard Overview</a>
            <a href="student_dashboard.php?view=results" class="btn btn-outline" style="width: 100%; text-align: center; border-radius: 8px;">My Quiz Results</a>
        </div>
    <?php endif; ?>
    <div class="glass-panel">
        <h3>Topics</h3>
        <ul class="sidebar-menu">
            <?php

            $stmt = $pdo->query("SELECT * FROM topics ORDER BY title ASC");
            $topics = $stmt->fetchAll();
            $current_topic = isset($_GET['topic']) ? (int)$_GET['topic'] : 0;

            $base_url = basename($_SERVER['PHP_SELF']);

            foreach ($topics as $t) {
                $active = ($current_topic === (int)$t['id']) ? 'active' : '';
                echo "<li><a href='$base_url?topic={$t['id']}' class='$active'>" . htmlspecialchars($t['title']) . "</a></li>";
            }
            ?>
        </ul>
    </div>
</aside>
