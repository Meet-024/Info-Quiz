<nav class="navbar">
    <?php
    $brand_link = 'index.php';
    if (isLoggedIn()) {
        if (hasRole('admin')) {
            $brand_link = 'admin_dashboard.php';
        } elseif (hasRole('teacher')) {
            $brand_link = 'teacher_dashboard.php';
        } elseif (hasRole('student')) {
            $brand_link = 'student_dashboard.php';
        }
    }
    ?>
    <a href="<?php echo $brand_link; ?>" class="navbar-brand" style="text-decoration: none;">InfoQuiz</a>
    <ul class="nav-links">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        
        $links = [
            'info.php' => 'Information',
            'quiz.php' => 'Quizzes'
        ];

        if (isLoggedIn()) {
            if (hasRole('admin')) {
                $links = [
                    'admin_dashboard.php' => 'Dashboard',
                    'manage_users.php' => 'Users',
                    'manage_info.php' => 'Content',
                    'manage_quizzes.php' => 'Quizzes',
                    'admin_results.php' => 'Results'
                ];
            } elseif (hasRole('teacher')) {
                $links = [
                    'teacher_dashboard.php' => 'Dashboard',
                    'manage_info.php' => 'My Content',
                    'manage_quizzes.php' => 'My Quizzes',
                    'manage_users.php' => 'Students'
                ];
            } elseif (hasRole('student')) {
                 $links = [
                    'student_dashboard.php' => 'Dashboard',
                    'info.php' => 'Learn',
                    'quiz.php' => 'Take Quiz'
                ];
            }
        }

        foreach ($links as $url => $label) {
            $activeClass = ($current_page == parse_url($url, PHP_URL_PATH)) ? 'active' : '';
            echo "<li><a href='$url' class='$activeClass'>$label</a></li>";
        }
        ?>
    </ul>
    
    <div class="nav-auth">
        <?php if (isLoggedIn()): ?>
            <span style="margin-right: 15px; color: var(--gold-secondary);">
                Hi, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b>
            </span>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-login">Login / Register</a>
        <?php endif; ?>
    </div>
</nav>
