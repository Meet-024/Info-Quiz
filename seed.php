<?php



$host = 'localhost';
$dbname = 'info_quiz_db';
$username = 'root';
$password = '';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Database Seeder | InfoQuiz</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #121212; color: #e0e0e0; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; background: #1e1e1e; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border: 1px solid #333; }
        h1 { color: #d4af37; border-bottom: 2px solid #d4af37; padding-bottom: 0.5rem; }
        .log { font-family: monospace; background: #000; padding: 1rem; border-radius: 4px; overflow-x: auto; white-space: pre-wrap; line-height: 1.5; color: #0f0; margin-bottom: 1rem; }
        .success { color: #2ed573; font-weight: bold; }
        .error { color: #ff4757; font-weight: bold; }
        .info { color: #1e90ff; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #d4af37; color: #121212; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 1rem; }
        .btn:hover { background: #fff; }
    </style>
</head>
<body>
<div class='container'>
    <h1>InfoQuiz Seeder Setup (12 Quizzes, 12 MCQs each)</h1>
    <div class='log'>";

try {
    echo "[Info] Connecting to MySQL server at $host...\n";
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "[Info] Re-creating database '$dbname'...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$dbname`");
    $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8 COLLATE utf8_general_ci");
    $pdo->exec("USE `$dbname`");
    echo "<span class='success'>[Success] Database '$dbname' created successfully.</span>\n\n";

    
    echo "[Info] Creating table 'users'...\n";
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    echo "[Info] Creating table 'topics'...\n";
    $pdo->exec("CREATE TABLE topics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");

    echo "[Info] Creating table 'information'...\n";
    $pdo->exec("CREATE TABLE information (
        id INT AUTO_INCREMENT PRIMARY KEY,
        topic_id INT,
        title VARCHAR(255) NOT NULL,
        content TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");

    echo "[Info] Creating table 'quizzes'...\n";
    $pdo->exec("CREATE TABLE quizzes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        topic_id INT,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");

    echo "[Info] Creating table 'questions'...\n";
    $pdo->exec("CREATE TABLE questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        quiz_id INT,
        question_text TEXT NOT NULL,
        option_a VARCHAR(255) NOT NULL,
        option_b VARCHAR(255) NOT NULL,
        option_c VARCHAR(255) NOT NULL,
        option_d VARCHAR(255) NOT NULL,
        correct_option CHAR(1) NOT NULL,
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    echo "[Info] Creating table 'quiz_results'...\n";
    $pdo->exec("CREATE TABLE quiz_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        quiz_id INT,
        score INT NOT NULL,
        total_questions INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    echo "<span class='success'>[Success] All tables created successfully.</span>\n\n";

    
    echo "[Info] Seeding Users...\n";
    $users = [
        ['username' => 'meet_monani', 'role' => 'admin', 'password' => 'admin123'],
        ['username' => 'rahul', 'role' => 'teacher', 'password' => 'teacher123'],
        ['username' => 'bhavya', 'role' => 'student', 'password' => 'student123'],
        ['username' => 'sumit', 'role' => 'student', 'password' => 'student123'],
        ['username' => 'kirtan', 'role' => 'student', 'password' => 'student123'],
        ['username' => 'meet', 'role' => 'student', 'password' => 'student123']
    ];

    $user_ids = [];
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    foreach ($users as $u) {
        $hashed = password_hash($u['password'], PASSWORD_DEFAULT);
        $stmt->execute([$u['username'], $hashed, $u['role']]);
        $user_ids[$u['username']] = $pdo->lastInsertId();
        echo "  - Added user: " . $u['username'] . " (" . $u['role'] . ")\n";
    }

    
    echo "\n[Info] Seeding 12 Topics...\n";
    $topics = [
        ['title' => 'Web Development', 'description' => 'Core building blocks of the web, including HTML, CSS, JavaScript, and Server-Side Scripting.', 'created_by' => $user_ids['rahul']],
        ['title' => 'Database Systems', 'description' => 'Relational databases, SQL queries, schema design, and normalisation techniques.', 'created_by' => $user_ids['rahul']],
        ['title' => 'Computer Networks', 'description' => 'Network architectures, protocols, routing, IP addressing, and security concepts.', 'created_by' => $user_ids['rahul']],
        ['title' => 'Software Engineering', 'description' => 'Principles of software design, SDLC methodologies, version control (Git), testing, and patterns.', 'created_by' => $user_ids['rahul']],
        ['title' => 'Operating Systems', 'description' => 'Core system concepts including process management, CPU scheduling, thread synchronization, memory virtualization, and file systems.', 'created_by' => $user_ids['rahul']],
        ['title' => 'Cloud Computing', 'description' => 'Introduction to cloud service models (IaaS, PaaS, SaaS), virtualization, serverless computing, and AWS/Azure architectures.', 'created_by' => $user_ids['rahul']],
        ['title' => 'Cybersecurity', 'description' => 'Core concepts of computer security including encryption, firewall functions, malware types, and secure hashing algorithms.', 'created_by' => $user_ids['rahul']],
        ['title' => 'Data Structures', 'description' => 'Fundamental structures for organizing data including arrays, linked lists, stacks, queues, trees, and graphs.', 'created_by' => $user_ids['rahul']],
        ['title' => 'Algorithms', 'description' => 'Common computational algorithms including sorting, searching, recursion, and analysis of time/space complexity (Big O).', 'created_by' => $user_ids['rahul']],
        ['title' => 'Object-Oriented Programming', 'description' => 'Key paradigms of OOP including inheritance, encapsulation, polymorphism, and abstraction.', 'created_by' => $user_ids['rahul']],
        ['title' => 'Version Control with Git', 'description' => 'Distributed version control concepts, repository tracking, commits, branches, merging, and remote collaboration.', 'created_by' => $user_ids['rahul']],
        ['title' => 'Machine Learning Basics', 'description' => 'Fundamental concepts of artificial intelligence, supervised vs unsupervised learning, regression, and neural networks.', 'created_by' => $user_ids['rahul']]
    ];

    $topic_ids = [];
    $stmt = $pdo->prepare("INSERT INTO topics (title, description, created_by) VALUES (?, ?, ?)");
    foreach ($topics as $t) {
        $stmt->execute([$t['title'], $t['description'], $t['created_by']]);
        $topic_ids[$t['title']] = $pdo->lastInsertId();
        echo "  - Added topic: " . $t['title'] . "\n";
    }

    
    echo "\n[Info] Seeding Information Articles...\n";
    $info_articles = [
        [
            'topic_id' => $topic_ids['Web Development'],
            'title' => 'Understanding Semantic HTML5',
            'content' => "Semantic HTML5 introduces tags that explicitly describe the meaning and structure of their content to both browser, search engine, and assistive technology.\n\nWhy Semantic HTML Matters:\n1. Accessibility: Screen readers use semantic tags to help visually impaired users navigate pages easily. Elements like <nav> allow them to skip directly to navigation, while <main> jumps to the primary content.\n2. Search Engine Optimisation (SEO): Search engine crawlers (Googlebot) categorise page content based on semantic wrappers. Text wrapped in <article> is weighted more highly as core page content compared to generic <div> sections.\n3. Clean Code & Maintenance: Developers reading semantic code can immediately recognise the layout structures. This simplifies maintenance and teamwork.\n\nKey Semantic Elements:\n- <header>: Defines a header container for a document or section, typically containing branding and navigation links.\n- <nav>: Wraps a set of navigation links.\n- <main>: Outlines the primary, unique content of the document.\n- <article>: Contains self-contained, independent content (e.g., blog posts, news stories, forum comments).\n- <section>: Groups related thematic contents together.\n- <aside>: Marks content tangentially related to the surrounding context (e.g., sidebars, callout boxes).\n- <footer>: Contains author information, copyrights, and sitemap links.",
            'created_by' => $user_ids['rahul']
        ],
        [
            'topic_id' => $topic_ids['Database Systems'],
            'title' => 'Introduction to SQL Joins',
            'content' => "In relational databases, data is split into multiple tables to eliminate redundancy. SQL Joins allow us to query and combine these scattered records based on a shared column (usually a foreign key).\n\nTypes of SQL Joins:\n\n1. INNER JOIN:\nFetches records only when there is a match in both tables. If a record in the left table doesn't have a matching key in the right, it is omitted.\nExample: Matching orders with users.\n\n2. LEFT JOIN (or LEFT OUTER JOIN):\nReturns all rows from the left table, plus matched rows from the right table. If there is no match, NULL values are returned for columns of the right table.\nExample: Listing all students, along with any quizzes they have taken (showing NULL if they haven't taken any).\n\n3. RIGHT JOIN (or RIGHT OUTER JOIN):\nOpposite of LEFT JOIN. Returns all rows from the right table, plus matched rows from the left. If no matches exist, NULL values represent left-side columns.\n\n4. FULL OUTER JOIN:\nCombines the functionality of both LEFT and RIGHT joins. Returns records when there is a match in either left or right table, filling missing values with NULLs.",
            'created_by' => $user_ids['rahul']
        ],
        [
            'topic_id' => $topic_ids['Computer Networks'],
            'title' => 'Understanding the TCP/IP Model',
            'content' => "The TCP/IP model is the conceptual framework that enables network communications across the global internet. It defines how data is packaged, addressed, transmitted, and routed.\n\nThe Four Layers of TCP/IP:\n\n1. Application Layer:\nResponsible for node-to-node communication and user interface interactions.\n- Key Protocols: HTTP (Web browsing), HTTPS (Secure browsing), SMTP (Email), FTP (File transfer), and DNS.\n\n2. Transport Layer:\nManages packet delivery, error correction, and flow control between source and host.\n- Key Protocols: TCP (Transmission Control Protocol - connection-oriented, guarantees delivery) and UDP (User Datagram Protocol - connectionless, faster but does not guarantee delivery).\n\n3. Internet Layer:\nHandles routing and addressing of packets across multiple network links.\n- Key Protocols: IP (Internet Protocol - manages IPv4/IPv6 addressing), ICMP (Ping diagnostics).\n\n4. Network Access Layer:\nDefines how data is physically sent through the hardware media (cables, fiber, wireless).\n- Key Protocols: Ethernet, Wi-Fi (802.11), and ARP (Address Resolution Protocol).",
            'created_by' => $user_ids['rahul']
        ],
        [
            'topic_id' => $topic_ids['Software Engineering'],
            'title' => 'Introduction to Agile & Scrum',
            'content' => "Agile is a software development approach focused on iterative development, collaboration, and adaptability. Scrum is the most popular framework implementing Agile.\n\nCore Scrum Elements:\n- Sprints: Fixed-length blocks of time (usually 2-4 weeks) during which usable, inspectable code increments are built.\n- Roles:\n  - Product Owner: Defines user requirements and priorities.\n  - Scrum Master: Facilitates progress, removes blockers, and guides Agile practices.\n  - Development Team: Cross-functional members writing the code and tests.\n- Ceremonies: Sprint Planning, Daily Stand-ups, Sprint Reviews, and Retrospectives.",
            'created_by' => $user_ids['rahul']
        ],
        [
            'topic_id' => $topic_ids['Operating Systems'],
            'title' => 'Understanding Processes & Threads',
            'content' => "An Operating System manages execution structures via Processes and Threads:\n\nProcess:\n- An independent, executing instance of a program loaded in memory.\n- Has its own address space, memory heap, file descriptors, and security context.\n- Process switching (context switching) requires significant overhead due to memory map updates.\n\nThread:\n- A lightweight unit of execution within a parent process.\n- Multiple threads share the same process memory space, global variables, and open file handles, but maintain separate stacks and registers.\n- Context switching between threads is faster, but sharing memory introduces synchronization challenges (race conditions).",
            'created_by' => $user_ids['rahul']
        ],
        [
            'topic_id' => $topic_ids['Cloud Computing'],
            'title' => 'Introduction to Cloud Architectures',
            'content' => "Cloud computing shifts hosting from local machines to virtualized remote servers. It is categorized by service and deployment structures.\n\nService Architectures:\n1. Infrastructure as a Service (IaaS): Offers basic compute, storage, and networking layers (e.g., AWS EC2, Google Compute Engine).\n2. Platform as a Service (PaaS): Provides runtime environments and databases for deployment, hiding server maintenance (e.g., Heroku, AWS Elastic Beanstalk).\n3. Software as a Service (SaaS): Offers fully functioning software ready for user interactions (e.g., Google Drive, Slack).\n\nKey Concepts:\n- Virtualisation: A software layer (hypervisor) partition physical servers into logical machines.\n- Elasticity: The cloud's ability to scale resources up or down dynamically based on user traffic spikes.",
            'created_by' => $user_ids['rahul']
        ],
        [
            'topic_id' => $topic_ids['Cybersecurity'],
            'title' => 'Security Fundamentals & Cryptography',
            'content' => "Cybersecurity represents the collection of tools, policies, and concepts used to protect resources, networks, and data from attacks.\n\nCore Cryptography:\n1. Symmetric Encryption: Uses one single shared key to lock and unlock data. Fast and efficient for bulk files, but key distribution remains a challenge.\n2. Asymmetric Encryption: Uses a key pair. Public keys encrypt data, and matching private keys decrypt. Crucial for establishing secure handshakes (like HTTPS).\n3. Hashing: One-way mathematics translating text into fixed-length strings. Used for password security (e.g. Bcrypt, SHA-256) and verifying download integrity.",
            'created_by' => $user_ids['rahul']
        ],
        [
            'topic_id' => $topic_ids['Data Structures'],
            'title' => 'Arrays, Stacks, Queues, and Lists',
            'content' => "Data structures are specialized formats for organizing and storing data in computer memory.\n\nCore Data Structures:\n- Array: A contiguous allocation of fixed-size elements accessible in O(1) time by index.\n- Singly Linked List: Dynamic chain of nodes containing values and pointers. Inserting at the head takes O(1) time, but accessing random elements takes O(n).\n- Stack (LIFO): Last-In, First-Out structure. Operations include push (insert) and pop (remove).\n- Queue (FIFO): First-In, First-Out structure. Elements insert at the back (enqueue) and exit from the front (dequeue).",
            'created_by' => $user_ids['rahul']
        ],
        [
            'topic_id' => $topic_ids['Algorithms'],
            'title' => 'Big O Notation & Sorting Algorithms',
            'content' => "Algorithms are step-by-step computational procedures designed to solve mathematical or operational problems.\n\n1. Big O Notation:\nMeasures how runtime or space requirements grow as the input size (n) scales to infinity.\n- O(1): Constant time (ideal).\n- O(log n): Logarithmic (binary search).\n- O(n): Linear growth (looping through arrays).\n- O(n^2): Quadratic growth (double nested loops like bubble sort).\n\n2. Sorting Paradigms:\n- Divide & Conquer: Split arrays, resolve subproblems, merge (e.g., Merge Sort, Quick Sort).\n- Adjacent Swaps: Cycle adjacent values, swap sorting order (e.g., Bubble Sort).",
            'created_by' => $user_ids['rahul']
        ],
        [
            'topic_id' => $topic_ids['Object-Oriented Programming'],
            'title' => 'The Four Pillars of OOP',
            'content' => "Object-Oriented Programming (OOP) models programs using entities called Objects.\n\nThe Four Pillars:\n1. Encapsulation: Bundles internal state (variables) with logic (methods) and limits direct outside access using visibility modifiers (private, protected, public).\n2. Inheritance: Lets subclasses inherit fields and methods from a parent class, promoting code reuse.\n3. Polymorphism: Allows objects of different types to respond to the same method signature differently (method overriding/overloading).\n4. Abstraction: Hides complex logic structures behind simple interfaces (e.g. abstract classes).",
            'created_by' => $user_ids['rahul']
        ],
        [
            'topic_id' => $topic_ids['Version Control with Git'],
            'title' => 'Distributed Version Control & Git Flow',
            'content' => "Git is a distributed version control system that records changes to a set of files over time, permitting multiple developers to collaborate.\n\nThe Three Git States:\n1. Working Directory: Local sandbox where modifications are actively made.\n2. Staging Area: Holds changes selected to be packaged in the next commit.\n3. Repository (.git directory): Stores committed snapshot logs permanently.\n\nKey Concepts:\n- Commit: A unique snapshot hash recording staged edits.\n- Branching: Creates isolated copies to write features safely without breaking the main release.",
            'created_by' => $user_ids['rahul']
        ],
        [
            'topic_id' => $topic_ids['Machine Learning Basics'],
            'title' => 'Supervised vs Unsupervised ML',
            'content' => "Machine Learning (ML) enables systems to identify patterns and make predictions from data without explicit manual programming rules.\n\nCore Methodologies:\n1. Supervised Learning: Models are trained using labeled input/output datasets. Tasks include Regression (predicting prices) and Classification (spam detection).\n2. Unsupervised Learning: Models process unlabeled data to uncover hidden grouping structures. Tasks include Clustering (customer segmentation).\n3. Reinforcement Learning: Trains autonomous agents to optimize decisions in dynamic environments using feedback loops of rewards and penalties.",
            'created_by' => $user_ids['rahul']
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO information (topic_id, title, content, created_by) VALUES (?, ?, ?, ?)");
    foreach ($info_articles as $art) {
        $stmt->execute([$art['topic_id'], $art['title'], $art['content'], $art['created_by']]);
        echo "  - Added information article: " . $art['title'] . "\n";
    }

    
    echo "\n[Info] Seeding Quizzes...\n";
    $quizzes = [
        ['topic_id' => $topic_ids['Web Development'], 'title' => 'HTML & CSS Foundations', 'description' => 'Test your knowledge on HTML5 semantic tags, box model properties, and layouts like Flexbox and Grid.', 'created_by' => $user_ids['rahul']],
        ['topic_id' => $topic_ids['Web Development'], 'title' => 'Modern JavaScript Essentials', 'description' => 'Assess your understanding of modern ES6+ JS features, variable scoping, arrow functions, and async patterns.', 'created_by' => $user_ids['rahul']],
        ['topic_id' => $topic_ids['Database Systems'], 'title' => 'SQL Queries & Relational DBs', 'description' => 'Check your understanding of SELECT queries, JOIN operations, aggregate functions, and table constraints.', 'created_by' => $user_ids['rahul']],
        ['topic_id' => $topic_ids['Computer Networks'], 'title' => 'TCP/IP & DNS Networking Basics', 'description' => 'Test your familiarity with network models, transport protocols, IP layers, and DNS queries.', 'created_by' => $user_ids['rahul']],
        ['topic_id' => $topic_ids['Software Engineering'], 'title' => 'Software Engineering Principles', 'description' => 'Evaluate your knowledge of SDLC methodologies (Agile/Scrum), SOLID design principles, version control (Git), and software testing.', 'created_by' => $user_ids['rahul']],
        ['topic_id' => $topic_ids['Operating Systems'], 'title' => 'Operating Systems Fundamentals', 'description' => 'Check your grasp of CPU scheduling, process management, deadlocks, virtualization, thread sync, and page faults.', 'created_by' => $user_ids['rahul']],
        ['topic_id' => $topic_ids['Cloud Computing'], 'title' => 'Cloud Computing Fundamentals', 'description' => 'Evaluate your understanding of infrastructure layers (IaaS, PaaS, SaaS), hypervisors, and autoscaling architectures.', 'created_by' => $user_ids['rahul']],
        ['topic_id' => $topic_ids['Cybersecurity'], 'title' => 'Cybersecurity & Cryptography', 'description' => 'Test your knowledge of malware, symmetric vs asymmetric encryption systems, firewalls, and secure hash utilities.', 'created_by' => $user_ids['rahul']],
        ['topic_id' => $topic_ids['Data Structures'], 'title' => 'Data Structures Fundamentals', 'description' => 'Assess your understanding of basic memory structures like Arrays, Linked Lists, LIFO Stacks, and FIFO Queues.', 'created_by' => $user_ids['rahul']],
        ['topic_id' => $topic_ids['Algorithms'], 'title' => 'Algorithms & Complexity', 'description' => 'Test your awareness of Big O complexity notation, recursive routines, and sorting search methodologies.', 'created_by' => $user_ids['rahul']],
        ['topic_id' => $topic_ids['Object-Oriented Programming'], 'title' => 'OOP Paradigms', 'description' => 'Check your understanding of encapsulation, subclass inheritance, class polymorphism, and OOP design abstraction.', 'created_by' => $user_ids['rahul']],
        ['topic_id' => $topic_ids['Version Control with Git'], 'title' => 'Git Version Control', 'description' => 'Evaluate your command of repositories, commits, branch handling, merging conflicts, and git configurations.', 'created_by' => $user_ids['rahul']],
        ['topic_id' => $topic_ids['Machine Learning Basics'], 'title' => 'Machine Learning Concepts', 'description' => 'Test your basic knowledge of supervised models, clustering sets, training data runs, and artificial neural networks.', 'created_by' => $user_ids['rahul']]
    ];

    $quiz_ids = [];
    $stmt = $pdo->prepare("INSERT INTO quizzes (topic_id, title, description, created_by) VALUES (?, ?, ?, ?)");
    foreach ($quizzes as $q) {
        $stmt->execute([$q['topic_id'], $q['title'], $q['description'], $q['created_by']]);
        $quiz_ids[$q['title']] = $pdo->lastInsertId();
        echo "  - Added quiz: " . $q['title'] . "\n";
    }

    
    echo "\n[Info] Seeding 12 Quiz Questions per Quiz...\n";
    $questions = [
        
        [
            'quiz_id' => $quiz_ids['HTML & CSS Foundations'],
            'question_text' => 'Which HTML5 element is used to represent self-contained composition in a document, page, or site?',
            'option_a' => '<section>', 'option_b' => '<article>', 'option_c' => '<aside>', 'option_d' => '<div>', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['HTML & CSS Foundations'],
            'question_text' => 'Which CSS property defines whether a flex container is single-line or multi-line?',
            'option_a' => 'flex-direction', 'option_b' => 'flex-wrap', 'option_c' => 'flex-flow', 'option_d' => 'justify-content', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['HTML & CSS Foundations'],
            'question_text' => 'Which HTML attribute specifies an alternate text for an image if the image cannot be displayed?',
            'option_a' => 'title', 'option_b' => 'src', 'option_c' => 'alt', 'option_d' => 'href', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['HTML & CSS Foundations'],
            'question_text' => 'What is the default value of the box-sizing property in CSS?',
            'option_a' => 'content-box', 'option_b' => 'border-box', 'option_c' => 'padding-box', 'option_d' => 'margin-box', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['HTML & CSS Foundations'],
            'question_text' => 'Which HTML5 element represents the primary, non-repeated content of a document?',
            'option_a' => '<section>', 'option_b' => '<body>', 'option_c' => '<main>', 'option_d' => '<article>', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['HTML & CSS Foundations'],
            'question_text' => 'The CSS unit "rem" is relative to which font-size?',
            'option_a' => 'The parent element font-size', 'option_b' => 'The HTML root element font-size', 'option_c' => 'The viewport width', 'option_d' => 'The browser default size only', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['HTML & CSS Foundations'],
            'question_text' => 'Which tag represents the highest priority heading in HTML?',
            'option_a' => '<h1>', 'option_b' => '<h6h>', 'option_c' => '<head>', 'option_d' => '<heading>', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['HTML & CSS Foundations'],
            'question_text' => 'Which CSS Grid property is used to define the width and count of columns?',
            'option_a' => 'grid-columns', 'option_b' => 'grid-template-columns', 'option_c' => 'grid-column-gap', 'option_d' => 'grid-auto-flow', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['HTML & CSS Foundations'],
            'question_text' => 'Which HTML element is used to represent content that is indirectly related to the main content (like a sidebar)?',
            'option_a' => '<section>', 'option_b' => '<summary>', 'option_c' => '<aside>', 'option_d' => '<nav>', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['HTML & CSS Foundations'],
            'question_text' => 'Which CSS declaration makes an element completely invisible but leaves its layout space intact?',
            'option_a' => 'display: none', 'option_b' => 'opacity: 0.5', 'option_c' => 'visibility: hidden', 'option_d' => 'position: absolute', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['HTML & CSS Foundations'],
            'question_text' => 'Which CSS property is used to align items individually inside a flex container (overriding align-items)?',
            'option_a' => 'align-content', 'option_b' => 'justify-self', 'option_c' => 'align-self', 'option_d' => 'order', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['HTML & CSS Foundations'],
            'question_text' => 'Which HTML element is used to display a preformatted block of text with a monospaced font?',
            'option_a' => '<code>', 'option_b' => '<pre>', 'option_c' => '<samp>', 'option_d' => '<text>', 'correct_option' => 'B'
        ],

        
        [
            'quiz_id' => $quiz_ids['Modern JavaScript Essentials'],
            'question_text' => 'What is the main difference between declaring variables using const instead of let?',
            'option_a' => 'const variables have global scope, while let variables have block scope.',
            'option_b' => 'const variables cannot be reassigned after declaration, while let variables can.',
            'option_c' => 'const variables are hoisted, while let variables are not hoisted.',
            'option_d' => 'const holds objects only, while let can hold any data type.', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Modern JavaScript Essentials'],
            'question_text' => 'Which statement describes the behavior of ES6 arrow functions regarding "this"?',
            'option_a' => 'They bind "this" dynamically to the element triggering them.',
            'option_b' => 'They inherit "this" lexically from the parent enclosing scope.',
            'option_c' => 'They have no "this" context at all and throw an error.',
            'option_d' => 'They force "this" to point to the window object.', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Modern JavaScript Essentials'],
            'question_text' => 'What ES6 syntax is used to extract properties from objects directly into individual variables?',
            'option_a' => 'Spreading', 'option_b' => 'Destructuring', 'option_c' => 'Constructing', 'option_d' => 'Mapping', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Modern JavaScript Essentials'],
            'question_text' => 'Which array method creates a new array filled with the results of calling a function on every element?',
            'option_a' => 'forEach()', 'option_b' => 'map()', 'option_c' => 'filter()', 'option_d' => 'reduce()', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Modern JavaScript Essentials'],
            'question_text' => 'What is the scope of variables declared with the legacy "var" keyword?',
            'option_a' => 'Function scope', 'option_b' => 'Block scope', 'option_c' => 'Global scope only', 'option_d' => 'Lexical scope', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Modern JavaScript Essentials'],
            'question_text' => 'What is the evaluated output of typeof null in JavaScript?',
            'option_a' => '"null"', 'option_b' => '"undefined"', 'option_c' => '"object"', 'option_d' => '"function"', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Modern JavaScript Essentials'],
            'question_text' => 'Which operator is used to compare both the value and the type of two variables (strict equality)?',
            'option_a' => '==', 'option_b' => '!=', 'option_c' => '===', 'option_d' => 'equal', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Modern JavaScript Essentials'],
            'question_text' => 'What is the state of a JavaScript Promise when the asynchronous operation has completed successfully?',
            'option_a' => 'pending', 'option_b' => 'fulfilled', 'option_c' => 'rejected', 'option_d' => 'resolved-error', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Modern JavaScript Essentials'],
            'question_text' => 'Which character syntax represents a template literal for string interpolation in ES6?',
            'option_a' => 'Double quotes ("")', 'option_b' => 'Single quotes (\'\')', 'option_c' => 'Parentheses (())', 'option_d' => 'Backticks (``)', 'correct_option' => 'D'
        ],
        [
            'quiz_id' => $quiz_ids['Modern JavaScript Essentials'],
            'question_text' => 'Which keyword is used to pause the execution of an async function until a Promise resolves?',
            'option_a' => 'wait', 'option_b' => 'pause', 'option_c' => 'await', 'option_d' => 'stop', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Modern JavaScript Essentials'],
            'question_text' => 'What is the default value of an uninitialized variable declared with the "let" keyword in JavaScript?',
            'option_a' => 'null', 'option_b' => 'undefined', 'option_c' => 'NaN', 'option_d' => '0', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Modern JavaScript Essentials'],
            'question_text' => 'Which JavaScript method is used to convert a JS object into a JSON string format?',
            'option_a' => 'JSON.stringify()', 'option_b' => 'JSON.parse()', 'option_c' => 'Object.toJSON()', 'option_d' => 'JSON.toString()', 'correct_option' => 'A'
        ],

        
        [
            'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'],
            'question_text' => 'Which SQL join returns all records from the left table and the matched records from the right table?',
            'option_a' => 'INNER JOIN', 'option_b' => 'LEFT JOIN', 'option_c' => 'RIGHT JOIN', 'option_d' => 'FULL JOIN', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'],
            'question_text' => 'Which SQL clause is used to filter group results after grouping has been performed?',
            'option_a' => 'WHERE', 'option_b' => 'HAVING', 'option_c' => 'ORDER BY', 'option_d' => 'GROUP BY', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'],
            'question_text' => 'Which database normalization form focuses on eliminating transitive dependencies?',
            'option_a' => '1NF', 'option_b' => '2NF', 'option_c' => '3NF', 'option_d' => 'BCNF', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'],
            'question_text' => 'Which column constraint guarantees that all values in a column are distinct?',
            'option_a' => 'NOT NULL', 'option_b' => 'DEFAULT', 'option_c' => 'UNIQUE', 'option_d' => 'CHECK', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'],
            'question_text' => 'Which SQL aggregate function is used to calculate the total number of rows matching a criteria?',
            'option_a' => 'COUNT()', 'option_b' => 'SUM()', 'option_c' => 'TOTAL()', 'option_d' => 'AVG()', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'],
            'question_text' => 'Which command immediately removes all data rows from a table without logging individual row deletions?',
            'option_a' => 'DELETE', 'option_b' => 'DROP', 'option_c' => 'TRUNCATE', 'option_d' => 'REMOVE', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'],
            'question_text' => 'What is a column in one table that links to the primary key of another table called?',
            'option_a' => 'Primary Key', 'option_b' => 'Foreign Key', 'option_c' => 'Candidate Key', 'option_d' => 'Composite Key', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'],
            'question_text' => 'In a SQL LIKE query, which wildcard character matches zero or more characters?',
            'option_a' => '%', 'option_b' => '_', 'option_c' => '*', 'option_d' => '?', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'],
            'question_text' => 'Which SQL clause is used to sort the queried result set in ascending or descending order?',
            'option_a' => 'SORT BY', 'option_b' => 'GROUP BY', 'option_c' => 'ORDER BY', 'option_d' => 'ARRANGE BY', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'],
            'question_text' => 'Which DDL SQL command is used to add, delete, or modify columns in an existing table structure?',
            'option_a' => 'UPDATE', 'option_b' => 'ALTER TABLE', 'option_c' => 'CHANGE TABLE', 'option_d' => 'MODIFY STRUCTURE', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'],
            'question_text' => 'Which SQL keyword is used to eliminate duplicate records from a query result set?',
            'option_a' => 'DISTINCT', 'option_b' => 'UNIQUE', 'option_c' => 'DIFFERENT', 'option_d' => 'FILTER', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'],
            'question_text' => 'What does the acronym ACID stand for in relational database transactions?',
            'option_a' => 'Action, Consistency, Integrity, Delivery', 'option_b' => 'Atomicity, Consistency, Isolation, Durability', 'option_c' => 'Access, Connection, Indexing, Database', 'option_d' => 'Algorithm, Cache, Iteration, Deployment', 'correct_option' => 'B'
        ],

        
        [
            'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'],
            'question_text' => 'Which layer of the TCP/IP model handles the logical addressing and routing of packets?',
            'option_a' => 'Application Layer', 'option_b' => 'Transport Layer', 'option_c' => 'Internet Layer', 'option_d' => 'Network Access Layer', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'],
            'question_text' => 'Which protocol is connectionless, prioritizing transmission speed over guaranteed packet delivery?',
            'option_a' => 'TCP', 'option_b' => 'HTTP', 'option_c' => 'UDP', 'option_d' => 'FTP', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'],
            'question_text' => 'What is the role of the recursive resolver in the Domain Name System (DNS)?',
            'option_a' => 'It holds the authoritative IP maps for all global domains.', 'option_b' => 'It acts as the client-side intermediary that queries other nameservers to resolve a domain.', 'option_c' => 'It is the root directory pointing to top-level domains.', 'option_d' => 'It assigns IP addresses to devices on a local network.', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'],
            'question_text' => 'What is the default port number used by HTTPS (Secure HTTP)?',
            'option_a' => '80', 'option_b' => '8080', 'option_c' => '22', 'option_d' => '443', 'correct_option' => 'D'
        ],
        [
            'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'],
            'question_text' => 'Which network device inspects incoming packets, determines their destination, and forwards them accordingly between separate networks?',
            'option_a' => 'Hub', 'option_b' => 'Repeater', 'option_c' => 'Router', 'option_d' => 'Access Point', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'],
            'question_text' => 'Which utility command is used to trace the route taken by packets across an IP network and check hop latency?',
            'option_a' => 'ping', 'option_b' => 'traceroute / tracert', 'option_c' => 'ipconfig', 'option_d' => 'netstat', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'],
            'question_text' => 'Which Transport Layer protocol is connection-oriented, ensuring data packets are received in order without errors?',
            'option_a' => 'UDP', 'option_b' => 'TCP', 'option_c' => 'IP', 'option_d' => 'ICMP', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'],
            'question_text' => 'What is the standard loopback IP address representing the localhost device?',
            'option_a' => '127.0.0.1', 'option_b' => '192.168.1.1', 'option_c' => '10.0.0.1', 'option_d' => '255.255.255.255', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'],
            'question_text' => 'Which protocol is responsible for dynamically assigning IP addresses to host devices on a local network?',
            'option_a' => 'DNS', 'option_b' => 'DHCP', 'option_c' => 'ARP', 'option_d' => 'NAT', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'],
            'question_text' => 'How many bits make up an IPv6 address configuration?',
            'option_a' => '32 bits', 'option_b' => '64 bits', 'option_c' => '128 bits', 'option_d' => '256 bits', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'],
            'question_text' => 'What is the standard subnet mask configuration for a Class C IP network?',
            'option_a' => '255.0.0.0', 'option_b' => '255.255.0.0', 'option_c' => '255.255.255.0', 'option_d' => '255.255.255.255', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'],
            'question_text' => 'Which networking protocol translates private local IP addresses to a public IP to facilitate internet communications?',
            'option_a' => 'NAT', 'option_b' => 'DHCP', 'option_c' => 'DNS', 'option_d' => 'ARP', 'correct_option' => 'A'
        ],

        
        [
            'quiz_id' => $quiz_ids['Software Engineering Principles'],
            'question_text' => 'Which Software Development Life Cycle (SDLC) model is highly iterative and features short cycles called sprints?',
            'option_a' => 'Waterfall', 'option_b' => 'Agile/Scrum', 'option_c' => 'Spiral', 'option_d' => 'V-Model', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Software Engineering Principles'],
            'question_text' => 'In SOLID object-oriented design, what does the "S" stand for?',
            'option_a' => 'Single Responsibility Principle', 'option_b' => 'Subclass Substitution Principle', 'option_c' => 'Software Separation Principle', 'option_d' => 'Static Scope Principle', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Software Engineering Principles'],
            'question_text' => 'Which testing level is designed to verify that individual code modules function correctly when tested in isolation?',
            'option_a' => 'Integration Testing', 'option_b' => 'Unit Testing', 'option_c' => 'System Testing', 'option_d' => 'User Acceptance Testing (UAT)', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Software Engineering Principles'],
            'question_text' => 'Which Git command is used to integrate changes from a source branch into the target branch?',
            'option_a' => 'git clone', 'option_b' => 'git branch', 'option_c' => 'git merge', 'option_d' => 'git checkout', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Software Engineering Principles'],
            'question_text' => 'Which software design pattern restricts instantiation of a class to a single instance and provides a global access point?',
            'option_a' => 'Singleton Pattern', 'option_b' => 'Factory Pattern', 'option_c' => 'Observer Pattern', 'option_d' => 'Decorator Pattern', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Software Engineering Principles'],
            'question_text' => 'What is the visual model called that maps the data flow or processing steps within a software application?',
            'option_a' => 'UML Diagram', 'option_b' => 'Flowchart', 'option_c' => 'Use Case Diagram', 'option_d' => 'Entity Relationship Diagram', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Software Engineering Principles'],
            'question_text' => 'Which SDLC phase focuses on collecting, documenting, and modeling user needs and software requirements?',
            'option_a' => 'Coding/Implementation', 'option_b' => 'Testing', 'option_c' => 'Requirements Analysis', 'option_d' => 'Deployment', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Software Engineering Principles'],
            'question_text' => 'Which term describes software components that have minimal dependencies on one another, making them easy to test and swap?',
            'option_a' => 'Loosely Coupled', 'option_b' => 'Highly Cohesive', 'option_c' => 'Tightly Coupled', 'option_d' => 'Monolithic', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Software Engineering Principles'],
            'question_text' => 'Which Git command displays a structured list of past commits along with their messages, hashes, and dates?',
            'option_a' => 'git status', 'option_b' => 'git log', 'option_c' => 'git diff', 'option_d' => 'git show', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Software Engineering Principles'],
            'question_text' => 'What is the type of testing called that verifies existing functionality has not been broken after new changes or fixes are introduced?',
            'option_a' => 'Alpha Testing', 'option_b' => 'Stress Testing', 'option_c' => 'Regression Testing', 'option_d' => 'Sanity Testing', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Software Engineering Principles'],
            'question_text' => 'Which development practice mandates writing automated test scripts before writing the actual feature code?',
            'option_a' => 'Behavior-Driven Development (BDD)', 'option_b' => 'Test-Driven Development (TDD)', 'option_c' => 'Agile Programming', 'option_d' => 'Continuous Integration', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Software Engineering Principles'],
            'question_text' => 'Which UML diagram displays the static architecture of classes, attributes, methods, and inheritance links in a system?',
            'option_a' => 'Class Diagram', 'option_b' => 'Sequence Diagram', 'option_c' => 'State Diagram', 'option_d' => 'Activity Diagram', 'correct_option' => 'A'
        ],

        
        [
            'quiz_id' => $quiz_ids['Operating Systems Fundamentals'],
            'question_text' => 'What is an active, executing instance of a program loaded in the system memory called?',
            'option_a' => 'Daemon', 'option_b' => 'Process', 'option_c' => 'Instruction', 'option_d' => 'Thread', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Operating Systems Fundamentals'],
            'question_text' => 'Which basic CPU scheduling algorithm executes processes in the exact chronological order of their arrival?',
            'option_a' => 'First-Come, First-Served (FCFS)', 'option_b' => 'Shortest Job First (SJF)', 'option_c' => 'Round Robin', 'option_d' => 'Priority Scheduling', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Operating Systems Fundamentals'],
            'question_text' => 'What is the system state called when multiple processes are stuck waiting indefinitely for resources held by each other?',
            'option_a' => 'Starvation', 'option_b' => 'Context Switch', 'option_c' => 'Deadlock', 'option_d' => 'Segmentation', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Operating Systems Fundamentals'],
            'question_text' => 'What core component of an operating system sits between the system hardware and application software to manage system resources?',
            'option_a' => 'Shell', 'option_b' => 'Kernel', 'option_c' => 'BIOS', 'option_d' => 'Compiler', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Operating Systems Fundamentals'],
            'question_text' => 'Which memory management process maps virtual memory blocks to physical RAM allocations using fixed-size blocks?',
            'option_a' => 'Fragmentation', 'option_b' => 'Swapping', 'option_c' => 'Paging', 'option_d' => 'Partitioning', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Operating Systems Fundamentals'],
            'question_text' => 'What type of memory is volatile, extremely fast, and embedded directly inside or next to the CPU processor?',
            'option_a' => 'Cache Memory', 'option_b' => 'Flash Memory', 'option_c' => 'Virtual Memory', 'option_d' => 'SSD', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Operating Systems Fundamentals'],
            'question_text' => 'What interface mechanism is used by a process to request a service (such as file operations) from the OS kernel?',
            'option_a' => 'API call', 'option_b' => 'System Call', 'option_c' => 'Interrupt Request', 'option_d' => 'Virtual Query', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Operating Systems Fundamentals'],
            'question_text' => 'What is the procedure of saving the execution state of a running process and loading the state of another process to the CPU called?',
            'option_a' => 'Process Migration', 'option_b' => 'Context Switch', 'option_c' => 'Interrupt Handling', 'option_d' => 'State Swapping', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Operating Systems Fundamentals'],
            'question_text' => 'Which scheduling algorithm is preemptive and allocates a fixed time quantum to each active process in a cyclic pattern?',
            'option_a' => 'SJF', 'option_b' => 'Multilevel Queue', 'option_c' => 'Round Robin', 'option_d' => 'Priority Preemptive', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Operating Systems Fundamentals'],
            'question_text' => 'What is a lightweight unit of CPU execution that lives within a parent process, sharing its memory and resources?',
            'option_a' => 'Thread', 'option_b' => 'Subprocess', 'option_c' => 'Worker', 'option_d' => 'Interrupt', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Operating Systems Fundamentals'],
            'question_text' => 'Which data structure maps human-readable file titles to physical data blocks stored on the disk?',
            'option_a' => 'File Allocation Table', 'option_b' => 'Inode Table / File System Index', 'option_c' => 'Memory Map', 'option_d' => 'Block directory', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Operating Systems Fundamentals'],
            'question_text' => 'What type of interrupt occurs when a process attempts to access a virtual memory page that is not currently mapped into physical RAM?',
            'option_a' => 'Segmentation Fault', 'option_b' => 'Stack Overflow', 'option_c' => 'Page Fault', 'option_d' => 'Core Dump', 'correct_option' => 'C'
        ],

        
        [
            'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'],
            'question_text' => 'Which cloud service model offers hardware resources like virtual machines, storage, and networking over the internet?',
            'option_a' => 'IaaS', 'option_b' => 'PaaS', 'option_c' => 'SaaS', 'option_d' => 'FaaS', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'],
            'question_text' => 'What service model is AWS Lambda or Google Cloud Functions where developers write code without managing servers?',
            'option_a' => 'IaaS', 'option_b' => 'Serverless / FaaS', 'option_c' => 'SaaS', 'option_d' => 'PaaS', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'],
            'question_text' => 'Which cloud model combines private cloud resources with public cloud infrastructure?',
            'option_a' => 'Public Cloud', 'option_b' => 'Private Cloud', 'option_c' => 'Hybrid Cloud', 'option_d' => 'Community Cloud', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'],
            'question_text' => 'What is the process of creating multiple virtual environments on a single physical server hardware?',
            'option_a' => 'Virtualization', 'option_b' => 'Grid Computing', 'option_c' => 'Clustering', 'option_d' => 'Containerization', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'],
            'question_text' => 'In cloud economics, what term describes shifting from upfront capital expenses to ongoing operational expenses?',
            'option_a' => 'CapEx', 'option_b' => 'OpEx', 'option_c' => 'ROI', 'option_d' => 'TCO', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'],
            'question_text' => 'Which AWS service provides resizable virtual server instances in the cloud?',
            'option_a' => 'EC2', 'option_b' => 'S3', 'option_c' => 'RDS', 'option_d' => 'Lambda', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'],
            'question_text' => 'What is a key benefit of cloud elasticity?',
            'option_a' => 'High latency connectivity', 'option_b' => 'Fixed billing structures', 'option_c' => 'Automatically scaling resources up or down based on demand', 'option_d' => 'Manual hardware configurations', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'],
            'question_text' => 'In cloud database services, what does a fully-managed service handle?',
            'option_a' => 'Writing user query code', 'option_b' => 'Backups, patching, and scaling automatically', 'option_c' => 'App front-end layout', 'option_d' => 'Physical power supply setups', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'],
            'question_text' => 'Which service model provides a ready-to-use software application accessed via web browser?',
            'option_a' => 'IaaS', 'option_b' => 'PaaS', 'option_c' => 'DaaS', 'option_d' => 'SaaS', 'correct_option' => 'D'
        ],
        [
            'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'],
            'question_text' => 'What is a region in cloud infrastructure terminology?',
            'option_a' => 'A geographical area containing multiple isolated Availability Zones', 'option_b' => 'A single physical server rack', 'option_c' => 'A localized database cache', 'option_d' => 'A virtual subnet', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'],
            'question_text' => 'What is the primary role of a Hypervisor in virtualization?',
            'option_a' => 'To route IP packets', 'option_b' => 'To create and run virtual machines on physical host hardware', 'option_c' => 'To compile source code', 'option_d' => 'To balance user traffic loads', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'],
            'question_text' => 'What does CDN stand for in cloud infrastructure?',
            'option_a' => 'Content Delivery Network', 'option_b' => 'Cloud Database Node', 'option_c' => 'Centralised Domain Name', 'option_d' => 'Computer Device Network', 'correct_option' => 'A'
        ],

        
        [
            'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'],
            'question_text' => 'What type of malware encrypts user files and demands payment for the decryption key?',
            'option_a' => 'Spyware', 'option_b' => 'Ransomware', 'option_c' => 'Trojan Horse', 'option_d' => 'Adware', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'],
            'question_text' => 'Which cryptographic method uses a single shared key for both encryption and decryption of data?',
            'option_a' => 'Symmetric Encryption', 'option_b' => 'Asymmetric Encryption', 'option_c' => 'Hashing', 'option_d' => 'Public Key Cryptography', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'],
            'question_text' => 'What is a security mechanism that acts as a barrier to inspect and filter incoming and outgoing network traffic?',
            'option_a' => 'Router', 'option_b' => 'Switch', 'option_c' => 'Firewall', 'option_d' => 'Gateway', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'],
            'question_text' => 'Which protocol provides secure, encrypted web browsing communications?',
            'option_a' => 'HTTP', 'option_b' => 'HTTPS', 'option_c' => 'FTP', 'option_d' => 'SMTP', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'],
            'question_text' => 'What type of attack attempts to redirect users to a fake website to steal their credentials?',
            'option_a' => 'Phishing', 'option_b' => 'DDoS', 'option_c' => 'SQL Injection', 'option_d' => 'Brute Force', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'],
            'question_text' => 'Which hashing algorithm is commonly used to verify file integrity and generate 256-bit hashes?',
            'option_a' => 'MD5', 'option_b' => 'SHA-1', 'option_c' => 'SHA-256', 'option_d' => 'AES-256', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'],
            'question_text' => 'What security principle states that users should only be granted the minimum permissions necessary to perform their jobs?',
            'option_a' => 'Principle of Least Effort', 'option_b' => 'Principle of Least Privilege', 'option_c' => 'Principle of Separation', 'option_d' => 'Principle of Open Design', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'],
            'question_text' => 'What type of security threat involves an attacker intercepting communication between two parties without their knowledge?',
            'option_a' => 'Man-in-the-Middle (MitM)', 'option_b' => 'Phishing', 'option_c' => 'Social Engineering', 'option_d' => 'Buffer Overflow', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'],
            'question_text' => 'Which cryptography system uses a public key to encrypt data and a private key to decrypt it?',
            'option_a' => 'Symmetric Encryption', 'option_b' => 'Asymmetric Encryption', 'option_c' => 'One-time Pad', 'option_d' => 'Block Cipher', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'],
            'question_text' => 'What does MFA stand for in security access controls?',
            'option_a' => 'Multiple File Access', 'option_b' => 'Mail Forwarding Agent', 'option_c' => 'Multi-Factor Authentication', 'option_d' => 'Matrix Filter Allocation', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'],
            'question_text' => 'Which attack model floods a targeted server with spoofed traffic from multiple distributed machines to make it unavailable?',
            'option_a' => 'DDoS', 'option_b' => 'XSS', 'option_c' => 'SQLi', 'option_d' => 'IP Spoofing', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'],
            'question_text' => 'What is a network security system that detects and logs unauthorized access attempts or suspicious traffic patterns?',
            'option_a' => 'IPS', 'option_b' => 'IDS', 'option_c' => 'WAF', 'option_d' => 'VPN', 'correct_option' => 'B'
        ],

        
        [
            'quiz_id' => $quiz_ids['Data Structures Fundamentals'],
            'question_text' => 'Which data structure follows the Last-In, First-Out (LIFO) principle?',
            'option_a' => 'Queue', 'option_b' => 'Stack', 'option_c' => 'Linked List', 'option_d' => 'Binary Tree', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Data Structures Fundamentals'],
            'question_text' => 'What is the time complexity to access an element by its index in a standard array?',
            'option_a' => 'O(1)', 'option_b' => 'O(log n)', 'option_c' => 'O(n)', 'option_d' => 'O(n log n)', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Data Structures Fundamentals'],
            'question_text' => 'Which data structure consists of nodes where each node contains data and a pointer to the next node?',
            'option_a' => 'Array', 'option_b' => 'Stack', 'option_c' => 'Linked List', 'option_d' => 'Hash Table', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Data Structures Fundamentals'],
            'question_text' => 'Which data structure follows the First-In, First-Out (FIFO) principle?',
            'option_a' => 'Stack', 'option_b' => 'Queue', 'option_c' => 'Tree', 'option_d' => 'Heap', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Data Structures Fundamentals'],
            'question_text' => 'In a binary search tree (BST), what is the maximum number of child nodes any node can have?',
            'option_a' => '2', 'option_b' => '1', 'option_c' => '4', 'option_d' => 'Unlimited', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Data Structures Fundamentals'],
            'question_text' => 'Which data structure maps keys to values using a hashing function?',
            'option_a' => 'Linked List', 'option_b' => 'Hash Table', 'option_c' => 'Stack', 'option_d' => 'Graph', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Data Structures Fundamentals'],
            'question_text' => 'What is a collection of vertices (nodes) and edges that connect them called?',
            'option_a' => 'Tree', 'option_b' => 'Queue', 'option_c' => 'Graph', 'option_d' => 'Heap', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Data Structures Fundamentals'],
            'question_text' => 'Which stack operation is used to retrieve the top element without removing it?',
            'option_a' => 'Pop', 'option_b' => 'Peek / Top', 'option_c' => 'Push', 'option_d' => 'Clear', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Data Structures Fundamentals'],
            'question_text' => 'What type of queue allows elements to be inserted or deleted from both ends?',
            'option_a' => 'Priority Queue', 'option_b' => 'Circular Queue', 'option_c' => 'Linear Queue', 'option_d' => 'Deque (Double-Ended Queue)', 'correct_option' => 'D'
        ],
        [
            'quiz_id' => $quiz_ids['Data Structures Fundamentals'],
            'question_text' => 'In a tree data structure, what is the topmost node that has no parent called?',
            'option_a' => 'Root', 'option_b' => 'Leaf', 'option_c' => 'Branch', 'option_d' => 'Child', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Data Structures Fundamentals'],
            'question_text' => 'Which data structure is ideal for representing hierarchical relationships (like file directories)?',
            'option_a' => 'Stack', 'option_b' => 'Tree', 'option_c' => 'Array', 'option_d' => 'Graph', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Data Structures Fundamentals'],
            'question_text' => 'What is the main drawback of a standard array data structure?',
            'option_a' => 'Slow access times', 'option_b' => 'Requires pointer overhead', 'option_c' => 'Fixed size allocation in memory', 'option_d' => 'Cannot hold values', 'correct_option' => 'C'
        ],

        
        [
            'quiz_id' => $quiz_ids['Algorithms & Complexity'],
            'question_text' => 'What notation is used to describe the worst-case performance or complexity of an algorithm?',
            'option_a' => 'Little o', 'option_b' => 'Big O Notation', 'option_c' => 'Omega', 'option_d' => 'Theta', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Algorithms & Complexity'],
            'question_text' => 'Which sorting algorithm repeatedly steps through the list, compares adjacent elements, and swaps them if they are in the wrong order?',
            'option_a' => 'Bubble Sort', 'option_b' => 'Merge Sort', 'option_c' => 'Quick Sort', 'option_d' => 'Insertion Sort', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Algorithms & Complexity'],
            'question_text' => 'What is the average time complexity of a Binary Search algorithm on a sorted array?',
            'option_a' => 'O(1)', 'option_b' => 'O(n)', 'option_c' => 'O(log n)', 'option_d' => 'O(n log n)', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Algorithms & Complexity'],
            'question_text' => 'What programming technique calls a function within itself to solve a smaller subproblem?',
            'option_a' => 'Iteration', 'option_b' => 'Recursion', 'option_c' => 'Compilation', 'option_d' => 'Inheritance', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Algorithms & Complexity'],
            'question_text' => 'What is the worst-case time complexity of the Quick Sort algorithm?',
            'option_a' => 'O(n)', 'option_b' => 'O(n log n)', 'option_c' => 'O(n^2)', 'option_d' => 'O(log n)', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Algorithms & Complexity'],
            'question_text' => 'Which search algorithm checks every element of a list sequentially until a match is found?',
            'option_a' => 'Linear Search', 'option_b' => 'Binary Search', 'option_c' => 'Hashing Search', 'option_d' => 'DFS', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Algorithms & Complexity'],
            'question_text' => 'What paradigm does Merge Sort use by splitting the array in half, sorting recursively, and merging?',
            'option_a' => 'Dynamic Programming', 'option_b' => 'Divide and Conquer', 'option_c' => 'Greedy Paradigm', 'option_d' => 'Backtracking', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Algorithms & Complexity'],
            'question_text' => 'What is the best-case time complexity of the standard Bubble Sort algorithm?',
            'option_a' => 'O(n)', 'option_b' => 'O(log n)', 'option_c' => 'O(n^2)', 'option_d' => 'O(1)', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Algorithms & Complexity'],
            'question_text' => 'Which algorithm finds the shortest path between nodes in a graph with non-negative edge weights?',
            'option_a' => 'Prim\'s Algorithm', 'option_b' => 'Kruskal\'s Algorithm', 'option_c' => 'Dijkstra\'s Algorithm', 'option_d' => 'Floyd-Warshall', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Algorithms & Complexity'],
            'question_text' => 'What is the term for an algorithm that makes the locally optimal choice at each stage with the hope of finding a global optimum?',
            'option_a' => 'Dynamic programming', 'option_b' => 'Greedy Algorithm', 'option_c' => 'Backtracking', 'option_d' => 'Brute Force', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Algorithms & Complexity'],
            'question_text' => 'Which concept describes an algorithm\'s memory usage relative to the size of the input?',
            'option_a' => 'Space Complexity', 'option_b' => 'Time Complexity', 'option_c' => 'Data structures', 'option_d' => 'Execution Stack', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Algorithms & Complexity'],
            'question_text' => 'What is the time complexity of inserting a node at the head of a singly linked list?',
            'option_a' => 'O(n)', 'option_b' => 'O(1)', 'option_c' => 'O(log n)', 'option_d' => 'O(n log n)', 'correct_option' => 'B'
        ],

        
        [
            'quiz_id' => $quiz_ids['OOP Paradigms'],
            'question_text' => 'Which OOP principle bundles data (variables) and the methods that operate on them into a single unit (class)?',
            'option_a' => 'Abstraction', 'option_b' => 'Encapsulation', 'option_c' => 'Inheritance', 'option_d' => 'Polymorphism', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['OOP Paradigms'],
            'question_text' => 'What OOP concept allows a subclass to inherit attributes and methods from a parent class?',
            'option_a' => 'Inheritance', 'option_b' => 'Polymorphism', 'option_c' => 'Encapsulation', 'option_d' => 'Interface', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['OOP Paradigms'],
            'question_text' => 'What term describes the ability of different objects to respond to the same method call in different ways?',
            'option_a' => 'Overloading', 'option_b' => 'Inheritance', 'option_c' => 'Polymorphism', 'option_d' => 'Abstraction', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['OOP Paradigms'],
            'question_text' => 'What is a blueprint or template from which individual objects are created called?',
            'option_a' => 'Module', 'option_b' => 'Class', 'option_c' => 'Struct', 'option_d' => 'Method', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['OOP Paradigms'],
            'question_text' => 'Which principle focuses on hiding internal implementation details and showing only essential features?',
            'option_a' => 'Abstraction', 'option_b' => 'Encapsulation', 'option_c' => 'Polymorphism', 'option_d' => 'Inheritance', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['OOP Paradigms'],
            'question_text' => 'What is a special method called automatically when an object is instantiated from a class?',
            'option_a' => 'Destructor', 'option_b' => 'Constructor', 'option_c' => 'Initializer', 'option_d' => 'Alloc', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['OOP Paradigms'],
            'question_text' => 'Which keyword is commonly used in languages like Java or C++ to refer to the current instance of the class?',
            'option_a' => 'this', 'option_b' => 'self', 'option_c' => 'parent', 'option_d' => 'super', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['OOP Paradigms'],
            'question_text' => 'What type of class cannot be instantiated directly and is meant to be subclassed?',
            'option_a' => 'Static Class', 'option_b' => 'Abstract Class', 'option_c' => 'Interface Class', 'option_d' => 'Final Class', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['OOP Paradigms'],
            'question_text' => 'What mechanism allows a subclass to provide a specific implementation of a method that is already defined in its superclass?',
            'option_a' => 'Method Overloading', 'option_b' => 'Method Hiding', 'option_c' => 'Method Overriding', 'option_d' => 'Multiple Inheritance', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['OOP Paradigms'],
            'question_text' => 'What feature allows multiple methods in the same class to have the same name but different parameters?',
            'option_a' => 'Method Overloading', 'option_b' => 'Method Overriding', 'option_c' => 'Method Hiding', 'option_d' => 'Polymorphism', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['OOP Paradigms'],
            'question_text' => 'What is an instance of a class called?',
            'option_a' => 'Function', 'option_b' => 'Object', 'option_c' => 'Blueprint', 'option_d' => 'Pointer', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['OOP Paradigms'],
            'question_text' => 'In OOP, what does a public access modifier mean?',
            'option_a' => 'Accessible only within the same package', 'option_b' => 'Accessible only by subclasses', 'option_c' => 'The member is accessible from any other class', 'option_d' => 'Locked by the system', 'correct_option' => 'C'
        ],

        
        [
            'quiz_id' => $quiz_ids['Git Version Control'],
            'question_text' => 'Which command initializes a new local Git repository in the current directory?',
            'option_a' => 'git clone', 'option_b' => 'git init', 'option_c' => 'git start', 'option_d' => 'git config', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Git Version Control'],
            'question_text' => 'Where does Git store the files that have been modified but are not yet committed?',
            'option_a' => 'Staging Area', 'option_b' => 'Working Directory', 'option_c' => 'Repository', 'option_d' => 'Branch', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Git Version Control'],
            'question_text' => 'What command records your staged changes to the repository history?',
            'option_a' => 'git push', 'option_b' => 'git add', 'option_c' => 'git commit', 'option_d' => 'git stage', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Git Version Control'],
            'question_text' => 'Which Git command copies an existing remote repository onto your local machine?',
            'option_a' => 'git copy', 'option_b' => 'git clone', 'option_c' => 'git pull', 'option_d' => 'git checkout', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Git Version Control'],
            'question_text' => 'What command is used to retrieve changes from a remote repository and immediately merge them into your current local branch?',
            'option_a' => 'git pull', 'option_b' => 'git fetch', 'option_c' => 'git push', 'option_d' => 'git merge', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Git Version Control'],
            'question_text' => 'What is the default primary branch name in modern Git repositories?',
            'option_a' => 'master', 'option_b' => 'main', 'option_c' => 'primary', 'option_d' => 'root', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Git Version Control'],
            'question_text' => 'Which command shows which files are currently untracked, staged, or modified?',
            'option_a' => 'git status', 'option_b' => 'git log', 'option_c' => 'git show', 'option_d' => 'git diff', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Git Version Control'],
            'question_text' => 'How do you create a new branch named "feature-login" and immediately switch to it?',
            'option_a' => 'git branch feature-login', 'option_b' => 'git checkout feature-login', 'option_c' => 'git checkout -b feature-login', 'option_d' => 'git switch feature-login', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Git Version Control'],
            'question_text' => 'What command shows a line-by-line comparison of changes that are not yet staged?',
            'option_a' => 'git status', 'option_b' => 'git diff', 'option_c' => 'git log', 'option_d' => 'git show', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Git Version Control'],
            'question_text' => 'Which command uploads your local commits to a remote repository?',
            'option_a' => 'git push', 'option_b' => 'git pull', 'option_c' => 'git upload', 'option_d' => 'git commit', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Git Version Control'],
            'question_text' => 'What is a Git conflict?',
            'option_a' => 'A bug in Git software', 'option_b' => 'Missing database logs', 'option_c' => 'An issue when two branches have incompatible changes in the same file block', 'option_d' => 'Network loss during push', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Git Version Control'],
            'question_text' => 'What Git file defines which local files or folders should never be tracked or committed?',
            'option_a' => '.gitinclude', 'option_b' => '.gitignore', 'option_c' => '.gitconfig', 'option_d' => '.env', 'correct_option' => 'B'
        ],

        
        [
            'quiz_id' => $quiz_ids['Machine Learning Concepts'],
            'question_text' => 'What type of machine learning trains models using labeled training data?',
            'option_a' => 'Unsupervised Learning', 'option_b' => 'Supervised Learning', 'option_c' => 'Reinforcement Learning', 'option_d' => 'Deep Learning', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Machine Learning Concepts'],
            'question_text' => 'What ML task aims to predict a continuous numerical value (such as housing prices)?',
            'option_a' => 'Regression', 'option_b' => 'Classification', 'option_c' => 'Clustering', 'option_d' => 'Dimensionality Reduction', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Machine Learning Concepts'],
            'question_text' => 'What type of ML groups unlabeled data into distinct clusters based on similarities?',
            'option_a' => 'Regression', 'option_b' => 'Unsupervised Learning', 'option_c' => 'Classification', 'option_d' => 'Supervised Learning', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Machine Learning Concepts'],
            'question_text' => 'Which ML algorithm mimics the structure and function of the human brain\'s interconnected nodes?',
            'option_a' => 'Decision Tree', 'option_b' => 'Support Vector Machine', 'option_c' => 'Neural Network', 'option_d' => 'K-Means Clustering', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Machine Learning Concepts'],
            'question_text' => 'What problem occurs when a model learns the training data too well, including its noise, causing it to perform poorly on new data?',
            'option_a' => 'Underfitting', 'option_b' => 'Overfitting', 'option_c' => 'Bias Error', 'option_d' => 'Convergence', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Machine Learning Concepts'],
            'question_text' => 'What is the input data used to train and calibrate a machine learning model called?',
            'option_a' => 'Training Set', 'option_b' => 'Test Set', 'option_c' => 'Validation Log', 'option_d' => 'Target Array', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Machine Learning Concepts'],
            'question_text' => 'Which learning style uses rewards and punishments to train an agent to make decisions in an environment?',
            'option_a' => 'Supervised Learning', 'option_b' => 'Unsupervised Learning', 'option_c' => 'Reinforcement Learning', 'option_d' => 'Semi-supervised Learning', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Machine Learning Concepts'],
            'question_text' => 'In ML, what is a feature?',
            'option_a' => 'A bug fix', 'option_b' => 'An individual measurable property or variable of a dataset', 'option_c' => 'The predicted output value', 'option_d' => 'A validation metric', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Machine Learning Concepts'],
            'question_text' => 'Which classification algorithm splits data based on feature values to form a tree-like model of decisions?',
            'option_a' => 'Decision Tree', 'option_b' => 'Linear Regression', 'option_c' => 'Random Clustering', 'option_d' => 'Perceptron', 'correct_option' => 'A'
        ],
        [
            'quiz_id' => $quiz_ids['Machine Learning Concepts'],
            'question_text' => 'What metrics evaluate a classification model\'s performance?',
            'option_a' => 'Mean Squared Error', 'option_b' => 'R-squared', 'option_c' => 'Precision, Recall, F1-Score', 'option_d' => 'Absolute Margin Deviation', 'correct_option' => 'C'
        ],
        [
            'quiz_id' => $quiz_ids['Machine Learning Concepts'],
            'question_text' => 'What phase of machine learning involves running new, unseen data through a trained model to make predictions?',
            'option_a' => 'Feature engineering', 'option_b' => 'Inference', 'option_c' => 'Preprocessing', 'option_d' => 'Validation tuning', 'correct_option' => 'B'
        ],
        [
            'quiz_id' => $quiz_ids['Machine Learning Concepts'],
            'question_text' => 'What is the process of scaling or normalizing features in a dataset to help models converge faster?',
            'option_a' => 'Feature Scaling', 'option_b' => 'Regularization', 'option_c' => 'Dimensionality Reduction', 'option_d' => 'Cross Validation', 'correct_option' => 'A'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($questions as $qs) {
        $stmt->execute([
            $qs['quiz_id'], $qs['question_text'],
            $qs['option_a'], $qs['option_b'], $qs['option_c'], $qs['option_d'],
            $qs['correct_option']
        ]);
        echo "  - Added question to quiz ID " . $qs['quiz_id'] . "\n";
    }

    
    echo "\n[Info] Seeding Quiz Results (Test Scores)...\n";
    $results = [
        ['user_id' => $user_ids['bhavya'], 'quiz_id' => $quiz_ids['HTML & CSS Foundations'], 'score' => 10, 'total_questions' => 12],
        ['user_id' => $user_ids['bhavya'], 'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'], 'score' => 9, 'total_questions' => 12],
        ['user_id' => $user_ids['bhavya'], 'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'], 'score' => 7, 'total_questions' => 12],
        ['user_id' => $user_ids['bhavya'], 'quiz_id' => $quiz_ids['Software Engineering Principles'], 'score' => 10, 'total_questions' => 12],
        ['user_id' => $user_ids['bhavya'], 'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'], 'score' => 11, 'total_questions' => 12],
        ['user_id' => $user_ids['bhavya'], 'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'], 'score' => 8, 'total_questions' => 12],

        ['user_id' => $user_ids['sumit'], 'quiz_id' => $quiz_ids['HTML & CSS Foundations'], 'score' => 8, 'total_questions' => 12],
        ['user_id' => $user_ids['sumit'], 'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'], 'score' => 11, 'total_questions' => 12],
        ['user_id' => $user_ids['sumit'], 'quiz_id' => $quiz_ids['Modern JavaScript Essentials'], 'score' => 12, 'total_questions' => 12],
        ['user_id' => $user_ids['sumit'], 'quiz_id' => $quiz_ids['Operating Systems Fundamentals'], 'score' => 9, 'total_questions' => 12],
        ['user_id' => $user_ids['sumit'], 'quiz_id' => $quiz_ids['Data Structures Fundamentals'], 'score' => 10, 'total_questions' => 12],
        ['user_id' => $user_ids['sumit'], 'quiz_id' => $quiz_ids['Algorithms & Complexity'], 'score' => 8, 'total_questions' => 12],

        ['user_id' => $user_ids['kirtan'], 'quiz_id' => $quiz_ids['HTML & CSS Foundations'], 'score' => 5, 'total_questions' => 12],
        ['user_id' => $user_ids['kirtan'], 'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'], 'score' => 7, 'total_questions' => 12],
        ['user_id' => $user_ids['kirtan'], 'quiz_id' => $quiz_ids['Software Engineering Principles'], 'score' => 8, 'total_questions' => 12],
        ['user_id' => $user_ids['kirtan'], 'quiz_id' => $quiz_ids['OOP Paradigms'], 'score' => 9, 'total_questions' => 12],
        ['user_id' => $user_ids['kirtan'], 'quiz_id' => $quiz_ids['Git Version Control'], 'score' => 10, 'total_questions' => 12],

        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['HTML & CSS Foundations'], 'score' => 11, 'total_questions' => 12],
        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['SQL Queries & Relational DBs'], 'score' => 10, 'total_questions' => 12],
        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['Modern JavaScript Essentials'], 'score' => 9, 'total_questions' => 12],
        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['TCP/IP & DNS Networking Basics'], 'score' => 11, 'total_questions' => 12],
        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['Software Engineering Principles'], 'score' => 11, 'total_questions' => 12],
        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['Operating Systems Fundamentals'], 'score' => 10, 'total_questions' => 12],
        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['Cloud Computing Fundamentals'], 'score' => 12, 'total_questions' => 12],
        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['Cybersecurity & Cryptography'], 'score' => 10, 'total_questions' => 12],
        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['Data Structures Fundamentals'], 'score' => 11, 'total_questions' => 12],
        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['Algorithms & Complexity'], 'score' => 9, 'total_questions' => 12],
        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['OOP Paradigms'], 'score' => 11, 'total_questions' => 12],
        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['Git Version Control'], 'score' => 12, 'total_questions' => 12],
        ['user_id' => $user_ids['meet'], 'quiz_id' => $quiz_ids['Machine Learning Concepts'], 'score' => 8, 'total_questions' => 12]
    ];

    $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, quiz_id, score, total_questions, created_at) VALUES (?, ?, ?, ?, ?)");
    foreach ($results as $res) {
        $random_day = rand(1, 27);
        $random_date = sprintf('2026-06-%02d %02d:%02d:%02d', $random_day, rand(8, 20), rand(0, 59), rand(0, 59));
        $stmt->execute([$res['user_id'], $res['quiz_id'], $res['score'], $res['total_questions'], $random_date]);
        echo "  - Added quiz attempt for user ID " . $res['user_id'] . " on quiz ID " . $res['quiz_id'] . " (Score: " . $res['score'] . "/" . $res['total_questions'] . ", Date: " . $random_date . ")\n";
    }

    echo "\n<span class='success'>[Success] Database seeding finished successfully!</span>\n";

} catch (PDOException $e) {
    echo "\n<span class='error'>[Error] Database connection or query failed: " . $e->getMessage() . "</span>\n";
}

echo "</div>
    <p>Database has been reset and fully loaded with the requested users, topics, quizzes, and result logs.</p>
    <h3>Seeded Accounts:</h3>
    <ul>
        <li><strong>Admin:</strong> meet_monani / admin123</li>
        <li><strong>Teacher:</strong> rahul / teacher123</li>
        <li><strong>Students:</strong> bhavya / student123, sumit / student123, kirtan / student123, meet / student123</li>
    </ul>
    <a href='index.php' class='btn'>Go to Login</a>
</div>
</body>
</html>";
