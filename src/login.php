<?php
session_start();

$host = $_ENV['DB_HOST'] ?? 'db';
$user = $_ENV['DB_USER'] ?? 'labuser';
$pass = $_ENV['DB_PASS'] ?? 'labpass';
$db = $_ENV['DB_NAME'] ?? 'school_lab';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

function pick_exact_user(mysqli_result $res, string $typed): ?array {
    if (method_exists($res, 'fetch_all')) {
        $rows = $res->fetch_all(MYSQLI_ASSOC);
    } else {
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
    }
    foreach ($rows as $r) {
        if (isset($r['username']) && strcasecmp($r['username'], $typed) === 0) {
            return $r;
        }
    }
    return null;
}

function redirect_by_role_and_set_session(array $u): void {
    $_SESSION['user'] = $u;
    unset($_SESSION['otp_verified']);

    if (($u['role'] ?? null) === 'admin') {
        header('Location: otp.php'); exit;
    }
    if (($u['role'] ?? null) === 'student') {
        $sid = $u['student_sid'] ?? ($u['id'] ?? '');
        header('Location: profile.php?sid=' . $sid); exit;
    }
    header('Location: index.php'); exit;
}


if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    usleep(150000);

    $sql  = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $res  = $conn->query($sql);

    if (!$res || $res->num_rows === 0) {
        $error = 'Invalid credentials';
    } else {
        $picked = pick_exact_user($res, $username);

        if (!$picked) {
            $error = 'Invalid credentials';
        } else {
            redirect_by_role_and_set_session($picked);
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ban Nong Ngu Hao University - Student Portal Login</title>
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
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="academics.php">Academics</a></li>
                        <li><a href="news.php">News</a></li>
                        <li><a href="events.php">Events</a></li>
                        <!-- <li><a href="admissions.php">Admissions</a></li> -->
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="login.php">Portal</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card" style="max-width: 500px; margin: 2rem auto;">
                <h2>Student Portal Login</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn mt-4">Login</button>
                </form>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                    <h4>Demo Accounts:</h4>
                    <p><strong>Student:</strong> student0001 / pass1</p>
                    <!-- <p><strong>Admin:</strong> admin / admin123</p> -->
                </div>
                
                <div style="margin-top: 1rem;">
                    <a class="active" href="register.php">Register</a> |
                    <a href="search.php">Teacher Directory</a> |
                    <a href="student.php">Course Search</a> |
                    <a href="upload.php">File Upload</a>
                </div>
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
