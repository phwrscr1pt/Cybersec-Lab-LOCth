<?php
session_start();

$host = $_ENV['DB_HOST'] ?? 'db';
$user = $_ENV['DB_USER'] ?? 'labuser';
$pass = $_ENV['DB_PASS'] ?? 'labpass';
$db   = $_ENV['DB_NAME'] ?? 'school_lab';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset('utf8mb4');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // จงใจบาง: ไม่มี prepared statements เพื่อคง SQLi
    $username   = $_POST['username']   ?? '';
    $password   = $_POST['password']   ?? '';
    $student_id = $_POST['student_id'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name  = $_POST['last_name']  ?? '';
    $class      = $_POST['class']      ?? '';
    $email      = $_POST['email']      ?? '';

    if ($username && $password && $student_id && $first_name && $last_name && $class && $email) {
        try {
            // เช็คซ้ำแบบง่าย (ก็ยัง SQLi ได้อยู่)
            $dupSid  = $conn->query("SELECT 1 FROM students WHERE student_id='$student_id' LIMIT 1");
            if ($dupSid && $dupSid->num_rows) {
                $error = "Student ID already exists.";
            }
            $dupUser = $conn->query("SELECT 1 FROM users WHERE username='$username' LIMIT 1");
            if (!$error && $dupUser && $dupUser->num_rows) {
                $error = "Username already exists.";
            }

            if (!$error) {
                $conn->begin_transaction();
                $conn->query("INSERT INTO students (student_id, first_name, last_name, class, email)
                              VALUES ('$student_id','$first_name','$last_name','$class','$email')");
                $conn->query("INSERT INTO users (username, password, role, student_sid)
                              VALUES ('$username','$password','student','$student_id')");
                $conn->commit();

                $_SESSION['user'] = [
                    'username'    => $username,
                    'role'        => 'student',
                    'student_sid' => $student_id
                ];
                header('Location: profile.php?sid=' . urlencode($student_id)); // IDOR flow
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            if ($conn->errno) { $conn->rollback(); }
            $error = 'Registration failed: ' . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ban Nong Ngu Hao University - Student Registration</title>
    <link rel="icon" href="assets/img/logo.svg">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modern-school.css">
</head>
<body>
<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <h1>Ban Nong Ngu Hao University</h1>
                <p class="tagline">Student Portal</p>
            </div>
            <nav>
                <ul class="menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="news.php">News</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="academics.php">Academics</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="login.php">Portal</a></li>
                    <li><a class="active" href="register.php">Register</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<main>
    <div class="container">
        <div class="card" style="max-width: 640px; margin: 2rem auto;">
            <h2>Create your student account</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <div class="grid two">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="student0001">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="text" name="password" placeholder="simple pass for lab">
                    </div>
                </div>

                <h3 style="margin-top:1rem;">Profile (matches profile.php)</h3>

                <div class="grid two">
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" placeholder="0001">
                    </div>
                    <div class="form-group">
                        <label>Class</label>
                        <input type="text" name="class" placeholder="CS-101">
                    </div>
                </div>

                <div class="grid two">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" placeholder="Alice">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" placeholder="Smith">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="alice.smith@school.edu">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Register</button>

                <p class="muted" style="margin-top:1rem;">
                    This page is intentionally vulnerable (no prepared statements, minimal validation) to demonstrate SQLi and IDOR.
                </p>

                <div style="margin-top: 1rem;">
                    <a href="login.php">Already have an account? Login</a>
                </div>
            </form>
        </div>
    </div>
</main>

<footer>
    <div class="container">
        <p>&copy; 2025 Ban Nong Ngu Hao University. For education only. Do not deploy publicly.</p>
    </div>
</footer>
<script src="assets/js/ui.js"></script>
</body>
</html>
