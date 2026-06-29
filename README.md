# InfoQuiz 🧠✨

InfoQuiz is a modern, responsive, and secure web-based quiz and interactive learning platform. Built with a clean PHP backend and stylized with a sleek modern dark-mode aesthetic, it allows administrators, teachers, and students to seamlessly interact, share learning articles, and assess knowledge.

---

## 🚀 Key Features

* **Multiple User Roles**:
  * 👑 **Admin**: Full control over users, quizzes, and learning materials.
  * 🎓 **Teacher**: Create and manage quizzes, questions, and learning articles.
  * 👤 **Student**: Learn from published articles, take interactive quizzes, and track performance scores.
* **Modern Aesthetic & Responsive Design**: Custom dark-themed layout, smooth micro-interactions, responsive grids, and visual statistics.
* **Secured Database Layer**: SQL Queries fully secured against SQL Injection (SQLi) vulnerabilities using **PDO Prepared Statements**.
* **Clean Architecture**: Modular structure with reusable headers, navigation components, and footer configurations.

---

## 🛠️ Tech Stack

* **Backend**: PHP (OOP & PDO)
* **Database**: MySQL
* **Frontend**: HTML5, Vanilla CSS, FontAwesome Icons, Google Fonts (Inter / Outfit)
* **Environment**: Local XAMPP/WAMP or any standard Apache web server.

---

## 💻 Installation & Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Meet-024/Info-Quiz.git
   ```
2. **Move to Web Directory**: Copy or clone the project folder into your server directory (e.g., `htdocs` for XAMPP).
3. **Database Configuration**:
   * Create a MySQL database named `info_quiz_db`.
   * Import the database structure and initial seed data by running the setup or executing the sql statements from `seed.php`.
   * Configure your database host, username, and password in [config/db.php](file:///config/db.php).
4. **Run Application**:
   * Start Apache and MySQL services (e.g., via XAMPP Control Panel).
   * Open your browser and navigate to `http://localhost/InfoQuiz/`.
